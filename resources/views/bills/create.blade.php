@extends('layouts.app')

@section('title', 'Buat Tagihan Manual — KosBilling')

@section('content')
    <div class="max-w-2xl mx-auto space-y-4">
        
        <div>
            <a href="{{ route('bills.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-900 transition">
                <span>&larr;</span> Kembali ke Daftar Tagihan
            </a>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg p-6 sm:p-8 shadow-sm">
            <div class="pb-5 border-b border-slate-200 mb-6">
                <h1 class="text-lg font-semibold text-slate-900">Buat Tagihan Manual</h1>
                <p class="text-xs text-slate-500 mt-1">Terbitkan invoice sewa secara manual untuk penyewa aktif tertentu.</p>
            </div>

            @if ($tenants->isEmpty())
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 mb-6">
                    <p class="text-sm font-medium text-amber-900">Belum ada penyewa aktif 👥</p>
                    <p class="text-xs text-amber-700 mt-1">Anda perlu memiliki penyewa aktif yang menempati kamar terlebih dahulu sebelum dapat menerbitkan tagihan.</p>
                    <div class="mt-3">
                        <a href="{{ route('tenants.create') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-teal-700 hover:bg-teal-800 rounded-md">
                            + Daftarkan Penyewa Sekarang &rarr;
                        </a>
                    </div>
                </div>
            @endif

            <form action="{{ route('bills.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Pilih Penghuni / Kamar <span class="text-red-500">*</span>
                    </label>
                    <select name="tenant_id" required autofocus
                        class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition">
                        @if ($tenants->isEmpty())
                            <option value="">-- Belum ada penyewa aktif --</option>
                        @else
                            <option value="">-- Pilih Penyewa --</option>
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                    {{ $tenant->name }} — Kamar {{ $tenant->room->room_number }} (Tarif Pokok: Rp {{ number_format($tenant->room->monthly_price, 0, ',', '.') }}/bln)
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('tenant_id') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Periode Bulan Tagihan <span class="text-red-500">*</span>
                    </label>
                    <input type="month" name="bill_month" value="{{ old('bill_month', date('Y-m')) }}" required
                        class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition font-mono">
                    <p class="text-[11px] text-slate-500 mt-1">Tanggal jatuh tempo tagihan akan otomatis dihitung berdasarkan tanggal jatuh tempo yang tercatat pada data penyewa.</p>
                    @error('bill_month') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Biaya Tambahan / Utilitas <span class="text-slate-400 font-normal lowercase">(opsional, misal: listrik / air)</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-sm text-slate-400 font-mono pointer-events-none">Rp</span>
                        <input type="number" name="additional_charge" value="{{ old('additional_charge', 0) }}" min="0" placeholder="0"
                            class="w-full text-sm bg-white border border-slate-300 rounded-md pl-10 pr-3.5 py-2.5 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition font-mono">
                    </div>
                    @error('additional_charge') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <a href="{{ route('bills.index') }}"
                        class="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 transition">
                        Batal
                    </a>
                    <button type="submit" {{ $tenants->isEmpty() ? 'disabled' : '' }}
                        class="px-4 py-2 bg-teal-700 hover:bg-teal-800 disabled:bg-slate-300 disabled:cursor-not-allowed text-white rounded-md text-sm font-semibold shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-teal-600 transition">
                        Simpan & Terbitkan Tagihan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

