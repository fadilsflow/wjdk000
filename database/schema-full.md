# Database Schema — Smart Sprayer IoT

> Dokumentasi struktur database lengkap dengan SQL DDL dan diagram ER.

---

## 📋 Daftar Tabel

| # | Tabel | Kategori | Migrasi |
|---|-------|----------|---------|
| 1 | `users` | Auth / System | `0001_01_01_000000_create_users_table.php` |
| 2 | `password_reset_tokens` | Auth / System | `0001_01_01_000000_create_users_table.php` |
| 3 | `sessions` | Auth / System | `0001_01_01_000000_create_users_table.php` |
| 4 | `cache` | Utility | `0001_01_01_000001_create_cache_table.php` |
| 5 | `cache_locks` | Utility | `0001_01_01_000001_create_cache_table.php` |
| 6 | `jobs` | Queue | `0001_01_01_000002_create_jobs_table.php` |
| 7 | `job_batches` | Queue | `0001_01_01_000002_create_jobs_table.php` |
| 8 | `failed_jobs` | Queue | `0001_01_01_000002_create_jobs_table.php` |
| 9 | `devices` | **Domain** | `2026_05_24_000004_create_smart_sprayer_domain_tables.php` |
| 10 | `threshold_settings` | **Domain** | `2026_05_24_000004_create_smart_sprayer_domain_tables.php` |
| 11 | `sensor_readings` | **Domain** | `2026_05_24_000004_create_smart_sprayer_domain_tables.php` + `2026_06_03_082039_add_actual_esp32_fields_to_sensor_readings_table.php` |
| 12 | `spray_logs` | **Domain** | `2026_05_24_000004_create_smart_sprayer_domain_tables.php` |
| 13 | `notification_logs` | **Domain** | `2026_05_24_000004_create_smart_sprayer_domain_tables.php` |
| 14 | `whatsapp_settings` | **Domain** | `2026_05_24_000005_create_whatsapp_settings_table.php` |

---

## 🗃️ SQL DDL — Full CREATE TABLE Queries

```sql
-- ============================================================
-- 1. users
-- ============================================================
CREATE TABLE `users` (
    `id`                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`              VARCHAR(255) NOT NULL,
    `email`             VARCHAR(255) NOT NULL UNIQUE,
    `email_verified_at` TIMESTAMP NULL,
    `password`          VARCHAR(255) NOT NULL,
    `role`              VARCHAR(255) NOT NULL DEFAULT 'petani',
    `phone_number`      VARCHAR(20) NULL,
    `remember_token`    VARCHAR(100) NULL,
    `created_at`        TIMESTAMP NULL,
    `updated_at`        TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. password_reset_tokens
