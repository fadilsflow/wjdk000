# Tutorial Pengembangan dari Awal — Smart Sprayer IoT Web

Panduan membangun proyek ini **langkah demi langkah**, mulai dari instalasi Laravel kosong sampai fitur inti jalan. Tidak perlu clone repo dulu — ikuti urutan di bawah.

> Semua halaman web **tanpa login**. Keamanan hanya di API IoT via `api_key` device.

---

## 0. Prasyarat

| Tool | Versi minimal |
|------|----------------|
| PHP | 8.3+ (ext: `pdo`, `mbstring`, `openssl`, `curl`) |
| Composer | 2.x |
| Node.js / Bun | Node 20+ atau Bun |
| Database | SQLite (dev) atau MySQL 8+ (prod) |
| Git | opsional |

Cek cepat:

```bash
php -v
composer -V
node -v   # atau bun -v
```

---

## 1. Buat Project Laravel

```bash
composer create-project laravel/laravel smart-sprayer
cd smart-sprayer
```

Atau pakai repo yang sudah ada:

```bash
git clone https://github.com/fadilsflow/wjdk000
cd wjdk000
composer install
```

---

## 2. Environment & Database

### 2.1 File `.env`

```bash
cp .env.example .env
php artisan key:generate
```

### 2.2 SQLite (paling cepat untuk dev)

```bash
touch database/database.sqlite
```

`.env`:

```env
DB_CONNECTION=sqlite

ADMIN_SEED_PASSWORD=password-dev-aman
DEVICE_SEED_API_KEY=dev-device-key-32-chars-minimum!!
```

### 2.3 MySQL (opsional)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_sprayer
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `smart_sprayer` di MySQL sebelum migrate.

---

## 3. Frontend Toolchain

```bash
bun install    # atau: npm install
```

Entry point:

- `resources/css/app.css` — Tailwind v4 (`@import "tailwindcss"`)
- `resources/js/app.js` — Alpine.js + Turbo

`vite.config.js` memakai `laravel-vite-plugin` + `@tailwindcss/vite`.

Build pertama:

```bash
bun run build
```

---

## 4. Skema Database (Migration)

Urutan migration yang dibutuhkan:

| Urutan | File | Isi |
|--------|------|-----|
| 1 | `create_users_table` | Bawaan Laravel |
| 2 | `add_role_and_phone_number_to_users` | `role`, `phone_number` |
| 3 | `create_smart_sprayer_domain_tables` | `devices`, `threshold_settings`, `sensor_readings`, `spray_logs`, `notification_logs` |
| 4 | `create_whatsapp_settings_table` | Template & nomor penerima |
| 5 | `add_actual_esp32_fields_to_sensor_readings` | Kolom tambahan dari firmware ESP32 |

Jalankan:

```bash
php artisan migrate
```

### Tabel inti (ringkas)

```
devices              → api_key, mode, sprayer_status
threshold_settings   → min_soil_moisture, max_temperature, min_air_humidity (1:1 device)
sensor_readings      → data sensor + condition_status (immutable)
spray_logs           → audit setiap perubahan sprayer
notification_logs    → riwayat WhatsApp
whatsapp_settings    → template pesan
users                → data pengguna (manajemen, created_by di spray_logs)
```

Index wajib: `(device_id, recorded_at)` pada `sensor_readings`.

---

## 5. Seeder Data Awal

`database/seeders/DatabaseSeeder.php` mengisi:

- User admin + petani (password dari `ADMIN_SEED_PASSWORD`)
- 1 device default + `api_key` dari `DEVICE_SEED_API_KEY`
- Threshold default
- Template WhatsApp default

```bash
php artisan migrate --seed
```

Simpan `DEVICE_SEED_API_KEY` — dipakai simulator & ESP32.

---

## 6. Models & Relasi

Buat model Eloquent (1 model = 1 tabel):

