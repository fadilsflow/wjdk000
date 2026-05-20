# Proposal Tugas Akhir

## Perancangan Sistem Monitoring dan Penyemprotan Otomatis Berbasis Website untuk Pengendalian Hama Kutu pada Bawang Merah di Brebes

---

## 1. Latar Belakang

Permasalahan pertanian modern semakin kompleks seiring meningkatnya kebutuhan pangan dan semakin terbatasnya lahan produktif. Pada budidaya bawang merah, khususnya di wilayah Brebes, serangan hama kutu atau thrips menjadi salah satu faktor yang dapat menurunkan produktivitas tanaman. Pengendalian hama yang masih dilakukan secara manual sering kali tidak berbasis data kondisi lingkungan, sehingga penggunaan pestisida menjadi kurang efisien dan respons terhadap kondisi kritis dapat terlambat.

Perkembangan teknologi digital mendorong penerapan sistem berbasis website pada sektor pertanian sebagai media monitoring dan kontrol terintegrasi. Website dapat berperan sebagai antarmuka pengguna untuk menampilkan data secara visual, interaktif, dan real-time, sehingga membantu pengguna memahami kondisi lahan secara lebih cepat dan akurat [1].

Dalam konteks smart farming, integrasi website dengan teknologi Internet of Things (IoT) memungkinkan data dari sensor seperti suhu udara, kelembapan udara, dan kelembapan tanah dikirimkan ke server untuk ditampilkan dalam bentuk dashboard monitoring [2]. Selain monitoring, sistem berbasis website juga dapat digunakan untuk mengontrol perangkat secara jarak jauh, misalnya mengaktifkan atau menonaktifkan alat penyemprot pestisida melalui jaringan internet [3].

Penelitian sebelumnya menunjukkan bahwa sistem monitoring berbasis IoT mampu meningkatkan efisiensi pengelolaan lahan melalui pengumpulan data real-time [4]. Namun, sebagian sistem yang telah dikembangkan masih terbatas pada fungsi monitoring dan belum mengintegrasikan kontrol otomatis serta notifikasi peringatan dini secara langsung kepada pengguna [5][6]. Keterbatasan tersebut menyebabkan pengguna tetap harus melakukan tindakan manual berdasarkan informasi yang ditampilkan.

Selain itu, notifikasi real-time menjadi aspek penting dalam sistem monitoring pertanian. WhatsApp merupakan aplikasi perpesanan yang banyak digunakan di Indonesia, sehingga integrasi WhatsApp API dapat menjadi solusi efektif untuk mengirimkan peringatan dini kepada petani ketika kondisi lahan berada pada status kritis atau ketika perangkat mengalami gangguan [9].

Berdasarkan permasalahan tersebut, penelitian ini merancang dan membangun sistem monitoring dan penyemprotan otomatis berbasis website yang terintegrasi dengan perangkat IoT dan WhatsApp API. Sistem ini diharapkan mampu menampilkan data kondisi lahan secara real-time, mengontrol penyemprotan pestisida secara otomatis maupun manual, serta mengirim notifikasi peringatan dini kepada petani. Implementasi website direncanakan menggunakan Laravel, PHP, Tailwind CSS, MySQL, serta REST API untuk komunikasi dengan perangkat IoT.

## 2. Perumusan Masalah

Berdasarkan latar belakang di atas, rumusan masalah dalam penelitian ini adalah:

> Bagaimana merancang dan membangun sistem monitoring kondisi lingkungan tanaman bawang merah berbasis IoT yang mampu menyajikan data secara real-time melalui dashboard website, mengimplementasikan kontrol penyemprotan pestisida otomatis maupun manual, serta menyediakan sistem peringatan dini melalui WhatsApp API dalam satu platform terpadu?

## 3. Batasan Masalah

Batasan masalah dalam penelitian ini adalah sebagai berikut:

