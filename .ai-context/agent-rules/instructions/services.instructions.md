---
applyTo: "app/Services/**/*.php"
---

# Services Layer Instructions

## Role

Service adalah **rumah business logic**. Semua keputusan domain ada di sini: hitung condition_status, tentukan sprayer_command, kirim notifikasi WhatsApp.

## Allowed

- Menerima data array/DTO yang sudah divalidasi (dari controller)
- Orchestrate calls ke Repository atau Model
- Memanggil external service (WhatsAppService, HTTP client)
- `DB::transaction()` jika perlu atomicity
- `Log::info()` / `Log::error()` untuk mencatat kejadian penting
- Dependency injection via constructor

## Forbidden

- Query Eloquent langsung (`Model::where()`) — delegasikan ke Repository
- Menerima `Request` object — terima array/DTO saja
- Return `view()` atau `response()` — itu tugas Controller
- Hardcode nilai threshold — ambil dari tabel `threshold_settings`
- `DB::` raw query

## Business Rules Domain yang WAJIB Diimplementasikan

Ini adalah invariant bisnis yang tidak boleh dilanggar, apapun kondisinya:

### Rule 1 — Rain blocks spray
```
if rain_status === 'rain':
    → sprayer_command = 'off'
    → STOP evaluasi otomatis, jangan aktifkan sprayer
```

### Rule 2 — Auto mode trigger
```
if mode === 'automatic'
AND rain_status === 'no_rain'
AND soil_moisture < threshold.min_soil_moisture:
    → sprayer_command = 'on'
```

### Rule 3 — Log wajib ada
```
Setiap perubahan sprayer_status → buat entri spray_logs
```

### Rule 4 — Notifikasi wajib dicatat
```
Setiap pengiriman WhatsApp → buat entri notification_logs (status: sent | failed)
```

## Checklist Sebelum Submit

- [ ] Tidak ada query Eloquent langsung di Service?
- [ ] Rule "no spray saat hujan" diimplementasikan?
- [ ] Setiap perubahan sprayer dicatat ke `spray_logs`?
- [ ] Setiap pengiriman WhatsApp dicatat ke `notification_logs`?
- [ ] Tidak ada hardcode threshold value?
- [ ] Service tidak menerima `Request` object?
