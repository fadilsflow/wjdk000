---
applyTo: "app/Http/Controllers/**/*.php"
---

# Controllers Layer Instructions

## Role

Controller adalah **traffic cop** — menerima request, delegate ke service, return response. Controller tidak tahu cara hitung kondisi sensor atau cara kirim WhatsApp.

## Allowed

- Inject service via constructor dependency injection
- Inject Form Request untuk validasi
- Call satu service method per controller action
- Return `view()`, `redirect()`, `response()->json()`
- Ambil data input via `$request->validated()` — bukan `$request->all()`

## Forbidden

- Query Eloquent langsung (`Model::where()`, `Model::find()`)
- Business logic (if/else panjang, kalkulasi kondisi)
- `DB::` raw query
- Nested logic yang dalam
- Method lebih dari ~20 baris

## Pola Wajib

```php
// 1. Constructor injection — selalu pakai readonly
public function __construct(
    private readonly SomeService $service,
) {}

// 2. Action method — tipis, satu panggilan ke service
public function store(SomeRequest $request): JsonResponse|RedirectResponse
{
    $result = $this->service->doSomething($request->validated());
    return response()->json(['success' => true, 'data' => $result]);
}
```

## Checklist Sebelum Submit

- [ ] Method ≤ 20 baris?
- [ ] Tidak ada query Eloquent langsung?
- [ ] Input dari `$request->validated()` saja?
- [ ] Middleware sudah diterapkan di route (bukan di controller)?
