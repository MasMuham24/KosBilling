@extends('layouts.app')

@section('title', 'Edit Tagihan — KosBilling')

@section('content')
    @php
        $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    @endphp

    <div class="max-w-2xl mx-auto space-y-4">
        
        <div>
            <a href="{{ route('bills.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-900 transition">
                <span>&larr;</span> Kembali ke Daftar Tagihan
            </a>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg p-6 sm:p-8 shadow-sm">
            <div class="pb-5 border-b border-slate-200 mb-6">
                <h1 class="text-lg font-semibold text-slate-900">Edit Tagihan #INV-{{ str_pad($bill->id, 5, '0', STR_PAD_LEFT) }}</h1>
                <p class="text-xs text-slate-500 mt-1">Perbarui biaya tambahan atau status pembayaran tagihan sewa.</p>
            </div>

            {{-- Info Ringkasan Penyewa --}}
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-6 grid grid-cols-2 gap-3 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">Nama Penyewa:</span>
                    <span class="font-semibold text-slate-900">{{ $bill->tenant->name }}</span>
                    <span class="text-slate-500 font-mono block">Kamar {{ $bill->room->room_number }} · {{ $bill->room->property->name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Periode & Jatuh Tempo:</span>
                    <span class="font-mono text-slate-900 font-semibold">Bulan {{ $bill->bill_month }}</span>
                    <span class="text-slate-500 font-mono block">Jatuh tempo: {{ \Carbon\Carbon::parse($bill->due_date)->format('d M Y') }}</span>
                </div>
            </div>

            <form action="{{ route('bills.update', $bill) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Tarif Sewa Pokok (Sesuai Kamar)
                    </label>
                    <input type="text" value="{{ $rp($bill->base_amount) }}" disabled
                        class="w-full text-sm bg-slate-100 border border-slate-300 rounded-md px-3.5 py-2.5 font-mono text-slate-600 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Biaya Tambahan / Utilitas (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-sm text-slate-400 font-mono pointer-events-none">Rp</span>
                        <input type="number" name="additional_charge" value="{{ old('additional_charge', (int)$bill->additional_charge) }}" min="0" required
                            class="w-full text-sm bg-white border border-slate-300 rounded-md pl-10 pr-3.5 py-2.5 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition font-mono">
                    </div>
                    @error('additional_charge') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Status Pembayaran <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required
                        class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition">
                        <option value="unpaid" {{ old('status', $bill->status) === 'unpaid' ? 'selected' : '' }}>
                            Belum Bayar (Unpaid)
                        </option>
                        <option value="pending_verification" {{ old('status', $bill->status) === 'pending_verification' ? 'selected' : '' }}>
                            Menunggu Verifikasi (Pending)
                        </option>
                        <option value="paid" {{ old('status', $bill->status) === 'paid' ? 'selected' : '' }}>
                            Lunas (Paid)
                        </option>
                        <option value="overdue" {{ old('status', $bill->status) === 'overdue' ? 'selected' : '' }}>
                            Menunggak / Terlambat (Overdue)
                        </option>
                    </select>
                    @error('status') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <a href="{{ route('bills.index') }}"
                        class="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white rounded-md text-sm font-semibold shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-teal-600 transition">
                        Perbarui Tagihan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