1. Sistem difokuskan pada pengembangan perangkat lunak berbasis website menggunakan Laravel, PHP, Tailwind CSS, dan MySQL.
2. Sistem menerima dan memproses data sensor suhu udara, kelembapan udara, dan kelembapan tanah.
3. Sistem digunakan untuk mendukung pengendalian hama kutu/thrips pada tanaman bawang merah.
4. Sistem menyediakan dashboard monitoring real-time, grafik data sensor, dan status kondisi lahan.
5. Sistem menyediakan kontrol penyemprotan pestisida secara manual dan otomatis berdasarkan threshold.
6. Sistem mengintegrasikan WhatsApp API/Gateway untuk notifikasi peringatan dini.
7. Sistem menyediakan hak akses untuk Admin, Petani, dan halaman publik terbatas.
8. Penelitian tidak membahas teknis perakitan hardware secara mendalam.
9. Penelitian tidak membahas aspek biologis tanaman atau jenis hama selain hama kutu/thrips secara mendalam.
10. Pengujian dilakukan dalam skala terbatas dan belum mencakup implementasi luas di banyak wilayah pertanian.

## 4. Tujuan Penelitian

Tujuan dari penelitian ini adalah:

1. Merancang dan membangun website monitoring kondisi lahan bawang merah secara real-time.
2. Mengintegrasikan website dengan perangkat IoT melalui REST API.
3. Membangun fitur kontrol penyemprotan pestisida secara manual dan otomatis.
4. Membangun sistem peringatan dini menggunakan WhatsApp API/Gateway.
5. Menyediakan riwayat data sensor, aktivitas penyemprotan, dan log notifikasi.
6. Menghasilkan sistem yang dapat membantu petani dalam pengambilan keputusan berbasis data.

## 5. Manfaat Penelitian

Manfaat penelitian ini meliputi:

1. **Bagi petani**  
   Membantu petani memantau kondisi lahan secara real-time dan merespons potensi serangan hama dengan lebih cepat.

2. **Bagi pengembangan teknologi pertanian**  
   Menjadi contoh penerapan smart farming berbasis IoT, website, dan WhatsApp API pada budidaya bawang merah.

3. **Bagi akademik**  
   Menjadi referensi penelitian terkait sistem monitoring, kontrol otomatis, dan integrasi IoT pada sektor pertanian.

4. **Bagi efisiensi operasional**  
   Membantu mengurangi keterlambatan informasi dan mendukung penggunaan pestisida secara lebih terukur.

## 6. Teori Terkait

### 6.1 Internet of Things (IoT)

Internet of Things (IoT) merupakan konsep yang memungkinkan perangkat fisik seperti sensor dan aktuator saling terhubung melalui jaringan internet. Dalam penelitian ini, IoT digunakan untuk mengirimkan data kondisi lahan dari sensor ke server website secara real-time serta menerima perintah kontrol penyemprotan dari sistem.

### 6.2 Sistem Monitoring Pertanian

Sistem monitoring pertanian berfungsi untuk mengumpulkan, menyimpan, dan menampilkan data kondisi lahan secara berkelanjutan. Dengan adanya monitoring berbasis website, petani dapat melihat kondisi suhu, kelembapan udara, kelembapan tanah, dan status perangkat tanpa harus selalu berada di lokasi lahan.

### 6.3 Sistem Kontrol Otomatis

Sistem kontrol otomatis digunakan untuk mengaktifkan atau menonaktifkan perangkat berdasarkan aturan tertentu. Pada penelitian ini, penyemprotan pestisida dapat dikontrol otomatis berdasarkan threshold sensor, misalnya suhu tinggi atau kelembapan yang tidak ideal.

### 6.4 WhatsApp API/Gateway

WhatsApp API/Gateway digunakan untuk mengirimkan notifikasi otomatis kepada pengguna. Notifikasi dikirim ketika kondisi lingkungan membutuhkan perhatian atau ketika terdapat aktivitas penyemprotan tertentu. Integrasi ini berfungsi sebagai early warning system agar pengguna tetap menerima informasi penting meskipun tidak sedang membuka dashboard.

### 6.5 Laravel

