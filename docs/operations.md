# Operations — Smart Sprayer IoT Web

## Environment Variables

Semua nilai sensitif WAJIB menggunakan env variable. Template `.env.example` wajib diperbarui setiap ada variabel baru.

Variabel yang dibutuhkan project ini:

```dotenv
APP_NAME="Smart Sprayer IoT"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_URL=
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_sprayer
DB_TEST_DATABASE=smart_sprayer_test
DB_USERNAME=root
DB_PASSWORD=
MYSQL_ATTR_SSL_CA=

QUEUE_CONNECTION=database
CACHE_STORE=file
SESSION_DRIVER=file

# WhatsApp Gateway/API
WHATSAPP_GATEWAY_URL=
WHATSAPP_GATEWAY_TOKEN=
# Token untuk self-hosted gateway Node.js; pada Docker boleh isi salah satu dari token ini atau WHATSAPP_GATEWAY_TOKEN.
GATEWAY_PORT=3000
GATEWAY_SECRET_TOKEN=
WHATSAPP_AUTH_DATA_PATH=
# Fallback saja — nomor pengirim aktif dideteksi otomatis dari gateway saat WhatsApp terhubung
WHATSAPP_SENDER_NUMBER=

# Docker runtime toggles
RUN_MIGRATIONS=false
RUN_SEEDERS=false
RUN_SEEDERS_ONCE=false
RUN_QUEUE_WORKER=true
RUN_SCHEDULER=true
RUN_OPTIMIZE=true
MIGRATION_RETRIES=30

# Seed Admin
ADMIN_SEED_NAME="Admin Smart Sprayer"
ADMIN_SEED_EMAIL=admin@smartsprayer.test
ADMIN_SEED_PASSWORD=change-me
ADMIN_SEED_PHONE=
```

> Variabel baru wajib ditambahkan ke `.env.example` segera setelah ditambahkan ke `.env`.

`DB_TEST_DATABASE` dipakai untuk suite automated test. Nilai ini harus menunjuk schema MySQL terpisah dari database aplikasi utama karena test harness menjalankan `migrate:fresh` pada schema test.

---

## Security

### Web Access

- Semua halaman web terbuka tanpa login (dashboard, sprayer, riwayat, admin)
- Halaman landing (`/`) menampilkan ringkasan publik non-sensitif

### IoT Device Auth

- ESP32 mengirim `api_key` di request body (POST) atau header `X-Api-Key` (GET)
- Middleware `AuthenticateDevice` memverifikasi key sebelum proses request
- `api_key` tidak boleh hardcoded — disimpan di tabel `devices`

### CSRF & Rate Limiting

- CSRF protection aktif untuk semua web route (default Laravel)
- API route (`routes/api.php`) dikecualikan dari CSRF secara default
- IoT endpoint: `throttle:60,1` (60 req/menit per IP)

---

## Logging & Audit

### Application Logs

- Lokasi: `storage/logs/laravel.log`
- Log level: `debug` (development), `error` (production)
- Gunakan `Log::info()` untuk event penting (sprayer on/off, notifikasi terkirim)
- Gunakan `Log::error()` untuk error system (WhatsApp gagal, device tidak ditemukan)

### Business Audit Trail

- **spray_logs** — Setiap perubahan status sprayer wajib dicatat (trigger_type, status, reason, created_by)
- **notification_logs** — Setiap pengiriman WhatsApp wajib dicatat (type, recipient, message, status, sent_at)

---

## Development Commands

```bash
# Setup awal
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan serve
npm run dev

# Artisan generators yang sering dipakai
php artisan make:model NamaModel -mc       # model + migration + controller
php artisan make:request NamaRequest       # form request
php artisan make:middleware NamaMiddleware # middleware
php artisan make:seeder NamaSeeder         # seeder

# Database
php artisan migrate                        # jalankan migration baru
php artisan migrate:rollback              # rollback satu batch
php artisan migrate:fresh --seed          # reset + seed (dev only)

# Queue
php artisan queue:work                    # jalankan worker

# Testing
php artisan test                          # semua test
php artisan test --filter NamaTest        # test spesifik
npm run test:e2e                          # semua browser E2E
npm run test:e2e:sprayer                 # Sprint 6 kontrol sprayer

# Cache
php artisan optimize:clear               # bersihkan semua cache
php artisan optimize                     # cache untuk production
```

## Browser E2E

Suite browser memakai Playwright dan dapat dijalankan berulang per skenario.

Prasyarat:

- database berisi seed user admin, petani, dan device
- `ADMIN_SEED_PASSWORD` terisi
- `DEVICE_SEED_API_KEY` terisi tetap agar skenario IoT bisa dipanggil ulang dengan konsisten
- jalankan `npm install` setelah perubahan dependency frontend

Command yang tersedia:

```bash
npm run test:e2e
npm run test:e2e:sprayer
npm run test:e2e:headed
```

Catatan:

- Playwright config otomatis menjalankan `php artisan serve --host=127.0.0.1 --port=8000` bila app belum aktif
- browser suite memakai `requestSubmit()` pada form sprayer agar stabil terhadap Turbo/Alpine behavior
- hasil report HTML disimpan di folder `playwright-report/`

