@extends('layouts.app')

@section('title', 'Edit Properti — KosBilling')

@section('content')
    <div class="max-w-2xl mx-auto space-y-4">
        
        <div>
            <a href="{{ route('properties.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-900 transition">
                <span>&larr;</span> Kembali ke Daftar Properti
            </a>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg p-6 sm:p-8 shadow-sm">
            <div class="pb-5 border-b border-slate-200 mb-6">
                <h1 class="text-lg font-semibold text-slate-900">Edit Properti Kos</h1>
                <p class="text-xs text-slate-500 mt-1">Perbarui informasi nama atau alamat gedung kos.</p>
            </div>

            <form action="{{ route('properties.update', $property) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Nama Properti / Kos <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $property->name) }}" placeholder="Contoh: Kos Griya Nara Asri" required autofocus
                        class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition">
                    @error('name') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Alamat Lengkap <span class="text-red-500">*</span>
                    </label>
                    <textarea name="address" rows="3" placeholder="Contoh: Jl. Kaliurang Km 5, Gang Pandega Marta No. 12, Sleman, DI Yogyakarta" required
                        class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 transition">{{ old('address', $property->address) }}</textarea>
                    @error('address') 
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <a href="{{ route('properties.index') }}"
                        class="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white rounded-md text-sm font-semibold shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-teal-600 transition">
                        Perbarui Properti
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
