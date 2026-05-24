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
DB_USERNAME=root
DB_PASSWORD=
MYSQL_ATTR_SSL_CA=

QUEUE_CONNECTION=database
CACHE_STORE=file
SESSION_DRIVER=file

# WhatsApp Gateway/API
WHATSAPP_GATEWAY_URL=
WHATSAPP_GATEWAY_TOKEN=
WHATSAPP_SENDER_NUMBER=

# Seed Admin
ADMIN_SEED_NAME="Admin Smart Sprayer"
ADMIN_SEED_EMAIL=admin@smartsprayer.test
ADMIN_SEED_PASSWORD=change-me
ADMIN_SEED_PHONE=
```

> Variabel baru wajib ditambahkan ke `.env.example` segera setelah ditambahkan ke `.env`.

---

## Security

### Authentication & Authorization

- Login via session (Laravel Breeze)
- Middleware `auth` untuk semua route yang butuh login
- Middleware `CheckRole` untuk pembatasan per role (`admin`, `petani`)
- Tiga kelompok route: `admin only`, `auth only`, `public (tanpa auth)`
- Halaman publik (`/public/summary`) tidak boleh menampilkan kontrol alat atau data sensitif

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

# Cache
php artisan optimize:clear               # bersihkan semua cache
php artisan optimize                     # cache untuk production
```

---

## WhatsApp Integration

Config sensitif dibaca dari `.env` via `config/services.php`. Semua nilai sensitif wajib dari env, tidak ada hardcode.

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
- Setup cron untuk Laravel Scheduler:
  ```
  * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
  ```
