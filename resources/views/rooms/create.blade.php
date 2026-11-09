@extends('layouts.app')

@section('title', 'Tambah Kamar — KosBilling')

@section('content')
    <div class="max-w-2xl mx-auto space-y-4">
        
        <div>
            <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-900 transition">
                <span>&larr;</span> Kembali ke Daftar Kamar
            </a>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg p-6 sm:p-8 shadow-sm">
            <div class="pb-5 border-b border-slate-200 mb-6">
                <h1 class="text-lg font-semibold text-slate-900">Tambah Kamar Baru</h1>
                <p class="text-xs text-slate-500 mt-1">Daftarkan nomor unit kamar dan tentukan tarif sewa bulanan.</p>
            </div>

            <form action="{{ route('rooms.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Pilih Properti Kos <span class="text-red-500">*</span>
                    </label>
                    <select name="property_id" required autofocus
                        class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition">
                        @foreach ($properties as $property)
                            <option value="{{ $property->id }}" {{ (old('property_id') ?? request('property_id')) == $property->id ? 'selected' : '' }}>
                                {{ $property->name }} ({{ $property->address }})
                            </option>
                        @endforeach
                    </select>
                    @error('property_id') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Nomor / Nama Kamar <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="room_number" value="{{ old('room_number') }}" placeholder="Contoh: 101, A-02, Lt.2-05" required
                        class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition font-mono">
                    @error('room_number') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Harga Sewa per Bulan (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-sm text-slate-400 font-mono pointer-events-none">Rp</span>
                        <input type="number" name="monthly_price" value="{{ old('monthly_price') }}" placeholder="1200000" min="0" required
                            class="w-full text-sm bg-white border border-slate-300 rounded-md pl-10 pr-3.5 py-2.5 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition font-mono">
                    </div>
                    @error('monthly_price') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Status Ketersediaan <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required
                        class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition">
                        <option value="available" {{ old('status') === 'available' ? 'selected' : '' }}>
                            Kosong (Available — Siap Dihuni)
                        </option>
                        <option value="occupied" {{ old('status') === 'occupied' ? 'selected' : '' }}>
                            Terisi (Occupied — Sudah Ada Penghuni)
                        </option>
                    </select>
                    @error('status') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <a href="{{ route('rooms.index') }}"
                        class="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white rounded-md text-sm font-semibold shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-teal-600 transition">
                        Simpan Kamar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
