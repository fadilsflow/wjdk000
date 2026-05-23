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
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ $head ?? '' }}
</head>
<body class="font-sans antialiased bg-[#111111] text-[#eeeeee]">

    {{-- TOPBAR --}}
    <header class="h-auto bg-[#232323] border-b border-[#151817]">
        <div class="flex items-center flex-wrap gap-y-2 px-6 h-[68px]">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 font-extrabold text-[#43c766] text-lg whitespace-nowrap mr-4">
                <span class="w-8 h-8 rounded-[10px] bg-gradient-to-br from-[#43c766] to-[#2d9650] grid place-items-center text-[#102015] font-black text-sm">S</span>
                Smart Sprayer IoT
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden md:flex h-full items-stretch">
                @foreach($navLinks as $link)
                    @if(isset($link['children']))
                        {{-- Dropdown --}}
                        <div class="relative group">
                            <button
                                    class="px-6 flex items-center text-[13px] font-semibold transition-colors h-full gap-1
                                           {{ $isHistoryRoute ? 'bg-[#353a38] text-white' : 'text-[#b9c0bc] hover:text-white hover:bg-[#2a2e2c]' }}">
                                {{ $link['label'] }}
                                <svg class="w-3 h-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div class="absolute top-full left-0 mt-0 w-48 bg-[#2b2b2b] border border-[#3b3b3b] rounded-lg overflow-hidden shadow-lg z-50 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150">
                                @foreach($link['children'] as $child)
                                    @if(\Illuminate\Support\Facades\Route::has($child['route']))
                                    <a href="{{ route($child['route']) }}"
                                       class="block px-5 py-3 text-sm font-semibold transition-colors border-b border-[#3b3b3b] last:border-0
                                              {{ request()->routeIs($child['route']) ? 'bg-[#353a38] text-white' : 'text-[#b9c0bc] hover:bg-[#353a38] hover:text-white' }}">
                                        {{ $child['label'] }}
                                    </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @else
                        @if(\Illuminate\Support\Facades\Route::has($link['route']))
                        <a href="{{ route($link['route']) }}"
                           class="px-6 flex items-center text-[13px] font-semibold transition-colors
                                  {{ request()->routeIs($link['route']) || request()->routeIs($link['route'].'*')
                                     ? 'bg-[#353a38] text-white'
                                     : 'text-[#b9c0bc] hover:text-white hover:bg-[#2a2e2c]' }}">
                            {{ $link['label'] }}
                        </a>
                        @endif
                    @endif
                @endforeach
            </nav>

            {{-- User info (static dummy) --}}
            <div class="ml-auto flex items-center gap-4">
                <span class="text-[#c8cfcb] text-sm hidden sm:block">
                    {{ $userName }}
                    <span class="capitalize text-xs text-[#999]">{{ $userRole }}</span>
                </span>
                <span class="text-[#666] text-xs hidden sm:block">{{ $currentTime }}</span>
                <button class="md:hidden text-[#999] hover:text-white" x-data @click="$dispatch('toggle-mobile-nav')">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Nav --}}
        <div class="md:hidden border-t border-[#2a2a2a]"
             x-data="{ open: false }"
             @toggle-mobile-nav.window="open = !open"
             x-show="open"
             x-cloak>
            @foreach($navLinks as $link)
                @if(isset($link['children']))
                    <div class="border-b border-[#2a2a2a]">
                        <div class="px-6 py-3 text-sm font-semibold text-[#999] uppercase tracking-wider">{{ $link['label'] }}</div>
                        @foreach($link['children'] as $child)
                            @if(\Illuminate\Support\Facades\Route::has($child['route']))
                            <a href="{{ route($child['route']) }}"
                               class="block pl-10 pr-6 py-3 text-sm font-semibold transition-colors border-b border-[#2a2a2a] last:border-0
                                      {{ request()->routeIs($child['route']) ? 'bg-[#353a38] text-white' : 'text-[#b9c0bc] hover:bg-[#2a2e2c] hover:text-white' }}">
                                {{ $child['label'] }}
                            </a>
                            @endif
                        @endforeach
                    </div>
                @else
                    @if(\Illuminate\Support\Facades\Route::has($link['route']))
                    <a href="{{ route($link['route']) }}"
                       class="block px-6 py-3 text-sm font-semibold border-b border-[#2a2a2a] transition-colors
                              {{ request()->routeIs($link['route']) || request()->routeIs($link['route'].'*')
                                 ? 'bg-[#353a38] text-white'
                                 : 'text-[#b9c0bc] hover:bg-[#2a2e2c] hover:text-white' }}">
                        {{ $link['label'] }}
                    </a>
                    @endif
                @endif
            @endforeach
        </div>
    </header>

    {{-- SUBBAR --}}
    @if($subbar)
        <div class="min-h-[58px] bg-[#242927] flex flex-wrap items-center gap-3 px-6 py-3 shadow-[inset_0_-1px_#151817]">
            {{ $subbar }}
        </div>
    @endif

    {{-- MAIN --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="text-center text-xs text-[#666] py-6 border-t border-[#2a2a2a] mt-8">
        <a href="{{ route('public.summary') }}" class="hover:text-[#43c766] transition-colors">
            📊 Ringkasan Publik
        </a>
        <span class="mx-3">•</span>
        Smart Sprayer IoT — Bawang Merah Brebes
    </footer>

    @stack('scripts')
</body>
</html>
