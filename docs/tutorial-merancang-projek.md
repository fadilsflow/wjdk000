# Tutorial Pengembangan Smart Sprayer IoT Web

Panduan ringkas membangun proyek ini dari setup sampai deploy. Fokus pada langkah pengembangan yang penting.

---

## 1. Tech Stack

| Layer | Pilihan |
|-------|---------|
| Backend | Laravel 13, PHP 8.3+ |
| Database | SQLite (dev) / MySQL (prod) |
| Frontend | Blade + Alpine.js + Tailwind CSS v4 |
| Charts | Chart.js |
| IoT | REST API JSON + `api_key` per device |
| Notifikasi | WhatsApp Gateway (HTTP) |
| Testing | PHPUnit + Playwright E2E |
| Firmware | ESP32 + PlatformIO (`iot/iot.ino`) |

> **Catatan:** Semua halaman web terbuka tanpa login. Keamanan API IoT memakai `api_key` device, bukan session user.

---

## 2. Setup Awal Proyek

### 2.1 Clone & dependensi

```bash
git clone https://github.com/fadilsflow/wjdk000
cd wjdk000
composer install
cp .env.example .env
php artisan key:generate
```

### 2.2 Database

**SQLite (dev):**

```bash
touch database/database.sqlite
# .env: DB_CONNECTION=sqlite
php artisan migrate --seed
```

