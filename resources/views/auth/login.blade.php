<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — KosBilling</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>
    <style>
        body { font-feature-settings: "cv11", "ss03"; -webkit-font-smoothing: antialiased; }
    </style>
</head>

@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;

    $inputBase = 'block w-full rounded-md border bg-white px-3 py-2.5 text-base sm:text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2';
    $inputOk   = 'border-slate-300 focus:ring-teal-600 focus:border-teal-600';
    $inputBad  = 'border-red-500 focus:ring-red-600 focus:border-red-600';
@endphp

<body class="bg-white text-slate-900 font-sans antialiased">
<div class="min-h-screen lg:grid lg:grid-cols-12">

    {{-- Panel kiri (hanya desktop) --}}
    <aside class="hidden lg:flex lg:col-span-5 lg:sticky lg:top-0 lg:h-screen flex-col justify-between bg-slate-900 p-10 xl:p-14 text-slate-300">
        <div class="flex items-center gap-2.5">
            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-teal-600 text-sm font-semibold text-white">KB</div>
            <span class="text-base font-semibold text-white">KosBilling</span>
        </div>

        <div class="max-w-md">
            <h2 class="text-3xl font-semibold leading-tight text-white">Penagihan sewa kos yang rapi dan tepat waktu.</h2>
            <p class="mt-4 text-sm leading-relaxed text-slate-400">
                Pantau okupansi kamar, kirim tagihan lewat WhatsApp dengan tautan pembayaran, dan verifikasi bukti transfer dalam satu dashboard.
            </p>

            <ul class="mt-8 space-y-4 text-sm">
                <li class="flex gap-3">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-teal-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                    <span><span class="font-medium text-white">Pengingat lewat WhatsApp.</span> Tagihan dan pengingat dikirim ke penghuni sesuai tanggal jatuh tempo.</span>
                </li>
                <li class="flex gap-3">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-teal-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                    <span><span class="font-medium text-white">QRIS dan Virtual Account.</span> Status pembayaran diperbarui otomatis lewat Midtrans.</span>
                </li>
                <li class="flex gap-3">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-teal-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                    <span><span class="font-medium text-white">Verifikasi transfer manual.</span> Tinjau bukti pembayaran penghuni dan konfirmasi dengan satu klik.</span>
                </li>
            </ul>
        </div>

        <p class="text-xs text-slate-500">&copy; {{ date('Y') }} KosBilling</p>
    </aside>

    {{-- Kolom form --}}
    <div class="flex min-h-screen flex-col lg:col-span-7">

        <header class="flex items-center px-4 py-4 sm:px-6 lg:hidden">
            <div class="flex items-center gap-2.5">
                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-teal-700 text-xs font-semibold text-white">KB</div>
                <span class="text-sm font-semibold text-slate-900">KosBilling</span>
            </div>
        </header>

        <main class="flex flex-1 items-center justify-center px-4 py-8 sm:px-6">
            <div class="w-full max-w-sm">
                <h1 class="text-2xl font-semibold text-slate-900">Masuk ke KosBilling</h1>
                <p class="mt-2 text-sm text-slate-500">Gunakan akun pengelola untuk mengelola penagihan properti Anda.</p>

                @if (session('success'))
                    <div role="status" class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 px-3.5 py-3 text-sm text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div role="alert" class="mt-6 rounded-md border border-red-200 bg-red-50 px-3.5 py-3 text-sm text-red-800">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form id="loginForm" action="{{ route('login') }}" method="POST" class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Alamat email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            autocomplete="email" inputmode="email" autocapitalize="none" spellcheck="false"
                            placeholder="nama@email.com" data-autofocus
                            @if ($errors->has('email')) aria-invalid="true" @endif
                            class="mt-1.5 {{ $inputBase }} {{ $errors->has('email') ? $inputBad : $inputOk }}">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                        <div class="relative mt-1.5">
                            <input id="password" type="password" name="password" required
                                autocomplete="current-password"
                                @if ($errors->has('password')) aria-invalid="true" @endif
                                class="{{ $inputBase }} pr-24 {{ $errors->has('password') ? $inputBad : $inputOk }}">
                            <button type="button" data-toggle-password="password" aria-controls="password" aria-pressed="false"
                                class="absolute inset-y-0 right-0 rounded-r-md px-3 text-sm font-medium text-slate-500 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-teal-600">
                                Tampilkan
                            </button>
                        </div>
                    </div>

                    <label class="flex items-center gap-2.5 text-sm text-slate-600 select-none">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                        <span>Ingat saya di perangkat ini</span>
                    </label>

                    <button type="submit" id="submitBtn"
                        class="w-full rounded-md bg-teal-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-teal-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-teal-600 disabled:cursor-not-allowed disabled:opacity-60">
                        Masuk
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-slate-500">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-medium text-teal-700 hover:text-teal-800 hover:underline">Daftar</a>
                </p>
            </div>
        </main>

        <footer class="px-4 py-4 text-center text-xs text-slate-500 lg:hidden">
            &copy; {{ date('Y') }} KosBilling
        </footer>
    </div>
</div>

<script>
    // Tampilkan / sembunyikan password
    document.querySelectorAll('[data-toggle-password]').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.togglePassword);
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.textContent = show ? 'Sembunyikan' : 'Tampilkan';
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
        });
    });

    // Cegah submit ganda
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    form.addEventListener('submit', () => {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Memproses…';
    });
    // Pulihkan tombol saat halaman dimuat dari cache (tombol Kembali)
    window.addEventListener('pageshow', () => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Masuk';
    });

    // Fokus: ke field error bila ada; autofocus hanya di layar lebar agar keyboard HP tidak langsung muncul
    const invalid = document.querySelector('[aria-invalid="true"]');
    const auto = document.querySelector('[data-autofocus]');
    if (invalid) invalid.focus();
    else if (auto && window.matchMedia('(min-width: 1024px)').matches) auto.focus();
</script>
</body>
</html>
