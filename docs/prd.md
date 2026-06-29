[docs/prd.md#312B]
1:[docs/prd.md#CFFB]
2:1:# PRD - Website Monitoring dan Kendali Smart Sprayer IoT Bawang Merah
3:2:
4:3:## 1. Acuan Dokumen
5:4:
6:5:PRD ini disusun berdasarkan dokumen berikut:
7:6:
8:7:1. `docs/proposal.md` - proposal website monitoring dan penyemprotan otomatis berbasis Laravel, MySQL, IoT, dan WhatsApp API/Gateway.
9:8:2. `docs/proposal-iot.md` - proposal IoT Smart Sprayer yang menjelaskan perangkat ESP32, sensor DHT22, sensor soil moisture, sensor hujan, relay/pompa, panel surya, serta logika penyemprotan otomatis.
10:9:
11:10:Seluruh kebutuhan pada PRD ini harus mengikuti ruang lingkup kedua proposal di atas, tanpa menambahkan fitur di luar kebutuhan.
12:11:
13:12:## 2. Ringkasan Produk
14:13:
15:14:Website ini digunakan sebagai sistem monitoring dan kendali untuk alat Smart Sprayer IoT pada tanaman bawang merah. Website menerima data dari perangkat IoT, menampilkan kondisi lingkungan secara real-time, menampilkan status penyemprotan, menyediakan kontrol penyemprotan manual/otomatis, serta mengirim notifikasi WhatsApp sebagai peringatan dini.
16:15:
17:16:Sistem difokuskan untuk mendukung pengendalian hama kutu pada bawang merah di Brebes, dengan mempertimbangkan parameter lingkungan pada musim hujan dan kemarau.
18:17:
19:18:## 3. Tujuan Produk
20:19:
21:20:- Menampilkan data sensor lingkungan dari alat Smart Sprayer IoT secara real-time.
22:21:- Membantu pengguna memantau kondisi suhu, kelembapan tanah, kelembapan udara, dan hujan.
23:22:- Menampilkan status kondisi lingkungan sebagai dasar penyemprotan.
24:23:- Menyediakan kontrol penyemprotan pestisida secara manual dan otomatis.
25:24:- Mengirim notifikasi WhatsApp untuk kondisi penting terkait penyemprotan atau kondisi lahan.
26:25:- Menyimpan riwayat data sensor, aktivitas penyemprotan, dan notifikasi.
27:26:
28:27:## 4. Ruang Lingkup Produk
29:28:
30:29:### 4.1 Termasuk
31:30:
32:31:- Website dashboard monitoring.
33:32:- Login dan hak akses pengguna.
34:33:- Penerimaan data sensor dari perangkat IoT.
35:34:- Tampilan data suhu udara, kelembapan udara, kelembapan tanah, dan status hujan.
36:35:- Tampilan status alat sprayer.
37:36:- Kontrol penyemprotan manual.
38:37:- Dukungan mode penyemprotan otomatis berdasarkan data sensor.
39:38:- Notifikasi WhatsApp API/Gateway.
40:39:- Riwayat data sensor.
41:40:- Riwayat aktivitas penyemprotan.
42:41:- Riwayat notifikasi.
43:42:- Tampilan responsif agar dapat digunakan melalui smartphone.
44:43:- Pengujian Black Box untuk fitur utama.
45:44:
46:45:### 4.2 Tidak Termasuk
47:46:
48:47:- Perakitan hardware secara detail.
49:48:- Penentuan jenis pestisida secara kimiawi.
50:49:- Analisis biologis tanaman secara mendalam.
51:50:- Analisis hama selain hama kutu.
52:51:- Implementasi skala luas pada banyak wilayah pertanian.
53:52:- Prediksi berbasis machine learning.
54:53:- Fitur di luar kebutuhan monitoring, kontrol sprayer, riwayat, dan notifikasi.
55:54:
56:55:## 5. Target Pengguna dan Hak Akses
57:56:
58:57:| Pengguna | Deskripsi | Hak Akses |
59:58:|---|---|---|
60:59:| Admin | Petugas/pemilik sistem | Mengelola data pengguna, konfigurasi alat, threshold, pengaturan WhatsApp, dan melihat seluruh data |
61:60:| Petani | Pengguna utama sistem | Melihat dashboard, melihat riwayat, mengontrol sprayer, dan menerima notifikasi |
62:61:| Publik/Masyarakat | Pengunjung umum | Melihat ringkasan visual kondisi lahan yang tidak sensitif melalui halaman publik terbatas |
63:62:
64:63:## 6. Parameter IoT yang Digunakan
65:64:
66:65:Berdasarkan proposal IoT, perangkat menggunakan ESP32 sebagai pusat kendali dan membaca data dari sensor berikut:
67:66:
68:67:| Komponen             | Data yang Digunakan Website        |
69:68:| -------------------- | ---------------------------------- |
70:69:| DHT22                | Suhu udara dan kelembapan udara    |
71:70:| Soil Moisture Sensor | Kelembapan tanah                   |
72:71:| Sensor Hujan         | Status hujan/curah hujan sederhana |
73:72:| Relay/Pompa          | Status penyemprotan on/off         |
74:73:
75:74:Catatan:
76:75:
77:76:- Website hanya menerima dan mengolah data yang dikirim perangkat IoT.
78:77:- Detail rangkaian, panel surya, baterai, dan wiring tidak menjadi scope website.
79:78:
80:79:## 7. Fitur Produk
81:80:
82:81:### 7.1 Manajemen Pengguna
83:82:
84:83:Functional requirements:
85:84:
86:85:- Admin dapat mengelola data pengguna (nama, email, nomor telepon, role).
87:86:- Semua halaman web dapat diakses tanpa login.
88:87:
89:88:Acceptance criteria:
90:89:
91:90:- Data pengguna dapat dibuat, diubah, dan dihapus dari halaman admin.
92:91:- Tidak ada halaman yang memerlukan login.
93:92:
94:93:---
95:94:
96:95:### 7.2 Dashboard Monitoring
97:96:
98:97:Dashboard menampilkan data terbaru dari alat Smart Sprayer IoT.
99:98:
100:99:Data yang ditampilkan:
101:100:
102:101:- Suhu udara.
103:102:- Kelembapan udara.
104:103:- Kelembapan tanah.
105:104:- Status hujan.
106:105:- Status sprayer/pompa: on atau off.
107:106:- Mode alat: manual atau otomatis.
108:107:- Waktu data terakhir diterima.
109:108:- Status kondisi lingkungan.
110:109:
111:110:Komponen UI:
112:111:
113:112:- Kartu ringkasan sensor.
114:113:- Indikator status hujan.
115:114:- Indikator status sprayer.
116:115:- Grafik data sensor.
117:116:- Informasi data terakhir.
118:117:
119:118:Acceptance criteria:
120:119:
121:120:- Dashboard menampilkan data sensor terbaru.
122:121:- Dashboard dapat dibuka melalui desktop dan smartphone.
123:122:- Data sensor tampil dalam format yang mudah dipahami petani.
124:123:
125:124:---
126:125:
127:126:### 7.3 Penerimaan Data Sensor IoT
128:127:
129:128:Website menyediakan API untuk menerima data dari perangkat IoT.
130:129:
131:130:Data minimal yang dikirim:
132:131:
133:132:- `temperature` / suhu udara.
134:133:- `air_humidity` / kelembapan udara.
135:134:- `soil_moisture` / kelembapan tanah.
136:135:- `rain_status` / status hujan.
137:136:- `sprayer_status` / status pompa sprayer.
138:137:- `recorded_at` / waktu pembacaan sensor.
139:138:
140:139:Functional requirements:
141:140:
142:141:- Perangkat IoT dapat mengirim data sensor ke website.
143:142:- Website menyimpan data sensor ke database.
144:143:- Website menampilkan data terbaru pada dashboard.
145:144:- Website menyimpan data historis untuk riwayat.
146:145:
147:146:Acceptance criteria:
148:147:
149:148:- Data sensor yang valid tersimpan ke database.
150:149:- Data terbaru muncul pada dashboard.
151:150:- Riwayat data sensor dapat dilihat kembali.
152:151:
153:152:---
154:153:
155:154:### 7.4 Status Kondisi Lingkungan
156:155:
157:156:Sistem menampilkan status kondisi lingkungan berdasarkan data sensor. Status ini digunakan sebagai dasar informasi monitoring dan penyemprotan otomatis.
158:157:
159:158:Status yang digunakan mengikuti proposal website:
160:159:
161:160:| Status | Keterangan |
162:161:|---|---|
163:162:| Normal | Kondisi lingkungan masih aman |
164:163:| Waspada | Kondisi lingkungan perlu diperhatikan berdasarkan threshold |
165:164:| Kritis | Kondisi lingkungan memenuhi aturan untuk tindakan penyemprotan |
166:165:
167:166:Aturan awal penyemprotan mengikuti flow proposal IoT:
168:167:
169:168:- Jika tanah kering dan tidak hujan, kondisi dapat masuk status kritis dan sprayer dapat aktif pada mode otomatis.
170:169:- Jika tanah basah atau sedang hujan, sprayer tidak aktif.
171:170:- Status hujan tetap ditampilkan sebagai informasi sensor dan menjadi penghambat penyemprotan otomatis.
172:171:- Threshold nilai sensor dapat diatur oleh Admin agar sesuai dengan kebutuhan pengujian.
173:172:
174:173:Acceptance criteria:
175:174:
176:175:- Status kondisi berubah sesuai data sensor dan threshold.
177:176:- Status normal, waspada, dan kritis tampil pada dashboard.
178:177:- Saat sensor mendeteksi hujan, sistem tidak menjalankan penyemprotan otomatis.
179:178:- Threshold dapat diubah oleh Admin.
180:179:
181:180:---
182:181:
183:182:### 7.5 Kontrol Penyemprotan
184:183:
185:184:Website mendukung kontrol penyemprotan manual dan otomatis.
186:185:
187:186:Mode manual:
188:187:
189:188:- Petani/Admin dapat menyalakan sprayer melalui website.
190:189:- Petani/Admin dapat mematikan sprayer melalui website.
191:190:- Aktivitas tersimpan pada riwayat penyemprotan.
192:191:
193:192:Mode otomatis:
194:193:
195:194:- Sistem menentukan status penyemprotan berdasarkan data sensor.
196:195:- Sprayer aktif jika kondisi memenuhi aturan penyemprotan.
197:196:- Sprayer mati jika tanah tidak kering atau sensor mendeteksi hujan.
198:197:
199:198:Acceptance criteria:
200:199:
201:200:- Tombol manual dapat mengubah status sprayer.
202:201:- Mode otomatis mengikuti aturan sensor.
203:202:- Semua aktivitas penyemprotan tersimpan pada log.
204:203:
205:204:---
206:205:
207:206:### 7.6 Notifikasi WhatsApp
208:207:
209:208:Website mengirim notifikasi WhatsApp untuk informasi penting.
210:209:
211:210:Trigger notifikasi sesuai scope proposal:
212:211:
213:212:- Kondisi lingkungan masuk status kritis atau memenuhi aturan penyemprotan.
214:213:- Penyemprotan dimulai.
215:214:- Penyemprotan dihentikan.
216:215:- Kondisi hujan terdeteksi sehingga penyemprotan tidak dilakukan.
217:216:
218:217:Isi pesan minimal:
219:218:
220:219:- Nama alat/lahan.
221:220:- Jenis informasi/peringatan.
222:221:- Nilai sensor terakhir.
223:222:- Status sprayer.
224:223:- Waktu kejadian.
225:224:
226:225:Acceptance criteria:
227:226:
228:227:- Notifikasi terkirim saat kondisi penting terjadi.
229:228:- Setiap pengiriman notifikasi tersimpan pada riwayat notifikasi.
230:229:- Nomor WhatsApp penerima dapat diatur oleh Admin.
231:230:
232:231:---
233:232:
234:233:### 7.7 Riwayat Data
235:234:
236:235:Sistem menyimpan data agar dapat dilihat kembali.
237:236:
238:237:Riwayat yang disediakan:
239:238:
240:239:- Riwayat data sensor.
241:240:- Riwayat penyemprotan.
242:241:- Riwayat notifikasi WhatsApp.
243:242:
244:243:Acceptance criteria:
245:244:
246:245:- User dapat melihat riwayat data sensor.
247:246:- User dapat melihat riwayat penyemprotan.
248:247:- User dapat melihat riwayat notifikasi.
249:248:
250:249:---
251:250:
252:251:### 7.8 Halaman Ringkasan Publik
253:252:
254:253:Halaman ini digunakan untuk menampilkan ringkasan visual non-sensitif bagi masyarakat/pengunjung.
255:254:
256:255:Data yang boleh ditampilkan:
257:256:
258:257:- Status kondisi lahan secara umum.
259:258:- Data sensor terbaru secara ringkas.
260:259:- Waktu update terakhir.
261:260:
262:261:Data yang tidak boleh ditampilkan:
263:262:
264:263:- Tombol kontrol sprayer.
265:264:- Nomor WhatsApp.
266:265:- Konfigurasi alat.
267:266:
268:267:Acceptance criteria:
269:268:
270:269:- Halaman dapat dibuka tanpa autentikasi.
271:270:- Tidak ada fitur kontrol alat pada halaman publik.
272:271:- Tidak ada data sensitif yang tampil.
273:272:
274:273:## 8. Halaman Website
275:274:
276:275:| Halaman              | Role          | Deskripsi                                 |
277:276:| -------------------- | ------------- | ----------------------------------------- |
278:277:| Login                | Admin, Petani | Masuk ke sistem                           |
279:278:| Dashboard            | Admin, Petani | Monitoring data sensor dan status sprayer |
280:279:| Kontrol Sprayer      | Admin, Petani | Kontrol manual dan mode otomatis          |
281:280:| Riwayat Sensor       | Admin, Petani | Riwayat pembacaan sensor                  |
282:281:| Riwayat Penyemprotan | Admin, Petani | Log aktivitas sprayer                     |
283:282:| Riwayat Notifikasi   | Admin, Petani | Log pesan WhatsApp                        |
284:283:| Pengguna             | Admin         | Manajemen akun pengguna                   |
285:284:| Konfigurasi Alat     | Admin         | Pengaturan alat dan threshold             |
286:285:| Pengaturan WhatsApp  | Admin         | Pengaturan nomor penerima notifikasi      |
287:286:| Ringkasan Publik     | Publik/Masyarakat | Ringkasan visual non-sensitif             |
288:287:
289:288:## 9. Alur Sistem
290:289:
291:290:### 9.1 Alur Monitoring
292:291:
293:292:1. ESP32 membaca data sensor DHT22, soil moisture, dan sensor hujan.
294:293:2. Perangkat mengirim data ke website.
295:294:3. Website menyimpan data sensor.
296:295:4. Website menampilkan data pada dashboard.
297:296:5. Website menentukan status kondisi lingkungan.
298:297:
299:298:### 9.2 Alur Penyemprotan Otomatis
300:299:
…
307:566:- Fitur utama lulus Black Box Testing.
…
309:[Showing lines 1-300 of 567. Use :301 to continue]

[Showing lines 1-300 of 309. Use :301 to continue]