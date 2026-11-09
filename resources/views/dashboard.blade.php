@extends('layouts.app')

@section('title', 'Dashboard — KosBilling')

@section('content')
    @php
        $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
        $period = now()->locale('id')->translatedFormat('F Y');
    @endphp

    {{-- Onboarding Banners --}}
    @if ($totalProperties === 0)
        <div class="rounded-xl border border-teal-200 bg-teal-50 p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-semibold text-teal-900">Selamat datang di KosBilling! 🏢</h2>
                <p class="text-sm text-teal-700 mt-1 max-w-2xl leading-relaxed">
                    Anda belum memiliki properti kos yang terdaftar. Tambahkan properti kos pertama Anda untuk mulai mengelola unit kamar, tarif sewa, dan pengingat tagihan.
                </p>
            </div>
            <a href="{{ route('properties.create') }}" class="shrink-0 px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                + Tambah Properti Pertama
            </a>
        </div>
    @elseif ($totalRooms === 0)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-semibold text-amber-900">Properti Terdaftar, Belum Ada Kamar 🚪</h2>
                <p class="text-sm text-amber-700 mt-1 max-w-2xl leading-relaxed">
                    Anda memiliki {{ $totalProperties }} gedung properti. Tambahkan nomor kamar dan harga sewa bulanan agar ketersediaan kamar dapat dipantau.
                </p>
            </div>
            <a href="{{ route('rooms.create') }}" class="shrink-0 px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                + Tambah Kamar Sekarang
            </a>
        </div>
    @elseif ($totalTenants === 0)
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-semibold text-blue-900">Unit Kamar Tersedia Siap Dihuni ✨</h2>
                <p class="text-sm text-blue-700 mt-1 max-w-2xl leading-relaxed">
                    Seluruh kamar saat ini berstatus kosong. Tambahkan data penyewa baru dan kaitkan dengan kamar untuk mulai mencatat sewa aktif.
                </p>
            </div>
            <a href="{{ route('tenants.create') }}" class="shrink-0 px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                + Daftarkan Penghuni Baru
            </a>
        </div>
    @endif

    {{-- Title & Action Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">Ringkasan Operasional Kos</h1>
            <p class="mt-1 text-sm text-slate-500">
                Periode {{ $period }} · 
                <strong class="text-slate-700">{{ $totalProperties }} gedung</strong> kos · 
                <strong class="text-slate-700">{{ $totalRooms }} unit</strong> kamar
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('properties.create') }}"
                class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-md shadow-sm transition">
                + Properti
            </a>
            <a href="{{ route('rooms.create') }}"
                class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-md shadow-sm transition">
                + Kamar
            </a>
            <a href="{{ route('tenants.create') }}"
                class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-md shadow-sm transition">
                + Penghuni
            </a>
            <a href="{{ route('bills.create') }}"
                class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-white bg-teal-700 hover:bg-teal-800 rounded-md shadow-sm transition">
                + Tagihan
            </a>
        </div>
    </div>

    {{-- 4 Dynamic Metric Cards --}}
    <section aria-label="Ringkasan Metrik" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-px bg-slate-200 border border-slate-200 rounded-lg overflow-hidden">
        
        {{-- Card 1: Okupansi --}}
        <div class="bg-white p-5">
            <div class="flex justify-between items-start">
                <p class="text-sm text-slate-500">Tingkat Okupansi</p>
                <a href="{{ route('rooms.index') }}" class="text-xs text-teal-700 hover:underline">Kelola Kamar &rarr;</a>
            </div>
            <p class="mt-2 text-2xl font-semibold text-slate-900 num">
                {{ $occupiedRooms }} <span class="text-base font-normal text-slate-500">/ {{ $totalRooms }} kamar</span>
            </p>
            <div class="mt-2.5 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full bg-teal-600 transition-all duration-500" style="width: {{ $occupancyRate }}%"></div>
            </div>
            <p class="mt-1.5 text-xs text-slate-500">{{ $occupancyRate }}% terisi · {{ $availableRooms }} kamar kosong</p>
        </div>

        {{-- Card 2: Pemasukan Berjalan --}}
        <div class="bg-white p-5">
            <p class="text-sm text-slate-500">Pemasukan Berjalan</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 num text-emerald-700">{{ $rp($monthlyRevenue) }}</p>
            <p class="mt-1.5 text-xs text-slate-500 num">
                Dari total potensi {{ $rp($potentialRevenue) }} /bulan
            </p>
        </div>

        {{-- Card 3: Properti --}}
        <div class="bg-white p-5">
            <div class="flex justify-between items-start">
                <p class="text-sm text-slate-500">Gedung Properti</p>
                <a href="{{ route('properties.index') }}" class="text-xs text-teal-700 hover:underline">Daftar Properti &rarr;</a>
            </div>
            <p class="mt-2 text-2xl font-semibold text-slate-900 num">
                {{ $totalProperties }} <span class="text-base font-normal text-slate-500">Gedung</span>
            </p>
            <p class="mt-1.5 text-xs text-slate-500 truncate">
                {{ $properties->first()?->name ?? 'Belum ada properti' }}
            </p>
        </div>

        {{-- Card 4: Penghuni Aktif --}}
        <div class="bg-white p-5">
            <div class="flex justify-between items-start">
                <p class="text-sm text-slate-500">Penghuni Aktif</p>
                <a href="{{ route('tenants.index') }}" class="text-xs text-teal-700 hover:underline">Data Penghuni &rarr;</a>
            </div>
            <p class="mt-2 text-2xl font-semibold text-slate-900 num">
                {{ $totalTenants }} <span class="text-base font-normal text-slate-500">Orang</span>
            </p>
            <p class="mt-1.5 text-xs text-slate-500">
                Terdata aktif di kamar kos
            </p>
        </div>

    </section>

    {{-- Dynamic Properties Quick Section --}}
    @if ($properties->isNotEmpty())
        <section class="bg-white border border-slate-200 rounded-lg p-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Gedung Properti Anda</h2>
                    <p class="text-xs text-slate-500">Ringkasan gedung kos dan kapasitas unit kamar.</p>
                </div>
                <a href="{{ route('properties.index') }}" class="text-xs font-medium text-teal-700 hover:underline">Kelola Semua &rarr;</a>
            </div>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($properties as $prop)
                    <div class="border border-slate-200 rounded-lg p-4 bg-slate-50 hover:bg-white hover:border-slate-300 transition">
                        <div class="flex items-start justify-between">
                            <h3 class="font-semibold text-slate-900 text-sm">{{ $prop->name }}</h3>
                            <span class="text-xs px-2 py-0.5 rounded bg-slate-200 text-slate-700 font-mono">
                                {{ $prop->rooms_count }} Kamar
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-1">{{ $prop->address }}</p>
                        <div class="mt-3 pt-3 border-t border-slate-200 flex items-center justify-between text-xs">
                            <a href="{{ route('rooms.index', ['property_id' => $prop->id]) }}" class="text-teal-700 font-medium hover:underline">
                                Lihat Unit Kamar &rarr;
                            </a>
                            <div class="space-x-2">
                                <a href="{{ route('rooms.create', ['property_id' => $prop->id]) }}" class="text-slate-600 hover:text-slate-900 font-medium">
                                    + Kamar
                                </a>
                                <a href="{{ route('properties.edit', $prop) }}" class="text-blue-600 hover:underline">
                                    Edit
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Dynamic Room Matrix & Table --}}
    <section class="bg-white border border-slate-200 rounded-lg" aria-labelledby="rooms-title">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 px-5 py-4 border-b border-slate-200">
            <div>
                <h2 id="rooms-title" class="text-base font-semibold text-slate-900">Status Ketersediaan Kamar</h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar seluruh unit kamar, tarif bulanan, dan penghuni aktif.</p>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="no-scrollbar flex sm:inline-flex max-w-full rounded-md border border-slate-300 bg-white p-0.5 text-sm overflow-x-auto" role="group" aria-label="Filter status">
                    <button type="button" data-filter="all" aria-pressed="true"
                        class="filter-btn px-2.5 py-1 rounded whitespace-nowrap text-slate-600 hover:text-slate-900 aria-pressed:bg-slate-900 aria-pressed:text-white transition">
                        Semua <span class="num opacity-60">{{ $totalRooms }}</span>
                    </button>
                    <button type="button" data-filter="occupied" aria-pressed="false"
                        class="filter-btn px-2.5 py-1 rounded whitespace-nowrap text-slate-600 hover:text-slate-900 aria-pressed:bg-slate-900 aria-pressed:text-white transition">
                        Terisi <span class="num opacity-60">{{ $occupiedRooms }}</span>
                    </button>
                    <button type="button" data-filter="available" aria-pressed="false"
                        class="filter-btn px-2.5 py-1 rounded whitespace-nowrap text-slate-600 hover:text-slate-900 aria-pressed:bg-slate-900 aria-pressed:text-white transition">
                        Kosong <span class="num opacity-60">{{ $availableRooms }}</span>
                    </button>
                </div>

                <label class="relative block">
                    <span class="sr-only">Cari kamar atau penghuni</span>
                    <input id="roomSearch" type="search" placeholder="Cari kamar atau penghuni..."
                        class="w-full sm:w-56 text-sm bg-white border border-slate-300 rounded-md px-3 py-1.5 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600">
                </label>
            </div>
        </div>

        {{-- Desktop Table View --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-medium">Nomor Kamar</th>
                        <th scope="col" class="px-3 py-3 font-medium">Properti Kos</th>
                        <th scope="col" class="px-3 py-3 font-medium text-right">Harga Sewa</th>
                        <th scope="col" class="px-3 py-3 font-medium">Penghuni Aktif</th>
                        <th scope="col" class="px-3 py-3 font-medium">Status</th>
                        <th scope="col" class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200" id="roomRows">
                    @forelse ($rooms as $room)
                        @php
                            $tenant = $room->activeTenant;
                            $searchQuery = strtolower($room->room_number . ' ' . ($room->property->name ?? '') . ' ' . ($tenant?->name ?? ''));
                            $cleanPhone = $tenant ? preg_replace('/[^0-9]/', '', $tenant->whatsapp_number) : '';
                            if (str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '62' . substr($cleanPhone, 1);
                            }
                            $waMsg = rawurlencode("Halo {$tenant?->name} (Kamar {$room->room_number}), ini pesan dari pengelola kos.");
                        @endphp
                        <tr class="room-row hover:bg-slate-50 transition" data-status="{{ $room->status }}" data-search="{{ $searchQuery }}">
                            <td class="px-5 py-4 font-semibold text-slate-900 whitespace-nowrap">
                                <span class="font-mono bg-slate-100 text-slate-800 px-2 py-0.5 rounded text-xs font-bold">
                                    Kamar {{ $room->room_number }}
                                </span>
                            </td>
                            <td class="px-3 py-4 text-slate-600 whitespace-nowrap">
                                {{ $room->property->name ?? '-' }}
                            </td>
                            <td class="px-3 py-4 text-right font-medium text-slate-900 num whitespace-nowrap">
                                {{ $rp($room->monthly_price) }}<span class="text-slate-400 font-normal">/bln</span>
                            </td>
                            <td class="px-3 py-4">
                                @if ($tenant)
                                    <div class="flex items-center gap-2">
                                        <div>
                                            <p class="font-medium text-slate-900 text-xs">{{ $tenant->name }}</p>
                                            <p class="text-[11px] text-slate-400 font-mono">{{ $tenant->whatsapp_number }}</p>
                                        </div>
                                        <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMsg }}" target="_blank"
                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition">
                                            WA &rarr;
                                        </a>
                                    </div>
                                @elseif ($room->status === 'occupied')
                                    <span class="text-slate-600 text-xs">Penghuni Aktif</span>
                                @else
                                    <span class="text-slate-400 text-xs">Belum ada penghuni</span>
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
                                <p class="font-medium text-slate-700">Belum ada data kamar</p>
                                <p class="text-xs text-slate-400 mt-1">Tambahkan unit kamar untuk mulai memantau okupansi kos Anda.</p>
                                @if ($totalProperties > 0)
                                    <a href="{{ route('rooms.create') }}" class="mt-3 inline-flex items-center px-3.5 py-2 text-xs font-medium text-white bg-teal-700 hover:bg-teal-800 rounded-md shadow-sm">
                                        + Tambah Kamar Baru
                                    </a>
                                @else
                                    <a href="{{ route('properties.create') }}" class="mt-3 inline-flex items-center px-3.5 py-2 text-xs font-medium text-white bg-teal-700 hover:bg-teal-800 rounded-md shadow-sm">
                                        + Tambah Properti Dahulu
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    <tr id="emptyRow" class="hidden">
                        <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-500">Tidak ada kamar yang cocok dengan pencarian / filter ini.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Mobile Card List View --}}
        <ul class="md:hidden divide-y divide-slate-200">
            @forelse ($rooms as $room)
                @php
                    $tenant = $room->activeTenant;
                    $searchQuery = strtolower($room->room_number . ' ' . ($room->property->name ?? '') . ' ' . ($tenant?->name ?? ''));
                @endphp
                <li class="room-item p-4 space-y-3" data-status="{{ $room->status }}" data-search="{{ $searchQuery }}">
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
                    @if ($tenant)
                        <div class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded border border-slate-200">
                            <span class="text-slate-400 block text-[11px]">Penghuni Aktif:</span>
                            <span class="font-medium text-slate-900">{{ $tenant->name }}</span>
                            <span class="text-slate-500 font-mono">({{ $tenant->whatsapp_number }})</span>
                        </div>
                    @endif
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
            <li id="emptyItem" class="hidden p-6 text-center text-sm text-slate-500">Tidak ada kamar yang cocok.</li>
        </ul>
    </section>

    {{-- Bottom Grid: Penghuni Aktif Terbaru & Status Sistem --}}
    <div class="grid lg:grid-cols-3 gap-6">

        {{-- Left: Penghuni Aktif --}}
        <section class="lg:col-span-2 bg-white border border-slate-200 rounded-lg p-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Daftar Penghuni Kos Aktif</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Penghuni yang saat ini menempati unit kamar Anda.</p>
                </div>
                <a href="{{ route('tenants.index') }}" class="text-xs font-medium text-teal-700 hover:underline">Kelola Semua &rarr;</a>
            </div>

            @if ($activeTenants->isNotEmpty())
                <div class="mt-3 divide-y divide-slate-100">
                    @foreach ($activeTenants->take(5) as $t)
                        @php
                            $cleanPhone = preg_replace('/[^0-9]/', '', $t->whatsapp_number);
                            if (str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '62' . substr($cleanPhone, 1);
                            }
                            $waMessage = rawurlencode("Halo {$t->name} (Kamar {$t->room->room_number}), ini pesan dari pengelola kos.");
                        @endphp
                        <div class="py-3 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center font-mono">
                                    {{ strtoupper(substr($t->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900 text-sm">{{ $t->name }}</p>
                                    <p class="text-slate-500 font-mono">Kamar {{ $t->room->room_number }} · {{ $t->room->property->name }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="hidden sm:inline font-mono text-slate-500">Jatuh tempo tgl {{ $t->due_date_day }}</span>
                                <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMessage }}" target="_blank"
                                    class="inline-flex items-center px-2 py-1 rounded bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 font-medium transition">
                                    Hubungi WA &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-slate-500 text-xs">
                    <p>Belum ada penghuni aktif yang menempati kamar saat ini.</p>
                    <a href="{{ route('tenants.create') }}" class="mt-2 inline-flex items-center text-teal-700 font-medium hover:underline">
                        + Tambah Penghuni Baru &rarr;
                    </a>
                </div>
            @endif
        </section>

        {{-- Right: Status Integrasi Sistem --}}
        <section class="bg-white border border-slate-200 rounded-lg p-5 space-y-4">
            <h2 class="text-base font-semibold text-slate-900">Status Sistem & Gateway</h2>
            
            <dl class="divide-y divide-slate-200 text-sm">
                <div class="flex items-center justify-between py-3">
                    <div>
                        <dt class="font-medium text-slate-900 text-xs">WhatsApp Gateway</dt>
                        <dd class="text-[11px] text-slate-500">Pengingat tagihan otomatis</dd>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Terhubung
                    </span>
                </div>
                <div class="flex items-center justify-between py-3">
                    <div>
                        <dt class="font-medium text-slate-900 text-xs">Midtrans QRIS / VA</dt>
                        <dd class="text-[11px] text-slate-500">Pembayaran sewa instan</dd>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Aktif
                    </span>
                </div>
            </dl>

            <div class="pt-2 text-xs text-slate-500 leading-relaxed bg-slate-50 p-3 rounded-md border border-slate-200">
                Penyewa dapat menerima kwitansi dan tautan pembayaran QRIS yang otomatis mengupdate status kamar setelah dibayarkan.
            </div>
        </section>

    </div>

    {{-- Filter Script --}}
    <script>
        const filterBtns = document.querySelectorAll('.filter-btn');
        const searchInput = document.getElementById('roomSearch');
        const rows = document.querySelectorAll('.room-row');
        const items = document.querySelectorAll('.room-item');
        const emptyRow = document.getElementById('emptyRow');
        const emptyItem = document.getElementById('emptyItem');

        let activeFilter = 'all';

        function applyFilter() {
            const query = (searchInput.value || '').trim().toLowerCase();
            let visibleCount = 0;

            function matches(el) {
                const status = el.dataset.status;
                const search = el.dataset.search || '';
                const matchFilter = activeFilter === 'all' || status === activeFilter;
                const matchSearch = !query || search.includes(query);
                return matchFilter && matchSearch;
            }

            rows.forEach(r => {
                const show = matches(r);
                r.classList.toggle('hidden', !show);
                if (show) visibleCount++;
            });

            items.forEach(it => {
                it.classList.toggle('hidden', !matches(it));
            });

            if (emptyRow) emptyRow.classList.toggle('hidden', visibleCount > 0);
            if (emptyItem) emptyItem.classList.toggle('hidden', visibleCount > 0);
        }

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => b.setAttribute('aria-pressed', 'false'));
                btn.setAttribute('aria-pressed', 'true');
                activeFilter = btn.dataset.filter;
                applyFilter();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', applyFilter);
        }
    </script>
@endsection
