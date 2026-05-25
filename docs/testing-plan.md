# Testing Plan — Smart Sprayer IoT Web

## Tujuan

Dokumen ini menjadi rencana lengkap pengujian untuk memastikan:

- Frontend terintegrasi penuh dengan backend Laravel
- Business rule IoT berjalan sesuai domain rules
- Fitur utama aman diuji berulang secara otomatis
- Ada jalur validasi bertingkat: backend, asset frontend, dan browser end-to-end

Dokumen ini disusun dalam bentuk sprint dan task agar bisa dipakai sebagai panduan eksekusi bertahap.

---

## Sasaran Utama

Target akhir pengujian project ini adalah:

1. Semua test backend inti lulus
2. Asset frontend berhasil dibangun tanpa error
3. Skenario browser penting bisa dijalankan otomatis
4. Aksi di UI terbukti mengubah data backend yang benar
5. Aturan domain penting tervalidasi dari API, service, dan UI

---

## Strategi Pengujian

Project ini memakai 3 lapis pengujian:

### 1. Backend Automated Tests

Dipakai untuk memverifikasi:

- route protection
- controller dan service behavior
- validasi request
- business rule IoT
- integritas database
- audit log
- response API

Tool utama:

- `php artisan test`
- PHPUnit Feature Test

### 2. Frontend Build Validation

Dipakai untuk memastikan:

- import JS/CSS valid
- bundle Vite tidak rusak
- dependency frontend lengkap

Tool utama:

- `npm run build`

### 3. Browser End-to-End Tests

Dipakai untuk memverifikasi alur user nyata:

- halaman bisa dibuka
- form bisa diisi
- tombol bisa diklik
- request sampai ke backend
- hasil perubahan muncul di UI

Tool utama:

- Playwright MCP
- browser automation berbasis Codex + Playwright

---

## Ruang Lingkup

Fitur yang wajib tercakup oleh testing plan ini:

- halaman publik
- autentikasi
- dashboard
- kontrol sprayer
- histori sensor, spray, dan notifikasi
- manajemen user
- manajemen device
- threshold setting
- pengaturan WhatsApp
- API IoT
- audit log `spray_logs`
- audit log `notification_logs`

---

## Test Pyramid Project

Urutan eksekusi yang direkomendasikan:

1. Jalankan backend tests
2. Jalankan frontend build validation
3. Jalankan browser E2E tests
4. Lakukan review hasil, gap coverage, dan error log

Command dasar:

```bash
php artisan test
npm run build
```

Untuk pengujian browser:

- jalankan Laravel app
- jalankan Vite dev server
- jalankan skenario Playwright MCP

---

## Environment Test

## Kebutuhan Dasar

- database test terpisah dari database development utama
- data seed dasar untuk user, device, threshold, dan WhatsApp setting
- route web dan API aktif
- queue behavior dapat diobservasi
- storage log aktif

## Data Minimum yang Harus Ada

### User

- 1 akun `admin`
- 1 akun `petani`

### Device

- 1 device aktif
- `api_key` valid
- mode default dapat diuji dalam `manual` dan `automatic`

### Threshold

- `min_soil_moisture`
- `max_temperature`
- `min_air_humidity`

### WhatsappSetting

- `recipient_phone`
- template `critical_condition`
- template `spray_start`
- template `spray_stop`
- template `rain_detected`

### Sensor Data Variants

- kondisi normal
- kondisi waspada
- kondisi kritis
- hujan (`rain`)
- tidak hujan (`no_rain`)

---

## Sprint Plan

## Sprint 1 — Stabilkan Fondasi Test

### Tujuan

Menyiapkan environment dan memastikan test existing bisa dijalankan konsisten.

### Task

- Audit seluruh test yang sudah ada di folder `tests/Feature`
- Identifikasi fitur yang sudah punya coverage dan yang belum
- Pastikan `.env` dan database test siap dipakai
- Pastikan seed user, device, threshold, dan WhatsApp tersedia
- Pastikan command `php artisan test` bisa dijalankan tanpa setup manual tambahan

### Deliverable

- Peta coverage test existing
- Daftar gap test
- Environment test siap pakai

### Definition of Done

- Semua test existing dapat dijalankan
- Environment test terdokumentasi
- Gap testing diketahui dengan jelas

---

## Sprint 2 — Validasi Backend Inti

### Tujuan

Menjamin business logic inti aman sebelum browser test dijalankan.

### Task

- Verifikasi access control untuk publik, petani, dan admin
- Verifikasi autentikasi login/logout
- Verifikasi validasi input form penting
- Verifikasi rule IoT device auth
- Verifikasi response wrapper API
- Verifikasi penyimpanan `sensor_readings`
- Verifikasi perubahan `sprayer_status`
- Verifikasi pencatatan `spray_logs`
- Verifikasi pencatatan `notification_logs`

### Fokus Test

- `GET /`
- `GET /dashboard`
- `GET /sprayer`
- `POST /sprayer/mode`
- `POST /sprayer/status`
- `POST /api/sensor-readings`
- `GET /api/devices/{device}/command`
- route admin `/admin/*`

