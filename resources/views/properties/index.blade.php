@extends('layouts.app')

@section('title', 'Daftar Properti — KosBilling')

@section('content')
    {{-- Header & Primary Action --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">Daftar Properti Kos</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola gedung kos dan monitor kapasitas kamar yang Anda miliki.</p>
        </div>
        <div>
            <a href="{{ route('properties.create') }}"
                class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-white bg-teal-700 hover:bg-teal-800 rounded-md shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-teal-600">
                + Tambah Properti
            </a>
        </div>
    </div>

    {{-- Metric Summary Strip --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-px bg-slate-200 border border-slate-200 rounded-lg overflow-hidden">
        <div class="bg-white p-5">
            <p class="text-sm text-slate-500">Total Properti</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 num">{{ $properties->count() }} <span class="text-base font-normal text-slate-500">Gedung</span></p>
            <p class="mt-1.5 text-xs text-slate-500">Aktif dikelola pada akun ini</p>
        </div>
        <div class="bg-white p-5">
            <p class="text-sm text-slate-500">Total Kapasitas Kamar</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 num">{{ $properties->sum('rooms_count') }} <span class="text-base font-normal text-slate-500">Unit</span></p>
            <p class="mt-1.5 text-xs text-slate-500">Terdaftar di seluruh gedung properti</p>
        </div>
        <div class="bg-white p-5 sm:col-span-2 lg:col-span-1">
            <p class="text-sm text-slate-500">Aksi Cepat</p>
            <div class="mt-2 flex items-center gap-2">
                <a href="{{ route('rooms.create') }}" class="text-xs font-medium text-teal-700 hover:underline">+ Tambah Kamar Baru</a>
                <span class="text-slate-300">·</span>
                <a href="{{ route('rooms.index') }}" class="text-xs font-medium text-slate-600 hover:text-slate-900">Lihat Semua Kamar &rarr;</a>
            </div>
            <p class="mt-1.5 text-xs text-slate-400">Atur nomor dan harga sewa bulanan</p>
        </div>
    </div>

    {{-- Main Table Container --}}
    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
        
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-900">Semua Properti</h2>
            <span class="text-xs text-slate-500 font-mono">{{ $properties->count() }} gedung terdata</span>
        </div>

        {{-- Desktop Table View --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-medium">Nama Properti</th>
                        <th scope="col" class="px-3 py-3 font-medium">Alamat</th>
                        <th scope="col" class="px-3 py-3 font-medium">Jumlah Kamar</th>
                        <th scope="col" class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($properties as $property)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-4 font-semibold text-slate-900 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                                    <span>{{ $property->name }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-4 text-slate-600 max-w-md">
                                {{ $property->address }}
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <a href="{{ route('rooms.index', ['property_id' => $property->id]) }}" 
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                                    <span class="num font-semibold">{{ $property->rooms_count }}</span> Kamar
                                    <span class="text-slate-400">&rarr;</span>
                                </a>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('rooms.create', ['property_id' => $property->id]) }}" 
                                    class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-teal-700 bg-teal-50 hover:bg-teal-100 rounded-md transition">
                                    + Kamar
                                </a>
                                <a href="{{ route('properties.edit', $property) }}" 
                                    class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-md transition">
                                    Edit
                                </a>
                                <form action="{{ route('properties.destroy', $property) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Hapus properti ini beserta seluruh kamarnya?')">
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
                            <td colspan="4" class="px-5 py-12 text-center text-slate-500">
                                <p class="font-medium text-slate-700">Belum ada properti terdaftar</p>
                                <p class="text-xs text-slate-400 mt-1">Tambahkan data properti pertama Anda untuk mulai mengelola unit kamar.</p>
                                <a href="{{ route('properties.create') }}" class="mt-4 inline-flex items-center px-3.5 py-2 text-xs font-medium text-white bg-teal-700 hover:bg-teal-800 rounded-md shadow-sm">
                                    + Tambah Properti Sekarang
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card List View --}}
        <ul class="md:hidden divide-y divide-slate-200">
            @forelse ($properties as $property)
                <li class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-slate-900 text-sm">{{ $property->name }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $property->address }}</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700 font-mono">
                            {{ $property->rooms_count }} Kamar
                        </span>
                    </div>
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                        <a href="{{ route('rooms.index', ['property_id' => $property->id]) }}" class="text-teal-700 font-medium hover:underline">
                            Lihat Unit Kamar &rarr;
                        </a>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('properties.edit', $property) }}" class="px-2.5 py-1 text-xs font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-md">
                                Edit
                            </a>
                            <form action="{{ route('properties.destroy', $property) }}" method="POST" class="inline"
                                onsubmit="return confirm('Hapus properti ini beserta seluruh kamarnya?')">
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
                    Belum ada properti. Silakan tambahkan properti pertama Anda.
                </li>
            @endforelse
        </ul>

    </div>
@endsection
