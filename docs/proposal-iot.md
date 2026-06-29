# Proposal IoT - Rancang Bangun Smart Sprayer IoT untuk Pengendalian Hama Kutu pada Bawang Merah di Musim Hujan dan Kemarau

## 1. Identitas Proposal

**Judul:** Rancang Bangun Smart Sprayer IoT untuk Pengendalian Hama Kutu pada Bawang Merah di Musim Hujan dan Kemarau  
**Nama:** Naela Alfa Rahmania  
**NIM:** 23040051  
**Program Studi:** D-3 Teknik Komputer Sekolah Vokasi  
**Institusi:** Universitas Harkat Negeri  
**Tahun:** 2026

Dokumen ini merupakan versi Markdown yang dirapikan dari proposal IoT asli. Isi difokuskan sebagai referensi teknis untuk penyelarasan dengan PRD website.

---

## 2. Latar Belakang

Perkembangan teknologi Internet of Things (IoT) membuka peluang baru dalam sektor pertanian, khususnya untuk meningkatkan produktivitas melalui sistem otomatisasi. Sistem berbasis IoT dapat memantau faktor lingkungan seperti kelembapan tanah, suhu udara, kelembapan udara, dan kondisi hujan secara real-time.

Tanaman bawang merah merupakan komoditas hortikultura penting di Indonesia yang memiliki nilai ekonomi tinggi. Namun, proses budidayanya sering menghadapi kendala serangan hama yang dapat menurunkan produktivitas. Salah satu kendala utama adalah hama kutu yang menyerang bagian tanaman dan dapat menurunkan kualitas hasil panen.

Kondisi lingkungan pada musim hujan dan kemarau memengaruhi perkembangan hama serta kebutuhan penyemprotan. Oleh karena itu, diperlukan sistem yang mampu memantau kondisi lingkungan dan melakukan penyemprotan pestisida secara otomatis berdasarkan data sensor.

Smart Sprayer berbasis IoT menjadi solusi untuk membantu proses pengendalian hama secara lebih efektif dan efisien. Sistem ini menggunakan sensor untuk membaca kondisi lingkungan, ESP32 sebagai pusat kendali, serta pompa sprayer sebagai aktuator. Dengan sistem ini, penyemprotan dapat dilakukan ketika kondisi memenuhi aturan tertentu, misalnya tanah kering dan tidak sedang hujan.

## 3. Perumusan Masalah

Perumusan masalah dalam penelitian ini berfokus pada bagaimana merancang dan membangun sistem Smart Sprayer berbasis Internet of Things (IoT) yang mampu memantau suhu, kelembapan tanah, dan curah hujan secara real-time, serta menentukan waktu penyemprotan yang optimal untuk pengendalian hama kutu pada tanaman bawang merah pada musim hujan dan musim kemarau.

## 4. Batasan Masalah

Penelitian ini dibatasi pada:

1. Perancangan dan pembangunan sistem Smart Sprayer berbasis IoT untuk pengendalian hama kutu pada tanaman bawang merah.
2. Penggunaan ESP32 sebagai pusat kendali.
3. Penggunaan sensor DHT22 untuk membaca suhu udara dan kelembapan udara.
4. Penggunaan sensor soil moisture untuk membaca kelembapan tanah.
5. Penggunaan sensor hujan untuk mendeteksi kondisi hujan.
6. Penggunaan relay dan pompa sebagai aktuator penyemprotan.
7. Penyemprotan otomatis berdasarkan parameter sensor.
8. Pengendalian hama difokuskan pada proses penyemprotan, tanpa mengkaji jenis pestisida secara kimiawi.
9. Kondisi musim hujan dan kemarau disimulasikan melalui perubahan nilai sensor, bukan pengujian langsung pada dua musim berbeda.
10. Objek penelitian difokuskan pada tanaman bawang merah dalam skala prototype.
11. Penelitian tidak mencakup analisis biologis tanaman secara mendalam dan tidak mencakup implementasi skala luas.

## 5. Tujuan Penelitian

Tujuan penelitian ini adalah mengembangkan Smart Sprayer IoT untuk pengendalian hama kutu pada bawang merah di musim hujan dan kemarau. Sistem diharapkan mampu melakukan penyemprotan pestisida secara otomatis dan efektif berdasarkan kondisi lingkungan yang dibaca oleh sensor.

## 6. Manfaat Penelitian

Manfaat penelitian ini adalah:

