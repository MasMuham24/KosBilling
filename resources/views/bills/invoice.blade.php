<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Invoice #INV-{{ str_pad($bill->id, 5, '0', STR_PAD_LEFT) }} — KosBilling</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                            950: '#042f2e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-feature-settings: "cv02", "cv03", "cv04", "cv11";
            -webkit-font-smoothing: antialiased;
        }
        .num { font-variant-numeric: tabular-nums; }
        
        /* Watermark Stamp Lunas */
        .stamp-paid {
            transform: rotate(-12deg);
            mask-image: radial-gradient(circle, rgba(0,0,0,1) 70%, rgba(0,0,0,0.7) 100%);
        }

        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff !important; padding: 0 !important; }
            .invoice-card {
                box-shadow: none !important;
                border: 1px solid #e2e8f0 !important;
                max-width: 100% !important;
                margin: 0 !important;
                border-radius: 0 !important;
            }
        }
    </style>

    {{-- Midtrans Snap JS --}}
    @if (!empty($snapToken))
        <script src="{{ config('midtrans.is_production', false) ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
            data-client-key="{{ config('midtrans.client_key') }}"></script>
    @endif
</head>
<body class="bg-gradient-to-br from-slate-100 via-slate-50 to-teal-50/30 text-slate-900 min-h-screen py-8 sm:py-12 px-4 sm:px-6 flex flex-col items-center justify-start font-sans selection:bg-teal-500 selection:text-white">

    @php
        $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
        $tenant = $bill->tenant;
        $room = $bill->room;
        $property = $room->property;
        $cleanPhone = $tenant ? preg_replace('/[^0-9]/', '', $tenant->whatsapp_number) : '';
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }
    @endphp

    {{-- Top Action Bar --}}
    <div class="w-full max-w-3xl flex flex-wrap items-center justify-between gap-3 mb-6 no-print">
        <a href="{{ Auth::check() ? route('bills.index') : route('login') }}" 
            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 bg-white/80 hover:bg-white border border-slate-200/80 shadow-xs hover:text-slate-900 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ Auth::check() ? 'Kembali ke Daftar Tagihan' : 'Masuk KosBilling' }}
        </a>

        <div class="flex items-center gap-2">
            {{-- Tombol Salin Tautan --}}
            <button type="button" onclick="copyInvoiceLink()" id="copyBtn"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 shadow-xs transition cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <span id="copyBtnText">Salin Tautan</span>
            </button>

            {{-- Tombol Cetak / Simpan PDF --}}
            <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 shadow-xs transition cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    {{-- Main Invoice Sheet --}}
    <div class="invoice-card w-full max-w-3xl bg-white border border-slate-200/90 rounded-2xl shadow-xl shadow-slate-200/50 overflow-hidden relative">

        {{-- Top Colored Accent Bar --}}
        <div class="h-2 w-full bg-gradient-to-r from-teal-600 via-emerald-600 to-teal-700"></div>

        <div class="p-6 sm:p-10 relative">

            {{-- Watermark LUNAS jika status paid --}}
            @if ($bill->status === 'paid')
                <div class="pointer-events-none absolute right-8 top-32 sm:right-16 sm:top-28 stamp-paid z-10 select-none">
                    <div class="border-4 border-emerald-600/30 text-emerald-600/30 font-black tracking-widest text-2xl sm:text-4xl px-4 py-2 rounded-xl uppercase font-mono border-dashed">
                        LUNAS / PAID
                    </div>
                </div>
            @endif

            {{-- Header: Brand Properti & Meta Tagihan --}}
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-8 border-b border-slate-100">
                <div>
                    {{-- Logo & App Name --}}
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-600 to-teal-800 text-white flex items-center justify-center shadow-sm shadow-teal-700/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-teal-700 font-mono">KosBilling</span>
                            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight leading-none mt-0.5">
                                {{ $property->name }}
                            </h1>
                        </div>
                    </div>

                    {{-- Alamat Properti --}}
                    <p class="text-xs text-slate-500 mt-3 max-w-sm leading-relaxed">
                        {{ $property->address }}
                    </p>
                </div>

                {{-- Nomor Invoice --}}
                <div class="sm:text-right">
                    <span class="text-[11px] font-mono tracking-widest uppercase font-semibold text-slate-400 block">
                        INVOICE TAGIHAN SEWA
                    </span>
                    <div class="text-xl sm:text-2xl font-black font-mono text-slate-900 tracking-tight mt-0.5">
                        #INV-{{ str_pad($bill->id, 5, '0', STR_PAD_LEFT) }}
                    </div>
                </div>
            </div>

            {{-- Info Dua Kolom: Penerbit & Penerima --}}
            <div class="grid sm:grid-cols-2 gap-4 py-6 border-b border-slate-100">
                {{-- Kolom Ditagihkan Kepada --}}
                <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-200/60">
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block font-mono">
                        DITAGIHKAN KEPADA
                    </span>
                    <p class="text-sm font-bold text-slate-900 mt-1">
                        {{ $tenant->name ?? 'Penghuni Kamar' }}
                    </p>
                    <div class="mt-2 text-xs text-slate-600 space-y-0.5 font-mono">
                        <p class="flex items-center gap-1.5">
                            <span class="text-slate-400">Kamar:</span>
                            <span class="font-semibold text-slate-800">Nomor {{ $room->room_number }}</span>
                        </p>
                        <p class="flex items-center gap-1.5">
                            <span class="text-slate-400">WhatsApp:</span>
                            <span class="text-slate-700">{{ $tenant->whatsapp_number ?? '-' }}</span>
                        </p>
                    </div>
                </div>

                {{-- Kolom Detail Periode & Jatuh Tempo --}}
                <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-200/60 flex flex-col justify-between">
                    <div>
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block font-mono">
                            DETAIL WAKTU & JATUH TEMPO
                        </span>
                        <div class="mt-2 text-xs text-slate-700 space-y-1 font-mono">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Periode Sewa:</span>
                                <span class="font-bold text-slate-900">{{ \Carbon\Carbon::createFromFormat('Y-m', $bill->bill_month)->translatedFormat('F Y') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Tanggal Terbit:</span>
                                <span>{{ $bill->created_at->translatedFormat('d M Y') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Batas Jatuh Tempo:</span>
                                <span class="font-bold {{ $bill->status === 'overdue' ? 'text-rose-600' : 'text-slate-900' }}">
                                    {{ \Carbon\Carbon::parse($bill->due_date)->translatedFormat('d M Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel Rincian Tagihan --}}
            <div class="py-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs sm:text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 uppercase text-[11px] font-mono tracking-wider font-semibold text-left">
                                <th class="pb-3 pl-1">Item & Keterangan</th>
                                <th class="pb-3 text-center w-24">Jumlah</th>
                                <th class="pb-3 text-right pr-1">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            {{-- Baris Sewa Kamar Pokok --}}
                            <tr class="group">
                                <td class="py-4 pl-1">
                                    <div class="flex items-start gap-3">
                                        <div class="w-7 h-7 rounded-lg bg-teal-50 text-teal-700 border border-teal-100 flex items-center justify-center shrink-0 mt-0.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">
                                                Sewa Kamar {{ $room->room_number }} — {{ \Carbon\Carbon::createFromFormat('Y-m', $bill->bill_month)->translatedFormat('F Y') }}
                                            </p>
                                            <p class="text-xs text-slate-500 mt-0.5">
                                                Biaya pokok sewa kamar bulanan (Tipe/Ukuran standard)
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 text-center font-mono text-slate-600 text-xs">
                                    1 Bulan
                                </td>
                                <td class="py-4 text-right pr-1 font-bold font-mono text-slate-900 num">
                                    {{ $rp($bill->base_amount) }}
                                </td>
                            </tr>

                            {{-- Baris Biaya Tambahan (jika ada) --}}
                            @if ($bill->additional_charge > 0)
                                <tr class="group">
                                    <td class="py-4 pl-1">
                                        <div class="flex items-start gap-3">
                                            <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-700 border border-amber-100 flex items-center justify-center shrink-0 mt-0.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900">Biaya Tambahan / Utilitas</p>
                                                <p class="text-xs text-slate-500 mt-0.5">Listrik, air, laundry, kebersihan, atau fasilitas ekstra</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 text-center font-mono text-slate-600 text-xs">
                                        1 Item
                                    </td>
                                    <td class="py-4 text-right pr-1 font-bold font-mono text-slate-900 num">
                                        {{ $rp($bill->additional_charge) }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Total Ringkasan Box --}}
                <div class="mt-4 pt-4 border-t border-slate-200">
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 bg-slate-900 text-white rounded-xl p-5 shadow-inner">
                        <div>
                            <span class="text-[11px] font-mono tracking-widest uppercase text-slate-400 font-semibold block">
                                TOTAL DITAGIHKAN
                            </span>
                            <p class="text-xs text-slate-400 mt-0.5">
                                @if ($bill->status === 'paid')
                                    Pembayaran telah terkonfirmasi lunas dan sah.
                                @else
                                    Mohon selesaikan pembayaran sebelum tanggal jatuh tempo.
                                @endif
                            </p>
                        </div>
                        <div class="sm:text-right">
                            <span class="text-2xl sm:text-3xl font-black font-mono tracking-tight text-teal-300 num">
                                {{ $rp($bill->total_amount) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kartu Aksi Pembayaran / Bukti Lunas --}}
            <div class="mt-2 pt-6 border-t border-slate-100 no-print">
                @if ($bill->status === 'paid')
                    {{-- Status: LUNAS --}}
                    <div class="rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50/50 border border-emerald-200 p-6 text-center">
                        <div class="w-12 h-12 mx-auto rounded-full bg-emerald-500 text-white flex items-center justify-center font-black text-xl shadow-md shadow-emerald-500/20 mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-extrabold text-emerald-950">
                            Pembayaran Berhasil & Telah Lunas
                        </h3>
                        <p class="text-xs text-emerald-700 mt-1 max-w-md mx-auto">
                            Terima kasih atas kedisiplinan pembayaran Anda. Simpan invoice ini sebagai tanda bukti pembayaran yang sah.
                        </p>

                        @if ($bill->payment)
                            <div class="mt-4 pt-4 border-t border-emerald-200/60 max-w-md mx-auto grid grid-cols-2 gap-2 text-left text-xs font-mono text-emerald-900 bg-white/70 p-3.5 rounded-lg border">
                                <div>
                                    <span class="text-slate-400 block text-[10px] uppercase">Metode</span>
                                    <span class="font-bold uppercase text-slate-800">
                                        {{ str_replace('_', ' ', $bill->payment->payment_type ?? 'Midtrans') }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-slate-400 block text-[10px] uppercase">Waktu Verifikasi</span>
                                    <span class="font-semibold text-slate-800">
                                        {{ $bill->payment->verified_at ? \Carbon\Carbon::parse($bill->payment->verified_at)->translatedFormat('d M Y H:i') : '-' }}
                                    </span>
                                </div>
                                <div class="col-span-2 pt-2 mt-1 border-t border-slate-100 flex justify-between items-center text-[11px]">
                                    <span class="text-slate-400">Order ID:</span>
                                    <span class="font-bold text-slate-700 select-all">{{ $bill->payment->order_id }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    {{-- Status: BELUM LUNAS --}}
                    <div class="space-y-4">
                        @if (!empty($snapToken))
                            <div class="bg-gradient-to-br from-teal-50/80 via-white to-emerald-50/50 border border-teal-200/80 rounded-xl p-5 sm:p-6 text-center">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-teal-100/70 text-teal-800 mb-3 font-mono">
                                    <span class="w-1.5 h-1.5 rounded-full bg-teal-600 animate-ping"></span>
                                    PEMBAYARAN INSTAN OTOMATIS
                                </div>
                                <h3 class="text-sm sm:text-base font-bold text-slate-900">
                                    Bayar Cepat via QRIS (GoPay, OVO, ShopeePay) atau Virtual Account Bank
                                </h3>
                                <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                                    Klik tombol di bawah ini untuk membuka popup pembayaran resmi Midtrans. Status akan otomatis lunas saat transaksi selesai.
                                </p>

                                <div class="mt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                                    <button type="button" id="payButton"
                                        class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-teal-700/25 transition transform active:scale-98 flex items-center justify-center gap-2.5 cursor-pointer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Bayar Sekarang ({{ $rp($bill->total_amount) }})</span>
                                    </button>
                                </div>

                                {{-- Logo Badge Fintech --}}
                                <div class="mt-4 flex items-center justify-center gap-2 text-[10px] text-slate-400 font-mono uppercase tracking-wider">
                                    <span>Didukung oleh: QRIS · BCA · MANDIRI · BNI · BRI · GOPAY</span>
                                </div>
                            </div>
                        @else
                            <div class="rounded-xl bg-slate-50 border border-slate-200 p-5 text-center text-xs text-slate-600">
                                <p class="font-bold text-slate-800 text-sm">Pembayaran Transfer Bank / Manual</p>
                                <p class="mt-1 max-w-sm mx-auto">
                                    Silakan transfer sesuai total ke rekening pemilik kos, kemudian konfirmasikan bukti transfer kepada pengelola.
                                </p>
                            </div>
                        @endif

                        {{-- Opsi Pemilik Kos: Tandai Lunas Manual --}}
                        @if (Auth::check() && $bill->room->property->user_id === Auth::id())
                            <div class="pt-3 border-t border-slate-200/60">
                                <form action="{{ route('bills.mark_as_paid', $bill) }}" method="POST">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Tandai tagihan #INV-{{ str_pad($bill->id, 5, '0', STR_PAD_LEFT) }} sebagai LUNAS secara manual (uang tunai / transfer bank diterima)?')"
                                        class="w-full py-2.5 px-4 bg-white hover:bg-slate-50 text-emerald-700 font-bold text-xs rounded-xl border border-emerald-300 shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Konfirmasi Lunas Manual (Tunai / Rekening Pribadi)</span>
                                    </button>
                                </form>
                                <p class="text-[11px] text-slate-400 text-center mt-1">
                                    Aksi khusus pemilik kos: gunakan ini jika penghuni sudah membayar tunai ke Anda.
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Footer Note --}}
            <div class="mt-8 pt-5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-slate-400">
                <p>Invoice ini dibuat secara otomatis oleh sistem KosBilling · {{ $property->name }}</p>
                <p class="font-mono">#INV-{{ str_pad($bill->id, 5, '0', STR_PAD_LEFT) }}</p>
            </div>

        </div>
    </div>

    {{-- Script Salin Link & Snap Trigger --}}
    <script type="text/javascript">
        function copyInvoiceLink() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(function() {
                const copyText = document.getElementById('copyBtnText');
                if (copyText) {
                    copyText.innerText = '✓ Tautan Disalin!';
                    setTimeout(() => { copyText.innerText = 'Salin Tautan'; }, 2000);
                }
            }).catch(function(e) {
                alert('Tautan invoice: ' + window.location.href);
            });
        }
    </script>

    @if (!empty($snapToken) && $bill->status !== 'paid')
        <script type="text/javascript">
            const payButton = document.getElementById('payButton');

            function syncPayment(result, onDone) {
                fetch("{{ route('bills.payment_success', $bill) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(result || {})
                })
                .then(function(res) {
                    return res.json();
                })
                .then(function(data) {
                    if (onDone) onDone(data);
                })
                .catch(function(err) {
                    console.error('Error sinkronisasi:', err);
                    if (onDone) onDone(null);
                });
            }

            if (payButton) {
                payButton.addEventListener('click', function () {
                    window.snap.pay('{{ $snapToken }}', {
                        onSuccess: function (result) {
                            payButton.disabled = true;
                            payButton.innerHTML = '<span>Menyimpan Status Pembayaran...</span>';
                            syncPayment(result, function () {
                                alert("Pembayaran berhasil dikonfirmasi! Invoice telah Lunas.");
                                window.location.reload();
                            });
                        },
                        onPending: function (result) {
                            payButton.disabled = true;
                            payButton.innerHTML = '<span>Menyimpan Status Pembayaran...</span>';
                            syncPayment(result, function () {
                                alert("Menunggu pembayaran Anda diselesaikan.");
                                window.location.reload();
                            });
                        },
                        onError: function (result) {
                            alert("Terjadi kendala pada proses pembayaran.");
                        },
                        onClose: function () {
                            // User closed the popup
                        }
                    });
                });
            }
        </script>
    @endif

</body>
</html>
