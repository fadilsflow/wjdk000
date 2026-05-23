<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Smart Sprayer IoT' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">

        {{-- Brand --}}
        <a href="{{ route('public.summary') }}" class="flex items-center justify-center gap-3 mb-8">
            <span class="w-10 h-10 rounded-full bg-[color:var(--color-brand)] grid place-items-center text-black font-black text-base">S</span>
            <span class="font-extrabold text-lg text-[color:var(--color-text)]">Smart Sprayer IoT</span>
        </a>

        {{-- Card --}}
        <div class="card p-8">
            {{ $slot }}
        </div>

        <div class="text-center text-xs text-[color:var(--color-text-muted)] mt-6">
            Smart Sprayer IoT — Bawang Merah Brebes
        </div>
    </div>
</body>
</html>