1. Membantu proses pengendalian hama kutu pada tanaman bawang merah.
2. Mengurangi ketergantungan pada penyemprotan manual.
3. Membantu menentukan waktu penyemprotan berdasarkan data lingkungan.
4. Mendukung efisiensi penggunaan pestisida.
5. Menjadi referensi pengembangan teknologi pertanian berbasis IoT.

## 7. Teori Terkait

Beberapa penelitian terdahulu menunjukkan bahwa penerapan IoT pada pertanian mampu membantu proses monitoring dan otomatisasi. Sistem seperti smart agriculture, smart greenhouse, dan sprayer otomatis telah digunakan untuk memantau lingkungan, mengontrol aktuator, dan meningkatkan efisiensi pengelolaan tanaman.

Penelitian terkait juga menunjukkan bahwa suhu, kelembapan, dan kondisi lingkungan dapat menjadi parameter penting dalam pengambilan keputusan penyemprotan. Dengan pemantauan berkelanjutan, petani dapat menentukan waktu tindakan seperti penyiraman, pemupukan, atau penyemprotan pestisida secara lebih tepat.

## 8. Landasan Teori

### 8.1 Internet of Things (IoT)

Internet of Things (IoT) merupakan konsep teknologi yang memungkinkan perangkat elektronik saling terhubung melalui jaringan internet untuk melakukan pertukaran data secara otomatis. Dalam pertanian, IoT digunakan untuk memantau kondisi lingkungan dan mengontrol perangkat secara real-time.

### 8.2 Tanaman Bawang Merah

Tanaman bawang merah (*Allium ascalonicum L.*) merupakan komoditas hortikultura bernilai ekonomi tinggi. Tanaman ini sensitif terhadap kondisi lingkungan seperti suhu, kelembapan, dan curah hujan.

### 8.3 Hama Kutu

Hama kutu merupakan salah satu hama yang dapat menyerang tanaman bawang merah. Hama ini dapat mengganggu pertumbuhan tanaman dan menurunkan kualitas hasil panen. Oleh karena itu, diperlukan metode pengendalian yang efektif.

### 8.4 Smart Sprayer

Smart Sprayer adalah sistem penyemprotan otomatis yang memanfaatkan sensor sebagai input, mikrokontroler sebagai pemroses, dan pompa sprayer sebagai output. Sistem ini bekerja berdasarkan kondisi lingkungan yang terdeteksi oleh sensor.

### 8.5 ESP32

ESP32 adalah mikrokontroler yang telah dilengkapi modul Wi-Fi sehingga cocok digunakan dalam pengembangan aplikasi IoT. Pada sistem ini, ESP32 berfungsi sebagai pusat kendali untuk membaca sensor dan mengontrol relay/pompa.

### 8.6 Protokol Komunikasi IoT

Proposal IoT menyebut penggunaan protokol MQTT sebagai protokol komunikasi ringan untuk pertukaran data IoT. Dalam integrasi dengan website, data sensor dapat dikirim ke sistem monitoring sesuai mekanisme komunikasi yang disepakati antara perangkat IoT dan aplikasi web.

### 8.7 Arduino IDE

Arduino IDE digunakan untuk menulis, menguji, dan mengunggah program ke mikrokontroler seperti ESP32.

### 8.8 Sensor Soil Moisture

Sensor soil moisture digunakan untuk mendeteksi kadar air di dalam tanah. Data sensor ini menjadi salah satu acuan utama untuk menentukan apakah tanah berada dalam kondisi kering atau lembap.

### 8.9 Sensor Hujan

Sensor hujan digunakan untuk mendeteksi adanya air hujan. Data ini digunakan agar sistem tidak melakukan penyemprotan ketika sedang hujan.

### 8.10 Sensor DHT22

Sensor DHT22 digunakan untuk membaca suhu udara dan kelembapan udara. Sensor ini mendukung pemantauan kondisi lingkungan di sekitar tanaman.

### 8.11 Relay

Relay berfungsi sebagai sakelar elektronik untuk mengaktifkan atau menonaktifkan pompa sprayer berdasarkan perintah dari ESP32.

### 8.12 Panel Surya

Panel surya dan sistem pengisian baterai digunakan sebagai sumber daya agar alat dapat berjalan secara mandiri. Detail perancangan daya menjadi bagian dari alat IoT, bukan bagian utama aplikasi website.

## 9. Gambaran Sistem IoT

Sistem Smart Sprayer IoT bekerja dengan alur berikut:

