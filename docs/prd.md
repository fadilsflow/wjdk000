# PRD - Website Monitoring dan Kendali Smart Sprayer IoT Bawang Merah

## 1. Acuan Dokumen

PRD ini disusun berdasarkan dokumen berikut:

1. `docs/proposal.md` - proposal website monitoring dan penyemprotan otomatis berbasis Laravel, MySQL, IoT, dan WhatsApp API/Gateway.
2. `docs/proposal-iot.md` - proposal IoT Smart Sprayer yang menjelaskan perangkat ESP32, sensor BME280, sensor soil moisture, sensor hujan, relay/pompa, panel surya, serta logika penyemprotan otomatis.

Seluruh kebutuhan pada PRD ini harus mengikuti ruang lingkup kedua proposal di atas, tanpa menambahkan fitur di luar kebutuhan.

## 2. Ringkasan Produk

Website ini digunakan sebagai sistem monitoring dan kendali untuk alat Smart Sprayer IoT pada tanaman bawang merah. Website menerima data dari perangkat IoT, menampilkan kondisi lingkungan secara real-time, menampilkan status penyemprotan, menyediakan kontrol penyemprotan manual/otomatis, serta mengirim notifikasi WhatsApp sebagai peringatan dini.

Sistem difokuskan untuk mendukung pengendalian hama kutu pada bawang merah di Brebes, dengan mempertimbangkan parameter lingkungan pada musim hujan dan kemarau.

## 3. Tujuan Produk

- Menampilkan data sensor lingkungan dari alat Smart Sprayer IoT secara real-time.
- Membantu pengguna memantau kondisi suhu, kelembapan tanah, kelembapan udara, dan hujan.
- Menampilkan status kondisi lingkungan sebagai dasar penyemprotan.
- Menyediakan kontrol penyemprotan pestisida secara manual dan otomatis.
- Mengirim notifikasi WhatsApp untuk kondisi penting terkait penyemprotan atau kondisi lahan.
- Menyimpan riwayat data sensor, aktivitas penyemprotan, dan notifikasi.

## 4. Ruang Lingkup Produk

### 4.1 Termasuk

- Website dashboard monitoring.
- Login dan hak akses pengguna.
- Penerimaan data sensor dari perangkat IoT.
- Tampilan data suhu udara, kelembapan udara, kelembapan tanah, dan status hujan.
- Tampilan status alat sprayer.
- Kontrol penyemprotan manual.
- Dukungan mode penyemprotan otomatis berdasarkan data sensor.
- Notifikasi WhatsApp API/Gateway.
- Riwayat data sensor.
- Riwayat aktivitas penyemprotan.
- Riwayat notifikasi.
- Tampilan responsif agar dapat digunakan melalui smartphone.
- Pengujian Black Box untuk fitur utama.

### 4.2 Tidak Termasuk

- Perakitan hardware secara detail.
- Penentuan jenis pestisida secara kimiawi.
- Analisis biologis tanaman secara mendalam.
- Analisis hama selain hama kutu.
- Implementasi skala luas pada banyak wilayah pertanian.
- Prediksi berbasis machine learning.
- Fitur di luar kebutuhan monitoring, kontrol sprayer, riwayat, dan notifikasi.

## 5. Target Pengguna dan Hak Akses

| Pengguna | Deskripsi | Hak Akses |
|---|---|---|
| Admin | Petugas/pemilik sistem | Mengelola data pengguna, konfigurasi alat, threshold, pengaturan WhatsApp, dan melihat seluruh data |
| Petani | Pengguna utama sistem | Melihat dashboard, melihat riwayat, mengontrol sprayer, dan menerima notifikasi |
| Publik/Masyarakat | Pengunjung umum | Melihat ringkasan visual kondisi lahan yang tidak sensitif melalui halaman publik terbatas |

## 6. Parameter IoT yang Digunakan

Berdasarkan proposal IoT, perangkat menggunakan ESP32 sebagai pusat kendali dan membaca data dari sensor berikut:

