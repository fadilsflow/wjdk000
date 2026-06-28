# Deploy Smart Sprayer IoT ke Render

Dokumen ini menjelaskan cara deploy image Docker Smart Sprayer IoT ke
Render.com. Image ini menjalankan Laravel, Apache/PHP, queue worker, scheduler,
Chromium, dan WhatsApp Gateway dalam satu container.

Panduan ini memakai database Aiven MySQL yang sama dengan `.env` lokal. Karena
itu, data aplikasi tetap tersimpan di MySQL walaupun container Render restart.

<!-- prettier-ignore -->
> [!IMPORTANT]
> Jangan commit `.env.prod` atau file env lain ke Git. File tersebut berisi
> kredensial database dan token WhatsApp Gateway.

## Prasyarat

Sebelum deploy, pastikan beberapa hal ini sudah siap.

- Akun Render.com.
- Image GHCR sudah berhasil dibuat:
  `ghcr.io/fadilsflow/wjdk000:latest`.
- Package GHCR bisa di-pull oleh Render. Jika package masih private, ubah
  visibility package menjadi public di GitHub Packages, atau pakai kredensial
  registry di Render jika tersedia.
- File `.env.prod` sudah ada di lokal.
- Database Aiven MySQL bisa diakses dari internet.

## Buat web service dari Docker image

Buat service Render baru dari image Docker yang sudah dipublish ke GHCR.

1. Buka **Render Dashboard**.
2. Klik **New +**.
3. Pilih **Web Service**.
4. Pilih opsi **Deploy an existing image**.
5. Isi **Image URL** dengan nilai berikut:

   ```txt
   ghcr.io/fadilsflow/wjdk000:latest
   ```

6. Isi **Name**, misalnya:

   ```txt
   smart-sprayer-iot
   ```

7. Jika Render meminta port, isi:

   ```txt
   80
   ```

8. Isi **Health Check Path** dengan:

   ```txt
   /up
   ```

9. Jangan deploy dulu kalau environment variables belum diisi.

Setelah service dibuat, Render memberi URL seperti
`https://smart-sprayer-iot.onrender.com`. URL itu akan dipakai sebagai
`APP_URL`.

## Isi environment variables

Render bisa menerima banyak environment variables sekaligus dari format `.env`.
Gunakan isi dari `.env.prod`, lalu ganti `APP_URL` dengan URL service Render.

1. Buka file `.env.prod` di lokal.
2. Ganti baris ini:

   ```dotenv
   APP_URL=https://CHANGE-ME.onrender.com
   ```

   menjadi URL Render service kamu, misalnya:

   ```dotenv
   APP_URL=https://smart-sprayer-iot.onrender.com
   ```

3. Copy seluruh isi `.env.prod`.
4. Di Render service, buka tab **Environment**.
5. Klik **Add from .env**.
6. Paste isi `.env.prod`.
7. Simpan perubahan.

Environment penting yang harus ada untuk Render adalah sebagai berikut.

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://smart-sprayer-iot.onrender.com
LOG_CHANNEL=stderr
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_URL="mysql://avnadmin:AVNS_K8PtFzy6p7OIQ35iV06@mysql-25a8a780-boyblaco77-766b.f.aivencloud.com:24558/Smart_Sprayer?ssl-mode=REQUIRED"
DB_HOST=mysql-25a8a780-boyblaco77-766b.f.aivencloud.com
DB_PORT=24558
DB_DATABASE=Smart_Sprayer
DB_USERNAME=avnadmin
DB_PASSWORD="AVNS_K8PtFzy6p7OIQ35iV06"

GATEWAY_PORT=3000
WHATSAPP_GATEWAY_URL=http://127.0.0.1:3000/send
WHATSAPP_GATEWAY_TOKEN=isi-token-yang-sama
GATEWAY_SECRET_TOKEN=isi-token-yang-sama
WHATSAPP_AUTH_DATA_PATH=/var/www/html/storage/app/whatsapp-auth

RUN_MIGRATIONS=true
RUN_SEEDERS_ONCE=true
RUN_QUEUE_WORKER=true
RUN_SCHEDULER=true
RUN_OPTIMIZE=true
```

<!-- prettier-ignore -->
> [!WARNING]
> Jangan pakai `WHATSAPP_GATEWAY_URL=http://localhost:3000/send` di Render.
> Pakai `http://127.0.0.1:3000/send` supaya Laravel memanggil gateway internal
> di container yang sama.

## Jika tidak ada disk atau mount path

Jika Render tidak menampilkan menu **Disk** atau **Mount Path**, lanjutkan deploy
tanpa disk. Ini biasanya terjadi pada plan atau tipe service tertentu.

Deploy tetap bisa berjalan karena database memakai Aiven MySQL. Namun, data yang
tersimpan di filesystem container tidak persisten.

Tanpa disk, efeknya adalah:

