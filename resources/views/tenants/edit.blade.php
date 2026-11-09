@extends('layouts.app')

@section('title', 'Edit Penyewa — KosBilling')

@section('content')
    <div class="max-w-2xl mx-auto space-y-4">

        <div>
            <a href="{{ route('tenants.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-900 transition">
                <span>&larr;</span> Kembali ke Daftar Penyewa
            </a>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg p-6 sm:p-8 shadow-sm">
            <div class="pb-5 border-b border-slate-200 mb-6">
                <h1 class="text-lg font-semibold text-slate-900">Edit Data Penyewa — {{ $tenant->name }}</h1>
                <p class="text-xs text-slate-500 mt-1">Perbarui nomor kamar, nomor WhatsApp, tanggal jatuh tempo, atau status checkout sewa.</p>
            </div>

            <form action="{{ route('tenants.update', $tenant) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Kamar yang Ditempati <span class="text-red-500">*</span>
                    </label>
                    <select name="room_id" required autofocus
                        class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition">
                        @foreach ($availableRooms as $room)
                            <option value="{{ $room->id }}" {{ old('room_id', $tenant->room_id) == $room->id ? 'selected' : '' }}>
                                Kamar {{ $room->room_number }} — {{ $room->property->name }} (Rp {{ number_format($room->monthly_price, 0, ',', '.') }}/bln)
                                @if ($room->id == $tenant->room_id)
                                    [Kamar Saat Ini]
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('room_id')
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Nama Lengkap Penyewa <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}" placeholder="Contoh: Rian Pratama" required
                        class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition">
                    @error('name')
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Nomor WhatsApp Penghuni <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" name="whatsapp_number" value="{{ old('whatsapp_number', $tenant->whatsapp_number) }}" placeholder="Contoh: 081234567890" required
                        class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition font-mono">
                    <p class="text-[11px] text-slate-500 mt-1">Digunakan untuk pengiriman notifikasi penagihan sewa otomatis.</p>
                    @error('whatsapp_number')
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                            Tanggal Masuk (Check-in) <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="checkin_in_date"
                            value="{{ old('checkin_in_date', $tenant->checkin_in_date ? \Carbon\Carbon::parse($tenant->checkin_in_date)->format('Y-m-d') : '') }}" required
                            class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition font-mono">
                        @error('checkin_in_date')
                            <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                            Tanggal Jatuh Tempo (1 - 31) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="due_date_day" value="{{ old('due_date_day', $tenant->due_date_day) }}" min="1" max="31" placeholder="Contoh: 10" required
                            class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition font-mono">
                        <p class="text-[11px] text-slate-500 mt-1">Hari dalam sebulan (mis. tgl 1, 5, atau 10).</p>
                        @error('due_date_day')
                            <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Status Keterisian <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required
                        class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition">
                        <option value="active" {{ old('status', $tenant->status) === 'active' ? 'selected' : '' }}>
                            Aktif (Sedang Menempati Kamar)
                        </option>
                        <option value="checkout" {{ old('status', $tenant->status) === 'checkout' ? 'selected' : '' }}>
                            Checkout (Selesai Sewa / Keluar)
                        </option>
                    </select>
                    <p class="text-[11px] text-slate-500 mt-1">Jika diubah ke "Checkout", status kamar terkait akan otomatis kembali menjadi "Kosong/Available".</p>
                    @error('status')
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <a href="{{ route('tenants.index') }}"
                        class="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white rounded-md text-sm font-semibold shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-teal-600 transition">
                        Perbarui Data Penyewa
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

