# Domain Rules — Smart Sprayer IoT Web

## Entities Utama

> Detail skema kolom (tipe, panjang, index) ada di **migration files**. Dokumen ini hanya mendefinisikan kontrak domain: apa yang diwakili entity, relasi, dan enum values yang penting untuk business logic.

### User
Pengguna aplikasi dengan role-based access. Role menentukan halaman dan fitur yang dapat diakses.
- **Role:** `'admin'` | `'petani'`
- Memiliki `phone_number` sebagai nomor tujuan notifikasi WhatsApp

### Device
Perangkat IoT (ESP32) yang terdaftar di sistem. Setiap device punya `api_key` unik untuk autentikasi.
- **mode:** `'manual'` | `'automatic'`
- **sprayer_status:** `'on'` | `'off'`
- Relasi: punya satu `ThresholdSetting`, banyak `SensorReading`, banyak `SprayLog`

### SensorReading
Data pembacaan sensor dari perangkat, disimpan setiap kali ESP32 mengirim data. **Immutable** — tidak boleh diedit/dihapus setelah tersimpan.
- Sensor fields: `temperature`, `soil_moisture` (kolom `air_humidity` tetap di DB/API untuk kompatibilitas ESP32, tidak ditampilkan di website), `soil_raw`, `rain_status`, `rain_raw`, `sprayer_status`, `simulation_mode`
- Payload ESP32 aktual boleh memakai alias: `humidity`, `soilPercent`, `soilRaw`, `raining`, `rainRaw`, `pumpOn`, `simulationMode`; backend memetakan alias ke field domain di atas.
- **rain_status:** `'rain'` | `'no_rain'`
- **condition_status:** `'normal'` | `'waspada'` | `'kritis'` (dihitung saat data masuk)
- **recorded_at:** waktu pembacaan di perangkat; jika ESP32 tidak mengirim waktu, backend memakai waktu server

### ThresholdSetting
Konfigurasi batas nilai sensor per device, diatur oleh Admin. Satu device = satu konfigurasi threshold.
- Fields utama: `min_soil_moisture`, `max_temperature`
- Nilai threshold ini yang digunakan Service untuk menentukan `condition_status`

### SprayLog
Audit trail setiap perubahan status sprayer. Wajib dibuat untuk setiap perubahan, tanpa pengecualian.
- **trigger_type:** `'manual'` | `'automatic'`
- **status:** `'on'` | `'off'`
- `created_by` → `users.id` (null jika otomatis)

### NotificationLog
Riwayat setiap upaya pengiriman notifikasi WhatsApp, termasuk yang gagal.
- **type:** lihat Notification Types
- **status:** `'sent'` | `'failed'`

### WhatsappSetting
Konfigurasi singleton untuk fitur WhatsApp yang bisa diubah Admin tanpa menyentuh file `.env`.
- Menyimpan `recipient_phone`
- Menyimpan template pesan untuk `critical_condition`, `spray_start`, `spray_stop`, `rain_detected`
- Tidak menyimpan token sensitif gateway

---

## Notification Types

| Type                  | Trigger                                                    |
|-----------------------|------------------------------------------------------------|
| `critical_condition`  | Kondisi lingkungan masuk status `kritis`                   |
| `spray_start`         | Penyemprotan dimulai (manual atau otomatis)                |
| `spray_stop`          | Penyemprotan dihentikan (manual atau otomatis)             |
| `rain_detected`       | Sensor hujan mendeteksi hujan (mode otomatis aktif)        |

---

## Condition Status Flow

```
Data sensor masuk
  ↓
Cek rain_status
  ├── rain_status = 'rain'
  │     → condition_status = 'normal'
  │     → sprayer_command = 'off' (force)
  │     → kirim notifikasi 'rain_detected' jika mode otomatis
  │
  └── rain_status = 'no_rain'
        ↓
        Evaluasi threshold
          ├── soil_moisture < threshold.min_soil_moisture
          │     → condition_status = 'kritis'
          │     → jika mode otomatis → sprayer_command = 'on'
          │     → kirim notifikasi 'critical_condition' & 'spray_start'
          │
          ├── temperature > threshold.max_temperature
          │     → condition_status = 'waspada'
          │     → sprayer_command tidak berubah otomatis
          │
          └── semua normal
                → condition_status = 'normal'
                → sprayer_command = 'off' (jika mode otomatis)
```

---

## Business Rules (Lengkap)

1. **Device registration required** — Request API IoT yang menyertakan `api_key` tidak valid akan ditolak dengan HTTP 401. Data sensor dari device yang tidak terdaftar tidak disimpan.

2. **Rain blocks automatic spray** — Jika `rain_status = 'rain'`, sistem TIDAK mengaktifkan sprayer dalam mode otomatis, apapun kondisi tanah atau suhu. Rule ini tidak bisa di-override dari mode otomatis.

3. **Automatic mode trigger** — Sprayer aktif otomatis HANYA jika:
   - `device.mode = 'automatic'`
   - `rain_status = 'no_rain'`
   - `soil_moisture < threshold_settings.min_soil_moisture`

4. **Manual mode** — Pada mode manual, sprayer hanya aktif/mati jika user menekan tombol di website. Sistem tidak mengubah status sprayer secara otomatis.

5. **Spray log mandatory** — Setiap perubahan `sprayer_status` (baik manual maupun otomatis) WAJIB membuat entri baru di `spray_logs`. Tidak ada perubahan tanpa log.

6. **WhatsApp notification triggers** — Notifikasi dikirim untuk: kondisi kritis, sprayer mulai, sprayer berhenti, hujan terdeteksi (pada mode otomatis). Setiap pengiriman dicatat di `notification_logs` dengan status `sent` atau `failed`.

7. **WhatsApp config split** — Kredensial gateway (`gateway_url`, `gateway_token`, `sender_number`) tetap di env/config. Nomor penerima dan template pesan dikelola via `whatsapp_settings`.

8. **Public page restrictions** — Halaman publik (`/public/summary`) TIDAK boleh menampilkan:
   - Tombol kontrol sprayer
   - Nomor WhatsApp pengguna
   - Konfigurasi alat / threshold

9. **Open web access** — Semua halaman web dapat diakses tanpa login. Halaman admin (manajemen user, device, threshold, WhatsApp) tidak dibatasi session.

10. **Sensor data immutable** — Data `sensor_readings` yang sudah tersimpan tidak boleh diedit atau dihapus melalui website (hanya sebagai riwayat historis).

---

## API Response Convention

### Success Response
```json
{
  "success": true,
  "message": "Deskripsi hasil.",
  "data": { ... },
  "errors": null
}
```

### Error Response
```json
{
  "success": false,
  "message": "Deskripsi error.",
  "data": null,
  "errors": {
    "field_name": ["Pesan validasi."]
  }
}
```

### IoT Specific Response (POST /api/sensor-readings)
```json
{
  "success": true,
  "condition_status": "kritis",
  "mode": "automatic",
  "sprayer_command": "on"
}
```

### HTTP Status Codes
| Code | Penggunaan                                         |
|------|----------------------------------------------------|
| 200  | Request berhasil                                   |
| 201  | Resource baru berhasil dibuat                      |
| 401  | Tidak terauthentikasi (device/user)                |
| 403  | Tidak punya hak akses (role tidak cocok)           |
| 422  | Validasi gagal                                     |
| 500  | Internal server error                              |
