@extends('layouts.app')

@section('title', 'Daftar Kamar — KosBilling')

@section('content')
    @php
        $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
        $totalCount     = $rooms->count();
        $occupiedCount  = $rooms->where('status', 'occupied')->count();
        $availableCount = $rooms->where('status', 'available')->count();
        $activePropId   = request('property_id');
    @endphp

    {{-- Header & Primary Action --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">Daftar Kamar Kos</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola unit kamar, ketersediaan, dan tarif sewa bulanan.</p>
        </div>
        <div class="flex items-center gap-2">
            @if ($properties->isNotEmpty())
                <a href="{{ route('rooms.create', $activePropId ? ['property_id' => $activePropId] : []) }}"
                    class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-white bg-teal-700 hover:bg-teal-800 rounded-md shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-teal-600">
                    + Tambah Kamar
                </a>
            @else
                <a href="{{ route('properties.create') }}"
                    class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-white bg-teal-700 hover:bg-teal-800 rounded-md shadow-sm">
                    + Buat Properti Dahulu
                </a>
            @endif
        </div>
    </div>

    {{-- Metric Summary Strip --}}
    <div class="grid sm:grid-cols-3 gap-px bg-slate-200 border border-slate-200 rounded-lg overflow-hidden">
        <div class="bg-white p-5">
            <p class="text-sm text-slate-500">Total Kamar</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 num">{{ $totalCount }} <span class="text-base font-normal text-slate-500">Unit</span></p>
            <p class="mt-1.5 text-xs text-slate-500">
                @if ($activePropId && $properties->firstWhere('id', $activePropId))
                    Gedung: {{ $properties->firstWhere('id', $activePropId)->name }}
                @else
                    Di seluruh gedung properti
                @endif
            </p>
        </div>

        <div class="bg-white p-5">
            <p class="text-sm text-slate-500">Kamar Terisi</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-700 num">{{ $occupiedCount }} <span class="text-base font-normal text-slate-500">Unit</span></p>
            <p class="mt-1.5 text-xs text-slate-500">
                {{ $totalCount ? round(($occupiedCount / $totalCount) * 100) : 0 }}% tingkat occupancy
            </p>
        </div>

        <div class="bg-white p-5">
            <p class="text-sm text-slate-500">Kamar Kosong (Tersedia)</p>
            <p class="mt-2 text-2xl font-semibold text-slate-700 num">{{ $availableCount }} <span class="text-base font-normal text-slate-500">Unit</span></p>
            <p class="mt-1.5 text-xs text-slate-500">Siap dipasarkan ke penyewa baru</p>
        </div>
    </div>

    {{-- Main Container --}}
    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
        
        {{-- Filter Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b border-slate-200">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Katalog Unit Kamar</h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar detail kamar per gedung properti.</p>
            </div>

            {{-- Property Filter Select --}}
            @if ($properties->count() > 1)
                <form action="{{ route('rooms.index') }}" method="GET" class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 whitespace-nowrap">Filter Gedung:</span>
                    <select name="property_id" onchange="this.form.submit()" 
                        class="text-sm text-slate-700 bg-white border border-slate-300 rounded-md py-1.5 pl-2.5 pr-8 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600">
                        <option value="">Semua Properti ({{ $properties->count() }})</option>
                        @foreach ($properties as $prop)
                            <option value="{{ $prop->id }}" {{ $activePropId == $prop->id ? 'selected' : '' }}>
                                {{ $prop->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>

        {{-- Desktop Table View --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-medium">Nomor Kamar</th>
                        <th scope="col" class="px-3 py-3 font-medium">Properti Kos</th>
                        <th scope="col" class="px-3 py-3 font-medium text-right">Harga Sewa / Bulan</th>
                        <th scope="col" class="px-3 py-3 font-medium">Penghuni</th>
                        <th scope="col" class="px-3 py-3 font-medium">Status</th>
                        <th scope="col" class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($rooms as $room)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-4 font-semibold text-slate-900 whitespace-nowrap">
                                <span class="font-mono bg-slate-100 text-slate-800 px-2 py-0.5 rounded text-xs">
                                    Kamar {{ $room->room_number }}
                                </span>
                            </td>
                            <td class="px-3 py-4 text-slate-600">
                                {{ $room->property->name ?? '-' }}
                            </td>
                            <td class="px-3 py-4 text-right font-medium text-slate-900 num whitespace-nowrap">
                                {{ $rp($room->monthly_price) }}<span class="text-slate-400 font-normal">/bln</span>
                            </td>
                            <td class="px-3 py-4">
                                @if ($room->activeTenant)
                                    <p class="font-medium text-slate-900">{{ $room->activeTenant->name }}</p>
                                    <p class="text-xs text-slate-500 num">{{ $room->activeTenant->whatsapp_number }}</p>
                                @elseif ($room->status === 'occupied')
                                    <span class="text-slate-600">Penghuni Aktif</span>
                                @else
                                    <span class="text-slate-400">Belum ada penghuni</span>
                                @endif
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                @if ($room->status === 'occupied')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600" aria-hidden="true"></span>
                                        Terisi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400" aria-hidden="true"></span>
                                        Kosong
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('rooms.edit', $room) }}" 
                                    class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-md transition">
                                    Edit
                                </a>
                                <form action="{{ route('rooms.destroy', $room) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Hapus Kamar {{ $room->room_number }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                        class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-red-600 bg-white border border-red-200 hover:bg-red-50 rounded-md transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                                <p class="font-medium text-slate-700">Belum ada kamar terdaftar</p>
                                <p class="text-xs text-slate-400 mt-1">Tambahkan unit kamar kos untuk mulai memantau sewa.</p>
                                @if ($properties->isNotEmpty())
                                    <a href="{{ route('rooms.create') }}" class="mt-4 inline-flex items-center px-3.5 py-2 text-xs font-medium text-white bg-teal-700 hover:bg-teal-800 rounded-md shadow-sm">
                                        + Tambah Kamar Sekarang
                                    </a>
                                @else
                                    <a href="{{ route('properties.create') }}" class="mt-4 inline-flex items-center px-3.5 py-2 text-xs font-medium text-white bg-teal-700 hover:bg-teal-800 rounded-md shadow-sm">
                                        + Buat Properti Terlebih Dahulu
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card List View --}}
        <ul class="md:hidden divide-y divide-slate-200">
            @forelse ($rooms as $room)
                <li class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="font-mono bg-slate-100 text-slate-800 px-2 py-0.5 rounded text-xs font-bold">
                                Kamar {{ $room->room_number }}
                            </span>
                            <p class="text-xs text-slate-500 mt-1">{{ $room->property->name ?? '-' }}</p>
                        </div>
                        @if ($room->status === 'occupied')
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600" aria-hidden="true"></span>
                                Terisi
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400" aria-hidden="true"></span>
                                Kosong
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Harga Sewa:</span>
                        <span class="font-semibold text-slate-900 num">{{ $rp($room->monthly_price) }}/bln</span>
                    </div>
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2 text-xs">
                        <a href="{{ route('rooms.edit', $room) }}" class="px-2.5 py-1 text-xs font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-md">
                            Edit
                        </a>
                        <form action="{{ route('rooms.destroy', $room) }}" method="POST" class="inline"
                            onsubmit="return confirm('Hapus Kamar {{ $room->room_number }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-2.5 py-1 text-xs font-medium text-red-600 bg-white border border-red-200 hover:bg-red-50 rounded-md">
                                Hapus
                            </button>
                        </form>
                    </div>
                </li>
            @empty
                <li class="p-8 text-center text-slate-500 text-sm">
                    Belum ada kamar terdaftar.
                </li>
            @endforelse
        </ul>

    </div>
@endsection
