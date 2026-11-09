<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenant;
use App\Services\MidtransService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userProperties = Property::where('user_id', Auth::id())->pluck('id');
        $query = Bill::whereHas('room', function ($q) use ($userProperties) {
            $q->whereIn('property_id', $userProperties);
        })->with(['tenant', 'room.property', 'payment']);
        if ($request->filled('month')) {
            $query->where('bill_month', $request->month);
        }
        $bills = $query->latest()->get();
        return view('bills.index', compact('bills'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $userProperties = Property::where('user_id', Auth::id())->pluck('id');
        $tenants = Tenant::where('status', 'active')->whereHas('room', function ($q) use ($userProperties) {
                $q->whereIn('property_id', $userProperties);
            })->with(['room.property'])->get();
        return view('bills.create', compact('tenants'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $userProperties = Property::where('user_id', Auth::id())->pluck('id');
        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'bill_month' => ['required', 'date_format:Y-m'],
            'additional_charge' => ['nullable', 'numeric', 'min:0'],
        ]);
        $tenant = Tenant::where('status', 'active')->whereHas('room', function ($q) use ($userProperties) {
                $q->whereIn('property_id', $userProperties);
            })->with('room')->findOrFail($validated['tenant_id']);
        $exists = Bill::where('tenant_id', $tenant->id)->where('bill_month', $validated['bill_month'])->exists();
        if ($exists) {
            return back()->withInput()->with('error', 'Tagihan untuk penyewa dan periode ini sudah ada.');
        }
        $additionalCharge = $validated['additional_charge'] ?? 0;
        $baseAmount = $tenant->room->monthly_price;
        $totalAmount = $baseAmount + $additionalCharge;
        $dueDate = Carbon::createFromFormat(
            'Y-m-d',
            "{$validated['bill_month']}-".str_pad($tenant->due_date_day, 2, '0', STR_PAD_LEFT)
        );
        Bill::create([
            'tenant_id' => $tenant->id,
            'room_id' => $tenant->room_id,
            'bill_month' => $validated['bill_month'],
            'base_amount' => $baseAmount,
            'additional_charge' => $additionalCharge,
            'total_amount' => $totalAmount,
            'status' => 'unpaid',
            'due_date' => $dueDate,
        ]);
        return redirect()->route('bills.index')->with('success', 'Tagihan manual berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Bill $bill, MidtransService $midtransService)
    {
        if (Auth::check() && $bill->room->property->user_id !== Auth::id()) {
            abort(403);
        }
        $bill->load(['tenant', 'room.property', 'payment']);
        $snapToken = null;
        if ($bill->status !== 'paid') {
            try {
                $snapToken = $midtransService->createSnapToken($bill);
            } catch (\Exception $e) {
            }
        }
        return view('bills.invoice', compact('bill', 'snapToken'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bill $bill)
    {
        if ($bill->room->property->user_id !== Auth::id()) {
            abort(403);
        }
        return view('bills.edit', compact('bill'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bill $bill)
    {
        if ($bill->room->property->user_id !== Auth::id()) {
            abort(403);
        }
        $validated = $request->validate([
            'additional_charge' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:unpaid,pending_verification,paid,overdue'],
        ]);
        $totalAmount = $bill->base_amount + $validated['additional_charge'];
        $resetToken = ($totalAmount != $bill->total_amount) ? null : $bill->snap_token;
        $bill->update([
            'additional_charge' => $validated['additional_charge'],
            'total_amount' => $totalAmount,
            'status' => $validated['status'],
            'snap_token' => $resetToken,
        ]);
        return redirect()->route('bills.index')->with('success', 'Tagihan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bill $bill)
    {
        if ($bill->room->property->user_id !== Auth::id()) {
            abort(403);
        }
        $bill->delete();
        return redirect()->route('bills.index')->with('success', 'Tagihan berhasil dihapus.');
    }

    /**
     * Custom Action: Generate tagihan massal otomatis untuk periode bulan tertentu.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'bill_month' => ['required', 'date_format:Y-m'],
        ]);
        $month = $request->bill_month;
        $userProperties = Property::where('user_id', Auth::id())->pluck('id');

        $activeTenants = Tenant::where('status', 'active')->whereHas('room', function ($q) use ($userProperties) {
                $q->whereIn('property_id', $userProperties);
            })->with('room')->get();
        $createdCount = 0;
        foreach ($activeTenants as $tenant) {
            $exists = Bill::where('tenant_id', $tenant->id)->where('bill_month', $month)->exists();
            if (! $exists) {
                $dueDate = Carbon::createFromFormat('Y-m-d',"{$month}-".str_pad($tenant->due_date_day, 2, '0', STR_PAD_LEFT));
                Bill::create([
                    'tenant_id' => $tenant->id,
                    'room_id' => $tenant->room_id,
                    'bill_month' => $month,
                    'base_amount' => $tenant->room->monthly_price,
                    'additional_charge' => 0,
                    'total_amount' => $tenant->room->monthly_price,
                    'status' => 'unpaid',
                    'due_date' => $dueDate,
                ]);
                $createdCount++;
            }
        }
        return redirect()->route('bills.index', ['month' => $month])->with('success', "Berhasil men-generate {$createdCount} tagihan baru untuk periode {$month}.");
    }

    /**
     * Callback dari frontend setelah proses Midtrans Snap selesai.
     */
    public function paymentSuccess(Request $request, Bill $bill)
    {
        $orderId = $request->input('order_id', 'BILL-' . $bill->id . '-' . time());
        $transactionId = $request->input('transaction_id');
        $paymentType = $request->input('payment_type', 'qris');
        $transactionStatus = $request->input('transaction_status', 'settlement');
        $grossAmount = $request->input('gross_amount', $bill->total_amount);

        DB::transaction(function () use ($bill, $orderId, $transactionId, $paymentType, $transactionStatus, $grossAmount, $request) {
            $isPaid = in_array($transactionStatus, ['settlement', 'capture']) || $transactionStatus === 'success';

            if ($isPaid) {
                $bill->update(['status' => 'paid']);
            } elseif ($transactionStatus === 'pending') {
                $bill->update(['status' => 'pending_verification']);
            }

            Payment::updateOrCreate(
                ['bill_id' => $bill->id],
                [
                    'order_id'           => $orderId,
                    'transaction_id'     => $transactionId,
                    'amount_paid'        => (float) $grossAmount,
                    'payment_type'       => $paymentType,
                    'transaction_status' => $transactionStatus,
                    'raw_response'       => json_encode($request->all()),
                    'verified_at'        => $isPaid ? now() : null,
                ]
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Status pembayaran berhasil dicatat.',
            'status'  => $bill->fresh()->status,
        ]);
    }

    /**
     * Tandai tagihan sebagai lunas secara manual oleh pemilik kos.
     */
    public function markAsPaid(Bill $bill)
    {
        if (Auth::check() && $bill->room->property->user_id !== Auth::id()) {
            abort(403);
        }

        DB::transaction(function () use ($bill) {
            $bill->update(['status' => 'paid']);

            Payment::updateOrCreate(
                ['bill_id' => $bill->id],
                [
                    'order_id'           => 'MANUAL-' . $bill->id . '-' . time(),
                    'transaction_id'     => null,
                    'amount_paid'        => $bill->total_amount,
                    'payment_type'       => 'manual_transfer',
                    'transaction_status' => 'settlement',
                    'raw_response'       => json_encode(['note' => 'Dikonfirmasi lunas secara manual oleh pemilik kos']),
                    'verified_at'        => now(),
                ]
            );
        });

        return back()->with('success', "Tagihan #INV-" . str_pad($bill->id, 5, '0', STR_PAD_LEFT) . " berhasil ditandai Lunas.");
    }
}
