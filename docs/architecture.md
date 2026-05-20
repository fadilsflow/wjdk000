# Architecture — Smart Sprayer IoT Web

## Tech Stack

| Layer             | Teknologi                       | Catatan                     |
|-------------------|---------------------------------|-----------------------------|
| Framework         | Laravel                         | Latest stable, PHP 8.2+     |
| Database          | MySQL                           | 8.0+                        |
| ORM               | Eloquent ORM                    | Bawaan Laravel              |
| Validation        | Laravel Form Request            | Bawaan Laravel              |
| Auth              | Laravel Breeze (session-based)  | Role: admin / petani        |
| Frontend          | Blade + Tailwind CSS v3         | Livewire opsional           |
| Charts            | Chart.js atau ApexCharts        | Pilih satu, konsisten       |
| Notifications     | WhatsApp Gateway/API            | Via Laravel HTTP Client      |
| API               | REST API (JSON)                 | routes/api.php              |
| Queue             | Laravel Queue (database driver) | Opsional, untuk notifikasi  |
| Testing           | PHPUnit + Black Box Testing     | Bawaan Laravel              |

---

## Architecture Pattern: MVC

Project ini menggunakan **Laravel MVC** standar, diperkuat dengan layer **Service** dan **Repository** yang mengikuti folder bawaan Laravel (`app/Services/`, `app/Repositories/`).

> **Prinsip utama:** Controller hanya menerima request dan return response. Business logic masuk ke Service. Query database masuk ke Repository atau langsung di Model jika sederhana.

### Data Flow

```
HTTP Request / IoT Device
  → routes/web.php atau routes/api.php
    → Middleware (auth, role, device-auth)
      → Form Request (validasi)
        → Controller
          → Service  (business logic)
            → Repository / Model  (data access)
              → MySQL Database
```

---

## Struktur Folder (Prinsip, bukan daftar file)

Ikuti struktur bawaan Laravel. Jangan buat subfolder baru di luar konvensi ini kecuali ada alasan kuat.

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/        ← Controller untuk IoT endpoint (api.php)
│   │   ├── Admin/      ← Controller halaman khusus role admin
│   │   └── ...         ← Controller fitur lain (1 controller per resource/fitur)
│   ├── Middleware/     ← CheckRole, AuthenticateDevice, dll
│   └── Requests/       ← 1 Form Request per use case (Store, Update, dll)
├── Models/             ← 1 file per tabel database
├── Services/           ← Business logic (1 service per domain/fitur besar)
├── Repositories/       ← Database access (1 repository per model utama)
└── Providers/          ← AppServiceProvider untuk binding
resources/views/
├── layouts/            ← Layout utama (app.blade.php, dll)
├── components/         ← Blade components reusable
├── {fitur}/            ← Folder per fitur (dashboard, sprayer, history, admin, public)
routes/
├── web.php             ← Route autentikasi, dashboard, admin, sprayer, riwayat
└── api.php             ← Route IoT (sensor-readings, device command)
database/
├── migrations/         ← 1 file per perubahan skema (jangan edit lama)
└── seeders/            ← DatabaseSeeder + seeder spesifik
config/
└── whatsapp.php        ← Konfigurasi WhatsApp Gateway (nilai dari .env)
```

> **Catatan nama file:** Ikuti konvensi Laravel (PascalCase untuk class, kebab-case untuk Blade). Tidak perlu update dokumen ini setiap kali ada file baru selama mengikuti konvensi folder di atas.

---

## Architecture Principles

1. **Thin Controller** — Controller max ~20 baris per method. Logic ada di Service.
2. **Single Responsibility** — 1 class = 1 tanggung jawab. Service tidak query DB, Repository tidak business logic.
3. **Form Request Wajib** — Semua input dari luar (form, API) wajib melewati Form Request.
4. **Dependency Injection** — Inject via constructor. Manfaatkan Laravel Service Container.
5. **No Hardcode** — Semua nilai sensitif (API key, token, nomor WA) dari `.env`.
6. **Migration Only Forward** — Selalu buat migration baru. Jangan edit migration yang sudah ada.
7. **Strict Types** — `declare(strict_types=1)` di semua file PHP.

---

## Coding Standards

### PHP
```php
<?php

declare(strict_types=1);

namespace App\Services;

class ExampleService
{
    // ✅ Constructor property promotion (PHP 8.1+)
    public function __construct(
        private readonly SomeRepository $repo,
    ) {}

    // ✅ Return type + parameter type selalu didefinisikan
    public function doSomething(int $id): array
    {
        return $this->repo->findById($id)->toArray();
    }
}
```

### Blade / Frontend
- Tailwind CSS utility classes, tidak ada inline style kecuali dynamic value
- Gunakan Blade components (`<x-card>`, `<x-alert>`) untuk reusability
- Color convention UI:

| Status   | Warna Tailwind        |
|----------|-----------------------|
| Normal   | `green-*`             |
| Waspada  | `yellow-*`            |
| Kritis   | `red-*`               |
| Hujan / Off | `blue-*` / `gray-*` |

### Database / Migrations
- Selalu buat migration baru — **jangan edit migration lama yang sudah dijalankan**
- Tambahkan index pada kolom yang sering di-query (`device_id`, `recorded_at`)
- Gunakan `->constrained()->cascadeOnDelete()` untuk foreign key
