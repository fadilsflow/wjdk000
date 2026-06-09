# Perancangan Sistem (UML)

Perancangan arsitektur perangkat lunak Smart Sprayer IoT dipetakan menggunakan
Unified Modelling Language (UML) untuk memvisualisasikan alur sistem berbasis web.
Diagram disusun pada tingkat gambaran umum agar mudah dibaca, namun tetap
mencerminkan alur dan struktur data sistem yang sebenarnya.

Aktor pada sistem ini terdiri dari **Publik** (melihat ringkasan tanpa login),
**Petani**, **Admin**, dan **Perangkat IoT** yang mengirimkan data sensor.

---

## 1. Use Case Diagram

```mermaid
flowchart LR
    publik([Publik])
    petani([Petani])
    admin([Admin])
    alat([Perangkat IoT])

    subgraph SYS[Sistem Smart Sprayer]
        uc1((Lihat Ringkasan Publik))
        uc2((Login))
        uc3((Lihat Dashboard Monitoring))
        uc4((Kontrol Penyemprotan))
        uc5((Lihat Riwayat & Notifikasi))
        uc6((Kelola Pengguna & Alat))
        uc7((Atur Notifikasi WhatsApp))
        uc8((Kirim Data Sensor))
    end

    publik --> uc1
    petani --> uc2
    petani --> uc3
    petani --> uc4
    petani --> uc5
    admin --> uc2
    admin --> uc3
    admin --> uc4
    admin --> uc5
    admin --> uc6
    admin --> uc7
    alat --> uc8
```

Aktor utama yang berinteraksi langsung adalah Petani dan Admin setelah melakukan
login. Publik hanya dapat melihat halaman ringkasan, sedangkan Perangkat IoT
berperan sebagai sumber data sensor lingkungan.

---

## 2. Activity Diagram — Login & Kontrol Sprayer

```mermaid
flowchart TD
    A([Mulai]) --> B[Pengguna membuka website]
    B --> C[Masuk halaman Login]
    C --> D[Memasukkan email & kata sandi]
    D --> E{Data login benar?}
    E -- Tidak --> C
    E -- Ya --> F[Sistem membuka sesi login]
    F --> G[Membuka menu Kontrol Sprayer]
    G --> H[Menekan tombol nyalakan/matikan]
    H --> I[Sistem memvalidasi permintaan]
    I --> J{Boleh dijalankan?}
    J -- Tidak --> K[Tampilkan pesan penolakan]
    K --> G
    J -- Ya --> L[Memperbarui status alat di basis data]
    L --> M[Mencatat riwayat penyemprotan]
    M --> N[Mengirim notifikasi WhatsApp]
    N --> O[Menampilkan status berhasil ke layar]
    O --> P([Selesai])
```

Alur dimulai saat pengguna membuka website dan melakukan login. Setelah sistem
memvalidasi kredensial, pengguna dapat membuka menu Kontrol Sprayer, menekan
tombol aktivasi, lalu sistem memvalidasi permintaan, memperbarui status pada
basis data, mencatat riwayat, mengirim notifikasi, dan menampilkan status
berhasil ke layar.

---

## 3. Sequence Diagram — Dashboard Monitoring

```mermaid
sequenceDiagram
    actor User as Pengguna (Browser)
    participant Web as Halaman Web
    participant Server as Server (Backend)
    participant DB as Basis Data MySQL

    User->>Server: Membuka halaman Dashboard
    Server->>DB: Mengambil data sensor terbaru
    DB-->>Server: Mengembalikan data
    Server-->>User: Menampilkan halaman + grafik

    loop Pembaruan berkala
        User->>Server: Meminta data terbaru
        Server->>DB: Mengambil data sensor
        DB-->>Server: Data sensor
        Server-->>User: Mengirim data (JSON)
        User->>Web: Memperbarui grafik & status
    end
```

Saat pengguna membuka halaman dashboard, server mengambil data sensor dari basis
data dan menampilkannya beserta grafik. Selanjutnya halaman memperbarui datanya
secara berkala dengan meminta data terbaru ke server. Grafik digambar oleh
library Chart.js, sedangkan Tailwind CSS digunakan untuk tampilan dan tata letak.

---

## 4. Class Diagram (struktur data utama)

```mermaid
classDiagram
    class Pengguna {
        +nama
        +email
        +kata_sandi
        +peran
    }
    class Alat {
        +nama
        +lokasi
        +mode
        +status_sprayer
    }
    class Threshold {
        +batas_kelembapan_tanah
        +batas_suhu
        +batas_kelembapan_udara
    }
    class DataSensor {
        +suhu
        +kelembapan_udara
        +kelembapan_tanah
        +status_hujan
        +kondisi
        +waktu
    }
    class RiwayatPenyemprotan {
        +jenis_pemicu
        +status
        +alasan
        +waktu
    }
    class RiwayatNotifikasi {
        +jenis
        +nomor_penerima
        +isi_pesan
        +status_kirim
        +waktu
    }
    class PengaturanWhatsApp {
        +nomor_penerima
        +template_pesan
    }

    Alat "1" --> "1" Threshold
    Alat "1" --> "*" DataSensor
    Alat "1" --> "*" RiwayatPenyemprotan
    Alat "1" --> "*" RiwayatNotifikasi
    Pengguna "1" --> "*" RiwayatPenyemprotan
```

Struktur data utama berpusat pada entitas Alat yang memiliki satu konfigurasi
Threshold serta banyak Data Sensor, Riwayat Penyemprotan, dan Riwayat Notifikasi.
Pengguna terhubung ke Riwayat Penyemprotan sebagai pihak yang melakukan kontrol
manual, dan Pengaturan WhatsApp menyimpan nomor penerima serta template pesan
notifikasi.
