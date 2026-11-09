@extends('layouts.app')

@section('title', 'Manajemen Tagihan — KosBilling')

@section('content')
    @php
        $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
        $selectedMonth = request('month') ?? now()->format('Y-m');

        $totalBills   = $bills->count();
        $paidBills    = $bills->where('status', 'paid');
        $unpaidBills  = $bills->whereIn('status', ['unpaid', 'overdue']);
        $pendingBills = $bills->where('status', 'pending_verification');

        $totalRevenue = $paidBills->sum('total_amount');
        $unpaidSum    = $unpaidBills->sum('total_amount');
    @endphp

    {{-- Header & Primary Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">Manajemen Tagihan Sewa</h1>
            <p class="mt-1 text-sm text-slate-500">
                Kelola invoice sewa kos, terbitkan tagihan otomatis, dan pantau status pelunasan.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            {{-- Tombol Modal Generate Massal --}}
            <button type="button" onclick="document.getElementById('generateModal').showModal()"
                class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-md shadow-sm transition">
                ⚡ Generate Massal
            </button>
            <a href="{{ route('bills.create') }}"
                class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-white bg-teal-700 hover:bg-teal-800 rounded-md shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-teal-600 transition">
                + Buat Tagihan Manual
            </a>
        </div>
    </div>

    {{-- Metric Summary Strip --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-px bg-slate-200 border border-slate-200 rounded-lg overflow-hidden">
        <div class="bg-white p-5">
            <p class="text-sm text-slate-500">Total Tagihan Terbit</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 num">{{ $totalBills }} <span class="text-base font-normal text-slate-500">Invoice</span></p>
            <p class="mt-1.5 text-xs text-slate-500">Periode terpilih</p>
        </div>

        <div class="bg-white p-5">
            <p class="text-sm text-slate-500">Tagihan Terbayar (Lunas)</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-700 num">{{ $rp($totalRevenue) }}</p>
            <p class="mt-1.5 text-xs text-slate-500">{{ $paidBills->count() }} transaksi berhasil</p>
        </div>

        <div class="bg-white p-5">
            <p class="text-sm text-slate-500">Belum Dibayar / Menunggak</p>
            <p class="mt-2 text-2xl font-semibold text-rose-700 num">{{ $rp($unpaidSum) }}</p>
            <p class="mt-1.5 text-xs text-slate-500">{{ $unpaidBills->count() }} kamar belum lunas</p>
        </div>

        <div class="bg-white p-5">
            <p class="text-sm text-slate-500">Menunggu Verifikasi</p>
            <p class="mt-2 text-2xl font-semibold text-amber-700 num">{{ $pendingBills->count() }} <span class="text-base font-normal text-slate-500">Kamar</span></p>
            <p class="mt-1.5 text-xs text-slate-500">Perlu konfirmasi transfer manual</p>
        </div>
    </div>

    {{-- Main Container --}}
    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
        
        {{-- Filter & Period Selector Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b border-slate-200">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Daftar Tagihan & Invoice</h2>
                <p class="text-xs text-slate-500 mt-0.5">Filter berdasarkan bulan penagihan atau status pembayaran.</p>
            </div>

            <form action="{{ route('bills.index') }}" method="GET" class="flex items-center gap-2">
                <span class="text-xs text-slate-500 whitespace-nowrap">Periode:</span>
                <input type="month" name="month" value="{{ request('month') }}" onchange="this.form.submit()"
                    class="text-sm text-slate-700 bg-white border border-slate-300 rounded-md py-1.5 px-2.5 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 font-mono">
                @if(request('month'))
                    <a href="{{ route('bills.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
                @endif
            </form>
        </div>

        {{-- Desktop Table View --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-medium">Invoice & Periode</th>
                        <th scope="col" class="px-3 py-3 font-medium">Penyewa & Kamar</th>
                        <th scope="col" class="px-3 py-3 font-medium">Jatuh Tempo</th>
                        <th scope="col" class="px-3 py-3 font-medium text-right">Total Tagihan</th>
                        <th scope="col" class="px-3 py-3 font-medium">Status</th>
                        <th scope="col" class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($bills as $bill)
                        @php
                            $tenant = $bill->tenant;
                            $cleanPhone = $tenant ? preg_replace('/[^0-9]/', '', $tenant->whatsapp_number) : '';
                            if (str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '62' . substr($cleanPhone, 1);
                            }
                            $invoiceUrl = route('bills.pay', $bill);
                            $waMessage = rawurlencode("Halo {$tenant?->name} (Kamar {$bill->room->room_number}), ini rincian tagihan sewa kos bulan {$bill->bill_month} sebesar {$rp($bill->total_amount)}. Silakan lihat invoice dan bayar via QRIS/VA melalui tautan berikut: {$invoiceUrl}");
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <a href="{{ route('bills.show', $bill) }}" class="font-mono text-xs font-bold text-teal-700 hover:underline">
                                    #INV-{{ str_pad($bill->id, 5, '0', STR_PAD_LEFT) }}
                                </a>
                                <p class="text-xs text-slate-500 font-mono">Bulan {{ $bill->bill_month }}</p>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <p class="font-semibold text-slate-900">{{ $tenant->name ?? 'Penghuni' }}</p>
                                <p class="text-xs text-slate-500 font-mono">
                                    Kamar {{ $bill->room->room_number }} · {{ $bill->room->property->name ?? '-' }}
                                </p>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap text-xs text-slate-600 font-mono">
                                {{ \Carbon\Carbon::parse($bill->due_date)->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-3 py-4 text-right font-medium text-slate-900 num whitespace-nowrap">
                                <span class="font-bold text-sm">{{ $rp($bill->total_amount) }}</span>
                                @if ($bill->additional_charge > 0)
                                    <p class="text-[11px] text-slate-400 font-normal">+ Utilitas {{ $rp($bill->additional_charge) }}</p>
                                @endif
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                @if ($bill->status === 'paid')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Lunas
                                    </span>
                                @elseif ($bill->status === 'pending_verification')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>Verifikasi
                                    </span>
                                @elseif ($bill->status === 'overdue')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-rose-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>Menunggak
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Belum Bayar
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap space-x-1.5">
                                @if ($bill->status !== 'paid')
                                    <form action="{{ route('bills.sendReminder', $bill) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Kirim notifikasi tagihan otomatis ke {{ $bill->tenant->name }}?');">
                                        @csrf
                                        <button type="submit" title="Kirim Notifikasi Otomatis via WA Gateway"
                                            class="inline-flex items-center bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-2.5 py-1 rounded-md font-medium transition">
                                            ⚡ Auto WA
                                        </button>
                                    </form>
                                @endif

                                {{-- Tombol Kirim Tagihan via WA (Manual) --}}
                                @if ($cleanPhone)
                                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMessage }}" target="_blank"
                                        title="Kirim link invoice ke WhatsApp secara manual"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-md transition">
                                        WA &rarr;
                                    </a>
                                @endif

                                @if ($bill->status !== 'paid')
                                    <form action="{{ route('bills.mark_as_paid', $bill) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Tandai tagihan #INV-{{ str_pad($bill->id, 5, '0', STR_PAD_LEFT) }} sebagai LUNAS?')">
                                        @csrf
                                        <button type="submit" title="Tandai Lunas Manual"
                                            class="inline-flex items-center px-2 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-md transition">
                                            ✓ Lunas
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('bills.show', $bill) }}"
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-md transition">
                                    Invoice
                                </a>

                                <a href="{{ route('bills.edit', $bill) }}"
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-md transition">
                                    Edit
                                </a>

                                <form action="{{ route('bills.destroy', $bill) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Hapus tagihan #INV-{{ str_pad($bill->id, 5, '0', STR_PAD_LEFT) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 bg-white border border-red-200 hover:bg-red-50 rounded-md transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                                <p class="font-medium text-slate-700">Belum ada tagihan sewa</p>
                                <p class="text-xs text-slate-400 mt-1">Gunakan tombol "Generate Massal" untuk membuat tagihan otomatis seluruh penyewa aktif bulan ini.</p>
                                <div class="mt-4 flex items-center justify-center gap-2">
                                    <button type="button" onclick="document.getElementById('generateModal').showModal()"
                                        class="inline-flex items-center px-3.5 py-2 text-xs font-medium text-white bg-teal-700 hover:bg-teal-800 rounded-md shadow-sm">
                                        ⚡ Generate Tagihan Otomatis
                                    </button>
                                    <a href="{{ route('bills.create') }}"
                                        class="inline-flex items-center px-3.5 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-md shadow-sm">
                                        + Buat Manual
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card List View --}}
        <ul class="md:hidden divide-y divide-slate-200">
            @forelse ($bills as $bill)
                @php
                    $tenant = $bill->tenant;
                    $cleanPhone = $tenant ? preg_replace('/[^0-9]/', '', $tenant->whatsapp_number) : '';
                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = '62' . substr($cleanPhone, 1);
                    }
                    $invoiceUrl = route('bills.pay', $bill);
                    $waMessage = rawurlencode("Halo {$tenant?->name} (Kamar {$bill->room->room_number}), ini rincian tagihan sewa kos bulan {$bill->bill_month} sebesar {$rp($bill->total_amount)}. Silakan lihat invoice dan bayar via QRIS/VA melalui tautan berikut: {$invoiceUrl}");
                @endphp
                <li class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <a href="{{ route('bills.show', $bill) }}" class="font-mono text-xs font-bold text-teal-700">
                                #INV-{{ str_pad($bill->id, 5, '0', STR_PAD_LEFT) }}
                            </a>
                            <p class="text-sm font-semibold text-slate-900 mt-0.5">{{ $tenant->name ?? 'Penghuni' }}</p>
                            <p class="text-xs text-slate-500 font-mono">Kamar {{ $bill->room->room_number }} · {{ $bill->room->property->name ?? '-' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-slate-900 num block">{{ $rp($bill->total_amount) }}</span>
                            <span class="text-[11px] text-slate-500 font-mono">Jatuh tempo: {{ \Carbon\Carbon::parse($bill->due_date)->format('d M') }}</span>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                        <div>
                            @if ($bill->status === 'paid')
                                <span class="text-xs font-medium text-emerald-700">● Lunas</span>
                            @elseif ($bill->status === 'pending_verification')
                                <span class="text-xs font-medium text-amber-700">● Verifikasi</span>
                            @elseif ($bill->status === 'overdue')
                                <span class="text-xs font-medium text-rose-700">● Menunggak</span>
                            @else
                                <span class="text-xs font-medium text-slate-500">● Belum Bayar</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($bill->status !== 'paid')
                                <form action="{{ route('bills.sendReminder', $bill) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Kirim notifikasi tagihan otomatis ke {{ $bill->tenant->name }}?');">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-2.5 py-1 rounded-md font-medium transition">
                                        ⚡ Auto WA
                                    </button>
                                </form>
                            @endif
                            @if ($cleanPhone)
                                <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMessage }}" target="_blank"
                                    class="px-2 py-1 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded">
                                    WA
                                </a>
                            @endif
                            <a href="{{ route('bills.show', $bill) }}" class="px-2 py-1 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded">
                                Invoice
                            </a>
                            <a href="{{ route('bills.edit', $bill) }}" class="px-2 py-1 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded">
                                Edit
                            </a>
                        </div>
                    </div>
                </li>
            @empty
                <li class="p-8 text-center text-slate-500 text-sm">
                    Belum ada tagihan sewa terbit.
                </li>
            @endforelse
        </ul>

    </div>

    {{-- Dialog Modal Generate Tagihan Massal --}}
    <dialog id="generateModal" class="m-auto w-[calc(100%-2rem)] max-w-md rounded-lg border border-slate-200 p-0 shadow-lg backdrop:bg-slate-900/40">
        <form action="{{ route('bills.generate') }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <h3 class="text-base font-semibold text-slate-900">⚡ Generate Tagihan Massal</h3>
                    <button type="button" onclick="document.getElementById('generateModal').close()" class="text-slate-400 hover:text-slate-700 text-sm font-bold">&times;</button>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Sistem akan otomatis menerbitkan tagihan sewa untuk <strong>seluruh penyewa aktif</strong> yang belum memiliki tagihan pada bulan yang dipilih.
                </p>

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-slate-600 mb-1.5">
                        Pilih Periode Bulan Tagihan <span class="text-red-500">*</span>
                    </label>
                    <input type="month" name="bill_month" value="{{ date('Y-m') }}" required
                        class="w-full text-sm bg-white border border-slate-300 rounded-md px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 font-mono">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 bg-slate-50 px-6 py-3 border-t border-slate-200 rounded-b-lg">
                <button type="button" onclick="document.getElementById('generateModal').close()"
                    class="px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-md">
                    Batal
                </button>
                <button type="submit"
                    class="px-3.5 py-2 text-sm font-medium text-white bg-teal-700 hover:bg-teal-800 rounded-md shadow-sm">
                    Generate Tagihan Sekarang
                </button>
            </div>
        </form>
    </dialog>
@endsection

