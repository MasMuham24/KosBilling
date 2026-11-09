<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengelola — KosBilling</title>

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
            <h2 class="text-3xl font-semibold leading-tight text-white">Kelola penagihan kos tanpa spreadsheet.</h2>
            <p class="mt-4 text-sm leading-relaxed text-slate-400">
                Ganti catatan manual dengan sistem pembayaran yang otomatis dan terdata rapi. Pengaturan awal hanya butuh tiga langkah.
            </p>

            <ol class="mt-8 space-y-5 text-sm">
                <li class="flex gap-4">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-slate-600 text-xs font-medium text-slate-300">1</span>
                    <span>
                        <span class="block font-medium text-white">Daftarkan properti dan kamar</span>
                        <span class="mt-0.5 block text-slate-400">Atur tarif bulanan, fasilitas, dan nomor kamar.</span>
                    </span>
                </li>
                <li class="flex gap-4">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-slate-600 text-xs font-medium text-slate-300">2</span>
                    <span>
                        <span class="block font-medium text-white">Hubungkan WhatsApp penghuni</span>
                        <span class="mt-0.5 block text-slate-400">Tagihan dan invoice digital terkirim saat jatuh tempo.</span>
                    </span>
                </li>
                <li class="flex gap-4">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-slate-600 text-xs font-medium text-slate-300">3</span>
                    <span>
                        <span class="block font-medium text-white">Cocokkan pembayaran</span>
                        <span class="mt-0.5 block text-slate-400">Terima QRIS atau Virtual Account Midtrans, atau verifikasi transfer manual.</span>
                    </span>
                </li>
            </ol>
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
            <div class="w-full max-w-md">
                <h1 class="text-2xl font-semibold text-slate-900">Buat akun pengelola</h1>
                <p class="mt-2 text-sm text-slate-500">Lengkapi data berikut untuk mengaktifkan workspace kos Anda.</p>

                @if ($errors->any())
                    <div role="alert" class="mt-6 rounded-md border border-red-200 bg-red-50 px-3.5 py-3 text-sm text-red-800">
                        Data belum lengkap atau belum sesuai. Periksa kolom yang ditandai di bawah.
                    </div>
                @endif

                <form id="registerForm" action="{{ route('register') }}" method="POST" class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700">Nama lengkap</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required
                            autocomplete="name" placeholder="Contoh: Rian Pratama" data-autofocus
                            @if ($errors->has('name')) aria-invalid="true" aria-describedby="name-error" @endif
                            class="mt-1.5 {{ $inputBase }} {{ $errors->has('name') ? $inputBad : $inputOk }}">
                        @error('name')
                            <p id="name-error" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Alamat email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            autocomplete="email" inputmode="email" autocapitalize="none" spellcheck="false"
                            placeholder="nama@email.com"
                            @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif
                            class="mt-1.5 {{ $inputBase }} {{ $errors->has('email') ? $inputBad : $inputOk }}">
                        @error('email')
                            <p id="email-error" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="whatsapp_number" class="block text-sm font-medium text-slate-700">
                            Nomor WhatsApp <span class="font-normal text-slate-400">(opsional)</span>
                        </label>
                        <input id="whatsapp_number" type="tel" name="whatsapp_number" value="{{ old('whatsapp_number') }}"
                            autocomplete="tel" inputmode="tel" placeholder="081234567890"
                            @if ($errors->has('whatsapp_number')) aria-invalid="true" aria-describedby="whatsapp-error" @else aria-describedby="whatsapp-hint" @endif
                            class="mt-1.5 {{ $inputBase }} {{ $errors->has('whatsapp_number') ? $inputBad : $inputOk }}">
                        @error('whatsapp_number')
                            <p id="whatsapp-error" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
                        @else
                            <p id="whatsapp-hint" class="mt-1.5 text-xs text-slate-500">Nomor pemilik, dipakai sebagai kontak pengelola.</p>
                        @enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                            <div class="relative mt-1.5">
                                <input id="password" type="password" name="password" required minlength="8"
                                    autocomplete="new-password" placeholder="Minimal 8 karakter"
                                    @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif
                                    class="{{ $inputBase }} pr-12 {{ $errors->has('password') ? $inputBad : $inputOk }}">
                                <button type="button" data-toggle-password="password" aria-controls="password" aria-pressed="false" aria-label="Tampilkan password"
                                    class="absolute inset-y-0 right-0 rounded-r-md px-3 text-slate-500 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-teal-600">
                                    <svg data-eye class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M1.5 10S4.5 4.5 10 4.5 18.5 10 18.5 10 15.5 15.5 10 15.5 1.5 10 1.5 10z"/><circle cx="10" cy="10" r="2.5"/></svg>
                                    <svg data-eye-off class="hidden h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M3 3l14 14M8.2 4.8A8.3 8.3 0 0110 4.5c5.500 0 8.500 5.500 8.500 5.500a14 14 0 01-2.600 3.100M5.600 6.100A13.800 13.800 0 001.500 10S4.500 15.500 10 15.500c1.200 0 2.200-.3 3.100-.7M8.500 8.500a2.500 2.500 0 003 3"/></svg>
                                </button>
                            </div>
                            @error('password')
                                <p id="password-error" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi password</label>
                            <div class="relative mt-1.5">
                                <input id="password_confirmation" type="password" name="password_confirmation" required
                                    autocomplete="new-password" placeholder="Ulangi password"
                                    class="{{ $inputBase }} pr-12 {{ $inputOk }}">
                                <button type="button" data-toggle-password="password_confirmation" aria-controls="password_confirmation" aria-pressed="false" aria-label="Tampilkan konfirmasi password"
                                    class="absolute inset-y-0 right-0 rounded-r-md px-3 text-slate-500 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-teal-600">
                                    <svg data-eye class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M1.5 10S4.5 4.5 10 4.5 18.5 10 18.5 10 15.5 15.5 10 15.5 1.5 10 1.5 10z"/><circle cx="10" cy="10" r="2.5"/></svg>
                                    <svg data-eye-off class="hidden h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M3 3l14 14M8.2 4.8A8.3 8.3 0 0110 4.5c5.500 0 8.500 5.500 8.500 5.500a14 14 0 01-2.600 3.100M5.600 6.100A13.800 13.800 0 001.500 10S4.500 15.500 10 15.500c1.200 0 2.200-.3 3.100-.7M8.500 8.500a2.500 2.500 0 003 3"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="submitBtn"
                        class="w-full rounded-md bg-teal-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-teal-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-teal-600 disabled:cursor-not-allowed disabled:opacity-60">
                        Daftar dan lanjutkan
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-slate-500">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="font-medium text-teal-700 hover:text-teal-800 hover:underline">Masuk</a>
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
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            btn.querySelector('[data-eye]').classList.toggle('hidden', show);
            btn.querySelector('[data-eye-off]').classList.toggle('hidden', !show);
        });
    });

    // Cegah submit ganda
    const form = document.getElementById('registerForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitLabel = submitBtn.textContent.trim();
    form.addEventListener('submit', () => {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Memproses…';
    });
    // Pulihkan tombol saat halaman dimuat dari cache (tombol Kembali)
    window.addEventListener('pageshow', () => {
        submitBtn.disabled = false;
        submitBtn.textContent = submitLabel;
    });

    // Fokus: ke field error bila ada; autofocus hanya di layar lebar agar keyboard HP tidak langsung muncul
    const invalid = document.querySelector('[aria-invalid="true"]');
    const auto = document.querySelector('[data-autofocus]');
    if (invalid) invalid.focus();
    else if (auto && window.matchMedia('(min-width: 1024px)').matches) auto.focus();
</script>
</body>
</html>
