<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    public function index()
    {
        $userProperties = Property::where('user_id', Auth::id())->pluck('id');
        $tenants = Tenant::whereHas('room', function ($query) use ($userProperties) {
            $query->whereIn('property_id', $userProperties);
        })->with(['room.property'])->latest()->get();
        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        $userProperties = Property::where('user_id', Auth::id())->pluck('id');
        $availableRooms = Room::whereIn('property_id', $userProperties)->where('status', 'available')->with('property')->get();
        return view('tenants.create', compact('availableRooms'));
    }

    public function store(Request $request)
    {
        $userProperties = Property::where('user_id', Auth::id())->pluck('id');
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'checkin_in_date' => ['required', 'date'],
            'due_date_day' => ['required', 'integer', 'between:1,31'],
        ]);
        $room = Room::whereIn('property_id', $userProperties)->findOrFail($validated['room_id']);
        DB::transaction(function () use ($validated, $room) {
            $validated['status'] = 'active';
            Tenant::create($validated);
            $room->update(['status' => 'occupied']);
        });
        return redirect()->route('tenants.index')->with('success', 'Penyewa baru berhasil ditambahkan.');
    }

    public function edit(Tenant $tenant)
    {
        if ($tenant->room->property->user_id !== Auth::id()) {
            abort(403);
        }
        $userProperties = Property::where('user_id', Auth::id())->pluck('id');
        $availableRooms = Room::whereIn('property_id', $userProperties)->where(function ($query) use ($tenant) {
                $query->where('status', 'available')->orWhere('id', $tenant->room_id);
            })->with('property')->get();
        return view('tenants.edit', compact('tenant', 'availableRooms'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        if ($tenant->room->property->user_id !== Auth::id()) {
            abort(403);
        }
        $userProperties = Property::where('user_id', Auth::id())->pluck('id');
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'checkin_in_date' => ['required', 'date'],
            'due_date_day' => ['required', 'integer', 'between:1,31'],
            'status' => ['required', 'in:active,checkout'],
        ]);
        $newRoom = Room::whereIn('property_id', $userProperties)->findOrFail($validated['room_id']);
        DB::transaction(function () use ($tenant, $newRoom, $validated) {
            $oldRoomId = $tenant->room_id;
            if ($oldRoomId != $newRoom->id) {
                Room::where('id', $oldRoomId)->update(['status' => 'available']);
                $newRoom->update(['status' => 'occupied']);
            }
            if ($validated['status'] === 'checkout') {
                $newRoom->update(['status' => 'available']);
            }
            $tenant->update($validated);
        });
        return redirect()->route('tenants.index')->with('success', 'Data penyewa berhasil diperbarui.');
    }

    public function destroy(Tenant $tenant)
    {
        if ($tenant->room->property->user_id !== Auth::id()) {
            abort(403);
        }
        DB::transaction(function () use ($tenant) {
            $tenant->room->update(['status' => 'available']);
            $tenant->delete();
        });
        return redirect()->route('tenants.index')->with('success', 'Data penyewa berhasil dihapus.');
    }
}