| Komponen             | Data yang Digunakan Website        |
| -------------------- | ---------------------------------- |
| BME280               | Suhu udara dan kelembapan udara    |
| Soil Moisture Sensor | Kelembapan tanah                   |
| Sensor Hujan         | Status hujan/curah hujan sederhana |
| Relay/Pompa          | Status penyemprotan on/off         |

Catatan:

- Website hanya menerima dan mengolah data yang dikirim perangkat IoT.
- Detail rangkaian, panel surya, baterai, dan wiring tidak menjadi scope website.

## 7. Fitur Produk

### 7.1 Login dan Hak Akses

Functional requirements:

- Admin dan Petani dapat login.
- User dapat logout.
- Sistem membatasi akses halaman berdasarkan role.
- Admin dapat mengelola data pengguna.

Acceptance criteria:

- User valid dapat login.
- User tidak valid ditolak.
- Petani tidak dapat membuka halaman khusus Admin.

---

### 7.2 Dashboard Monitoring

Dashboard menampilkan data terbaru dari alat Smart Sprayer IoT.

Data yang ditampilkan:

- Suhu udara.
- Kelembapan udara.
- Kelembapan tanah.
- Status hujan.
- Status sprayer/pompa: on atau off.
- Mode alat: manual atau otomatis.
- Waktu data terakhir diterima.
- Status kondisi lingkungan.

Komponen UI:

- Kartu ringkasan sensor.
- Indikator status hujan.
- Indikator status sprayer.
- Grafik data sensor.
- Informasi data terakhir.

Acceptance criteria:

- Dashboard menampilkan data sensor terbaru.
- Dashboard dapat dibuka melalui desktop dan smartphone.
- Data sensor tampil dalam format yang mudah dipahami petani.

---

### 7.3 Penerimaan Data Sensor IoT

Website menyediakan API untuk menerima data dari perangkat IoT.

Data minimal yang dikirim:

- `temperature` / suhu udara.
- `air_humidity` / kelembapan udara.
- `soil_moisture` / kelembapan tanah.
- `rain_status` / status hujan.
- `sprayer_status` / status pompa sprayer.
- `recorded_at` / waktu pembacaan sensor.

Functional requirements:

- Perangkat IoT dapat mengirim data sensor ke website.
- Website menyimpan data sensor ke database.
- Website menampilkan data terbaru pada dashboard.
- Website menyimpan data historis untuk riwayat.

Acceptance criteria:

- Data sensor yang valid tersimpan ke database.
- Data terbaru muncul pada dashboard.
- Riwayat data sensor dapat dilihat kembali.

---

### 7.4 Status Kondisi Lingkungan

Sistem menampilkan status kondisi lingkungan berdasarkan data sensor. Status ini digunakan sebagai dasar informasi monitoring dan penyemprotan otomatis.

Status yang digunakan mengikuti proposal website:

| Status | Keterangan |
|---|---|
| Normal | Kondisi lingkungan masih aman |
| Waspada | Kondisi lingkungan perlu diperhatikan berdasarkan threshold |
| Kritis | Kondisi lingkungan memenuhi aturan untuk tindakan penyemprotan |

Aturan awal penyemprotan mengikuti flow proposal IoT:

- Jika tanah kering dan tidak hujan, kondisi dapat masuk status kritis dan sprayer dapat aktif pada mode otomatis.
- Jika tanah basah atau sedang hujan, sprayer tidak aktif.
- Status hujan tetap ditampilkan sebagai informasi sensor dan menjadi penghambat penyemprotan otomatis.
- Threshold nilai sensor dapat diatur oleh Admin agar sesuai dengan kebutuhan pengujian.

Acceptance criteria:

- Status kondisi berubah sesuai data sensor dan threshold.
- Status normal, waspada, dan kritis tampil pada dashboard.
- Saat sensor mendeteksi hujan, sistem tidak menjalankan penyemprotan otomatis.
- Threshold dapat diubah oleh Admin.

---

### 7.5 Kontrol Penyemprotan

Website mendukung kontrol penyemprotan manual dan otomatis.

Mode manual:

- Petani/Admin dapat menyalakan sprayer melalui website.
- Petani/Admin dapat mematikan sprayer melalui website.
- Aktivitas tersimpan pada riwayat penyemprotan.

