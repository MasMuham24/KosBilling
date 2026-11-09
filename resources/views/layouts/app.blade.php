<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'KosBilling — Manajemen & Otomasi Tagihan Kos')</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS with custom styling matching dashboard -->
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
        .num { font-variant-numeric: tabular-nums; }
        .no-scrollbar { scrollbar-width: none; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col font-sans antialiased">

    @php
        $userName  = Auth::user()?->name ?? 'Pengelola';
        $userEmail = Auth::user()?->email ?? 'admin@kosbilling.id';
    @endphp

    {{-- Top Navigation Header matching Dashboard --}}
    <header class="sticky top-0 z-40 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">

                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-md bg-teal-700 text-white text-xs font-semibold flex items-center justify-center">KB</div>
                        <span class="text-sm font-semibold text-slate-900 tracking-tight">KosBilling</span>
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold flex items-center justify-center" aria-hidden="true">
                            {{ strtoupper(substr($userName, 0, 1)) }}
                        </div>
                        <div class="hidden lg:block leading-tight">
                            <p class="text-sm font-medium text-slate-900">{{ $userName }}</p>
                            <p class="text-xs text-slate-500">{{ $userEmail }}</p>
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="text-sm text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-md border border-slate-300 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>

            {{-- Secondary Navigation Tabs --}}
            <nav class="no-scrollbar -mx-4 sm:mx-0 px-4 sm:px-0 -mb-px flex gap-6 overflow-x-auto text-sm" aria-label="Navigasi utama">
                <a href="{{ route('dashboard') }}"
                    class="py-3 border-b-2 font-medium whitespace-nowrap {{ request()->routeIs('dashboard') ? 'border-teal-700 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-900' }}">
                    Dashboard
                </a>
                <a href="{{ route('properties.index') }}"
                    class="py-3 border-b-2 font-medium whitespace-nowrap {{ request()->routeIs('properties.*') ? 'border-teal-700 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-900' }}">
                    Properti
                </a>
                <a href="{{ route('rooms.index') }}"
                    class="py-3 border-b-2 font-medium whitespace-nowrap {{ request()->routeIs('rooms.*') ? 'border-teal-700 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-900' }}">
                    Kamar
                </a>
                <a href="{{ route('tenants.index') }}"
                    class="py-3 border-b-2 font-medium whitespace-nowrap {{ request()->routeIs('tenants.*') ? 'border-teal-700 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-900' }}">
                    Penghuni
                </a>
                <a href="{{ route('bills.index') }}"
                    class="py-3 border-b-2 font-medium whitespace-nowrap {{ request()->routeIs('bills.*') ? 'border-teal-700 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-900' }}">
                    Tagihan
                </a>
            </nav>
        </div>
    </header>

    {{-- Main Content Container --}}
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        {{-- Flash Success Alert --}}
        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        {{-- Flash Error Alert --}}
        @if (session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-slate-200 bg-white px-6 py-4 text-center text-xs text-slate-500 mt-auto">
        &copy; {{ date('Y') }} KosBilling · Manajemen dan otomasi tagihan sewa kos
    </footer>

</body>
</html>
