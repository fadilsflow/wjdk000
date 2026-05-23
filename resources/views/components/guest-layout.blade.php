<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
</head>
<body class="font-sans antialiased min-h-screen flex items-center justify-center px-4 py-10">

    {{-- Theme toggle (corner) --}}
    <button type="button"
            x-data="{ light: document.documentElement.classList.contains('light') }"
            @click="light = !light; document.documentElement.classList.toggle('light', light); try { localStorage.setItem('theme', light ? 'light' : 'dark'); } catch (e) {}"
            class="btn-circle absolute top-4 right-4"
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

    <div class="w-full max-w-md">

        {{-- Brand --}}
        <a href="{{ route('home') }}" class="flex items-center justify-center mb-8">
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