Mode otomatis:

- Sistem menentukan status penyemprotan berdasarkan data sensor.
- Sprayer aktif jika kondisi memenuhi aturan penyemprotan.
- Sprayer mati jika tanah tidak kering atau sensor mendeteksi hujan.

Acceptance criteria:

- Tombol manual dapat mengubah status sprayer.
- Mode otomatis mengikuti aturan sensor.
- Semua aktivitas penyemprotan tersimpan pada log.

---

### 7.6 Notifikasi WhatsApp

Website mengirim notifikasi WhatsApp untuk informasi penting.

Trigger notifikasi sesuai scope proposal:

- Kondisi lingkungan masuk status kritis atau memenuhi aturan penyemprotan.
- Penyemprotan dimulai.
- Penyemprotan dihentikan.
- Kondisi hujan terdeteksi sehingga penyemprotan tidak dilakukan.

Isi pesan minimal:

- Nama alat/lahan.
- Jenis informasi/peringatan.
- Nilai sensor terakhir.
- Status sprayer.
- Waktu kejadian.

Acceptance criteria:

- Notifikasi terkirim saat kondisi penting terjadi.
- Setiap pengiriman notifikasi tersimpan pada riwayat notifikasi.
- Nomor WhatsApp penerima dapat diatur oleh Admin.

---

### 7.7 Riwayat Data

Sistem menyimpan data agar dapat dilihat kembali.

Riwayat yang disediakan:

- Riwayat data sensor.
- Riwayat penyemprotan.
- Riwayat notifikasi WhatsApp.

Acceptance criteria:

- User dapat melihat riwayat data sensor.
- User dapat melihat riwayat penyemprotan.
- User dapat melihat riwayat notifikasi.

---

### 7.8 Halaman Ringkasan Publik

Halaman ini digunakan untuk menampilkan ringkasan visual non-sensitif bagi masyarakat/pengunjung.

Data yang boleh ditampilkan:

- Status kondisi lahan secara umum.
- Data sensor terbaru secara ringkas.
- Waktu update terakhir.

Data yang tidak boleh ditampilkan:

- Tombol kontrol sprayer.
- Nomor WhatsApp.
- Data login user.
- Konfigurasi alat.

Acceptance criteria:

- Halaman dapat dibuka tanpa login.
- Tidak ada fitur kontrol alat pada halaman publik.
- Tidak ada data sensitif yang tampil.

## 8. Halaman Website

| Halaman              | Role          | Deskripsi                                 |
| -------------------- | ------------- | ----------------------------------------- |
| Login                | Admin, Petani | Masuk ke sistem                           |
| Dashboard            | Admin, Petani | Monitoring data sensor dan status sprayer |
| Kontrol Sprayer      | Admin, Petani | Kontrol manual dan mode otomatis          |
| Riwayat Sensor       | Admin, Petani | Riwayat pembacaan sensor                  |
| Riwayat Penyemprotan | Admin, Petani | Log aktivitas sprayer                     |
| Riwayat Notifikasi   | Admin, Petani | Log pesan WhatsApp                        |
| Pengguna             | Admin         | Manajemen akun pengguna                   |
| Konfigurasi Alat     | Admin         | Pengaturan alat dan threshold             |
| Pengaturan WhatsApp  | Admin         | Pengaturan nomor penerima notifikasi      |
| Ringkasan Publik     | Publik/Masyarakat | Ringkasan visual non-sensitif             |

## 9. Alur Sistem

### 9.1 Alur Monitoring

1. ESP32 membaca data sensor BME280, soil moisture, dan sensor hujan.
2. Perangkat mengirim data ke website.
3. Website menyimpan data sensor.
4. Website menampilkan data pada dashboard.
5. Website menentukan status kondisi lingkungan.

### 9.2 Alur Penyemprotan Otomatis

1. Data sensor masuk ke website.
2. Sistem mengecek kelembapan tanah dan status hujan.
3. Jika tanah kering dan tidak hujan, status penyemprotan dapat aktif.
4. Jika tanah basah atau hujan, penyemprotan tidak aktif.
5. Aktivitas penyemprotan disimpan pada riwayat.
6. Notifikasi WhatsApp dikirim jika diperlukan.