Laravel adalah framework berbasis PHP yang digunakan untuk membangun aplikasi website secara terstruktur, aman, dan mudah dikembangkan. Laravel mendukung arsitektur Model-View-Controller (MVC), routing, middleware, validasi request, ORM Eloquent, autentikasi, dan pembuatan REST API. Dalam sistem ini, Laravel digunakan sebagai framework utama untuk membangun backend, antarmuka dashboard, pengolahan data sensor, kontrol penyemprotan, serta integrasi WhatsApp API.

### 6.6 PHP

PHP digunakan sebagai bahasa pemrograman utama pada Laravel. PHP berperan dalam pengolahan request dari perangkat IoT, pemrosesan logika sistem, validasi data, pengelolaan database, dan pengiriman response API.

### 6.7 Tailwind CSS

Tailwind CSS adalah framework CSS berbasis utility-first yang digunakan untuk membangun antarmuka responsif secara cepat dan konsisten. Pada penelitian ini, Tailwind CSS digunakan untuk membuat dashboard yang mudah dibaca oleh petani melalui perangkat desktop maupun mobile.

### 6.8 MySQL

MySQL digunakan sebagai basis data untuk menyimpan data user, lahan, perangkat, data sensor, threshold, riwayat penyemprotan, dan log notifikasi. Data historis tersebut digunakan untuk laporan dan visualisasi grafik pada dashboard.

### 6.9 REST API

REST API digunakan sebagai penghubung antara perangkat IoT dan website. Perangkat IoT mengirim data sensor ke endpoint API, sedangkan website menyediakan endpoint untuk membaca perintah kontrol perangkat.

### 6.10 UML

Unified Modeling Language (UML) digunakan sebagai alat bantu pemodelan sistem. Diagram yang dapat digunakan meliputi Use Case Diagram, Activity Diagram, Sequence Diagram, dan Class Diagram untuk menggambarkan kebutuhan dan alur sistem.

### 6.11 Black Box Testing

Black Box Testing digunakan untuk menguji fungsi sistem berdasarkan input dan output tanpa melihat struktur internal kode. Pengujian dilakukan pada fitur login, dashboard, API sensor, kontrol penyemprotan, notifikasi WhatsApp, riwayat, dan tampilan responsif.

## 7. Metodologi Penelitian

Penelitian ini menggunakan metode System Development Life Cycle (SDLC) model Waterfall. Model Waterfall dipilih karena memiliki tahapan yang terstruktur dan sesuai untuk pengembangan sistem yang kebutuhannya telah didefinisikan sejak awal.

### 7.1 Identifikasi Masalah dan Perencanaan

Tahap awal dilakukan dengan mengidentifikasi masalah pada proses monitoring dan pengendalian hama kutu bawang merah di Brebes. Perencanaan dilakukan dengan menentukan kebutuhan sistem, teknologi yang digunakan, dan ruang lingkup pengembangan.

### 7.2 Analisis Kebutuhan

Tahap ini mengidentifikasi kebutuhan perangkat lunak dan data yang dibutuhkan, meliputi:

- Data sensor suhu udara, kelembapan udara, dan kelembapan tanah.
- Threshold untuk menentukan status normal, waspada, dan kritis.
- Kebutuhan dashboard monitoring real-time.
- Kebutuhan kontrol penyemprotan manual dan otomatis.
- Kebutuhan integrasi WhatsApp API/Gateway.
- Kebutuhan penyimpanan data menggunakan MySQL.

### 7.3 Desain Sistem

Tahap desain mencakup:

- Perancangan arsitektur sistem website dan IoT.
- Perancangan database MySQL.
- Perancangan REST API.
- Perancangan antarmuka dashboard menggunakan Tailwind CSS.
- Perancangan UML seperti Use Case, Activity, Sequence, dan Class Diagram.

### 7.4 Implementasi

Tahap implementasi dilakukan dengan membangun sistem menggunakan:

