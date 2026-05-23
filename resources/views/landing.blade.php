<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Smart Sprayer IoT — Monitoring Bawang Merah Brebes</title>
    <meta name="description" content="Sistem monitoring dan penyemprotan otomatis berbasis IoT untuk lahan bawang merah di Brebes. Data sensor real-time terbuka untuk publik.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">

    @php
        $cs = $sensor['condition_status'] ?? 'normal';
        $pillClass = match($cs) {
            'kritis'  => 'status-pill-kritis',
            'waspada' => 'status-pill-waspada',
            default   => 'status-pill-normal',
        };
        $dotColor = match($cs) {
            'kritis'  => 'var(--color-negative)',
            'waspada' => 'var(--color-warning)',
            default   => 'var(--color-brand)',
        };
    @endphp

    {{-- NAV (overlay hero) --}}
    <header class="absolute top-0 inset-x-0 z-20 px-4 sm:px-8 py-5 flex items-center gap-3">
        <a href="/" class="flex items-center gap-2.5 mr-auto">
            <span class="w-9 h-9 rounded-full bg-[color:var(--color-brand)] grid place-items-center text-black font-black">S</span>
            <span class="font-extrabold text-[color:var(--color-text)] tracking-tight">Smart Sprayer IoT</span>
        </a>
        <nav class="hidden md:flex items-center gap-1">
            <a href="#data" class="nav-pill">Data Publik</a>
            <a href="#tentang" class="nav-pill">Tentang</a>
        </nav>
        @if(\Illuminate\Support\Facades\Route::has('login'))
            <a href="{{ route('login') }}" class="btn-primary btn-sm">Login</a>
        @else
            <a href="{{ route('dashboard') }}" class="btn-primary btn-sm">Login</a>
        @endif
    </header>

    {{-- HERO --}}
    <section class="relative min-h-[92vh] flex items-end overflow-hidden">
        <img src="{{ asset('hero-img.avif') }}" alt="Lahan bawang merah Brebes"
             class="absolute inset-0 w-full h-full object-cover"
             loading="eager" fetchpriority="high">
        <div class="absolute inset-0 pointer-events-none"
             style="background: linear-gradient(180deg, rgba(18,18,18,0.55) 0%, rgba(18,18,18,0.55) 30%, rgba(18,18,18,0.85) 70%, #121212 100%);"></div>

        <div class="relative z-10 w-full max-w-6xl mx-auto px-6 sm:px-8 pb-20 pt-32">
            {{-- Live status pill --}}
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[rgba(18,18,18,0.7)] border border-[rgba(255,255,255,0.08)] backdrop-blur-sm">
                <span class="relative flex w-2 h-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-60" style="background: {{ $dotColor }};"></span>
                    <span class="relative inline-flex w-2 h-2 rounded-full" style="background: {{ $dotColor }};"></span>
                </span>
                <span class="text-xs font-bold uppercase tracking-wider" style="color: {{ $dotColor }};">{{ $cs }}</span>
                <span class="text-xs text-[color:var(--color-text-muted)]">· diperbarui {{ \Carbon\Carbon::parse($sensor['recorded_at'])->format('d M Y H:i') }}</span>
            </div>

            <h1 class="mt-6 text-5xl sm:text-6xl md:text-7xl font-black leading-[1.05] tracking-tight text-[color:var(--color-text)]">
                Smart Sprayer<br>
                <span class="text-[color:var(--color-brand)]">Bawang Merah Brebes</span>
            </h1>

            <p class="mt-6 max-w-xl text-base sm:text-lg text-[color:var(--color-text-near)] leading-relaxed">
                Sistem monitoring dan penyemprotan otomatis berbasis sensor lingkungan. Data publik diperbarui langsung dari perangkat IoT di lahan petani.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#data" class="btn-primary">Lihat Data Publik</a>
                @if(\Illuminate\Support\Facades\Route::has('login'))
                    <a href="{{ route('login') }}" class="btn-outline">Login Petani</a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn-outline">Buka Dashboard</a>
                @endif
            </div>
        </div>

        {{-- Scroll hint --}}
        <a href="#data" class="hidden sm:flex absolute bottom-6 left-1/2 -translate-x-1/2 z-10 flex-col items-center gap-1 text-[color:var(--color-text-muted)] hover:text-[color:var(--color-text)] transition-colors" aria-label="Gulir ke data">
            <span class="text-[10px] uppercase tracking-widest font-bold">Gulir</span>
            <svg class="w-4 h-4 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
            </svg>
        </a>
    </section>

    {{-- DATA SECTION --}}
    <section id="data" class="max-w-6xl mx-auto px-6 sm:px-8 py-16 sm:py-20 scroll-mt-8">
        <div class="flex items-end justify-between flex-wrap gap-4 mb-8">
            <div>
                <div class="text-[11px] uppercase tracking-widest font-bold text-[color:var(--color-brand)]">Live</div>
                <h2 class="mt-1 text-3xl sm:text-4xl font-extrabold text-[color:var(--color-text)]">Data Terkini</h2>
            </div>
            <div class="text-xs text-[color:var(--color-text-muted)]">
                <span class="uppercase tracking-wider font-bold">Diperbarui</span>
                · {{ \Carbon\Carbon::parse($sensor['recorded_at'])->format('d M Y, H:i') }}
            </div>
        </div>

        {{-- Hero status card --}}
        <div class="card p-8 mb-6 text-center">
            <div class="text-xs uppercase tracking-widest font-bold text-[color:var(--color-text-muted)] mb-4">
                Status Lingkungan
            </div>
            <span class="status-pill {{ $pillClass }} text-base px-6 py-3">{{ $cs }}</span>
            <p class="mt-4 text-sm text-[color:var(--color-text-muted)] max-w-md mx-auto">
                @if($cs === 'kritis')
                    Tanah kering, kondisi memenuhi aturan penyemprotan otomatis.
                @elseif($cs === 'waspada')
                    Kondisi lingkungan perlu diperhatikan.
                @else
                    Kondisi lingkungan dalam batas aman.
                @endif
            </p>
        </div>

        {{-- 4 sensor cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="card p-5">
                <div class="text-[11px] uppercase tracking-widest font-bold text-[color:var(--color-text-muted)]">Suhu Udara</div>
                <div class="mt-2 text-4xl font-black text-[color:var(--color-text)]">
                    {{ $sensor['temperature'] }}<span class="text-xl text-[color:var(--color-text-muted)] font-bold">°C</span>
                </div>
                <div class="mt-2 text-xs text-[color:var(--color-text-muted)]">Sensor BME280</div>
            </div>
            <div class="card p-5">
                <div class="text-[11px] uppercase tracking-widest font-bold text-[color:var(--color-text-muted)]">Kelemb. Udara</div>
                <div class="mt-2 text-4xl font-black text-[color:var(--color-text)]">
                    {{ $sensor['air_humidity'] }}<span class="text-xl text-[color:var(--color-text-muted)] font-bold">%</span>
                </div>
                <div class="mt-2 text-xs text-[color:var(--color-text-muted)]">Sensor BME280</div>
            </div>
            <div class="card p-5">
                <div class="text-[11px] uppercase tracking-widest font-bold text-[color:var(--color-text-muted)]">Kelemb. Tanah</div>
                <div class="mt-2 text-4xl font-black text-[color:var(--color-text)]">
                    {{ $sensor['soil_moisture'] }}<span class="text-xl text-[color:var(--color-text-muted)] font-bold">%</span>
                </div>
                <div class="mt-2 text-xs text-[color:var(--color-text-muted)]">Soil moisture</div>
            </div>
            <div class="card p-5">
                <div class="text-[11px] uppercase tracking-widest font-bold text-[color:var(--color-text-muted)]">Hujan</div>
                <div class="mt-2 text-2xl font-black" style="color: var(--color-info);">
                    {{ ($sensor['rain_status'] ?? '') === 'rain' ? 'Hujan' : 'Cerah' }}
                </div>
                <div class="mt-2 text-xs text-[color:var(--color-text-muted)]">Sensor hujan</div>
            </div>
        </div>

        <p class="mt-6 text-xs text-[color:var(--color-text-muted)]">
            Data bersifat informatif. Untuk kontrol alat, silakan login.
        </p>
    </section>

    {{-- TENTANG SECTION --}}
    <section id="tentang" class="border-t border-[color:var(--color-bg-surface)]">
        <div class="max-w-6xl mx-auto px-6 sm:px-8 py-16 sm:py-20 scroll-mt-8">
            <div class="text-[11px] uppercase tracking-widest font-bold text-[color:var(--color-brand)] mb-2">Tentang</div>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-[color:var(--color-text)] mb-8">Bagaimana Sistem Bekerja</h2>

            <div class="grid md:grid-cols-2 gap-10 items-start">
                <p class="text-base sm:text-lg text-[color:var(--color-text-near)] leading-relaxed">
                    Smart Sprayer IoT memantau kondisi lingkungan lahan bawang merah di Brebes dan mengaktifkan penyemprotan otomatis ketika tanah kering dan tidak hujan. Sistem dirancang untuk mendukung pengendalian hama kutu pada bawang merah dengan mempertimbangkan parameter musim hujan dan kemarau.
                </p>

                <ul class="space-y-5">
                    <li class="flex gap-4">
                        <span class="shrink-0 w-10 h-10 rounded-full bg-[color:var(--color-bg-elevated)] grid place-items-center text-[color:var(--color-brand)] font-black text-sm">01</span>
                        <div>
                            <div class="font-extrabold text-[color:var(--color-text)]">Sensor Lingkungan</div>
                            <div class="text-sm text-[color:var(--color-text-muted)] mt-1">BME280 mengukur suhu &amp; kelembapan udara. Soil moisture &amp; sensor hujan melengkapi data lahan.</div>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="shrink-0 w-10 h-10 rounded-full bg-[color:var(--color-bg-elevated)] grid place-items-center text-[color:var(--color-brand)] font-black text-sm">02</span>
                        <div>
                            <div class="font-extrabold text-[color:var(--color-text)]">Penyemprotan Otomatis</div>
                            <div class="text-sm text-[color:var(--color-text-muted)] mt-1">Sprayer aktif bila tanah kering dan tidak hujan. Threshold dapat disesuaikan oleh Admin.</div>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="shrink-0 w-10 h-10 rounded-full bg-[color:var(--color-bg-elevated)] grid place-items-center text-[color:var(--color-brand)] font-black text-sm">03</span>
                        <div>
                            <div class="font-extrabold text-[color:var(--color-text)]">Notifikasi WhatsApp</div>
                            <div class="text-sm text-[color:var(--color-text-muted)] mt-1">Peringatan WhatsApp dikirim saat kondisi kritis, sprayer dimulai/berhenti, atau hujan terdeteksi.</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="border-t border-[color:var(--color-bg-surface)]">
        <div class="max-w-6xl mx-auto px-6 sm:px-8 py-8 flex flex-wrap items-center gap-3 text-xs text-[color:var(--color-text-muted)]">
            <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-full bg-[color:var(--color-brand)] grid place-items-center text-black font-black text-[10px]">S</span>
                <span class="font-bold text-[color:var(--color-text-near)]">Smart Sprayer IoT</span>
            </div>
            <span class="text-[color:var(--color-border)]">/</span>
            <span>Bawang Merah Brebes</span>
            <div class="ml-auto flex items-center gap-4">
                <a href="#data" class="hover:text-[color:var(--color-text)] transition-colors">Data Publik</a>
                <a href="#tentang" class="hover:text-[color:var(--color-text)] transition-colors">Tentang</a>
            </div>
        </div>
    </footer>

</body>
</html>
