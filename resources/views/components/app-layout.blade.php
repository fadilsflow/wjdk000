@props(['title' => null, 'subbar' => null, 'navLinks' => null, 'userName' => null, 'userRole' => null, 'currentTime' => null])
@php
$navLinks = $navLinks ?? [
    ['route' => 'dashboard', 'label' => 'Dashboard'],
    ['route' => 'history.sensor', 'label' => 'Riwayat', 'children' => [
        ['route' => 'history.sensor', 'label' => 'Data Sensor'],
        ['route' => 'history.spray', 'label' => 'Penyemprotan'],
        ['route' => 'history.notification', 'label' => 'Notifikasi'],
    ]],
    ['route' => 'admin.devices.index', 'label' => 'Konfigurasi'],
];
$userName = $userName ?? 'Petani';
$userRole = $userRole ?? 'Petani';
$currentTime = $currentTime ?? now()->format('d M Y, H:i');
$isHistoryRoute = request()->routeIs('history.*');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Smart Sprayer IoT' }}</title>
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
                <span class="w-8 h-8 rounded-full grid place-items-center bg-[color:var(--color-brand)] text-black font-black text-sm">S</span>
                <span class="font-extrabold text-[15px] text-[color:var(--color-text)]">Smart Sprayer IoT</span>
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden md:flex items-center gap-1">
                @foreach($navLinks as $link)
                    @if(isset($link['children']))
                        <div class="relative group">
                            <button
                                class="nav-pill {{ $isHistoryRoute ? 'is-active' : '' }}">
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

            {{-- User info --}}
            <div class="ml-auto flex items-center gap-3">
                <div class="hidden sm:flex flex-col items-end leading-tight">
                    <span class="text-[color:var(--color-text)] text-sm font-bold">{{ $userName }}</span>
                    <span class="text-[color:var(--color-text-muted)] text-[11px] uppercase tracking-wider">{{ $userRole }}</span>
                </div>
                <span class="text-[color:var(--color-text-muted)] text-xs hidden lg:block">{{ $currentTime }}</span>

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
