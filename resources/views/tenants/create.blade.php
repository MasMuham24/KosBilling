@extends('layouts.app')

@section('title', 'Tambah Penyewa — KosBilling')

@section('content')
    <div class="max-w-2xl mx-auto space-y-4">
        
        <div>
            <a href="{{ route('tenants.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-900 transition">
                <span>&larr;</span> Kembali ke Daftar Penyewa
            </a>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg p-6 sm:p-8 shadow-sm">
            <div class="pb-5 border-b border-slate-200 mb-6">
                <h1 class="text-lg font-semibold text-slate-900">Tambah Penyewa Baru</h1>
                <p class="text-xs text-slate-500 mt-1">Daftarkan penghuni baru dan hubungkan ke kamar yang berstatus kosong.</p>
            </div>

            @if ($availableRooms->isEmpty())
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 mb-6">
                    <p class="text-sm font-medium text-amber-900">Tidak ada kamar kosong yang tersedia saat ini 🚪</p>
                    <p class="text-xs text-amber-700 mt-1">Semua kamar sudah terisi atau belum ada kamar yang dibuat. Tambahkan kamar baru terlebih dahulu untuk mendaftarkan penyewa.</p>
                    <div class="mt-3">
                        <a href="{{ route('rooms.create') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-teal-700 hover:bg-teal-800 rounded-md">
                            + Tambah Kamar Baru &rarr;
                        </a>
                    </div>
                </div>
            @endif

            <form action="{{ route('tenants.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Pilih Kamar yang Ditempati <span class="text-red-500">*</span>
                    </label>
                    <select name="room_id" required autofocus
                        class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition">
                        @if ($availableRooms->isEmpty())
                            <option value="">-- Tidak ada kamar kosong --</option>
                        @else
                            <option value="">-- Pilih Kamar Kosong --</option>
                            @foreach ($availableRooms as $room)
                                <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                    Kamar {{ $room->room_number }} — {{ $room->property->name }} (Rp {{ number_format($room->monthly_price, 0, ',', '.') }}/bln)
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('room_id') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Nama Lengkap Penyewa <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Rian Pratama" required
                        class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition">
                    @error('name') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Nomor WhatsApp Penghuni <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" name="whatsapp_number" value="{{ old('whatsapp_number') }}" placeholder="Contoh: 081234567890" required
                        class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition font-mono">
                    <p class="text-[11px] text-slate-500 mt-1">Nomor ini akan digunakan sistem untuk mengirimkan pengingat tagihan dan tautan QRIS otomatis.</p>
                    @error('whatsapp_number') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                            Tanggal Masuk (Check-in) <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="checkin_in_date" value="{{ old('checkin_in_date', date('Y-m-d')) }}" required
                            class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition font-mono">
                        @error('checkin_in_date') 
                            <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                            Tanggal Jatuh Tempo (1 - 31) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="due_date_day" value="{{ old('due_date_day', 10) }}" min="1" max="31" placeholder="Contoh: 10" required
                            class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition font-mono">
                        <p class="text-[11px] text-slate-500 mt-1">Hari dalam sebulan (mis. tgl 1, 5, atau 10).</p>
                        @error('due_date_day') 
                            <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <a href="{{ route('tenants.index') }}"
                        class="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 transition">
                        Batal
                    </a>
                    <button type="submit" {{ $availableRooms->isEmpty() ? 'disabled' : '' }}
                        class="px-4 py-2 bg-teal-700 hover:bg-teal-800 disabled:bg-slate-300 disabled:cursor-not-allowed text-white rounded-md text-sm font-semibold shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-teal-600 transition">
                        Simpan Penyewa
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