- Laravel sebagai framework utama website.
- PHP sebagai bahasa pengembangan.
- Tailwind CSS untuk antarmuka.
- MySQL sebagai database.
- REST API untuk komunikasi IoT.
- WhatsApp API/Gateway untuk pengiriman notifikasi.

### 7.5 Integrasi dan Pengujian

Sistem yang telah dibangun diuji menggunakan Black Box Testing. Pengujian berfokus pada:

- Validasi data sensor yang masuk ke sistem.
- Tampilan data sensor pada dashboard.
- Kontrol penyemprotan manual.
- Kontrol penyemprotan otomatis berdasarkan threshold.
- Pengiriman notifikasi WhatsApp.
- Riwayat data sensor, penyemprotan, dan notifikasi.
- Responsivitas tampilan website.

### 7.6 Operasi dan Pemeliharaan

Tahap pemeliharaan dilakukan setelah sistem berjalan. Kegiatan pemeliharaan meliputi perbaikan bug, pembaruan keamanan, optimasi performa, dan penyesuaian threshold sesuai kebutuhan lapangan.

## 8. Metode Pengumpulan Data

### 8.1 Observasi

Observasi dilakukan melalui pengamatan langsung pada lahan pertanian bawang merah di wilayah Brebes. Observasi bertujuan untuk mengetahui kondisi lahan, pola serangan hama kutu, serta proses penyemprotan pestisida yang dilakukan secara konvensional.

### 8.2 Wawancara

Wawancara dilakukan dengan pihak terkait, yaitu petani bawang merah di lokasi penelitian. Wawancara bertujuan untuk memperoleh informasi mengenai kendala pengendalian hama, kebutuhan monitoring, serta harapan pengguna terhadap sistem yang akan dibangun.

### 8.3 Studi Literatur

Studi literatur dilakukan dengan mempelajari penelitian, jurnal, buku, dan sumber lain yang berkaitan dengan IoT, sistem monitoring pertanian, kontrol otomatis, WhatsApp API, Laravel, MySQL, dan pengujian sistem.

## 9. Rencana Luaran Tugas Akhir

Luaran dari penelitian ini adalah:

1. **Produk perangkat lunak**  
   Website monitoring dan kendali penyemprotan hama kutu pada bawang merah berbasis IoT, Laravel, MySQL, dan WhatsApp API/Gateway.

2. **Dokumentasi sistem**  
   Dokumentasi kebutuhan sistem, rancangan database, API, alur sistem, dan hasil pengujian.

3. **Artikel ilmiah/laporan Tugas Akhir**  
   Artikel atau laporan yang memuat latar belakang, metodologi, implementasi, hasil pengujian, dan pembahasan.

## 10. Gambaran Fitur Sistem

Fitur utama sistem yang akan dibangun adalah:

1. Login dan manajemen hak akses.
2. Dashboard monitoring real-time.
3. Integrasi API data sensor IoT.
4. Grafik data sensor real-time dan historis.
5. Pengaturan threshold kondisi lahan.
6. Status kondisi normal, waspada, dan kritis.
7. Kontrol penyemprotan manual.
8. Kontrol penyemprotan otomatis.
9. Notifikasi WhatsApp untuk peringatan dini.
10. Riwayat data sensor.
11. Riwayat penyemprotan.
12. Riwayat notifikasi.
13. Halaman publik ringkasan kondisi lahan.

## 11. Jadwal Kegiatan

Jadwal kegiatan penelitian direncanakan dalam beberapa tahap berikut:

| Tahap | Kegiatan                                 |
| ----- | ---------------------------------------- |
| 1     | Identifikasi masalah dan studi literatur |
| 2     | Observasi dan wawancara                  |
| 3     | Analisis kebutuhan sistem                |
| 4     | Perancangan database, API, UI, dan UML   |
| 5     | Implementasi website dan integrasi IoT   |
| 6     | Integrasi WhatsApp API/Gateway           |
| 7     | Pengujian sistem                         |
| 8     | Perbaikan dan finalisasi                 |
| 9     | Penyusunan laporan Tugas Akhir           |