### 9.3 Alur Kontrol Manual

1. User login.
2. User membuka halaman kontrol sprayer.
3. User menekan tombol nyalakan atau matikan sprayer.
4. Website menyimpan perintah kontrol.
5. Perangkat membaca atau menerima status kontrol.
6. Aktivitas tersimpan pada riwayat penyemprotan.

## 10. Struktur Database

### `users`

- `id`
- `name`
- `email`
- `password`
- `role` (`admin`, `petani`)
- `phone_number`
- `created_at`
- `updated_at`

### `devices`

- `id`
- `name`
- `location`
- `api_key`
- `mode` (`manual`, `automatic`)
- `sprayer_status` (`on`, `off`)
- `created_at`
- `updated_at`

### `sensor_readings`

- `id`
- `device_id`
- `temperature`
- `air_humidity`
- `soil_moisture`
- `rain_status`
- `sprayer_status`
- `condition_status` (`normal`, `waspada`, `kritis`)
- `recorded_at`
- `created_at`

### `threshold_settings`

- `id`
- `device_id`
- `min_soil_moisture`
- `max_temperature`
- `min_air_humidity`
- `created_at`
- `updated_at`

### `spray_logs`

- `id`
- `device_id`
- `trigger_type` (`manual`, `automatic`)
- `status` (`on`, `off`)
- `reason`
- `created_by`
- `created_at`

### `notification_logs`

- `id`
- `device_id`
- `type`
- `recipient_phone`
- `message`
- `status` (`sent`, `failed`)
- `sent_at`
- `created_at`

## 11. API Requirements

### 11.1 Kirim Data Sensor dari IoT ke Website

`POST /api/sensor-readings`

Payload contoh:

```json
{
  "api_key": "DEVICE_API_KEY",
  "temperature": 31.5,
  "air_humidity": 70,
  "soil_moisture": 35,
  "rain_status": "no_rain",
  "sprayer_status": "off",
  "recorded_at": "2026-05-20 10:00:00"
}
```

Response contoh:

```json
{
  "success": true,
  "condition_status": "kritis",
  "mode": "automatic",
  "sprayer_command": "on"
}
```

### 11.2 Baca Perintah Sprayer oleh IoT

`GET /api/devices/{device}/command`

Response contoh:

```json
{
  "mode": "automatic",
  "sprayer_command": "on"
}
```

### 11.3 Kontrol Sprayer dari Website

- `POST /devices/{device}/sprayer/on`
- `POST /devices/{device}/sprayer/off`
- `POST /devices/{device}/mode`

## 12. Business Rules

- Website hanya memproses data dari perangkat yang terdaftar.
- Data sensor disimpan sebagai riwayat monitoring.
- Mode otomatis menggunakan status kondisi, data kelembapan tanah, dan status hujan sebagai dasar utama penyemprotan.
- Penyemprotan otomatis tidak dilakukan saat sensor mendeteksi hujan.
- Kontrol manual hanya dapat dilakukan oleh Admin dan Petani yang login.
- Aktivitas penyemprotan harus dicatat pada log.
- Notifikasi WhatsApp dikirim untuk kejadian penting sesuai scope sistem.
- Halaman publik tidak boleh menampilkan kontrol alat atau data sensitif.

## 13. UI/UX Requirements

- Tampilan sederhana dan mudah dipahami petani.
- Dashboard nyaman dibuka melalui smartphone.
- Gunakan indikator warna yang jelas:
  - Hijau: kondisi normal.
  - Kuning/oranye: waspada/perlu perhatian.
  - Biru/abu-abu: hujan atau sprayer tidak aktif.
  - Merah: kondisi kritis.
- Tombol kontrol sprayer harus jelas.
- Tampilkan waktu data terakhir diterima.

## 14. Tech Stack

- Framework: Laravel.
- Bahasa: PHP.
- Database: MySQL.
- ORM: Eloquent ORM.
- Frontend: Blade/Livewire atau stack Laravel lain sesuai kebutuhan.
- Styling: Tailwind CSS.
- Chart: Chart.js atau ApexCharts.
- API: REST API menggunakan Laravel Route/Controller.
- Notifikasi: WhatsApp Gateway/API.
- Testing: Black Box Testing.

