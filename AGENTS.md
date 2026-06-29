# AGENTS.md — Smart Sprayer IoT Web

> **Kontrak utama AI Agent untuk project ini.**
> Baca file ini terlebih dahulu sebelum mengerjakan task apapun.

---

## Mission

Membangun website monitoring dan kendali sistem **Smart Sprayer IoT** untuk pengendalian hama kutu pada tanaman bawang merah di Brebes. Sistem menerima data dari perangkat ESP32 (sensor DHT22, soil moisture, sensor hujan, relay/pompa), menampilkan kondisi lingkungan real-time, mengelola penyemprotan otomatis/manual, dan mengirim notifikasi WhatsApp sebagai peringatan dini.

---

## Tech Stack

| Layer               | Teknologi                                          |
|---------------------|----------------------------------------------------|
| Framework           | Laravel (latest stable, PHP 8.2+)                  |
| Language            | PHP 8.2+ with `declare(strict_types=1)`            |
| Database            | MySQL 8.0+                                         |
| ORM                 | Eloquent ORM                                       |
| Validation          | Laravel Form Request + Eloquent rules              |
| Cache               | Laravel Cache (file/redis driver)                  |
| Queue / Worker      | Laravel Queue (database driver)                    |
| Frontend            | Blade + Livewire (optional for reactive UI)        |
| Styling             | Tailwind CSS v3                                    |
| Charts              | Chart.js atau ApexCharts                           |
| Notifications       | WhatsApp Gateway/API (via HTTP client)             |
| API                 | REST API (Laravel Routes + API Controllers)        |
| Testing             | Black Box Testing (PHPUnit + manual)               |

---

## Architecture Pattern

**Laravel MVC standar** — Model, View, Controller mengikuti konvensi folder bawaan Laravel. Business logic yang kompleks diekstrak ke layer **Service** (`app/Services/`). Query database yang berulang dapat diekstrak ke **Repository** (`app/Repositories/`), namun untuk query sederhana boleh langsung di Model atau Controller via Eloquent.

> Gunakan `docs/architecture.md` untuk detail folder convention dan prinsip coding.

### Data Flow

```
Request (HTTP / IoT)
  → Route → Middleware → Form Request
    → Controller (tipis, delegasi ke Service)
      → Service (business logic domain)
        → Eloquent Model / Repository
          → MySQL
```

---

## Layer Boundaries

### Routes (`routes/web.php`, `routes/api.php`)
- **Boleh:** Mendefinisikan endpoint, memanggil controller method, apply middleware
- **Dilarang:** Business logic, query database langsung, kondisional yang panjang

### Middleware (`app/Http/Middleware/`)
- **Boleh:** Autentikasi perangkat IoT via API key (`AuthenticateDevice`)
- **Dilarang:** Business logic, query langsung, manipulasi data response

### Form Requests (`app/Http/Requests/`)
- **Boleh:** Rules validasi, `authorize()`, pesan error kustom
- **Dilarang:** Business logic, memanggil service

### Controllers (`app/Http/Controllers/`)
- **Boleh:** Terima request, delegasi ke Service, return response/view
- **Dilarang:** Business logic langsung, query Eloquent langsung untuk logic kompleks
- **Wajib:** Tipis — max ~20 baris per method

### Services (`app/Services/`) — _gunakan jika logic kompleks_
- **Boleh:** Business logic, orchestrate calls ke Model/Repository, memanggil WhatsApp, menentukan condition_status dan sprayer_command
- **Dilarang:** Manipulasi HTTP request/response, `DB::` raw query

### Repositories (`app/Repositories/`) — _opsional, untuk query yang dipakai berulang_
- **Boleh:** Eloquent query, CRUD, pagination, filter
- **Dilarang:** Business logic, memanggil service lain
- **Kapan dibuat:** Hanya jika query yang sama digunakan di lebih dari satu tempat

### Models (`app/Models/`)
- **Boleh:** Eloquent relationships, casts, fillable, scopes, accessors/mutators
- **Dilarang:** Business logic, memanggil service, HTTP calls

### Views (`resources/views/`)
- **Boleh:** Menampilkan data, Blade directives, Blade components
- **Dilarang:** Business logic, query database, PHP kondisional yang kompleks

