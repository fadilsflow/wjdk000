---
applyTo: "app/Models/**/*.php"
---

# Models Layer Instructions

## Role

Model adalah **representasi tabel database**. Model mendefinisikan relasi, casting, fillable, dan scopes. Model tidak boleh tahu tentang business logic.

## Allowed

- `$fillable` dan `$guarded`
- `$casts` — casting tipe data (int, float, datetime, boolean, enum)
- `$hidden` — sembunyikan field sensitif (`password`, `api_key`, `remember_token`)
- Eloquent relationships (`hasMany`, `belongsTo`, `hasOne`, `belongsToMany`)
- Accessor dan mutator (PHP 8.x style)
- Local scopes (`scopeActive()`, `scopeByDevice()`)
- `$timestamps = true` (default, jangan diubah kecuali ada alasan)

## Forbidden

- Business logic (if/else domain)
- HTTP calls / external service call
- Memanggil Service atau Repository
- Raw SQL dalam model

## Prinsip Penting

- Field sensitif **WAJIB** masuk ke `$hidden`: minimal `api_key`, `password`
- Tipe data numerik sensor WAJIB di-cast ke `float` (bukan string)
- Kolom datetime seperti `recorded_at` dan `sent_at` WAJIB di-cast ke `datetime`
- Enum values (mode, status, role, rain_status) WAJIB dikommentari di atas `$fillable`

```php
// Contoh annotasi enum wajib
// mode: 'manual' | 'automatic'
// sprayer_status: 'on' | 'off'
// rain_status: 'rain' | 'no_rain'
// condition_status: 'normal' | 'waspada' | 'kritis'
```

## Checklist Sebelum Submit

- [ ] `$fillable` sudah lengkap dan benar?
- [ ] Field sensitif ada di `$hidden`?
- [ ] `$casts` sudah didefinisikan untuk tipe numerik dan datetime?
- [ ] Enum values sudah dikommentari?
- [ ] Relasi sudah menggunakan return type yang benar?
- [ ] Tidak ada business logic di model?
