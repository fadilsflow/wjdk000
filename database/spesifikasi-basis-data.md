# 4.3.5 Spesifikasi Basis Data (Struktur Tabel)

Spesifikasi basis data merupakan penjabaran detail mengenai rancangan tabel yang digunakan di dalam sistem. Basis data ini berfungsi sebagai media penyimpanan (*storage*) untuk seluruh data operasional website. Berdasarkan perancangan sistem yang telah dimodelkan sebelumnya, basis data pada proyek ini (**smart_sprayer_iot**) terdiri dari tujuh tabel utama, yang rincian strukturnya dijabarkan sebagai berikut:

## 1. Tabel Users

Tabel **users** berfungsi untuk menyimpan data identitas dan kredensial (password terenkripsi) milik Administrator dan Petani yang memiliki hak akses sistem. Setiap pengguna memiliki peran (*role*) yang menentukan tingkat akses terhadap fitur-fitur yang tersedia. Rincian spesifikasi struktur tabel ini dapat dilihat pada Tabel 4.5 di bawah ini.

**Tabel 4.5 Struktur Tabel users**

| No | Nama Field        | Tipe Data | Panjang | Keterangan                                    |
| -- | ----------------- | --------- | ------- | --------------------------------------------- |
| 1  | id                | BIGINT    | 20      | Primary Key, Auto Increment                   |
| 2  | name              | VARCHAR   | 255     | Menyimpan nama lengkap pengguna               |
| 3  | email             | VARCHAR   | 255     | Menyimpan alamat surel (Unique)               |
| 4  | email_verified_at | TIMESTAMP | -       | Menyimpan waktu verifikasi email              |
| 5  | password          | VARCHAR   | 255     | Menyimpan kata sandi terenkripsi              |
| 6  | role              | VARCHAR   | 255     | Menyimpan peran pengguna (Default: 'petani')  |
| 7  | phone_number      | VARCHAR   | 20      | Menyimpan nomor telepon pengguna              |
| 8  | remember_token    | VARCHAR   | 100     | Kolom legacy Laravel (tidak dipakai)          |
| 9  | created_at        | TIMESTAMP | -       | Waktu data dibuat                             |
| 10 | updated_at        | TIMESTAMP | -       | Waktu data terakhir diubah                    |

## 2. Tabel Devices

Tabel **devices** berfungsi untuk menyimpan data registrasi dan konfigurasi setiap perangkat IoT (ESP32) yang terpasang di lahan pertanian. Setiap perangkat memiliki *API key* unik sebagai mekanisme autentikasi saat mengirim data sensor. Spesifikasi atribut pada tabel ini dapat dilihat pada Tabel 4.6 di bawah ini.

**Tabel 4.6 Struktur Tabel devices**

| No | Nama Field     | Tipe Data | Panjang | Keterangan                                       |
| -- | -------------- | --------- | ------- | ------------------------------------------------ |
| 1  | id             | BIGINT    | 20      | Primary Key, Auto Increment                      |
| 2  | name           | VARCHAR   | 255     | Menyimpan nama perangkat IoT                     |
| 3  | location       | VARCHAR   | 255     | Menyimpan lokasi pemasangan perangkat            |
| 4  | api_key        | VARCHAR   | 255     | Menyimpan kunci API autentikasi perangkat (Unique) |
| 5  | mode           | VARCHAR   | 255     | Menyimpan mode operasi (manual/automatic)        |
| 6  | sprayer_status | VARCHAR   | 255     | Menyimpan status sprayer (on/off)                |
| 7  | created_at     | TIMESTAMP | -       | Waktu perangkat didaftarkan                      |
| 8  | updated_at     | TIMESTAMP | -       | Waktu data perangkat diperbarui                  |

## 3. Tabel Threshold Settings

Tabel **threshold_settings** berfungsi untuk menyimpan konfigurasi batas (*threshold*) kondisi lingkungan yang digunakan sebagai acuan penentuan status kondisi dan keputusan penyemprotan otomatis. Setiap perangkat hanya memiliki satu konfigurasi threshold. Spesifikasi atribut pada tabel ini dapat dilihat pada Tabel 4.7 di bawah ini.

