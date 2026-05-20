# Prototype dan IoT Simulator Smart Sprayer

Folder ini berisi alat bantu development web ketika perangkat IoT asli belum tersedia.

## File

### `index.html`

Prototype UI dashboard dummy. Dipakai untuk melihat gambaran tampilan dashboard, status sensor, grafik, mode manual/otomatis, dan payload JSON.

### `iot-trigger.html`

Simulator trigger IoT berbasis HTML. Dipakai untuk mengirim request langsung ke API Laravel yang nanti dibuat.

Target default:

```txt
POST http://127.0.0.1:8000/api/sensor-readings
```

Payload yang dikirim:

```json
{
  "api_key": "DEVICE_API_KEY",
  "temperature": 31.5,
  "air_humidity": 70,
  "soil_moisture": 35,
  "rain_status": "no_rain",
  "sprayer_status": "off",
  "recorded_at": "2026-05-20T10:00:00.000Z"
}
```

## Trigger yang Tersedia

- Normal
- Waspada
- Kritis
- Hujan
- Random Data
- Start Loop / Stop Loop

## Cara Pakai

1. Jalankan Laravel:

```bash
php artisan serve
```

2. Buka file:

```txt
prototype/iot-trigger.html
```

3. Pastikan Base URL mengarah ke Laravel:

```txt
http://127.0.0.1:8000
```

4. Klik trigger seperti `Normal`, `Waspada`, `Kritis`, atau `Hujan`.

## Catatan CORS

Jika browser memblokir request karena CORS, aktifkan CORS pada Laravel untuk endpoint API, atau jalankan simulator dari origin yang diizinkan.

## Catatan

Prototype ini bukan aplikasi final. Implementasi final tetap mengikuti `docs/prd.md` dan dibuat menggunakan Laravel, MySQL, Tailwind CSS, REST API, dan WhatsApp Gateway/API.
