# Smart Sprayer IoT Web

Website monitoring + kendali Smart Sprayer IoT bawang merah.

Stack target:

- Laravel
- PHP
- MySQL
- Tailwind CSS
- REST API
- WhatsApp Gateway/API

## Docs

- `docs/proposal.md` - proposal web
- `docs/proposal-iot.md` - proposal IoT
- `docs/prd.md` - product requirements untuk development

## Prototype Dev

- `prototype/index.html` - dummy dashboard UI
- `prototype/iot-trigger.html` - simulator IoT untuk trigger API Laravel
- `prototype/README.md` - cara pakai simulator

## Scope Utama

- Login Admin/Petani
- Dashboard sensor real-time
- Data sensor:
  - suhu udara
  - kelembapan udara
  - kelembapan tanah
  - status hujan
- Status kondisi:
  - normal
  - waspada
  - kritis
- Kontrol sprayer manual
- Mode sprayer otomatis
- Riwayat sensor
- Riwayat penyemprotan
- Riwayat notifikasi
- WhatsApp notification
- Halaman publik ringkasan

## Dev Flow

1. Setup Laravel + MySQL + Tailwind.
2. Buat auth + role Admin/Petani.
3. Buat migration sesuai `docs/prd.md`.
4. Buat endpoint:

```txt
POST /api/sensor-readings
GET /api/devices/{device}/command
POST /devices/{device}/sprayer/on
POST /devices/{device}/sprayer/off
POST /devices/{device}/mode
```

5. Test API pakai:

```txt
prototype/iot-trigger.html
```

6. Buat dashboard.
7. Buat kontrol sprayer.
8. Buat riwayat.
9. Integrasi WhatsApp.
10. Buat halaman publik.
11. Black Box Testing.

## IoT Simulator

Jalankan Laravel:

```bash
php artisan serve
```

Buka:

```txt
prototype/iot-trigger.html
```

Default target:

```txt
http://127.0.0.1:8000/api/sensor-readings
```

Trigger tersedia:

- Normal
- Waspada
- Kritis
- Hujan
- Random Data
- Loop

## Catatan

IoT asli belum tersedia. Development web pakai simulator dulu. Semua fitur ikut `docs/prd.md`. Jangan tambah fitur di luar proposal tanpa approval client.