- Session WhatsApp Web bisa hilang saat service restart atau redeploy.
- Kamu mungkin harus scan QR WhatsApp ulang dari halaman admin.
- File di `storage/` yang bukan database tidak dijamin bertahan.

Untuk kondisi tanpa disk, konfigurasi ini masih bisa dipakai:

```dotenv
DB_CONNECTION=mysql
QUEUE_CONNECTION=database
CACHE_STORE=file
SESSION_DRIVER=file
WHATSAPP_AUTH_DATA_PATH=/var/www/html/storage/app/whatsapp-auth
```

Jika nanti kamu pindah ke plan Render yang menyediakan persistent disk, tambahkan
disk dengan mount path berikut:

```txt
/var/www/html/storage
```

Dengan disk tersebut, session WhatsApp dan file storage bisa bertahan saat
service restart.

## Deploy service

Setelah image, environment variables, dan health check siap, jalankan deploy dari
Render.

1. Klik **Create Web Service** atau **Deploy Latest Commit**.
2. Tunggu proses build atau pull image selesai.
3. Buka tab **Logs**.
4. Pastikan log menunjukkan service berjalan, misalnya:

   ```txt
   Apache/2.4 configured -- resuming normal operations
   [WA GATEWAY] Server berjalan di http://localhost:3000
   [WA GATEWAY] Menginisialisasi WhatsApp client...
   ```

5. Buka URL Render service.
6. Pastikan halaman utama tampil.

## Login admin dan scan WhatsApp QR

Setelah service aktif, buka halaman admin WhatsApp dan hubungkan akun.

1. Buka URL Render service.
2. Buka halaman admin WhatsApp:

   ```txt
   Email: admin@smartsprayer.test
   Password: change-me
   ```

3. Buka halaman:

   ```txt
   /admin/whatsapp
   ```

4. Scan QR dengan WhatsApp di HP.
5. Refresh halaman setelah scan.
6. Pastikan status berubah menjadi terhubung.

<!-- prettier-ignore -->
> [!IMPORTANT]
> Ganti `ADMIN_SEED_PASSWORD=change-me` sebelum production sungguhan.
> Password default tidak aman untuk website publik.

## Verifikasi endpoint IoT

Setelah deploy, uji endpoint IoT dari laptop atau ESP32 dengan URL Render.
Gunakan `DEVICE_SEED_API_KEY` dari `.env.prod`.

Contoh payload untuk test manual:

```bash
curl -X POST "https://smart-sprayer-iot.onrender.com/api/sensor-readings" \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "HtfEoED9PhayKSg46lydZxa2QAkUfTas",
    "temperature": 30,
    "air_humidity": 70,
    "soil_moisture": 45,
    "rain_status": "no_rain"
  }'
```

Jika berhasil, API mengembalikan response JSON dengan status kondisi dan command
sprayer.

## Troubleshooting

Gunakan bagian ini jika deploy tidak berjalan sesuai harapan.

### Render tidak bisa pull image GHCR

Jika Render gagal pull image, package GHCR kemungkinan masih private.

Perbaiki dengan salah satu cara berikut:

- Ubah visibility package `ghcr.io/fadilsflow/wjdk000` menjadi public di GitHub
  Packages.
- Login ke registry dari Render jika fitur private registry tersedia di akun
  kamu.

### Health check gagal

Jika health check gagal, pastikan nilai health check path adalah:

```txt
/up
```

Jika Render meminta port, isi:

```txt
80
```

### Database gagal connect

Jika log menunjukkan `SQLSTATE[HY000] [2002]`, periksa env berikut:

```dotenv
DB_CONNECTION=mysql
DB_HOST=mysql-25a8a780-boyblaco77-766b.f.aivencloud.com
DB_PORT=24558
DB_DATABASE=Smart_Sprayer
DB_USERNAME=avnadmin
DB_PASSWORD="AVNS_K8PtFzy6p7OIQ35iV06"
```

Pastikan database Aiven aktif dan menerima koneksi dari internet.

### WhatsApp QR hilang setelah restart

Jika tidak ada persistent disk, ini normal. Buka `/admin/whatsapp` dan scan QR
ulang.

Jika kamu ingin QR tidak perlu discan ulang setelah restart, gunakan Render plan
yang menyediakan persistent disk, lalu mount ke:

```txt
/var/www/html/storage
```

## Next steps

Setelah deploy berhasil, lakukan hardening dasar sebelum website dipakai publik.

1. Ganti `ADMIN_SEED_PASSWORD` ke password kuat.
2. Ganti `WHATSAPP_GATEWAY_TOKEN` dan `GATEWAY_SECRET_TOKEN` ke token acak.
3. Pastikan `APP_DEBUG=false`.
4. Update ESP32 agar memakai URL Render untuk endpoint API.
5. Pantau tab **Logs** setelah WhatsApp QR discan.
