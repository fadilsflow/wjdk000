# Tutorial flash ESP32 dan menjalankan server Laravel

Dokumen ini menjelaskan cara menyiapkan server Laravel, menghubungkan ESP32,
melakukan flash firmware, dan menguji sinkronisasi realtime antara perangkat
ESP32 dan dashboard Smart Sprayer IoT.

<!-- prettier-ignore -->
> [!WARNING]
> File `iot/iot.ino` saat ini berisi SSID WiFi, password WiFi, alamat backend,
> dan API key perangkat. Jangan commit atau push kredensial asli ke repository
> publik.

## Prasyarat

Pastikan perangkat dan software berikut sudah tersedia sebelum menjalankan
proses flash dan testing.

- ESP32 Dev Board dengan kabel USB data.
- Driver USB-to-Serial sesuai chip board, seperti CP2102 atau CH340.
- PHP dan Composer untuk menjalankan Laravel.
- Node.js dan npm atau bun untuk asset frontend jika diperlukan.
- PlatformIO CLI untuk compile dan upload firmware ESP32.
- Database Laravel sudah terisi device dengan `api_key` yang sama seperti
  `DEVICE_API_KEY` di `iot/iot.ino`.

## Struktur file yang dipakai

Proses flash dan server memakai beberapa file utama di repository ini. Gunakan
file berikut sebagai acuan saat mengubah konfigurasi.

- `iot/iot.ino`: firmware ESP32 untuk sensor, relay, web dashboard lokal, dan
  sinkronisasi ke Laravel.
- `iot/platformio.ini`: konfigurasi PlatformIO untuk board ESP32.
- `routes/api.php`: endpoint IoT Laravel.
- `routes/web.php`: endpoint dashboard Laravel, termasuk polling realtime.
- `app/Http/Controllers/Api/SensorReadingController.php`: controller penerima
  data sensor dari ESP32.
- `app/Services/IotSensorService.php`: logic evaluasi sensor dan perintah
  sprayer.

## Menjalankan server Laravel

Server Laravel harus berjalan di alamat IP yang bisa dijangkau oleh ESP32.
Jangan gunakan `127.0.0.1` untuk target ESP32 karena alamat itu hanya berlaku di
komputer lokal.

1. Masuk ke folder project:

   ```bash
   cd /Users/fadil/repo/web/wjdk000
   ```

2. Cek IP komputer di jaringan WiFi:

   ```bash
   ipconfig getifaddr en0
   ```

   Contoh hasil:

   ```txt
   192.168.1.8
   ```

3. Jalankan server Laravel agar bisa diakses dari perangkat lain di jaringan:

   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

4. Buka dashboard Laravel di browser komputer:

   ```txt
   http://127.0.0.1:8000/dashboard
   ```

5. Pastikan endpoint IoT tersedia:

   ```bash
   php artisan route:list --path=api
   ```

   Endpoint yang dibutuhkan:

   ```txt
   POST /api/sensor-readings
   GET  /api/devices/{device}/command
   ```

## Mengatur firmware ESP32

Firmware ESP32 harus diarahkan ke alamat IP server Laravel. Nilai ini berada di
`iot/iot.ino` pada bagian `LARAVEL API SETTINGS`.

1. Buka `iot/iot.ino`.

2. Sesuaikan WiFi dan endpoint Laravel:

   ```cpp
   const char *STA_SSID = "Nama WiFi";
   const char *STA_PASSWORD = "Password WiFi";
   const char *BACKEND_SENSOR_URL = "http://192.168.1.8:8000/api/sensor-readings";
   const char *DEVICE_API_KEY = "API_KEY_DEVICE_DARI_DATABASE";
   ```

3. Pastikan interval sinkronisasi realtime sudah pendek:

   ```cpp
   const unsigned long BACKEND_SYNC_INTERVAL_MS = 2000;
   ```

   Nilai `2000` berarti ESP32 mengirim data ke Laravel setiap 2 detik.

## Mengecek port ESP32

ESP32 harus muncul sebagai port serial sebelum bisa di-flash. Nama port bisa
berbeda tergantung chip USB-to-Serial dan sistem operasi.

1. Colok ESP32 ke komputer dengan kabel USB data.

2. Cek port dengan PlatformIO:

   ```bash
   pio device list
   ```

3. Cari port ESP32. Contoh untuk CP2102:

   ```txt
   /dev/cu.usbserial-0001
   /dev/cu.SLAB_USBtoUART
   ```

4. Jika port berbeda, update `iot/platformio.ini`:

   ```ini
   upload_port = /dev/cu.usbserial-0001
   monitor_port = /dev/cu.usbserial-0001
   ```

## Melakukan flash ESP32

Gunakan PlatformIO untuk compile dan upload firmware ke ESP32. Jalankan command
ini dari folder `iot`.

1. Masuk ke folder firmware:

   ```bash
   cd /Users/fadil/repo/web/wjdk000/iot
   ```

2. Compile dan upload firmware:

   ```bash
   pio run -t upload
   ```

3. Pastikan output berakhir dengan status sukses:

   ```txt
   ========================= [SUCCESS] =========================
   ```

4. Jika upload berhenti di tahap `Connecting`, tahan tombol **BOOT** di ESP32
   saat proses upload dimulai, lalu lepaskan setelah proses menulis flash
   berjalan.

## Memantau log ESP32

Serial monitor membantu memastikan ESP32 berhasil terhubung ke WiFi dan Laravel.
Gunakan baud rate `115200`.

1. Jalankan monitor serial:

   ```bash
   cd /Users/fadil/repo/web/wjdk000/iot
   pio device monitor --port /dev/cu.usbserial-0001 --baud 115200
   ```