---

## Core Domain Rules

### Entities Utama

| Entity               | Table                | Deskripsi                                       |
|----------------------|----------------------|-------------------------------------------------|
| User                 | `users`              | Data pengguna (manajemen admin, `created_by` log) |
| Device               | `devices`            | Perangkat IoT (ESP32), mode, dan status sprayer |
| SensorReading        | `sensor_readings`    | Data sensor dari perangkat IoT                  |
| ThresholdSetting     | `threshold_settings` | Konfigurasi threshold per device oleh Admin     |
| SprayLog             | `spray_logs`         | Log aktivitas penyemprotan (manual/otomatis)    |
| NotificationLog      | `notification_logs`  | Log pengiriman notifikasi WhatsApp              |
| WhatsappSetting      | `whatsapp_settings`  | Nomor penerima dan template pesan WhatsApp      |

### Status Kondisi Lingkungan

```
sensor_data masuk
  → evaluasi threshold
    → NORMAL    : kondisi aman
    → WASPADA   : perlu diperhatikan
    → KRITIS    : memenuhi aturan penyemprotan
```

### Business Rules Kritis

1. **Device harus terdaftar** — Setiap request IoT wajib menyertakan `api_key` valid. Jika tidak, return 401.
2. **Sprayer tidak aktif saat hujan** — Jika `rain_status = 'rain'`, penyemprotan otomatis TIDAK dijalankan, apapun kondisi tanah.
3. **Mode otomatis** — Sprayer aktif HANYA jika: `soil_moisture < threshold.min_soil_moisture` DAN `rain_status = 'no_rain'`.
4. **Log wajib dicatat** — Setiap perubahan status sprayer WAJIB membuat entri di `spray_logs`.
5. **Notifikasi WhatsApp** — Dikirim untuk: kondisi kritis, sprayer mulai/berhenti, hujan terdeteksi (jika mode otomatis).
6. **Konfigurasi gateway sensitif** — URL gateway, token, dan sender WhatsApp wajib dibaca dari `.env` / `config/services.php`, bukan dari database.
7. **Recipient & template configurable** — Nomor penerima dan template pesan WhatsApp dikelola Admin di tabel `whatsapp_settings`.
8. **Halaman publik** — Tidak boleh menampilkan tombol kontrol atau nomor WhatsApp sensitif.

### Akses Web

- Semua halaman web terbuka tanpa login.
- Landing (`/`) menampilkan ringkasan publik non-sensitif.

---

## API Conventions

Semua API response menggunakan wrapper standar:

```json
{
  "success": true,
  "message": "Data sensor berhasil disimpan.",
  "data": {},
  "errors": null
}
```

- `success`: `true` | `false`
- `message`: string deskriptif
- `data`: payload utama (bisa null)
- `errors`: detail validasi error (bisa null)

**IoT API khusus** (response setelah sensor reading):
```json
{
  "success": true,
  "condition_status": "kritis",
  "mode": "automatic",
  "sprayer_command": "on"
}
```

---

## Naming Conventions

| Context         | Convention              | Contoh                                |
|-----------------|-------------------------|---------------------------------------|
| PHP Classes     | PascalCase              | `SensorReadingService`                |
| Methods/Vars    | camelCase               | `calculateConditionStatus()`          |
| Database tables | snake_case (plural)     | `sensor_readings`, `spray_logs`       |
| DB columns      | snake_case              | `soil_moisture`, `rain_status`        |
| Routes          | kebab-case              | `/sensor-readings`, `/spray-logs`     |
| Blade files     | kebab-case              | `dashboard.blade.php`                 |
| Config keys     | snake_case              | `whatsapp_gateway_url`                |

---

## Security & Operations

### IoT Device Authentication
- ESP32 memakai `api_key` di header `X-Api-Key` atau request body
- Middleware `AuthenticateDevice` memverifikasi key sebelum proses request API IoT

### Sensitive Data
- **Zero hardcode** untuk API key, token, password — selalu gunakan `.env`
- File `.env` wajib ada di `.gitignore`
- File `.env.example` wajib diperbarui setiap ada variabel baru

