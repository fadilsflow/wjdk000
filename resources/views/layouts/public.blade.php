<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Monitoring Lahan — Smart Sprayer IoT' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="font-sans antialiased bg-[#111111] text-[#eeeeee]">

    {{-- Minimal public header --}}
    <header class="bg-[#232323] border-b border-[#151817] px-6 py-4 flex items-center gap-3">
        <span class="w-8 h-8 rounded-[10px] bg-gradient-to-br from-[#43c766] to-[#2d9650] grid place-items-center text-[#102015] font-black text-sm">S</span>
        <span class="font-extrabold text-[#43c766] text-lg">Smart Sprayer IoT</span>
        <span class="ml-auto text-xs text-[#999]">Monitoring Publik</span>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="text-center text-xs text-[#666] py-6 border-t border-[#2a2a2a] mt-8">
        Smart Sprayer IoT — Sistem Monitoring Bawang Merah
    </footer>

    @stack('scripts')
</body>
</html>