### Deliverable

- Feature tests yang lulus untuk auth, dashboard, sprayer, API, history, dan admin

### Definition of Done

- Business rules kritis lulus di backend tests
- Tidak ada route penting yang belum punya coverage minimal

---

## Sprint 3 — Validasi Frontend Build dan Asset

### Tujuan

Menjamin frontend dapat dibangun tanpa error dependency atau import.

### Task

- Verifikasi `npm install` sinkron dengan `package.json`
- Verifikasi `npm run build`
- Pastikan Vite tidak gagal resolve import
- Pastikan asset `app.js` dan CSS utama ikut terbundle
- Pastikan tidak ada error build pada layout dan halaman Blade utama

### Deliverable

- Frontend build stabil
- Dependency frontend sinkron

### Definition of Done

- `npm run build` lulus konsisten
- Tidak ada import frontend yang broken

---

## Sprint 4 — E2E Publik dan Auth

### Tujuan

Menguji alur paling dasar dari sisi browser.

### Task

- Test halaman landing publik terbuka normal
- Test halaman publik tidak menampilkan tombol kontrol sprayer
- Test halaman publik tidak menampilkan data sensitif
- Test guest tidak bisa akses route yang butuh login
- Test login admin berhasil
- Test login petani berhasil
- Test logout berhasil

### Skenario Browser

1. Buka `/`
2. Verifikasi ringkasan publik tampil
3. Pastikan kontrol admin tidak tampil
4. Coba buka `/dashboard` tanpa login dan verifikasi redirect
5. Login admin dan verifikasi redirect sukses
6. Login petani dan verifikasi redirect sukses

### Deliverable

- Script E2E untuk publik dan auth

### Definition of Done

- Alur publik dan login tervalidasi otomatis dari browser

---

## Sprint 5 — E2E Dashboard dan Public Summary

### Tujuan

Membuktikan data backend tampil benar di UI dashboard dan halaman publik.

### Task

- Test dashboard menampilkan device aktif
- Test dashboard menampilkan sensor reading terbaru
- Test perubahan sensor reading tercermin di UI
- Test public summary mengikuti data terbaru
- Test status visual sesuai `normal`, `waspada`, `kritis`

### Skenario Browser

1. Seed device dan sensor reading
2. Login sebagai user yang berhak
3. Buka `/dashboard`
4. Verifikasi nilai sensor tampil
5. Kirim sensor reading baru via backend/API
6. Refresh halaman
7. Verifikasi data UI ikut berubah

### Deliverable

- Script E2E dashboard dan public summary

### Definition of Done

- UI terbukti membaca state terbaru dari backend

---

## Sprint 6 — E2E Kontrol Sprayer

### Tujuan

Memastikan aksi manual dan mode sprayer berjalan dari UI sampai backend.

### Task

- Test admin dapat ubah mode `manual` dan `automatic`
- Test petani dapat kontrol sprayer sesuai role
- Test perubahan status sprayer muncul di UI
- Test setiap perubahan status membuat `spray_logs`
- Test mode otomatis memengaruhi command sesuai kondisi

### Skenario Browser

1. Login admin
2. Buka `/sprayer`
3. Ubah mode device
4. Aktifkan sprayer manual
5. Verifikasi status baru tampil
6. Verifikasi data backend berubah
7. Verifikasi `spray_logs` bertambah

### Deliverable

- Script E2E kontrol sprayer

### Definition of Done

- Aksi kontrol dari browser terbukti mengubah backend sesuai aturan

---

## Sprint 7 — E2E Rule IoT dan Otomasi Sprayer

### Tujuan

Mengunci business rule terpenting yang mempengaruhi integrasi IoT.

### Task

- Test `rain_status = rain` memaksa `sprayer_command = off`
- Test mode otomatis hanya aktif saat `soil_moisture < threshold` dan `no_rain`
- Test kondisi kritis menghasilkan response IoT yang benar
- Test kondisi normal mematikan sprayer otomatis
- Test event penting menghasilkan log dan notifikasi

### Skenario Integrasi

1. Set mode device ke `automatic`
2. Kirim data sensor dengan `rain`
3. Verifikasi command `off`
4. Kirim data sensor `no_rain` dengan `soil_moisture` rendah
5. Verifikasi command `on`
6. Verifikasi dashboard ikut berubah
7. Verifikasi log terkait tercatat

### Deliverable

- Test integrasi IoT-to-UI untuk skenario otomatis

### Definition of Done

- Rule domain otomatis tervalidasi end-to-end

---

## Sprint 8 — E2E History dan Audit Trail

### Tujuan

Memastikan seluruh riwayat domain tampil benar di UI.

### Task

- Test halaman `history.sensor`
- Test halaman `history.spray`
- Test halaman `history.notification`
- Test data terbaru muncul sesuai aksi yang baru dijalankan
- Test urutan data masuk akal dan dapat dibaca user

