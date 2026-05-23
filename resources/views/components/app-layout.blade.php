@props(['title' => null, 'subbar' => null, 'navLinks' => null, 'userName' => null, 'userRole' => null, 'currentTime' => null])
@php
$navLinks = $navLinks ?? [
    ['route' => 'dashboard', 'label' => 'Dashboard'],
    ['route' => 'sprayer.control', 'label' => 'Kontrol Sprayer'],
    ['route' => 'history.sensor', 'label' => 'Riwayat', 'children' => [
        ['route' => 'history.sensor', 'label' => 'Data Sensor'],
        ['route' => 'history.spray', 'label' => 'Penyemprotan'],
        ['route' => 'history.notification', 'label' => 'Notifikasi'],
    ]],
    ['route' => 'admin.devices.index', 'label' => 'Pengaturan', 'group' => 'admin', 'children' => [
        ['route' => 'admin.devices.index', 'label' => 'Konfigurasi Alat'],
        ['route' => 'admin.users.index', 'label' => 'Pengguna'],
        ['route' => 'admin.whatsapp.index', 'label' => 'WhatsApp'],
    ]],
];
$userName = $userName ?? 'Petani Demo';
$userRole = $userRole ?? 'Petani';
$currentTime = $currentTime ?? now()->format('d M Y, H:i');
$isHistoryRoute = request()->routeIs('history.*');
$isAdminRoute = request()->routeIs('admin.*');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Smart Sprayer IoT' }}</title>
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('theme');
                var prefersLight = window.matchMedia('(prefers-color-scheme: light)').matches;
                if (stored === 'light' || (!stored && prefersLight)) {
                    document.documentElement.classList.add('light');
                }
            } catch (e) {}
        })();
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ $head ?? '' }}
</head>
<body class="font-sans antialiased">

    {{-- TOPBAR --}}
    <header class="bg-[color:var(--color-bg-surface)]">
        <div class="flex items-center flex-wrap gap-y-2 px-6 h-[64px]">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 mr-6 whitespace-nowrap">
                <span class="font-extrabold text-xl text-[color:var(--color-text)]">Smart Sprayer</span>
            </a>
            {{-- Desktop Nav --}}
            <nav class="hidden md:flex items-center gap-1">
                @foreach($navLinks as $link)
                    @if(isset($link['children']))
                        @php $groupActive = ($link['group'] ?? null) === 'admin' ? $isAdminRoute : $isHistoryRoute; @endphp
                        <div class="relative group">
                            <button
                                class="nav-pill {{ $groupActive ? 'is-active' : '' }}">
                                {{ $link['label'] }}
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div class="absolute top-full left-0 mt-1 w-52 bg-[color:var(--color-bg-card-2)] rounded-lg overflow-hidden shadow-dialog z-50 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150">
                                @foreach($link['children'] as $child)
                                    @if(\Illuminate\Support\Facades\Route::has($child['route']))
                                    <a href="{{ route($child['route']) }}"
                                       class="block px-5 py-3 text-sm font-semibold transition-colors
                                              {{ request()->routeIs($child['route']) ? 'bg-[color:var(--color-bg-elevated)] text-white' : 'text-[color:var(--color-text-muted)] hover:bg-[color:var(--color-bg-elevated)] hover:text-white' }}">
                                        {{ $child['label'] }}
                                    </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @else
                        @if(\Illuminate\Support\Facades\Route::has($link['route']))
                        <a href="{{ route($link['route']) }}"
                           class="nav-pill {{ request()->routeIs($link['route']) || request()->routeIs($link['route'].'*') ? 'is-active' : '' }}">
                            {{ $link['label'] }}
                        </a>
                        @endif
                    @endif
                @endforeach
            </nav>

            {{-- Right: clock + user dropdown + mobile hamburger --}}
            <div class="ml-auto flex items-center gap-3">
                <span class="text-[color:var(--color-text-muted)] text-xs hidden lg:block">{{ $currentTime }}</span>

                {{-- Theme toggle --}}
                <button type="button"
                        x-data="{ light: document.documentElement.classList.contains('light') }"
                        @click="light = !light; document.documentElement.classList.toggle('light', light); try { localStorage.setItem('theme', light ? 'light' : 'dark'); } catch (e) {}"
                        class="btn-circle"
                        style="width:2.25rem;height:2.25rem;"
                        :aria-label="light ? 'Aktifkan mode gelap' : 'Aktifkan mode terang'"
                        title="Ganti tema">
                    <svg x-show="!light" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                    <svg x-show="light" x-cloak class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0-5a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0V3a1 1 0 0 1 1-1zm0 18a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0v-1a1 1 0 0 1 1-1zM4 13H3a1 1 0 1 1 0-2h1a1 1 0 1 1 0 2zm17 0h-1a1 1 0 1 1 0-2h1a1 1 0 1 1 0 2zM5.64 5.64a1 1 0 0 1 1.41 0l.71.71a1 1 0 1 1-1.41 1.41l-.71-.71a1 1 0 0 1 0-1.41zm12.02 12.02a1 1 0 0 1 1.41 0l.71.71a1 1 0 0 1-1.41 1.41l-.71-.71a1 1 0 0 1 0-1.41zM5.64 18.36a1 1 0 0 1 0-1.41l.71-.71a1 1 0 1 1 1.41 1.41l-.71.71a1 1 0 0 1-1.41 0zm12.02-12.02a1 1 0 0 1 0-1.41l.71-.71a1 1 0 0 1 1.41 1.41l-.71.71a1 1 0 0 1-1.41 0z"/>
                    </svg>
                </button>

                {{-- User dropdown (desktop & tablet) --}}
                <div class="hidden sm:block relative" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" @click="open = !open"
                            class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-full hover:bg-[color:var(--color-bg-elevated)] transition-colors"
                            aria-haspopup="true" :aria-expanded="open">
                        <span class="w-8 h-8 rounded-full bg-[color:var(--color-bg-elevated)] grid place-items-center text-[color:var(--color-text)] font-extrabold text-sm">
                            {{ strtoupper(substr($userName, 0, 1)) }}
                        </span>
                        <span class="flex flex-col items-start leading-tight">
                            <span class="text-[color:var(--color-text)] text-sm font-bold">{{ $userName }}</span>
                            <span class="text-[color:var(--color-text-muted)] text-[10px] uppercase tracking-wider">{{ $userRole }}</span>
                        </span>
                        <svg class="w-3 h-3 text-[color:var(--color-text-muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 top-full mt-2 w-56 bg-[color:var(--color-bg-card-2)] rounded-lg overflow-hidden shadow-dialog z-50 origin-top-right"
                         style="display: none;">
                        <div class="px-4 py-3 border-b border-[color:var(--color-bg-elevated)]">
                            <div class="text-[color:var(--color-text)] text-sm font-bold truncate">{{ $userName }}</div>
                            <div class="text-[color:var(--color-text-muted)] text-xs">{{ $userRole }}</div>
                        </div>
                        @if(\Illuminate\Support\Facades\Route::has('profile.edit'))
                            <a href="{{ route('profile.edit') }}"
                               class="block px-4 py-2.5 text-sm font-semibold text-[color:var(--color-text-muted)] hover:bg-[color:var(--color-bg-elevated)] hover:text-white transition-colors">
                                Profil
                            </a>
                        @endif
                        @if(\Illuminate\Support\Facades\Route::has('logout'))
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="block w-full text-left px-4 py-2.5 text-sm font-semibold text-[color:var(--color-text-muted)] hover:bg-[color:var(--color-bg-elevated)] hover:text-white transition-colors border-t border-[color:var(--color-bg-elevated)]">
                                    Keluar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <button type="button" class="md:hidden btn-circle" style="width:2.5rem;height:2.5rem;" x-data @click="$dispatch('toggle-mobile-nav')" aria-label="Menu">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Nav --}}
        <div class="md:hidden"
             x-data="{ open: false }"
             @toggle-mobile-nav.window="open = !open"
             x-show="open"
             x-cloak>
            <div class="px-3 pb-3 pt-1 space-y-1">
            @foreach($navLinks as $link)
                @if(isset($link['children']))
                    <div class="pt-2">
                        <div class="px-3 py-1 text-[11px] font-bold text-[color:var(--color-text-muted)] uppercase tracking-wider">{{ $link['label'] }}</div>
                        @foreach($link['children'] as $child)
                            @if(\Illuminate\Support\Facades\Route::has($child['route']))
                            <a href="{{ route($child['route']) }}"
                               class="block px-3 py-2.5 ml-2 text-sm font-bold rounded-full transition-colors
                                      {{ request()->routeIs($child['route']) ? 'bg-[color:var(--color-bg-elevated)] text-white' : 'text-[color:var(--color-text-muted)] hover:bg-[color:var(--color-bg-elevated)] hover:text-white' }}">
                                {{ $child['label'] }}
                            </a>
                            @endif
                        @endforeach
                    </div>
                @else
                    @if(\Illuminate\Support\Facades\Route::has($link['route']))
                    <a href="{{ route($link['route']) }}"
                       class="block px-3 py-2.5 text-sm font-bold rounded-full transition-colors
                              {{ request()->routeIs($link['route']) || request()->routeIs($link['route'].'*') ? 'bg-[color:var(--color-bg-elevated)] text-white' : 'text-[color:var(--color-text-muted)] hover:bg-[color:var(--color-bg-elevated)] hover:text-white' }}">
                        {{ $link['label'] }}
                    </a>
                    @endif
                @endif
            @endforeach

            {{-- Mobile user section --}}
            <div class="border-t border-[color:var(--color-bg-elevated)] mt-3 pt-3">
                <div class="px-3 py-1 text-[11px] font-bold text-[color:var(--color-text-muted)] uppercase tracking-wider">Akun</div>
                <div class="px-3 py-2">
                    <div class="text-[color:var(--color-text)] text-sm font-bold">{{ $userName }}</div>
                    <div class="text-[color:var(--color-text-muted)] text-xs">{{ $userRole }}</div>
                </div>
                @if(\Illuminate\Support\Facades\Route::has('profile.edit'))
                    <a href="{{ route('profile.edit') }}"
                       class="block px-3 py-2.5 ml-2 text-sm font-bold rounded-full text-[color:var(--color-text-muted)] hover:bg-[color:var(--color-bg-elevated)] hover:text-white transition-colors">
                        Profil
                    </a>
                @endif
                @if(\Illuminate\Support\Facades\Route::has('logout'))
                    <form method="POST" action="{{ route('logout') }}" class="ml-2">
                        @csrf
                        <button type="submit"
                                class="block w-full text-left px-3 py-2.5 text-sm font-bold rounded-full text-[color:var(--color-text-muted)] hover:bg-[color:var(--color-bg-elevated)] hover:text-white transition-colors">
                            Keluar
                        </button>
                    </form>
                @endif
            </div>
            </div>
        </div>
    </header>

    {{-- SUBBAR --}}
    @if($subbar)
        <div class="min-h-[64px] bg-[color:var(--color-bg-base)] flex flex-wrap items-center gap-3 px-6 py-4 border-b border-[color:var(--color-bg-surface)]">
            {{ $subbar }}
        </div>
    @endif

    {{-- MAIN --}}
    <main class="min-h-[calc(100vh-200px)]">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="text-center text-xs text-[color:var(--color-text-muted)] py-6 mt-8 border-t border-[color:var(--color-bg-surface)]">
        <a href="{{ route('home') }}" class="hover:text-[color:var(--color-brand)] transition-colors font-semibold">
            Beranda Publik
        </a>
        <span class="mx-3 text-[color:var(--color-border)]">/</span>
        <span>Smart Sprayer IoT — Bawang Merah Brebes</span>
    </footer>

    @stack('scripts')
</body>
</html>
