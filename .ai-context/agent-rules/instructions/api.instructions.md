---
applyTo: "routes/api.php"
---

# API Layer Instructions — IoT Integration

## Role

API layer menangani komunikasi antara **perangkat IoT (ESP32)** dan website. Endpoint harus ringan, cepat, dan aman.

## IoT Endpoints (Kontrak)

### POST /api/sensor-readings
ESP32 mengirim data sensor. Response berisi perintah untuk sprayer.

**Fields wajib di request:** `api_key`, `temperature`, `air_humidity`, `soil_moisture`, `rain_status`, `sprayer_status`, `recorded_at`

**Response wajib:** `success`, `condition_status`, `mode`, `sprayer_command`

### GET /api/devices/{device}/command
ESP32 polling perintah terbaru. Auth via header `X-Api-Key`.

**Response wajib:** `mode`, `sprayer_command`

## Autentikasi IoT

- Semua IoT endpoint wajib diproteksi via middleware `AuthenticateDevice`
- Middleware mengecek `api_key` dari request body (POST) atau header `X-Api-Key` (GET)
- Jika `api_key` tidak valid → return 401 dengan pesan error standar
- `api_key` tidak boleh hardcoded — disimpan di tabel `devices`

## Rate Limiting

- Semua IoT endpoint wajib menggunakan `throttle:60,1` (60 req/menit per IP)

## Validasi Fields Sensor

- `rain_status` — enum, hanya `'rain'` atau `'no_rain'`
- `sprayer_status` — enum, hanya `'on'` atau `'off'`
- Nilai sensor numerik — validasi range yang masuk akal (misal temperature: -50 s.d. 100)
- `recorded_at` — format datetime valid

## Response Format

Semua response API mengikuti format standar proyek:
```json
{ "success": true|false, "message": "...", "data": {...}|null, "errors": {...}|null }
```

Khusus sensor-readings endpoint, response langsung (tanpa wrapper `data`):
```json
{ "success": true, "condition_status": "...", "mode": "...", "sprayer_command": "..." }
```

## Checklist Sebelum Submit

- [ ] Endpoint menggunakan `throttle` middleware?
- [ ] Device diverifikasi via `api_key` sebelum proses data?
- [ ] Semua field sensor divalidasi via Form Request?
- [ ] Response menggunakan format standar?
- [ ] Error 401 untuk device tidak terdaftar?
- [ ] `rain_status` divalidasi hanya `'rain'` atau `'no_rain'`?
