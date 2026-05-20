---
applyTo: "app/Repositories/**/*.php"
---

# Repositories Layer Instructions

## Role

Repository adalah **satu-satunya pintu ke database** untuk query yang dipakai di lebih dari satu tempat. Untuk query sederhana yang hanya dipakai sekali, boleh langsung di Model atau Service.

## Kapan Dibuat

Buat Repository hanya jika query yang sama digunakan di lebih dari satu Service atau Controller.

## Allowed

- Eloquent query (`where()`, `paginate()`, `first()`, `get()`)
- `create()`, `update()`, `delete()`
- `with()` eager loading
- Eloquent scopes
- Pagination dan filtering
- Return type: Model, Collection, LengthAwarePaginator, atau `null`

## Forbidden

- Business logic (tidak boleh ada if/else domain)
- `DB::commit()` untuk mengakhiri transaksi (itu di Service)
- Memanggil Service lain
- HTTP calls atau external service
- `DB::` raw query (kecuali sangat terpaksa, dengan komentar alasannya)

## Pola Umum

```php
// Setiap repository mengikuti pola ini
public function create(array $data): Model { ... }
public function findById(int $id): ?Model { ... }
public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator { ... }
```

## Checklist Sebelum Submit

- [ ] Tidak ada business logic di Repository?
- [ ] Query menggunakan Eloquent (bukan raw SQL)?
- [ ] Return type sudah ditentukan?
- [ ] Eager loading (`with()`) sudah dipertimbangkan untuk menghindari N+1?
