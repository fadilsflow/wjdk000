# Smart Sprayer IoT Web

Website monitoring + kendali Smart Sprayer IoT untuk pengendalian hama kutu pada tanaman bawang merah di Brebes.

## Tech Stack

| Layer         | Teknologi                                        |
| ------------- | ------------------------------------------------ | --- |
| Framework     | Laravel 13 (PHP 8.2+)                            |
| Database      | SQLite (default) / MySQL                         |
| ORM           | Eloquent ORM                                     |
| Auth          | Laravel Breeze (session-based) + role middleware |
| Frontend      | Blade + Alpine.js                                |
| Styling       | Tailwind CSS v4 (via `@tailwindcss/vite`)        |
| Charts        | Chart.js                                         | vel |
| Notifications | WhatsApp Gateway/API (via HTTP client)           |
| API           | REST API                                         |

## Quick Start

```bash
# 1. Clone
git clone https://github.com/fadilsflow/wjdk000
cd wjdk000

# 2. Install PHP dependencies
composer install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Setup database (SQLite — default)
touch database/database.sqlite
php artisan migrate

# 5. (Opsional) Buat user test
php artisan tinker --execute="\App\Models\User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('password')]);"

# 6. Install frontend dependencies
bun install    # atau npm install
bun run build  # atau npm run build

# 7. Jalankan
php artisan serve
# Terminal lain: bun run dev (untuk hot-reload CSS/JS)
```

## Browser E2E

Project ini sekarang punya suite Playwright yang bisa dijalankan ulang per skenario.

Prasyarat:

- `ADMIN_SEED_PASSWORD` terisi
- `DEVICE_SEED_API_KEY` terisi tetap di `.env`
- database sudah `migrate --seed`

Command:

```bash
npm run test:e2e
npm run test:e2e:public-auth
npm run test:e2e:sprayer
```

Akses `http://localhost:8000`.

### Pakai MySQL

Kalo mau pake MySQL, edit `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_sprayer
DB_USERNAME=root
DB_PASSWORD=
```

Lalu:

```bash
php artisan migrate
```

## User Role

| Role   | Akses                                                    |
| ------ | -------------------------------------------------------- |
| Admin  | Semua fitur: manajemen user, konfigurasi alat, threshold |
| Petani | Dashboard, riwayat, kontrol sprayer, notifikasi          |
| Publik | Halaman ringkasan publik (tanpa login)                   |

## Fitur

- Login/Register Admin & Petani
- Dashboard sensor real-time (suhu, kelembapan udara/tanah, status hujan)
- Status kondisi: Normal / Waspada / Kritis
- Kontrol sprayer manual & otomatis
- Riwayat sensor, penyemprotan, notifikasi
- Notifikasi WhatsApp
- Mode gelap/terang
- Halaman publik

## API Endpoint

```txt
POST /api/sensor-readings
GET  /api/devices/{device}/command
POST /devices/{device}/sprayer/on
POST /devices/{device}/sprayer/off
POST /devices/{device}/mode
```

## IoT Simulator

```bash
php artisan serve
```

Lalu buka:

```txt
prototype/iot-trigger.html
```

Default target: `http://127.0.0.1:8000/api/sensor-readings`

Trigger: Normal, Waspada, Kritis, Hujan, Random Data, Loop.

## Docs

- `docs/proposal.md` — proposal web
- `docs/proposal-iot.md` — proposal IoT
- `docs/prd.md` — product requirements
- `docs/architecture.md` — arsitektur & folder convention
- `docs/domain-rules.md` — business rules & entities
- `docs/operations.md` — deployment & ops
- `docs/flash-esp32-laravel.md` — tutorial flash ESP32 & server Laravel

## Catatan

IoT asli belum tersedia. Development web pakai simulator dulu. Semua fitur ikut `docs/prd.md`.
