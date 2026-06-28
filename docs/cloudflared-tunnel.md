# Tunnel lokal dengan Cloudflare Tunnel

Panduan ini menjelaskan cara membuka project Laravel lokal ke domain
`ssp.kreeasi.web.id` memakai `cloudflared`.

## Arsitektur

Tunnel hanya perlu mengarah ke Laravel.

```txt
https://ssp.kreeasi.web.id
  -> Cloudflare Tunnel
  -> http://localhost:8000
  -> Laravel
```

WhatsApp Gateway tidak perlu ditunnel. Biarkan gateway berjalan lokal di port
`3000` dan dipanggil oleh Laravel dari server yang sama.

```txt
Laravel -> http://127.0.0.1:3000/send -> WhatsApp Gateway
```

## DNS Cloudflare

Buat DNS record di zone `kreeasi.web.id`.

```txt
Type: CNAME
Name: ssp
Target: 0ca15039-be11-4956-8008-6eef58471fd0.cfargotunnel.com
Proxy status: Proxied
```

Jika domain `kreeasi.web.id` belum ada di Cloudflare, tambahkan domain itu ke
Cloudflare terlebih dahulu.

## Environment Laravel

Ubah `.env` lokal agar URL aplikasi memakai domain tunnel.

```dotenv
APP_URL=https://ssp.kreeasi.web.id
WHATSAPP_GATEWAY_URL=http://127.0.0.1:3000/send
```

Setelah mengubah `.env`, jalankan:

```bash
php artisan optimize:clear
```

## Jalankan service lokal

Buka tiga terminal terpisah.

### Terminal 1: Laravel

Jalankan Laravel di port `8000`.

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### Terminal 2: WhatsApp Gateway

Jalankan gateway WhatsApp lokal.

```bash
cd whatsapp-gateway
npm start
```

### Terminal 3: Cloudflare Tunnel

Jalankan tunnel dengan config yang sudah dibuat.

```bash
cloudflared tunnel --config ~/.cloudflared/smart-sprayer.yml run
```

## Akses website

Buka domain berikut di browser.

```txt
https://ssp.kreeasi.web.id
```

Untuk WhatsApp, buka:

```txt
https://ssp.kreeasi.web.id/admin/whatsapp
```

Scan QR dari halaman tersebut.

## Catatan penting

- Jangan expose WhatsApp Gateway langsung ke internet.
- Cukup tunnel Laravel di port `8000`.
- Pastikan `WHATSAPP_GATEWAY_URL` tetap memakai `127.0.0.1:3000`.
- Jika domain belum aktif, cek DNS CNAME dan pastikan tunnel sedang berjalan.
