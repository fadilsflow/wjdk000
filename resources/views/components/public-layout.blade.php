@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Monitoring Lahan — Smart Sprayer IoT' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ $head ?? '' }}
</head>
<body class="font-sans antialiased">
    <header class="bg-[color:var(--color-bg-surface)] px-6 py-4 flex items-center gap-3">
        <span class="w-8 h-8 rounded-full bg-[color:var(--color-brand)] grid place-items-center text-black font-black text-sm">S</span>
        <span class="font-extrabold text-[15px] text-[color:var(--color-text)]">Smart Sprayer IoT</span>
        <span class="ml-auto text-[11px] font-bold text-[color:var(--color-text-muted)] uppercase tracking-wider">Monitoring Publik</span>
        @if(\Illuminate\Support\Facades\Route::has('login'))
            <a href="{{ route('login') }}" class="btn-primary btn-sm hidden sm:inline-flex">Login</a>
        @endif
    </header>

    <main class="min-h-[calc(100vh-180px)]">
        {{ $slot }}
    </main>

    <footer class="text-center text-xs text-[color:var(--color-text-muted)] py-6 border-t border-[color:var(--color-bg-surface)] mt-8">
        Smart Sprayer IoT — Sistem Monitoring Bawang Merah
    </footer>

    {{ $scripts ?? '' }}
</body>
</html>