**Tabel 4.7 Struktur Tabel threshold_settings**

| No | Nama Field         | Tipe Data | Panjang | Keterangan                                           |
| -- | ------------------ | --------- | ------- | ---------------------------------------------------- |
| 1  | id                 | BIGINT    | 20      | Primary Key, Auto Increment                          |
| 2  | device_id          | BIGINT    | 20      | Foreign Key ke tabel devices (Unique)                |
| 3  | min_soil_moisture  | DECIMAL   | 5,2     | Menyimpan batas minimum kelembaban tanah (%)         |
| 4  | max_temperature    | DECIMAL   | 5,2     | Menyimpan batas maksimum suhu (°C)                   |
| 5  | min_air_humidity   | DECIMAL   | 5,2     | Menyimpan batas minimum kelembaban udara (%)         |
| 6  | created_at         | TIMESTAMP | -       | Waktu data dibuat                                    |
| 7  | updated_at         | TIMESTAMP | -       | Waktu data terakhir diubah                           |

## 4. Tabel Sensor Readings

Tabel **sensor_readings** berfungsi untuk menyimpan data hasil pembacaan sensor yang dikirim oleh perangkat ESP32 secara periodik. Data yang dicakup meliputi suhu (*temperature*), kelembaban udara (*air humidity*), kelembaban tanah (*soil moisture*), dan status hujan (*rain status*). Tabel ini juga mencatat status kondisi lingkungan (*condition status*) hasil evaluasi terhadap threshold. Spesifikasi atribut pada tabel ini dapat dilihat pada Tabel 4.8 di bawah ini.

**Tabel 4.8 Struktur Tabel sensor_readings**

| No | Nama Field      | Tipe Data       | Panjang | Keterangan                                           |
| -- | --------------- | --------------- | ------- | ---------------------------------------------------- |
| 1  | id              | BIGINT          | 20      | Primary Key, Auto Increment                          |
| 2  | device_id       | BIGINT          | 20      | Foreign Key ke tabel devices                         |
| 3  | temperature     | DECIMAL         | 5,2     | Menyimpan data suhu lingkungan (°C)                  |
| 4  | air_humidity    | DECIMAL         | 5,2     | Menyimpan data kelembaban udara (%)                  |
| 5  | soil_moisture   | DECIMAL         | 5,2     | Menyimpan data kelembaban tanah (%)                  |
| 6  | soil_raw        | SMALLINT UNSIGNED | -     | Menyimpan nilai mentah ADC sensor tanah (nullable)   |
| 7  | rain_status     | VARCHAR         | 255     | Menyimpan status hujan (rain/no_rain)                |
| 8  | rain_raw        | SMALLINT UNSIGNED | -     | Menyimpan nilai mentah ADC sensor hujan (nullable)   |
| 9  | sprayer_status  | VARCHAR         | 255     | Menyimpan status sprayer saat pembacaan (on/off)     |
| 10 | simulation_mode | BOOLEAN         | -       | Menandakan data berasal dari mode simulasi           |
| 11 | condition_status| VARCHAR         | 255     | Menyimpan status kondisi (normal/waspada/kritis)     |
| 12 | recorded_at     | TIMESTAMP       | -       | Menyimpan waktu data direkam oleh perangkat           |
| 13 | created_at      | TIMESTAMP       | -       | Waktu data dibuat                                    |
| 14 | updated_at      | TIMESTAMP       | -       | Waktu data terakhir diubah                           |

## 5. Tabel Spray Logs

Tabel **spray_logs** berfungsi untuk mencatat seluruh riwayat aktivitas penyemprotan yang dilakukan oleh sistem, baik yang dipicu secara otomatis maupun manual oleh pengguna. Setiap perubahan status sprayer wajib dicatat pada tabel ini sebagai jejak audit (*audit trail*). Spesifikasi atribut pada tabel ini dapat dilihat pada Tabel 4.9 di bawah ini.

**Tabel 4.9 Struktur Tabel spray_logs**