2. Pastikan log berisi koneksi WiFi:

   ```txt
   WiFi STA connected. IP: 192.168.1.25
   ```

3. Pastikan sinkronisasi Laravel berhasil:

   ```txt
   Laravel sync HTTP 200: {"success":true,"condition_status":"kritis","mode":"automatic","sprayer_command":"on"}
   ```

4. Pastikan interval sinkronisasi sekitar 2 detik:

   ```txt
   13s | Laravel sync HTTP 200
   15s | Laravel sync HTTP 200
   17s | Laravel sync HTTP 200
   ```

## Menguji dashboard realtime

Dashboard Laravel mengambil data terbaru lewat polling endpoint
`/dashboard/latest` setiap 2 detik. Halaman dashboard tidak perlu di-refresh
manual untuk melihat data baru.

1. Jalankan server Laravel:

   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

2. Buka dashboard:

   ```txt
   http://127.0.0.1:8000/dashboard
   ```

3. Biarkan halaman terbuka selama beberapa detik.

4. Pastikan nilai sensor, status kondisi, status sprayer, dan chart berubah
   otomatis mengikuti data terbaru dari ESP32.

5. Optional: buka dashboard lokal ESP32 dengan menghubungkan laptop atau HP ke
   WiFi access point ESP32:

   ```txt
   SSID: Watering-System
   Password: 12345678
   URL: http://192.168.4.1
   ```

## Menguji endpoint Laravel secara manual

Gunakan `curl` untuk memastikan Laravel bisa menerima payload ESP32 sebelum
menguji perangkat fisik.

1. Kirim payload sensor ke Laravel:

   ```bash
   curl -X POST http://127.0.0.1:8000/api/sensor-readings \
     -H 'Content-Type: application/json' \
     -H 'X-Api-Key: API_KEY_DEVICE_DARI_DATABASE' \
     -d '{
       "temperature": 30.4,
       "humidity": 84.8,
       "soilPercent": 0,
       "raining": false,
       "pumpOn": false,
       "soilRaw": 4095,
       "rainRaw": 4095,
       "simulationMode": false
     }'
   ```

2. Pastikan response berhasil:

   ```json
   {
     "success": true,
     "condition_status": "kritis",
     "mode": "automatic",
     "sprayer_command": "on"
   }
   ```

## Troubleshooting

Bagian ini merangkum masalah umum saat flash dan testing ESP32 dengan Laravel.
Ikuti gejala yang sesuai dengan kondisi perangkat.

### ESP32 tidak muncul di `pio device list`

Masalah ini biasanya terjadi karena kabel USB, driver, atau port komputer.
Cek hal berikut secara berurutan.

- Gunakan kabel USB data, bukan kabel charge-only.
- Coba port USB lain.
- Cek LED power ESP32 menyala.
- Instal driver CP2102 jika port muncul sebagai Silicon Labs CP210x.
- Instal driver CH340 jika board memakai chip CH340.
- Restart komputer setelah instal driver.

### Upload gagal di tahap `Connecting`

Masalah ini biasanya terjadi karena board tidak masuk bootloader mode. Gunakan
mode upload manual.

1. Jalankan upload:

   ```bash
   pio run -t upload
   ```

2. Saat muncul `Connecting`, tahan tombol **BOOT**.

3. Lepaskan tombol **BOOT** ketika proses `Writing at` sudah berjalan.

4. Tekan tombol **EN** atau **RESET** setelah upload selesai jika board tidak
   reboot otomatis.

### ESP32 WiFi connected, tetapi Laravel sync gagal

Masalah ini biasanya terjadi karena alamat backend salah atau firewall menolak
akses dari ESP32.

- Pastikan `BACKEND_SENSOR_URL` memakai IP komputer, bukan `127.0.0.1`.
- Pastikan Laravel berjalan dengan `--host=0.0.0.0`.
- Pastikan ESP32 dan komputer berada di jaringan WiFi yang sama.
- Pastikan port `8000` tidak diblokir firewall.
- Pastikan `DEVICE_API_KEY` sama dengan kolom `api_key` di tabel `devices`.

### Dashboard tidak update otomatis

Dashboard memakai polling JavaScript ke endpoint `/dashboard/latest`. Jika data
tidak berubah, cek sisi server dan browser.

- Pastikan kamu sudah login ke dashboard Laravel.
- Pastikan `GET /dashboard/latest` tersedia di `php artisan route:list --path=dashboard`.
- Buka DevTools browser dan cek tab **Network** untuk request
  `/dashboard/latest` setiap 2 detik.
- Pastikan ESP32 menulis `Laravel sync HTTP 200` di serial monitor.

## Perintah ringkas harian

Gunakan rangkaian command ini untuk workflow paling umum saat development.

1. Jalankan server Laravel:

   ```bash
   cd /Users/fadil/repo/web/wjdk000
   php artisan serve --host=0.0.0.0 --port=8000
   ```

2. Flash ESP32:

   ```bash
   cd /Users/fadil/repo/web/wjdk000/iot
   pio run -t upload
   ```

3. Monitor ESP32:

   ```bash
   pio device monitor --port /dev/cu.usbserial-0001 --baud 115200
   ```

4. Buka dashboard Laravel:

   ```txt
   http://127.0.0.1:8000/dashboard
   ```

## Next steps

Setelah proses flash dan testing stabil, pertimbangkan memindahkan kredensial
ESP32 ke mekanisme konfigurasi yang lebih aman dan menambahkan MQTT atau
WebSocket jika membutuhkan realtime tanpa polling.