**MySQL (prod):**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=smart_sprayer
DB_USERNAME=root
DB_PASSWORD=
```

```bash
php artisan migrate --seed
```

### 2.3 Environment seed

```env
ADMIN_SEED_PASSWORD=password-aman
DEVICE_SEED_API_KEY=kunci-device-32-karakter
```

### 2.4 Frontend & jalankan

```bash
bun install    # atau npm install
bun run build
composer dev     # serve + queue + vite
# atau:
php artisan serve --host=0.0.0.0 --port=8000
bun run dev
```

---

## 3. Arsitektur Kode

Pola **thin controller**:

```
Request → Middleware → Form Request → Controller → Service → Repository/Model → DB
```

### Struktur folder penting

```
app/
├── Http/Controllers/     # Web + Api/ + Admin/
├── Http/Middleware/      # AuthenticateDevice (API IoT)
├── Http/Requests/
├── Models/
├── Services/
└── Repositories/
routes/
├── web.php
└── api.php
resources/views/
database/migrations/
```

**Prinsip:**

- Controller tipis (~20 baris per method)
- Semua input lewat Form Request
- `declare(strict_types=1)` di setiap file PHP
- Migration hanya maju — jangan edit migration lama

---

## 4. Urutan Pengembangan

Bangun fitur bertahap:

| Tahap | Fokus | Deliverable |
|-------|-------|-------------|
| **1** | Database & API IoT | Migration domain, `POST /api/sensor-readings`, domain rules |
| **2** | Dashboard | Sensor realtime, status kondisi, polling `/dashboard/latest` |
| **3** | Kontrol sprayer | Manual on/off, mode manual/otomatis |
| **4** | Otomasi & WhatsApp | Rule hujan/kritis, notifikasi, queue worker |
| **5** | Riwayat | Sensor, spray log, notification log |
| **6** | Admin | Device, threshold, pengguna, template WhatsApp |
| **7** | Halaman publik | Landing ringkasan tanpa kontrol sensitif |
| **8** | Hardening | PHPUnit + E2E + deploy |

---

## 5. Implementasi per Lapisan

### 5.1 Database domain

Tabel utama (satu migration domain):

| Tabel | Fungsi |
|-------|--------|
| `devices` | `api_key`, `mode`, `sprayer_status` |
| `threshold_settings` | Batas suhu, kelembapan, tanah (1:1 device) |
| `sensor_readings` | Data sensor + `condition_status` (immutable) |
| `spray_logs` | Audit trail penyemprotan |
| `notification_logs` | Riwayat WhatsApp |
| `whatsapp_settings` | Template pesan & nomor penerima |
| `users` | Data pengguna (untuk manajemen & `created_by` log) |

Index pada `device_id` + `recorded_at`.

### 5.2 API IoT

```txt
POST /api/sensor-readings          ← ESP32 kirim data
GET  /api/devices/{device}/command ← ESP32 polling perintah
```

Middleware `device.auth` → validasi `api_key` (header `X-Api-Key` atau body).

**Service:** `IotSensorService`

- Simpan `sensor_readings`
- Evaluasi threshold → `condition_status` (normal / waspada / kritis)
- Tentukan `sprayer_command` (on/off)
- Buat `spray_logs` jika status berubah
- Trigger notifikasi WhatsApp

**Aturan bisnis kritis:**

- Hujan → sprayer otomatis **mati**
- Otomatis nyala hanya jika: mode `automatic` + tanah kering + tidak hujan
- Setiap perubahan sprayer wajib tercatat di `spray_logs`

### 5.3 Web routes

| Route | Fungsi |
|-------|--------|
| `GET /` | Landing / ringkasan publik |
| `GET /dashboard` | Monitoring realtime |
| `GET /sprayer` | Kontrol manual + mode |
| `GET /history/*` | Riwayat sensor & spray |
| `GET/POST /admin/*` | Konfigurasi device, threshold, user, WhatsApp |

Polling JSON: `/dashboard/latest`, `/sprayer/latest` (update tanpa reload).

### 5.4 Services

| Service | Tanggung jawab |
|---------|-----------------|
| `IotSensorService` | Evaluasi sensor, perintah sprayer |
| `SprayerControlService` | Kontrol manual dari web |
| `DashboardService` | Data dashboard terbaru |
| `HistoryService` | Filter & pagination riwayat |
| `WhatsAppNotificationService` | Kirim & log notifikasi |
| `DeviceConfigurationService` | CRUD device & threshold |
| `UserManagementService` | CRUD data pengguna |
| `PublicSummaryService` | Data aman untuk landing |

### 5.5 WhatsApp

- Kredensial gateway di `.env`: `WHATSAPP_GATEWAY_URL`, `WHATSAPP_GATEWAY_TOKEN`
- Template & nomor penerima di `whatsapp_settings` (halaman admin)
- Setiap pengiriman dicatat di `notification_logs`
- Jalankan queue: `php artisan queue:listen`

---

## 6. Frontend

- **Blade** — layout & halaman
- **Tailwind CSS v4** — via Vite
- **Alpine.js** — toggle mode, polling data
- **Chart.js** — grafik sensor

| Status | Warna |
|--------|-------|
| Normal | hijau |
| Waspada | kuning |
| Kritis | merah |
| Hujan / Off | biru / abu |

Komponen reusable: `<x-card>`, `<x-alert>`, layout `app-layout.blade.php`.

---

## 7. Development Tanpa Hardware

1. `php artisan serve`
2. Buka `prototype/iot-trigger.html`
3. Set Base URL ke `http://127.0.0.1:8000`
4. Pastikan `api_key` sama dengan `DEVICE_SEED_API_KEY`
5. Trigger: Normal, Waspada, Kritis, Hujan, Random, Loop

---

## 8. Integrasi ESP32

**File:** `iot/iot.ino`, `iot/platformio.ini`

```cpp
const char *BACKEND_SENSOR_URL = "http://192.168.x.x:8000/api/sensor-readings";
const char *DEVICE_API_KEY = "API_KEY_DARI_DATABASE";
```

```bash
cd iot
pio run -t upload
pio device monitor --baud 115200
```

- ESP32 tidak bisa akses `127.0.0.1` — pakai IP LAN server
- `api_key` firmware harus sama dengan record di `devices`
- Jangan commit kredensial WiFi/API key

Detail: `docs/flash-esp32-laravel.md`

---

## 9. Testing

```bash
php artisan test
npm run build
npm run test:e2e
npm run test:e2e:sprayer
```

| Test file | Cakupan |
|-----------|---------|
| `SprintOneAccessTest` | Halaman web terbuka |
| `SprintTwoUserManagementTest` | CRUD pengguna |
| `SprintTwoBackendCoreValidationTest` | Validasi API |
| `SprintFourIotApiTest` | API IoT |
| `SprintFiveDashboardTest` | Dashboard |
| `SprintSixSprayerControlTest` | Kontrol sprayer |
| `SprintSevenWhatsappNotificationTest` | Notifikasi |
| `SprintEightHistoryTest` | Riwayat |
| `SprintNinePublicSummaryTest` | Landing publik |
| `SprintTenTestingTest` | Regression |

**Urutan:** backend test → build → E2E.

---

## 10. Deployment

```bash
docker compose -f compose.ghcr.yml up -d
```

Env production minimal:

```env
APP_KEY=...
APP_URL=https://domain-anda.com
DB_CONNECTION=mysql
WHATSAPP_GATEWAY_URL=...
WHATSAPP_GATEWAY_TOKEN=...
```

Checklist:

- [ ] `php artisan migrate --force`
- [ ] `npm run build`
- [ ] Queue worker aktif
- [ ] `APP_DEBUG=false`

Detail: `docs/operations.md`, `docs/render-deploy.md`

---

## 11. Checklist Selesai

| Area | Selesai jika... |
|------|-----------------|
| Dashboard | Sensor realtime & status kondisi tampil |
| Sprayer | Manual on/off + mode otomatis berfungsi |
| API IoT | Simulator/ESP32 kirim data & terima perintah |
| Domain rules | Hujan blok spray, kritis trigger otomatis |
| Audit | Perubahan sprayer ada di `spray_logs` |
| WhatsApp | Notifikasi terkirim & tercatat |
| Riwayat | Filter sensor, spray, notifikasi |
| Admin | Device, threshold, user, template WA |
| Publik | Landing aman tanpa kontrol sensitif |
| Test | `php artisan test` lulus |

---

## 12. Referensi

| Dokumen | Isi |
|---------|-----|
| `README.md` | Quick start |
| `docs/architecture.md` | Pola kode |
| `docs/domain-rules.md` | Aturan bisnis |
| `docs/operations.md` | Env & deploy |
| `docs/flash-esp32-laravel.md` | Flash firmware |
| `prototype/README.md` | Simulator IoT |

---

## Ringkasan Alur Kerja

```
1. Setup Laravel + database + seed
2. Migration domain & model
3. API IoT (sensor + command) + IotSensorService
4. Dashboard & polling realtime
5. Kontrol sprayer (manual + otomatis)
6. WhatsApp + queue
7. Riwayat & halaman admin
8. Landing publik
9. Uji simulator → integrasi ESP32
10. Test otomatis + deploy
```

Mulai dari **API IoT dan domain rules** — UI menyusul setelah logika inti stabil.