| Model | Relasi penting |
|-------|----------------|
| `Device` | `hasOne(ThresholdSetting)`, `hasMany(SensorReading, SprayLog)` |
| `SensorReading` | `belongsTo(Device)` |
| `SprayLog` | `belongsTo(Device)`, `belongsTo(User, 'created_by')` |
| `ThresholdSetting` | `belongsTo(Device)` |
| `WhatsappSetting` | singleton (id = 1) |
| `NotificationLog` | `belongsTo(Device)` |

Set `fillable`, `casts`, dan `declare(strict_types=1)` di setiap file.

---

## 7. Lapisan Backend

### 7.1 Pola alur

```
Route → Middleware → Form Request → Controller → Service → Repository/Model
```

Controller **tipis** (~20 baris/method). Logic di **Service**.

### 7.2 Middleware IoT

`AuthenticateDevice` — validasi `api_key` dari header `X-Api-Key` atau body. Daftarkan di `bootstrap/app.php` sebagai alias `device.auth`.

### 7.3 API IoT (`routes/api.php`)

```php
Route::middleware(['throttle:60,1', 'device.auth'])->group(function (): void {
    Route::post('/sensor-readings', [SensorReadingController::class, 'store']);
    Route::get('/devices/{device}/command', [DeviceCommandController::class, 'show']);
});
```

**`IotSensorService`** (inti sistem):

1. Simpan `sensor_readings`
2. Evaluasi threshold → `condition_status` (normal / waspada / kritis)
3. Tentukan `sprayer_command` on/off
4. Update `devices.sprayer_status` jika berubah
5. Tulis `spray_logs`
6. Queue notifikasi WhatsApp

**Aturan bisnis:**

- `rain_status = rain` → sprayer otomatis **mati**
- Mode `automatic` + tanah kering + tidak hujan → sprayer **nyala**
- Setiap perubahan sprayer → wajib `spray_logs`

### 7.4 Web routes (`routes/web.php`)

| Method | Path | Fungsi |
|--------|------|--------|
| GET | `/` | Landing publik |
| GET | `/dashboard` | Monitoring |
| GET | `/dashboard/latest` | JSON polling |
| GET | `/sprayer` | Kontrol sprayer |
| POST | `/sprayer/mode` | Ubah manual/automatic |
| POST | `/sprayer/status` | On/off manual |
| GET | `/history/sensor`, `/history/spray` | Riwayat |
| GET/POST/PUT/DELETE | `/admin/*` | Device, threshold, user, WhatsApp |

Semua route web **tanpa middleware auth**.

### 7.5 Services lain

| Service | Tugas |
|---------|-------|
| `SprayerControlService` | Kontrol dari web |
| `DashboardService` | Data terbaru + chart |
| `HistoryService` | Filter & pagination |
| `WhatsAppNotificationService` | HTTP ke gateway + `notification_logs` |
| `DeviceConfigurationService` | CRUD device & threshold |
| `UserManagementService` | CRUD user |
| `PublicSummaryService` | Data aman untuk `/` |

### 7.6 WhatsApp

`.env`:

```env
WHATSAPP_GATEWAY_URL=http://127.0.0.1:3001
WHATSAPP_GATEWAY_TOKEN=secret-token
```

Queue worker wajib untuk kirim async:

```bash
php artisan queue:listen
```

Atau sekaligus dengan `composer dev` (serve + queue + vite + logs).

---

## 8. Frontend (Blade)

### 8.1 Layout

- `resources/views/layouts/app-layout.blade.php` — sidebar, navigasi
- Komponen: `<x-card>`, `<x-alert>`, badge status

### 8.2 Halaman per fitur

```
resources/views/
├── landing.blade.php
├── dashboard/
├── sprayer/
├── history/
└── admin/
```

### 8.3 Realtime tanpa reload

- Dashboard: `fetch('/dashboard/latest')` tiap ~2 detik
- Sprayer: `fetch('/sprayer/latest')`
- Chart.js untuk grafik sensor
- Alpine.js untuk toggle mode / UI interaktif

### 8.4 Warna status

