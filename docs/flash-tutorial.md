# Flash ESP32 dari laptop lain

Dokumen ini menjelaskan cara flash firmware ESP32 dari laptop lain. Ada dua
opsi: PlatformIO dan Arduino IDE.

## Prasyarat

Siapkan kebutuhan berikut sebelum flash ESP32.

- Clone repository project.
- Gunakan kabel USB data.
- Install driver CP210x karena board ESP32 terdeteksi sebagai CP2102.
- Pastikan ESP32 dan server Laravel berada di jaringan WiFi yang sama.

## Opsi 1: PlatformIO

PlatformIO direkomendasikan karena konfigurasi project sudah tersedia di
`iot/platformio.ini`.

1. Clone repository:

   ```bash
   git clone https://github.com/fadilsflow/wjdk000
   cd wjdk000/iot
   ```

2. Install PlatformIO:

   ```bash
   pip install platformio
   ```

3. Cek port ESP32:

   ```bash
   pio device list
   ```

4. Jika port berbeda, edit `iot/platformio.ini`:

   ```ini
   upload_port = /dev/cu.usbserial-0001
   monitor_port = /dev/cu.usbserial-0001
   ```

   Untuk Windows, contoh port biasanya seperti ini:

   ```ini
   upload_port = COM3
   monitor_port = COM3
   ```

5. Edit `iot/iot.ino` sesuai WiFi dan server Laravel:

   ```cpp
   const char *STA_SSID = "Nama WiFi";
   const char *STA_PASSWORD = "Password WiFi";
   const char *BACKEND_SENSOR_URL = "http://IP_LAPTOP_SERVER:8000/api/sensor-readings";
   const char *DEVICE_API_KEY = "API_KEY_DEVICE";
   ```

6. Flash ESP32:

   ```bash
   pio run -t upload
   ```

7. Monitor ESP32:

   ```bash
   pio device monitor --baud 115200
   ```

## Opsi 2: Arduino IDE

Arduino IDE bisa digunakan jika tidak ingin memakai terminal atau PlatformIO.

1. Install Arduino IDE.

2. Tambahkan ESP32 board URL di **Preferences**:

   ```txt
   https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json
   ```

3. Buka **Boards Manager**, cari `esp32`, lalu install **esp32 by Espressif
   Systems**.

4. Buka **Library Manager**, lalu install library berikut:

   - `DHT sensor library` by Adafruit
   - `Adafruit Unified Sensor`

5. Buka file firmware:

   ```txt
   iot/iot.ino
   ```

6. Pilih board dan port:

   ```txt
   Board: ESP32 Dev Module
   Port: port ESP32 yang terdeteksi
   ```

7. Klik **Upload**.

8. Jika upload berhenti di `Connecting...`, tahan tombol **BOOT** di ESP32
   sampai proses upload mulai berjalan.

## Jika server Laravel juga pindah laptop

Jika Laravel dijalankan di laptop baru, update IP backend di `iot/iot.ino`.

1. Cari IP laptop server.

   macOS:

   ```bash
   ipconfig getifaddr en0
   ```

   Windows:

   ```bat
   ipconfig
   ```

2. Update endpoint ESP32:

   ```cpp
   const char *BACKEND_SENSOR_URL = "http://IP_LAPTOP_BARU:8000/api/sensor-readings";
   ```

3. Jalankan Laravel agar bisa diakses ESP32:

   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

4. Pastikan ESP32 dan laptop server memakai WiFi yang sama.