## 15. Task Breakdown

### 15.1 Setup Proyek

- [ ] Install dan konfigurasi Laravel.
- [ ] Konfigurasi MySQL.
- [ ] Konfigurasi Tailwind CSS.
- [ ] Buat layout dasar dashboard.
- [ ] Buat seed user Admin.

### 15.2 Auth dan User

- [ ] Buat login dan logout.
- [ ] Buat role Admin dan Petani.
- [ ] Buat middleware role.
- [ ] Buat manajemen user untuk Admin.

### 15.3 Database

- [ ] Buat tabel `users`.
- [ ] Buat tabel `devices`.
- [ ] Buat tabel `sensor_readings`.
- [ ] Buat tabel `threshold_settings`.
- [ ] Buat tabel `spray_logs`.
- [ ] Buat tabel `notification_logs`.

### 15.4 API IoT

- [ ] Buat endpoint input data sensor.
- [ ] Validasi perangkat terdaftar.
- [ ] Simpan data sensor.
- [ ] Hitung status kondisi lingkungan.
- [ ] Kirim response perintah sprayer.
- [ ] Buat endpoint baca command sprayer.

### 15.5 Dashboard

- [ ] Buat halaman dashboard.
- [ ] Tampilkan suhu udara.
- [ ] Tampilkan kelembapan udara.
- [ ] Tampilkan kelembapan tanah.
- [ ] Tampilkan status hujan.
- [ ] Tampilkan status sprayer.
- [ ] Tampilkan grafik sensor real-time dan historis.

### 15.6 Kontrol Sprayer

- [ ] Buat halaman kontrol sprayer.
- [ ] Buat tombol sprayer on.
- [ ] Buat tombol sprayer off.
- [ ] Buat pengaturan mode manual/otomatis.
- [ ] Simpan log penyemprotan.

### 15.7 Notifikasi WhatsApp

- [ ] Buat konfigurasi WhatsApp Gateway/API.
- [ ] Buat pengaturan nomor penerima.
- [ ] Buat template pesan.
- [ ] Kirim notifikasi saat kondisi kritis/perlu penyemprotan.
- [ ] Kirim notifikasi saat sprayer mulai/berhenti.
- [ ] Kirim notifikasi saat hujan terdeteksi.
- [ ] Simpan log notifikasi.

### 15.8 Riwayat

- [ ] Buat halaman riwayat sensor.
- [ ] Buat halaman riwayat penyemprotan.
- [ ] Buat halaman riwayat notifikasi.

### 15.9 Halaman Publik

- [ ] Buat halaman ringkasan publik.
- [ ] Tampilkan data sensor terbaru secara ringkas.
- [ ] Sembunyikan kontrol alat dan data sensitif.

### 15.10 Testing

- [ ] Test login dan role.
- [ ] Test input API sensor.
- [ ] Test tampilan dashboard.
- [ ] Test aturan penyemprotan otomatis.
- [ ] Test kontrol manual.
- [ ] Test notifikasi WhatsApp.
- [ ] Test riwayat data.
- [ ] Test halaman publik.
- [ ] Test tampilan mobile.

## 16. Kriteria Selesai

Sistem dianggap selesai jika:

- Admin dan Petani dapat login sesuai hak akses.
- Website dapat menerima data sensor dari perangkat IoT.
- Dashboard menampilkan suhu udara, kelembapan udara, kelembapan tanah, status hujan, status kondisi, dan status sprayer.
- Sistem dapat menentukan status kondisi berdasarkan sensor dan threshold.
- Sprayer dapat dikontrol manual dari website.
- Mode otomatis mengikuti aturan tanah kering dan tidak hujan.
- Notifikasi WhatsApp terkirim untuk kondisi penting sesuai scope.
- Riwayat sensor, penyemprotan, dan notifikasi tersimpan.
- Halaman publik hanya menampilkan ringkasan non-sensitif.
- Tampilan dapat digunakan melalui smartphone.
- Fitur utama lulus Black Box Testing.