-- ============================================================
CREATE TABLE `password_reset_tokens` (
    `email`      VARCHAR(255) NOT NULL PRIMARY KEY,
    `token`      VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. sessions
-- ============================================================
CREATE TABLE `sessions` (
    `id`            VARCHAR(255) NOT NULL PRIMARY KEY,
    `user_id`       BIGINT UNSIGNED NULL,
    `ip_address`    VARCHAR(45) NULL,
    `user_agent`    TEXT NULL,
    `payload`       LONGTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    INDEX `sessions_user_id_index` (`user_id`),
    INDEX `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. cache
-- ============================================================
CREATE TABLE `cache` (
    `key`        VARCHAR(255) NOT NULL PRIMARY KEY,
    `value`      MEDIUMTEXT NOT NULL,
    `expiration` BIGINT NOT NULL,
    INDEX `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. cache_locks
-- ============================================================
CREATE TABLE `cache_locks` (
    `key`        VARCHAR(255) NOT NULL PRIMARY KEY,
    `owner`      VARCHAR(255) NOT NULL,
    `expiration` BIGINT NOT NULL,
    INDEX `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. jobs
-- ============================================================
CREATE TABLE `jobs` (
    `id`           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `queue`        VARCHAR(255) NOT NULL,
    `payload`      LONGTEXT NOT NULL,
    `attempts`     TINYINT UNSIGNED NOT NULL,
    `reserved_at`  INT UNSIGNED NULL,
    `available_at` INT UNSIGNED NOT NULL,
    `created_at`   INT UNSIGNED NOT NULL,
    INDEX `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. job_batches
-- ============================================================
CREATE TABLE `job_batches` (
    `id`             VARCHAR(255) NOT NULL PRIMARY KEY,
    `name`           VARCHAR(255) NOT NULL,
    `total_jobs`     INT NOT NULL,
    `pending_jobs`   INT NOT NULL,
    `failed_jobs`    INT NOT NULL,
    `failed_job_ids` LONGTEXT NOT NULL,
    `options`        MEDIUMTEXT NULL,
    `cancelled_at`   INT NULL,
    `created_at`     INT NOT NULL,
    `finished_at`    INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. failed_jobs
-- ============================================================
CREATE TABLE `failed_jobs` (
    `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `uuid`       VARCHAR(255) NOT NULL UNIQUE,
    `connection` VARCHAR(255) NOT NULL,
    `queue`      VARCHAR(255) NOT NULL,
    `payload`    LONGTEXT NOT NULL,
    `exception`  LONGTEXT NOT NULL,
    `failed_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `failed_jobs_connection_queue_failed_at_index` (`connection`, `queue`, `failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. devices  (Domain Core)
-- ============================================================
CREATE TABLE `devices` (
    `id`             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`           VARCHAR(255) NOT NULL,
    `location`       VARCHAR(255) NOT NULL,
    `api_key`        VARCHAR(255) NOT NULL UNIQUE,
    `mode`           VARCHAR(255) NOT NULL DEFAULT 'manual',
    `sprayer_status` VARCHAR(255) NOT NULL DEFAULT 'off',
    `created_at`     TIMESTAMP NULL,
    `updated_at`     TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. threshold_settings  (1:1 dengan devices)
-- ============================================================
CREATE TABLE `threshold_settings` (
    `id`                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `device_id`         BIGINT UNSIGNED NOT NULL UNIQUE,
    `min_soil_moisture` DECIMAL(5, 2) NOT NULL,
    `max_temperature`   DECIMAL(5, 2) NOT NULL,
    `min_air_humidity`  DECIMAL(5, 2) NOT NULL,
    `created_at`        TIMESTAMP NULL,
    `updated_at`        TIMESTAMP NULL,
    CONSTRAINT `threshold_settings_device_id_foreign`
        FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. sensor_readings  (data dari ESP32)
-- ============================================================
CREATE TABLE `sensor_readings` (
    `id`               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `device_id`        BIGINT UNSIGNED NOT NULL,
    `temperature`      DECIMAL(5, 2) NOT NULL,
    `air_humidity`     DECIMAL(5, 2) NOT NULL,
    `soil_moisture`    DECIMAL(5, 2) NOT NULL,
    `soil_raw`         SMALLINT UNSIGNED NULL,
    `rain_status`      VARCHAR(255) NOT NULL,
    `rain_raw`         SMALLINT UNSIGNED NULL,
    `sprayer_status`   VARCHAR(255) NOT NULL,
    `simulation_mode`  TINYINT(1) NOT NULL DEFAULT 0,
    `condition_status` VARCHAR(255) NOT NULL,
    `recorded_at`      TIMESTAMP NOT NULL,
    `created_at`       TIMESTAMP NULL,
    `updated_at`       TIMESTAMP NULL,
    INDEX `sensor_readings_device_id_recorded_at_index` (`device_id`, `recorded_at`),
    CONSTRAINT `sensor_readings_device_id_foreign`
        FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 12. spray_logs  (riwayat penyemprotan)
-- ============================================================
CREATE TABLE `spray_logs` (
    `id`           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `device_id`    BIGINT UNSIGNED NOT NULL,
    `trigger_type` VARCHAR(255) NOT NULL,
    `status`       VARCHAR(255) NOT NULL,
    `reason`       TEXT NULL,
    `created_by`   BIGINT UNSIGNED NULL,
    `created_at`   TIMESTAMP NULL,
    `updated_at`   TIMESTAMP NULL,
    INDEX `spray_logs_device_id_created_at_index` (`device_id`, `created_at`),
    CONSTRAINT `spray_logs_device_id_foreign`
        FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `spray_logs_created_by_foreign`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 13. notification_logs  (riwayat WhatsApp)
-- ============================================================
CREATE TABLE `notification_logs` (
    `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `device_id`       BIGINT UNSIGNED NOT NULL,
    `type`            VARCHAR(255) NOT NULL,
    `recipient_phone` VARCHAR(20) NOT NULL,
    `message`         TEXT NOT NULL,
    `status`          VARCHAR(255) NOT NULL,
    `sent_at`         TIMESTAMP NULL,
    `created_at`      TIMESTAMP NULL,
    `updated_at`      TIMESTAMP NULL,
    INDEX `notification_logs_device_id_sent_at_index` (`device_id`, `sent_at`),
    CONSTRAINT `notification_logs_device_id_foreign`
        FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 14. whatsapp_settings  (konfigurasi template)
-- ============================================================
CREATE TABLE `whatsapp_settings` (
    `id`                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `recipient_phone`             VARCHAR(20) NULL,
    `critical_condition_template` TEXT NULL,
    `spray_start_template`        TEXT NULL,
    `spray_stop_template`         TEXT NULL,
    `rain_detected_template`      TEXT NULL,
    `created_at`                  TIMESTAMP NULL,
    `updated_at`                  TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📐 Diagram ER — Mermaid

```mermaid
erDiagram
    %% ========== AUTH / SYSTEM ==========
    users {
        bigint id PK
        varchar name
        varchar email UK
        timestamp email_verified_at
        varchar password
        varchar role "default 'petani'"
        varchar phone_number
        varchar remember_token
        timestamp created_at
        timestamp updated_at
    }

    sessions {
        varchar id PK
        bigint user_id FK
        varchar ip_address
        text user_agent
        longtext payload
        int last_activity
    }

    password_reset_tokens {
        varchar email PK
        varchar token
        timestamp created_at
    }

    %% ========== LARAVEL SYSTEM ==========
    cache {
        varchar key PK
        mediumtext value
        bigint expiration
    }

    cache_locks {
        varchar key PK
        varchar owner
        bigint expiration
    }

    jobs {
        bigint id PK
        varchar queue
        longtext payload
        tinyint attempts
        int unsigned reserved_at
        int unsigned available_at
        int unsigned created_at
    }

    job_batches {
        varchar id PK
        varchar name
        int total_jobs
        int pending_jobs
        int failed_jobs
        longtext failed_job_ids
        mediumtext options
        int cancelled_at
        int created_at
        int finished_at
    }

    failed_jobs {
        bigint id PK
        varchar uuid UK
        varchar connection
        varchar queue
        longtext payload
        longtext exception
        timestamp failed_at
    }

    %% ========== DOMAIN CORE ==========
    devices {
        bigint id PK
        varchar name
        varchar location
        varchar api_key UK
        varchar mode "manual | automatic"
        varchar sprayer_status "on | off"
        timestamp created_at
        timestamp updated_at
    }

    threshold_settings {
        bigint id PK
        bigint device_id FK "unique, 1-to-1"
        decimal min_soil_moisture
        decimal max_temperature
        decimal min_air_humidity
        timestamp created_at
        timestamp updated_at
    }

    sensor_readings {
        bigint id PK
        bigint device_id FK
        decimal temperature
        decimal air_humidity
        decimal soil_moisture
        smallint soil_raw "nullable, ADC value"
        varchar rain_status "rain | no_rain"
        smallint rain_raw "nullable, ADC value"
        varchar sprayer_status "on | off"
        tinyint simulation_mode
        varchar condition_status "normal | waspada | kritis"
        timestamp recorded_at
        timestamp created_at
        timestamp updated_at
    }

    spray_logs {
        bigint id PK
        bigint device_id FK
        varchar trigger_type "manual | automatic"
        varchar status "on | off"
        text reason "nullable"
        bigint created_by FK "nullable, FK users"
        timestamp created_at
        timestamp updated_at
    }

    notification_logs {
        bigint id PK
        bigint device_id FK
        varchar type
        varchar recipient_phone
        text message
        varchar status "sent | failed"
        timestamp sent_at "nullable"
        timestamp created_at
        timestamp updated_at
    }

    whatsapp_settings {
        bigint id PK
        varchar recipient_phone "nullable"
        text critical_condition_template "nullable"
        text spray_start_template "nullable"
        text spray_stop_template "nullable"
        text rain_detected_template "nullable"
        timestamp created_at
        timestamp updated_at
    }

    %% ========== RELATIONSHIPS ==========
    users ||--o{ sessions : "memiliki"
    users ||--o{ spray_logs : "membuat"

    devices ||--|| threshold_settings : "memiliki threshold"
    devices ||--o{ sensor_readings : "mencatat"
    devices ||--o{ spray_logs : "mencatat"
    devices ||--o{ notification_logs : "mencatat"
```

---

## 🔗 Relasi Lengkap

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                             │
│  ┌──────────┐       ┌──────────────────┐                                   │
│  │  users   │──1:N──│    sessions      │                                   │
│  └────┬─────┘       └──────────────────┘                                   │
│       │                                                                    │
│       │ 1:N                                                               │
│       │                                                                    │
│       ▼                                                                    │
│  ┌──────────────────┐       ┌──────────────────┐                           │
│  │   spray_logs     │──N:1──│    devices        │──1:1── threshold_settings │
│  └──────────────────┘       └────────┬─────────┘                           │
│                                      │                                      │
│                             ┌────────┼────────┐                            │
│                             │        │        │                            │
│                             ▼        ▼        ▼                           │
│                      ┌──────────┐ ┌──────┐ ┌───────────────────┐          │
│                      │sensor_   │ │spray_│ │notification_logs  │          │
│                      │readings  │ │logs  │ └───────────────────┘          │
│                      └──────────┘ └──────┘                                │
│                                                                             │
│  ┌──────────────────┐                                                      │
│  │whatsapp_settings │  (standalone, tanpa FK ke tabel lain)                │
│  └──────────────────┘                                                      │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 🧠 Catatan Penting

### Enum Values

| Kolom | Values |
|-------|--------|
| `users.role` | `'admin'`, `'petani'` |
| `devices.mode` | `'manual'`, `'automatic'` |
| `devices.sprayer_status` | `'on'`, `'off'` |
| `sensor_readings.rain_status` | `'rain'`, `'no_rain'` |
| `sensor_readings.sprayer_status` | `'on'`, `'off'` |
| `sensor_readings.condition_status` | `'normal'`, `'waspada'`, `'kritis'` |
| `spray_logs.trigger_type` | `'manual'`, `'automatic'` |
| `spray_logs.status` | `'on'`, `'off'` |
| `notification_logs.status` | `'sent'`, `'failed'` |

### Foreign Key Rules

| FK | On Delete |
|----|-----------|
| `threshold_settings.device_id → devices.id` | **CASCADE** |
| `sensor_readings.device_id → devices.id` | **CASCADE** |
| `spray_logs.device_id → devices.id` | **CASCADE** |
| `spray_logs.created_by → users.id` | **SET NULL** |
| `notification_logs.device_id → devices.id` | **CASCADE** |

### Catatan Domain

1. **`threshold_settings`** memiliki relasi **1:1** dengan `devices` (kolom `device_id` UNIQUE)
2. **`device.api_key`** digunakan untuk autentikasi perangkat ESP32 saat mengirim data
3. **`whatsapp_settings`** adalah tabel standalone tanpa foreign key (konfigurasi global)
4. **`sensor_readings.simulation_mode`** = `true` menandakan data berasal dari mode simulasi/testing
5. **Index komposit** `(device_id, recorded_at)` pada `sensor_readings` untuk optimasi query time-series
6. Semua tabel domain menggunakan `DECIMAL(5,2)` untuk nilai sensor kecuali nilai mentah ADC (`SMALLINT UNSIGNED`)