### Audit & Logging
- Setiap perubahan status sprayer → buat entri `spray_logs`
- Setiap pengiriman WhatsApp → buat entri `notification_logs`
- Gunakan `storage/logs/laravel.log` untuk log error system

### Rate Limiting
- API IoT endpoint: throttle `60/minute` per device API key
- Web routes: Laravel default session throttle

---

## AI Context Map

File-file pendukung dalam `.ai-context/`:

| File                                         | Isi                                         |
|----------------------------------------------|---------------------------------------------|
| `.ai-context/agent-rules/agent-instructions.md`              | Cara kerja agent, self-check wajib          |
| `.ai-context/agent-rules/instructions/controllers.instructions.md` | Aturan layer Controllers                    |
| `.ai-context/agent-rules/instructions/services.instructions.md`    | Aturan layer Services                       |
| `.ai-context/agent-rules/instructions/repositories.instructions.md`| Aturan layer Repositories                   |
| `.ai-context/agent-rules/instructions/models.instructions.md`      | Aturan layer Models                         |
| `.ai-context/agent-rules/instructions/api.instructions.md`         | Aturan API routes dan IoT integration       |
| `docs/architecture.md`                       | Tech stack, prinsip arsitektur, struktur folder |
| `docs/domain-rules.md`                       | Entity, status flow, business rules, API convention |
| `docs/operations.md`                         | Storage, security, dev commands, deployment |

---

## Definition of Done

Sebuah task dianggap **selesai** jika semua kondisi berikut terpenuhi:

- [ ] Kode mengikuti PSR-12 dan `declare(strict_types=1)`
- [ ] Controller tipis — complex logic ada di Service, bukan di Controller
- [ ] Semua input eksternal divalidasi via Form Request
- [ ] Semua nilai sensitif menggunakan env variable
- [ ] Perubahan status sprayer tercatat di `spray_logs`
- [ ] Pengiriman notifikasi tercatat di `notification_logs`
- [ ] API IoT mengembalikan format response standar
- [ ] Rule: sprayer tidak aktif saat `rain_status = 'rain'` sudah diimplementasikan
- [ ] Halaman publik tidak menampilkan data sensitif
- [ ] Migration baru ditambahkan (bukan edit migration lama)
- [ ] Tidak ada API key / token hardcoded di kode
- [ ] `.env.example` diperbarui jika ada variabel baru
- [ ] **Cek apakah `AGENTS.md` atau docs terkait perlu diupdate** — update jika perlu, skip jika tidak ada perubahan domain

---

## Doc Update Policy

> Docs diupdate **setelah** perubahan kode, bukan sebelumnya. Cek dulu apakah perubahan yang dilakukan masuk kategori di bawah ini..

### Wajib update docs jika:

| Perubahan | File yang perlu diupdate |
|-----------|--------------------------|
| Business rule baru atau berubah | `AGENTS.md` (Business Rules) + `docs/domain-rules.md` |
| Entity/tabel baru | `AGENTS.md` (Entities) + `docs/domain-rules.md` |
| Env variable baru | `docs/operations.md` + `.env.example` |
| Endpoint IoT baru atau berubah | `.ai-context/agent-rules/instructions/api.instructions.md` |
| Perubahan arsitektur signifikan | `docs/architecture.md` |
| Notification type baru | `docs/operations.md` + `docs/domain-rules.md` |

### Tidak perlu update docs jika:

- Menambah controller, service, model, atau repository baru (selama mengikuti konvensi)
- Menambah method baru di class yang sudah ada
- Rename variabel atau method internal
- Refactor tanpa mengubah behavior
- Menambah kolom minor yang tidak mengubah domain rules

---

## Reference Docs

- [Dokumentasi Laravel](https://laravel.com/docs)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Eloquent ORM](https://laravel.com/docs/eloquent)
- [Laravel Form Request](https://laravel.com/docs/validation#form-request-validation)
- [Chart.js](https://www.chartjs.org/docs/)
- `docs/prd.md` — Product Requirements Document
- `docs/proposal.md` — Proposal website
- `docs/proposal-iot.md` — Proposal perangkat IoT

<!-- lean-ctx -->
## lean-ctx

Prefer lean-ctx MCP tools over native equivalents for token savings.
Full rules: @LEAN-CTX.md
<!-- /lean-ctx -->
