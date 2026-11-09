@extends('layouts.app')

@section('title', 'Daftar Penyewa — KosBilling')

@section('content')
    @php
        $totalTenants    = $tenants->count();
        $activeTenants   = $tenants->where('status', 'active')->count();
        $checkoutTenants = $tenants->where('status', 'checkout')->count();
        $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    @endphp

    {{-- Header & Primary Action --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">Daftar Penyewa Kos</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola data penghuni kos, nomor kontak WhatsApp, dan tanggal jatuh tempo sewa.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('tenants.create') }}"
                class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-white bg-teal-700 hover:bg-teal-800 rounded-md shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-teal-600 transition">
                + Tambah Penyewa
            </a>
        </div>
    </div>

    {{-- Metric Summary Strip --}}
    <div class="grid sm:grid-cols-3 gap-px bg-slate-200 border border-slate-200 rounded-lg overflow-hidden">
        <div class="bg-white p-5">
            <p class="text-sm text-slate-500">Total Penyewa Terdata</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 num">{{ $totalTenants }} <span class="text-base font-normal text-slate-500">Orang</span></p>
            <p class="mt-1.5 text-xs text-slate-500">Riwayat penyewa aktif & checkout</p>
        </div>

        <div class="bg-white p-5">
            <p class="text-sm text-slate-500">Penyewa Aktif</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-700 num">{{ $activeTenants }} <span class="text-base font-normal text-slate-500">Penghuni</span></p>
            <p class="mt-1.5 text-xs text-slate-500">Sedang menempati kamar saat ini</p>
        </div>

        <div class="bg-white p-5">
            <p class="text-sm text-slate-500">Sudah Checkout</p>
            <p class="mt-2 text-2xl font-semibold text-slate-700 num">{{ $checkoutTenants }} <span class="text-base font-normal text-slate-500">Orang</span></p>
            <p class="mt-1.5 text-xs text-slate-500">Selesai masa kontrak sewa</p>
        </div>
    </div>

    {{-- Main Table Container --}}
    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b border-slate-200">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Data Penghuni Kos</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pantau status sewa dan hubungi penghuni langsung via WhatsApp.</p>
            </div>
            <span class="text-xs text-slate-500 font-mono">{{ $activeTenants }} aktif dari {{ $totalTenants }} terdaftar</span>
        </div>

        {{-- Desktop Table View --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-medium">Nama Penyewa</th>
                        <th scope="col" class="px-3 py-3 font-medium">Kamar & Properti</th>
                        <th scope="col" class="px-3 py-3 font-medium">Kontak WhatsApp</th>
                        <th scope="col" class="px-3 py-3 font-medium">Tanggal Masuk</th>
                        <th scope="col" class="px-3 py-3 font-medium">Jatuh Tempo</th>
                        <th scope="col" class="px-3 py-3 font-medium">Status</th>
                        <th scope="col" class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($tenants as $tenant)
                        @php
                            $cleanPhone = preg_replace('/[^0-9]/', '', $tenant->whatsapp_number);
                            if (str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '62' . substr($cleanPhone, 1);
                            }
                            $waMessage = rawurlencode("Halo {$tenant->name} (Kamar {$tenant->room->room_number}), ini pesan dari pengelola kos.");
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-4 font-semibold text-slate-900 whitespace-nowrap">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-700 text-xs font-bold flex items-center justify-center font-mono">
                                        {{ strtoupper(substr($tenant->name, 0, 1)) }}
                                    </div>
                                    <span>{{ $tenant->name }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <p class="font-semibold text-slate-900 font-mono text-xs">
                                    Kamar {{ $tenant->room->room_number ?? '-' }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ $tenant->room->property->name ?? '-' }}
                                </p>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs text-slate-700">{{ $tenant->whatsapp_number }}</span>
                                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMessage }}" target="_blank"
                                        title="Chat WhatsApp"
                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition">
                                        WA &rarr;
                                    </a>
                                </div>
                            </td>
                            <td class="px-3 py-4 text-slate-600 whitespace-nowrap text-xs">
                                {{ $tenant->checkin_in_date ? \Carbon\Carbon::parse($tenant->checkin_in_date)->translatedFormat('d M Y') : '-' }}
                            </td>
                            <td class="px-3 py-4 text-slate-700 whitespace-nowrap text-xs font-mono">
                                Tiap tgl <strong class="text-slate-900 font-bold">{{ $tenant->due_date_day }}</strong>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                @if ($tenant->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600" aria-hidden="true"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400" aria-hidden="true"></span>
                                        Checkout
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('tenants.edit', $tenant) }}"
                                    class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-md transition">
                                    Edit
                                </a>
                                <form action="{{ route('tenants.destroy', $tenant) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Hapus data penyewa {{ $tenant->name }}? Kamar akan otomatis kembali kosong.')">
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
                            <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                                <p class="font-medium text-slate-700">Belum ada penyewa terdaftar</p>
                                <p class="text-xs text-slate-400 mt-1">Tambahkan data penyewa baru dan kaitkan dengan kamar yang tersedia.</p>
                                <a href="{{ route('tenants.create') }}" class="mt-4 inline-flex items-center px-3.5 py-2 text-xs font-medium text-white bg-teal-700 hover:bg-teal-800 rounded-md shadow-sm">
                                    + Tambah Penyewa Sekarang
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card List View --}}
        <ul class="md:hidden divide-y divide-slate-200">
            @forelse ($tenants as $tenant)
                @php
                    $cleanPhone = preg_replace('/[^0-9]/', '', $tenant->whatsapp_number);
                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = '62' . substr($cleanPhone, 1);
                    }
                    $waMessage = rawurlencode("Halo {$tenant->name} (Kamar {$tenant->room->room_number}), ini pesan dari pengelola kos.");
                @endphp
                <li class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-slate-900 text-sm">{{ $tenant->name }}</h3>
                            <p class="text-xs text-slate-500 font-mono mt-0.5">
                                Kamar {{ $tenant->room->room_number ?? '-' }} · {{ $tenant->room->property->name ?? '-' }}
                            </p>
                        </div>
                        @if ($tenant->status === 'active')
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600" aria-hidden="true"></span>
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400" aria-hidden="true"></span>
                                Checkout
                            </span>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-600 pt-1">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Jatuh Tempo:</span>
                            <span class="font-mono font-medium text-slate-900">Tiap tgl {{ $tenant->due_date_day }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Check-in:</span>
                            <span>{{ $tenant->checkin_in_date ? \Carbon\Carbon::parse($tenant->checkin_in_date)->translatedFormat('d M Y') : '-' }}</span>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                        <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMessage }}" target="_blank"
                            class="inline-flex items-center gap-1 text-emerald-700 font-medium hover:underline">
                            <span>Hubungi WA</span> &rarr;
                        </a>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('tenants.edit', $tenant) }}" class="px-2.5 py-1 text-xs font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-md">
                                Edit
                            </a>
                            <form action="{{ route('tenants.destroy', $tenant) }}" method="POST" class="inline"
                                onsubmit="return confirm('Hapus penyewa {{ $tenant->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1 text-xs font-medium text-red-600 bg-white border border-red-200 hover:bg-red-50 rounded-md">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </li>
            @empty
                <li class="p-8 text-center text-slate-500 text-sm">
                    Belum ada penyewa terdaftar.
                </li>
            @endforelse
        </ul>

    </div>
@endsection