---

## WhatsApp Integration

Config sensitif dibaca dari `.env` via `config/services.php`. Semua nilai sensitif wajib dari env, tidak ada hardcode.

### Self-Hosted WhatsApp Gateway (whatsapp-web.js)

Project ini menyertakan gateway WhatsApp lokal mandiri di folder `/whatsapp-gateway` berbasis Node.js dan Puppeteer.

**Cara Menjalankan Gateway:**
1. Masuk ke folder: `cd whatsapp-gateway`
2. Konfigurasi file `.env` di dalam folder tersebut (tentukan `PORT` dan `GATEWAY_SECRET_TOKEN`).
3. Jalankan `npm install` untuk mengunduh package.
4. Jalankan `npm start` atau `node server.js`.
5. **Scan QR Code langsung dari halaman admin** (`/admin/whatsapp`) — QR ditampilkan otomatis di UI jika gateway aktif tapi belum terhubung.
   - QR juga tetap muncul di terminal sebagai alternatif.
6. Tekan `Ctrl+C` untuk menghentikan gateway secara bersih (*graceful shutdown* otomatis menutup Chrome).

**Konfigurasi di Laravel (.env):**
- `WHATSAPP_GATEWAY_URL` = `http://localhost:3000/send`
- `WHATSAPP_GATEWAY_TOKEN` = `<GATEWAY_SECRET_TOKEN>`
- `WHATSAPP_SENDER_NUMBER` = (opsional — fallback saja, nomor aktif dideteksi otomatis dari gateway)

Pengaturan non-sensitif yang dapat diubah Admin disimpan di tabel `whatsapp_settings`:
- `recipient_phone`
- template `critical_condition`
- template `spray_start`
- template `spray_stop`
- template `rain_detected`

**Notification types yang wajib dikirim:**

| Event                     | Tipe Notifikasi        |
|---------------------------|------------------------|
| Kondisi kritis            | `critical_condition`   |
| Sprayer mulai             | `spray_start`          |
| Sprayer berhenti          | `spray_stop`           |
| Hujan terdeteksi (auto)   | `rain_detected`        |

Setiap pengiriman, apapun hasilnya (`sent` atau `failed`), wajib dicatat ke `notification_logs`.

---

## Deployment Notes

- File `.env` wajib ada di `.gitignore`
- Set `APP_ENV=production` dan `APP_DEBUG=false` di server
- Jalankan `php artisan optimize` sebelum deploy
- Pastikan `storage/` dan `bootstrap/cache/` writable
- Setup cron untuk Laravel Scheduler jika deploy tanpa Docker:
  ```
  * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
  ```

## Docker + GHCR Deployment

Image production dibangun dari `Dockerfile` dan berisi Laravel, Apache/PHP, queue worker, scheduler, Chromium, dan self-hosted `whatsapp-gateway` dalam satu container. Workflow `.github/workflows/docker-ghcr.yml` otomatis publish ke GitHub Container Registry (`ghcr.io/fadilsflow/wjdk000`) saat push ke branch default atau tag `v*.*.*`. Jika package GHCR masih private, ubah visibility package menjadi public di GitHub Packages atau login dulu dengan `docker login ghcr.io`.

### Jalankan dari GHCR (1 container)

```bash
docker compose -f compose.ghcr.yml up -d
```

Default compose memakai SQLite di volume `app-storage` agar bisa langsung dicoba satu container. Untuk production MySQL, override env berikut saat menjalankan compose:

```dotenv
APP_IMAGE=ghcr.io/fadilsflow/wjdk000:latest
APP_URL=https://domain-anda.example
APP_KEY=base64:isi-dengan-key-production
DB_CONNECTION=mysql
DB_HOST=host-mysql
DB_PORT=3306
DB_DATABASE=smart_sprayer
DB_USERNAME=smart_sprayer
DB_PASSWORD=password-kuat
RUN_MIGRATIONS=true
RUN_SEEDERS_ONCE=false
WHATSAPP_GATEWAY_TOKEN=token-kuat-yang-sama-dengan-gateway
GATEWAY_SECRET_TOKEN=token-kuat-yang-sama-dengan-gateway
```

> Buat `APP_KEY` production dengan `php artisan key:generate --show` atau set secret dari panel hosting. Jika `APP_KEY` atau token gateway kosong, entrypoint membuat nilai sementara untuk booting, tetapi nilai tersebut berubah saat container dibuat ulang.

### WhatsApp Gateway dalam Container

- Laravel default memanggil gateway internal lewat `http://127.0.0.1:3000/send`.
- QR WhatsApp tetap dilihat dari halaman Admin `/admin/whatsapp`.
- Session WhatsApp disimpan di `storage/app/whatsapp-auth`; pada compose file, path ini ikut persisten di volume `app-storage`.
- Queue worker dan scheduler aktif otomatis via Supervisor. Matikan dengan `RUN_QUEUE_WORKER=false` atau `RUN_SCHEDULER=false`.