### Skenario Browser

1. Lakukan aksi sensor atau sprayer
2. Buka halaman history terkait
3. Verifikasi record terbaru muncul
4. Verifikasi field penting tampil dengan benar

### Deliverable

- Script E2E halaman history

### Definition of Done

- Riwayat tidak hanya tersimpan di backend, tetapi juga tampil benar di frontend

---

## Sprint 9 — E2E Admin Configuration

### Tujuan

Menguji seluruh flow konfigurasi admin dari browser.

### Task

- Test admin dapat membuka manajemen user
- Test admin dapat tambah/edit user
- Test admin dapat membuka manajemen device
- Test admin dapat tambah/edit device
- Test admin dapat ubah threshold
- Test admin dapat ubah WhatsApp setting
- Test petani tidak bisa akses route admin

### Skenario Browser

1. Login sebagai admin
2. Buka `/admin/users`
3. Tambah atau update user
4. Buka `/admin/devices`
5. Tambah atau update device
6. Ubah threshold
7. Buka `/admin/whatsapp`
8. Simpan perubahan

### Deliverable

- Script E2E fitur admin

### Definition of Done

- Seluruh fitur konfigurasi utama admin tervalidasi otomatis

---

## Sprint 10 — Hardening, Regression, dan Reporting

### Tujuan

Menjadikan testing dapat dijalankan berulang dengan hasil yang mudah dibaca.

### Task

- Gabungkan seluruh suite test ke alur eksekusi standar
- Susun urutan run yang stabil
- Dokumentasikan cara menjalankan regression test
- Catat fitur yang sudah tercakup dan yang belum
- Tambahkan checklist investigasi jika test gagal

### Deliverable

- Regression workflow
- Laporan coverage fitur
- Daftar gap test residual

### Definition of Done

- Ada jalur regression yang jelas dan bisa dijalankan ulang kapan saja

---

## Matrix Fitur vs Jenis Test

| Fitur | Backend Test | Build Check | Browser E2E |
|------|--------------|-------------|-------------|
| Landing publik | Ya | Ya | Ya |
| Login/logout | Ya | Tidak langsung | Ya |
| Dashboard | Ya | Ya | Ya |
| Sprayer control | Ya | Tidak langsung | Ya |
| History | Ya | Tidak langsung | Ya |
| Admin users | Ya | Tidak langsung | Ya |
| Admin devices | Ya | Tidak langsung | Ya |
| Threshold | Ya | Tidak langsung | Ya |
| WhatsApp settings | Ya | Tidak langsung | Ya |
| IoT API | Ya | Tidak | Ya, via skenario integrasi |

---

## Prioritas Eksekusi

Jika waktu terbatas, kerjakan urutan ini:

1. `php artisan test`
2. `npm run build`
3. E2E publik
4. E2E login
5. E2E dashboard
6. E2E sprayer control
7. E2E IoT automatic rules
8. E2E history
9. E2E admin configuration

---

## Kriteria Keberhasilan

Pengujian dianggap sehat jika:

- test backend inti lulus
- build frontend lulus
- tidak ada error JavaScript kritis pada halaman utama
- alur user penting lulus di browser test
- perubahan UI terbukti sinkron dengan database dan log
- rule hujan dan mode otomatis terbukti benar

---

## Risiko dan Gap yang Harus Diwaspadai

- browser test flakey jika data seed tidak konsisten
- test UI bisa lolos walau notifikasi eksternal dimock tidak benar
- queue dan HTTP gateway bisa butuh strategi mock/fake khusus
- perbedaan data lokal dan data production bisa menyebabkan false confidence
- frontend Blade yang sangat bergantung pada data dinamis perlu selector test yang stabil

---

## Rekomendasi Implementasi Teknis

### Tahap 1

- rapikan test existing
- pastikan `php artisan test` stabil

### Tahap 2

- siapkan seeder/factory khusus test browser
- siapkan akun admin dan petani konsisten

### Tahap 3

- buat skenario Playwright MCP untuk fitur prioritas
- gunakan selector yang stabil dan mudah dirawat

### Tahap 4

- tambah regression run berkala sebelum merge/perubahan besar

---

## Checklist Implementasi

- [ ] Audit test existing
- [ ] Siapkan environment test
- [ ] Siapkan data seed/factory test
- [ ] Pastikan `php artisan test` stabil
- [ ] Pastikan `npm run build` stabil
- [ ] Buat E2E publik
- [ ] Buat E2E auth
- [ ] Buat E2E dashboard
- [ ] Buat E2E sprayer
- [ ] Buat E2E IoT automatic rules
- [ ] Buat E2E history
- [ ] Buat E2E admin configuration
- [ ] Buat regression workflow
- [ ] Dokumentasikan coverage dan gap akhir

---

## Referensi Internal

- `routes/web.php`
- `routes/api.php`
- `tests/Feature/*`
- `docs/architecture.md`
- `docs/domain-rules.md`
- `docs/operations.md`
- `AGENTS.md`
