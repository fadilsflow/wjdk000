# Agent Instructions — Smart Sprayer IoT Web

Panduan kerja agent AI dalam repository ini.

---

## Mandatory First Steps

Setiap kali memulai task baru, agent WAJIB:

1. Baca `AGENTS.md` di root project (jika belum dibaca di session ini)
2. Identifikasi layer mana yang terlibat dalam task
3. Baca file `.instructions.md` yang relevan untuk layer tersebut
4. Periksa `docs/prd.md` jika task berkaitan dengan fitur atau domain baru

---

## Cara Mengidentifikasi Layer

| Sedang mengerjakan...              | Layer yang terlibat              |
|------------------------------------|----------------------------------|
| Endpoint baru / routing            | Routes + Middleware + Controller |
| Validasi input form atau API       | Form Request                     |
| Business logic / keputusan sprayer | Service                          |
| Query database / CRUD              | Repository atau Model langsung   |
| Tampilan / Blade view              | Views + Blade components         |
| Integrasi WhatsApp                 | Service (WhatsAppService)        |
| Endpoint IoT (sensor, command)     | Api/Controller + Service         |

---

## Preferred Working Style

- **Baca sebelum tulis** — Baca file yang akan dimodifikasi sebelum menulis kode.
- **Perubahan minimal** — Ubah hanya yang dibutuhkan, jangan refactor di luar scope task.
- **Satu concern per file** — Controller routing saja, Service business logic saja.
- **Explicit over implicit** — Type hint dan return type di semua method.
- **Tidak ada hardcode** — API key, URL eksternal, nomor WhatsApp — semua via `.env`.
- **Ikuti konvensi folder** — Cek `docs/architecture.md` untuk konvensi folder.

---

## Business Logic Priorities (urutan check)

Sebelum mengimplementasikan logika penyemprotan otomatis, selalu verifikasi urutan ini:

1. Apakah device terdaftar? → Cek `devices.api_key`
2. Apakah sedang hujan? → Cek `rain_status = 'rain'` → Jika ya, **STOP**, sprayer tidak aktif
3. Apakah tanah kering? → Cek `soil_moisture < threshold.min_soil_moisture`
4. Jika 2 dan 3 terpenuhi → sprayer AKTIF (jika mode `automatic`)
5. Catat perubahan ke `spray_logs`
6. Kirim notifikasi WhatsApp jika diperlukan, catat ke `notification_logs`

---

## Mandatory Self-Check (sebelum selesai task)

```
[ ] Controller sudah tipis? (logic di service, bukan di controller)
[ ] Input divalidasi via Form Request?
[ ] Semua env variable sudah ada di .env.example?
[ ] Migration baru dibuat? (bukan edit migration lama)
[ ] Perubahan sprayer status dicatat ke spray_logs?
[ ] Pengiriman WhatsApp dicatat ke notification_logs?
[ ] Rule "no spray saat hujan" diterapkan di service?
[ ] Halaman publik tidak expose data sensitif?
[ ] Middleware role diterapkan ke route yang dibatasi?
[ ] API response menggunakan format standar?
[ ] Apakah AGENTS.md atau docs perlu diupdate? → cek "Doc Update Policy" di AGENTS.md
```

---

## Error Handling Convention

- API: return JSON dengan format standar `{success, message, errors}`
- Web: redirect dengan flash message
- Tidak ada `dd()` atau `var_dump()` di kode production
- Gunakan `Log::error()` untuk error yang tidak terduga