| Status | Class |
|--------|--------|
| Normal | `badge-normal` |
| Waspada | `badge-waspada` |
| Kritis | `badge-kritis` |
| Off / gagal | `badge-off` |

---

## 9. Urutan Coding (disarankan)

| Step | Kerjakan | Verifikasi |
|------|----------|------------|
| **1** | Migration + seeder + models | `php artisan migrate --seed` |
| **2** | `AuthenticateDevice` + API sensor | `curl` POST dengan `api_key` |
| **3** | `IotSensorService` + domain rules | Test kritis/hujan/otomatis |
| **4** | Dashboard view + `/dashboard/latest` | Browser tampil data |
| **5** | Sprayer control + `/sprayer/*` | Manual on/off + mode |
| **6** | WhatsApp service + queue | `notification_logs` terisi |
| **7** | History pages | Filter & tabel |
| **8** | Admin (device, threshold, user, WA) | Form CRUD jalan |
| **9** | Landing publik | Tanpa kontrol sensitif |
| **10** | PHPUnit + Playwright | `php artisan test`, `npm run test:e2e` |

---

## 10. Jalankan Development

**Opsi A — satu perintah:**

```bash
composer dev
```

**Opsi B — manual (2 terminal):**

```bash
# Terminal 1
php artisan serve --host=0.0.0.0 --port=8000
php artisan queue:listen

# Terminal 2
bun run dev
```

Buka: `http://127.0.0.1:8000`

---

## 11. Uji Tanpa ESP32

1. Server jalan di port 8000
2. Buka `prototype/iot-trigger.html`
3. Base URL: `http://127.0.0.1:8000`
4. `api_key` = `DEVICE_SEED_API_KEY` di `.env`
5. Trigger: Normal → Waspada → Kritis → Hujan
6. Cek `/dashboard` dan `/sprayer`

Test API manual:

```bash
curl -X POST http://127.0.0.1:8000/api/sensor-readings \
  -H "Content-Type: application/json" \
  -H "X-Api-Key: dev-device-key-32-chars-minimum!!" \
  -d '{"temperature":28,"air_humidity":70,"soil_moisture":25,"rain_status":"no_rain"}'
```

---

## 12. Integrasi ESP32

Firmware: `iot/iot.ino` + `iot/platformio.ini`

```cpp
const char *BACKEND_SENSOR_URL = "http://192.168.x.x:8000/api/sensor-readings";
const char *DEVICE_API_KEY = "sama-dengan-database";
```

```bash
cd iot
pio run -t upload
pio device monitor --baud 115200
```

- Pakai **IP LAN** server, bukan `127.0.0.1`
- `api_key` harus match tabel `devices`

Detail: `docs/flash-esp32-laravel.md`

---

## 13. Testing

```bash
php artisan test
npm run build
npm run test:e2e
npm run test:e2e:sprayer
```

**Definition of Done:** PHPUnit lulus → build lulus → E2E relevan lulus.

---

## 14. Deploy

```bash
docker compose -f compose.ghcr.yml up -d
```

Checklist: `migrate --force`, `npm run build`, queue worker, HTTPS.

Detail: `docs/operations.md`

---

## 15. Ringkasan Alur (dari nol)

```
1. composer create-project / clone
2. .env + database
3. bun install && bun run build
4. php artisan migrate --seed
5. Models + Services + Controllers
6. routes/api.php → uji curl / simulator
7. routes/web.php (dashboard → sprayer → history → admin)
8. WhatsApp + queue
9. Landing publik
10. Test + deploy
```

Mulai dari **database + API IoT** — UI menyusul setelah logika sensor & sprayer benar.

---

## Dokumen terkait

| File | Isi |
|------|-----|
| `docs/tutorial-merancang-projek.md` | Ringkasan arsitektur & referensi cepat |
| `docs/architecture.md` | Konvensi folder & layer |
| `docs/domain-rules.md` | Aturan bisnis detail |
| `docs/flash-esp32-laravel.md` | Flash firmware ESP32 |