1. Panel surya menghasilkan energi dan mengisi baterai.
2. ESP32 aktif dan melakukan inisialisasi komponen.
3. ESP32 membaca sensor soil moisture.
4. ESP32 membaca sensor DHT22 untuk suhu dan kelembapan udara.
5. ESP32 membaca sensor hujan.
6. Data sensor dianalisis untuk menentukan kondisi lingkungan.
7. Jika tanah kering dan tidak hujan, pompa sprayer aktif.
8. Jika tanah basah atau hujan, pompa sprayer mati.
9. Data monitoring dapat dikirim ke sistem monitoring website.
10. Proses berjalan berulang secara real-time.

## 10. Flowchart Sistem

| No | Proses | Keterangan |
|---:|---|---|
| 1 | Start | Proses dimulai saat sistem diaktifkan |
| 2 | Panel surya menghasilkan energi | Energi matahari diubah menjadi listrik |
| 3 | Pengisian baterai | Energi disimpan dalam baterai |
| 4 | Sistem ON | ESP32 mulai berjalan |
| 5 | Inisialisasi komponen | Sensor dan relay disiapkan |
| 6 | Membaca sensor tanah | Data kelembapan tanah diambil |
| 7 | Membaca sensor cuaca | Data suhu dan kelembapan udara diambil |
| 8 | Membaca sensor hujan | Status hujan dideteksi |
| 9 | Analisis data sensor | Data diolah untuk menentukan kondisi |
| 10 | Cek tanah kering | Menentukan apakah tanah membutuhkan penyemprotan |
| 11 | Cek hujan | Menentukan apakah sedang hujan |
| 12 | Pompa OFF | Pompa mati jika tanah basah atau hujan |
| 13 | Pompa ON | Pompa menyala jika tanah kering dan tidak hujan |
| 14 | Informasi monitoring | Data kondisi dikirim ke sistem monitoring |
| 15 | Loop | Sistem kembali melakukan monitoring |

## 11. Metodologi Penelitian

Penelitian menggunakan pendekatan SDLC dengan model Prototype. Model Prototype digunakan karena pengembangan alat membutuhkan evaluasi berulang terhadap rancangan hardware dan logika kerja sistem.

Tahapan penelitian:

1. **Pengumpulan kebutuhan**  
   Menentukan kebutuhan sistem, parameter sensor, dan komponen utama seperti ESP32, sensor, relay, dan pompa.

2. **Desain cepat**  
   Membuat rancangan awal rangkaian, alur kerja sistem, dan logika penyemprotan.

3. **Evaluasi prototype**  
   Mengevaluasi rancangan awal bersama pembimbing atau pengguna.

4. **Pembuatan alat**  
   Merakit perangkat keras dan menulis program ESP32.

5. **Pengujian sistem**  
   Menguji pembacaan sensor, logika penyemprotan, dan respons pompa.

## 12. Metode Pengumpulan Data

### 12.1 Observasi

Observasi dilakukan terhadap kondisi lingkungan dan proses budidaya bawang merah. Fokus observasi meliputi suhu udara, kelembapan udara, kondisi hujan, kelembapan tanah, pola kemunculan hama kutu, dan cara penyemprotan manual yang biasa dilakukan petani.

### 12.2 Wawancara

Wawancara dilakukan dengan petani bawang merah dan pihak terkait untuk memperoleh informasi mengenai kendala pengendalian hama kutu, metode penyemprotan yang digunakan, serta kebutuhan terhadap sistem penyemprotan otomatis.

## 13. Rencana Luaran TA

Luaran penelitian IoT adalah prototype Smart Sprayer berbasis IoT untuk pengendalian hama kutu pada tanaman bawang merah. Luaran tambahan berupa artikel ilmiah atau laporan tugas akhir yang menjelaskan latar belakang, metode, implementasi, pengujian, dan pembahasan.

## 14. Kaitan dengan PRD Website

Bagian yang perlu didukung oleh website berdasarkan proposal IoT:

- Menerima data suhu udara dari perangkat IoT.
- Menerima data kelembapan udara dari perangkat IoT.
- Menerima data kelembapan tanah dari perangkat IoT.
- Menerima data status hujan dari perangkat IoT.
- Menampilkan data sensor secara real-time pada dashboard.
- Menampilkan status pompa/sprayer.
- Mendukung kontrol sprayer manual dan otomatis.
- Menyimpan riwayat data sensor dan penyemprotan.
- Mengirim notifikasi WhatsApp sesuai kebutuhan sistem monitoring.

Bagian yang tidak menjadi scope website:

- Detail rangkaian ESP32.
- Wiring sensor dan relay.
- Desain panel surya dan baterai.
- Pemilihan pestisida secara kimiawi.
- Analisis biologis hama secara mendalam.