| No | Nama Field   | Tipe Data | Panjang | Keterangan                                         |
| -- | ------------ | --------- | ------- | -------------------------------------------------- |
| 1  | id           | BIGINT    | 20      | Primary Key, Auto Increment                        |
| 2  | device_id    | BIGINT    | 20      | Foreign Key ke tabel devices                       |
| 3  | trigger_type | VARCHAR   | 255     | Menyimpan jenis pemicu (manual/automatic)          |
| 4  | status       | VARCHAR   | 255     | Menyimpan status sprayer (on/off)                  |
| 5  | reason       | TEXT      | -       | Menyimpan alasan atau keterangan penyemprotan      |
| 6  | created_by   | BIGINT    | 20      | Foreign Key ke tabel users (nullable)              |
| 7  | created_at   | TIMESTAMP | -       | Waktu log dibuat                                   |
| 8  | updated_at   | TIMESTAMP | -       | Waktu data terakhir diubah                         |

## 6. Tabel Notification Logs

Tabel **notification_logs** berfungsi untuk mencatat seluruh riwayat pengiriman notifikasi WhatsApp yang dikirim oleh sistem kepada petani. Setiap notifikasi yang dikirimkan, baik untuk kondisi kritis, perubahan status sprayer, maupun deteksi hujan, dicatat lengkap dengan status pengirimannya. Spesifikasi atribut pada tabel ini dapat dilihat pada Tabel 4.10 di bawah ini.

**Tabel 4.10 Struktur Tabel notification_logs**

| No | Nama Field      | Tipe Data | Panjang | Keterangan                                    |
| -- | --------------- | --------- | ------- | --------------------------------------------- |
| 1  | id              | BIGINT    | 20      | Primary Key, Auto Increment                   |
| 2  | device_id       | BIGINT    | 20      | Foreign Key ke tabel devices                  |
| 3  | type            | VARCHAR   | 255     | Menyimpan jenis notifikasi                    |
| 4  | recipient_phone | VARCHAR   | 20      | Menyimpan nomor telepon penerima              |
| 5  | message         | TEXT      | -       | Menyimpan isi pesan notifikasi                |
| 6  | status          | VARCHAR   | 255     | Menyimpan status pengiriman (sent/failed)     |
| 7  | sent_at         | TIMESTAMP | -       | Menyimpan waktu notifikasi dikirim            |
| 8  | created_at      | TIMESTAMP | -       | Waktu data dibuat                             |
| 9  | updated_at      | TIMESTAMP | -       | Waktu data terakhir diubah                    |

## 7. Tabel Whatsapp Settings

Tabel **whatsapp_settings** berfungsi untuk menyimpan konfigurasi pengaturan notifikasi WhatsApp, berupa nomor telepon penerima dan template pesan untuk setiap jenis notifikasi. Template ini digunakan sebagai kerangka pesan yang akan dikirimkan kepada petani ketika terjadi kondisi tertentu pada sistem. Spesifikasi atribut pada tabel ini dapat dilihat pada Tabel 4.11 di bawah ini.

**Tabel 4.11 Struktur Tabel whatsapp_settings**

| No | Nama Field                   | Tipe Data | Panjang | Keterangan                                         |
| -- | ---------------------------- | --------- | ------- | -------------------------------------------------- |
| 1  | id                           | BIGINT    | 20      | Primary Key, Auto Increment                        |
| 2  | recipient_phone              | VARCHAR   | 20      | Menyimpan nomor telepon penerima notifikasi        |
| 3  | critical_condition_template  | TEXT      | -       | Menyimpan template pesan kondisi kritis            |
| 4  | spray_start_template         | TEXT      | -       | Menyimpan template pesan sprayer mulai             |
| 5  | spray_stop_template          | TEXT      | -       | Menyimpan template pesan sprayer berhenti          |
| 6  | rain_detected_template       | TEXT      | -       | Menyimpan template pesan hujan terdeteksi          |
| 7  | created_at                   | TIMESTAMP | -       | Waktu data dibuat                                  |
| 8  | updated_at                   | TIMESTAMP | -       | Waktu data terakhir diubah                         |
