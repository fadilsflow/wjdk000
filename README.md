[README.md#6DF7]
1:# Smart Sprayer IoT Web
2:
3:Website monitoring + kendali Smart Sprayer IoT untuk pengendalian hama kutu pada tanaman bawang merah di Brebes.
4:
5:## Tech Stack
6:
7:| Layer         | Teknologi                                        |
8:| ------------- | ------------------------------------------------ |
9:| Framework     | Laravel 13 (PHP 8.2+)                            |
10:| Database      | SQLite (default) / MySQL                         |
11:| ORM           | Eloquent ORM                                     |
12:| Frontend      | Blade + Alpine.js                                |
13:| Styling       | Tailwind CSS v4 (via `@tailwindcss/vite`)        |
14:| Charts        | Chart.js                                         |
15:| Notifications | WhatsApp Gateway/API (via HTTP client)           |
16:| API           | REST API + `api_key` device                      |
17:
18:Semua halaman web dapat diakses tanpa login. API IoT dilindungi `api_key` per device.
19:
20:## Quick Start
21:
22:```bash
23:# 1. Clone
24:git clone https://github.com/fadilsflow/wjdk000
25:cd wjdk000
26:
27:# 2. Install PHP dependencies
28:composer install
29:
30:# 3. Setup environment
31:cp .env.example .env
32:php artisan key:generate
33:
34:# 4. Setup database (SQLite — default)
35:touch database/database.sqlite
36:php artisan migrate --seed
37:
38:# 5. Install frontend dependencies
39:bun install    # atau npm install
40:bun run build  # atau npm run build
41:
42:# 6. Jalankan
43:php artisan serve
44:# Terminal lain: bun run dev (untuk hot-reload CSS/JS)
45:```
46:
47:Set di `.env` sebelum seed:
48:
49:```env
50:ADMIN_SEED_PASSWORD=password-aman
51:DEVICE_SEED_API_KEY=kunci-device-32-karakter
52:```
53:
54:## Docker / GHCR
55:
56:Image production berisi Laravel + self-hosted WhatsApp Gateway dalam satu container.
57:
58:```bash
59:docker compose -f compose.ghcr.yml up -d
60:```
61:
62:Default compose berjalan di `http://localhost:8080` dengan SQLite persisten di volume. Detail lengkap ada di `docs/operations.md`.
63:
64:## Browser E2E
65:
66:Prasyarat:
67:
68:- `ADMIN_SEED_PASSWORD` terisi
69:- `DEVICE_SEED_API_KEY` terisi tetap di `.env`
70:- database sudah `migrate --seed`
71:
72:```bash
73:npm run test:e2e
74:npm run test:e2e:sprayer
75:```
76:
77:Akses `http://localhost:8000`.
78:
79:### Pakai MySQL
80:
81:```
82:DB_CONNECTION=mysql
83:DB_HOST=127.0.0.1
84:DB_PORT=3306
85:DB_DATABASE=smart_sprayer
86:DB_USERNAME=root
87:DB_PASSWORD=
88:```
89:
90:```bash
91:php artisan migrate --seed
92:```
93:
94:## Fitur
95:
96:- Dashboard sensor real-time (suhu, kelembapan udara/tanah, status hujan)
97:- Status kondisi: Normal / Waspada / Kritis
98:- Kontrol sprayer manual & otomatis
99:- Riwayat sensor, penyemprotan, notifikasi
100:- Notifikasi WhatsApp
101:- Manajemen device, threshold, pengguna, template WhatsApp
102:- Mode gelap/terang
103:- Halaman publik (landing)
104:
105:## API Endpoint
106:
107:```txt
108:POST /api/sensor-readings
109:GET  /api/devices/{device}/command
110:POST /sprayer/mode
111:POST /sprayer/status
112:```
113:
114:## IoT Simulator
115:
116:```bash
117:php artisan serve
118:```
119:
120:Buka `prototype/iot-trigger.html` — target default: `http://127.0.0.1:8000/api/sensor-readings`
121:
122:Trigger: Normal, Waspada, Kritis, Hujan, Random Data, Loop.
123:
124:## Docs
125:
126:- `docs/tutorial-pengembangan-dari-awal.md` — **tutorial utama**: setup dari nol + coding fitur bertahap
- `docs/tutorial-merancang-projek.md` — ringkasan arsitektur & referensi cepat
127:- `docs/proposal.md` — proposal web
128:- `docs/proposal-iot.md` — proposal IoT
129:- `docs/prd.md` — product requirements
130:- `docs/architecture.md` — arsitektur & folder convention
131:- `docs/domain-rules.md` — business rules & entities
132:- `docs/operations.md` — deployment & ops
133:- `docs/flash-esp32-laravel.md` — tutorial flash ESP32 & server Laravel
134:
135:## Catatan
136:
137:Development web bisa memakai simulator IoT (`prototype/iot-trigger.html`) sebelum hardware ESP32 siap.
138: