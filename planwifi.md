# Rencana Penyesuaian Skema Perhitungan Keuangan WiFi BUMDes Ciwuni
## (Sistem Full Manual Nominal & Otomatis Persentase Bagi Hasil)

Dokumen ini disusun sebagai panduan teknis dan alur bisnis pembukuan unit usaha internet WiFi BUMDes Ciwuni agar sistem pencatatan keuangan akurat, fleksibel terhadap berbagai jenis paket/pajak, dan transparan.

---

## 1. Latar Belakang & Masalah Skema Lama

### A. Masalah pada Skema Lama (Hitungan Mundur 9% Kaku):
- Sistem lama menghitung bagi hasil BUMDes 9% langsung dari tagihan warga (Rp 165.000):
  - Hak BUMDes = Rp 14.850
  - Hak Provider = Rp 150.150
- **Kelemahan:**
  - Menghasilkan angka pecahan aneh pada invoice provider.
  - Tidak sesuai dengan realita invoice provider yang mematok biaya dasar bulat (misal Rp 148.000).
  - BUMDes kehilangan potensi pendapatan yang seharusnya didapatkan.

### B. Mengapa Tidak Bisa Dipatok Rumus Pajak Otomatis (Misal 11% PPN):
- Setiap paket memiliki perlakuan berbeda. Paket rumahan (misal 11 Mbps) berbeda perlakuannya dengan paket bisnis/kantor (misal 100 Mbps) yang mungkin dikenakan PPh Pasal 23 (2%), biaya sewa jaringan, atau kontrak harga grosir khusus.
- Memaksakan rumus pajak otomatis akan membuat sistem kaku dan salah hitung saat ada paket khusus.

---

## 2. Logika Baru: Full Manual Nominal, Otomatis di Perkalian Persen

### A. Komponen Input Manual (Per Pelanggan / Paket):
1. **Tarif Pelanggan (Warga):** Nominal yang dibayar konsumen (misal: Rp 165.000, atau Rp 750.000 untuk 100 Mbps).
2. **Dasar Tarikan Non PPN (Dasar Provider):** Nominal dasar sebelum PPN yang disepakati dengan provider (misal: Rp 148.500 untuk paket 11 Mbps Media Cepat).
3. **Persen Bagi Hasil BUMDes:** Nilai persentase komisi BUMDes (default: 9%, dihitung dari Dasar Tarikan Non PPN).

### B. Rumus Otomatis Sistem:
1. **Keuntungan / Hak Bagi Hasil BUMDes:**
   $$\text{Hak BUMDes} = \text{Dasar Tarikan Non PPN} \times \left(\frac{9}{100}\right)$$
   *Contoh per transaksi:* Rp 148.500 x 9% = **Rp 13.365**.
2. **Setor Bersih ke Provider:**
   $$\text{Setor ke Provider} = \text{Dasar Tarikan Non PPN} - \text{Hak BUMDes}$$
   *Contoh per transaksi:* Rp 148.500 - Rp 13.365 = **Rp 135.135**.
3. **Alokasi PPN / Cadangan Jaringan:**
   $$\text{Alokasi PPN} = \text{Tarif Pelanggan} - \text{Dasar Tarikan Non PPN}$$
   *Contoh per transaksi:* Rp 165.000 - Rp 148.500 = **Rp 16.500** (PPN 10%/11% / penyesuaian tarif).

---

## 3. Poin Baru dari Arahan Klien (Voice Note Analysis)

Dari voice note klien, terdapat 2 poin krusial tambahan yang selaras dan memperkaya sistem:

### A. Pemisahan Metode Pembayaran (Cash vs Transfer):
- Laporan rekapitulasi membedakan realisasi pembayaran yang masuk via **Cash (Tunai)** dan **Transfer**.
- Hal ini memudahkan kasir dan manajer mencocokkan fisik uang kas di laci dengan mutasi rekening bank BUMDes.

### B. Laporan Laba Bersih Unit Internet (Setelah Beban Operasional):
- Klien mengarahkan format laporan akhir yang ditandatangani Manajer & Direktur (seperti di unit Simpan Pinjam):
  1. **Pendapatan Bagi Hasil BUMDes:** Total akumulasi 9% dari Dasar Tarikan Non PPN transaksi lunas.
  2. **Beban Pengeluaran Operasional Unit:**
     - Gaji
     - Honor petugas / teknisi
     - ATB / ATK / perlengkapan
     - Biaya Operasional harian
  3. **Laba Bersih Unit Internet:**
     $$\text{Laba Bersih} = \text{Pendapatan Bagi Hasil BUMDes} - \text{Total Pengeluaran}$$

---

## 4. Simulasi Perhitungan Riil (Contoh 2 Transaksi Pelanggan)

| No | Pelanggan | Paket | Bayar Warga | Dasar Tarikan Non PPN | Bagi Hasil | Hak BUMDes (9%) | Setor ke Provider |
|:---|:---|:---|:---:|:---:|:---:|:---:|:---:|
| 1 | Bpk. Samsudin | 11 Mbps | Rp 165.000 | Rp 148.500 | 9% | Rp 13.365 | Rp 135.135 |
| 2 | Ibu Nasilah | 11 Mbps | Rp 165.000 | Rp 148.500 | 9% | Rp 13.365 | Rp 135.135 |

### Total Akumulasi di Laporan Pendapatan:
- **Total Uang Ditarik dari Konsumen:** 2 x Rp 165.000 = **Rp 330.000**
- **Total Dasar Tarikan Non PPN:** 2 x Rp 148.500 = **Rp 297.000**
- **Total Bagi Hasil BUMDes (9%):** 9% x Rp 297.000 = **Rp 26.730**
- **Total Setoran yang Ditransfer ke Provider:** Rp 297.000 - Rp 26.730 = **Rp 270.270**
*(Alokasi Pajak/Sisa:* Rp 330.000 - Rp 297.000 = Rp 33.000)*.

---

## 5. Rencana Perubahan Berkas (File Changes)

### 1. Master Pelanggan Frontend (`resources/js/Pages/Wifi/Pelanggan.vue`)
- **Modal Tambah & Edit Pelanggan:**
  - Kolom 1: `Tarif Pelanggan (Rp)` -> Input manual (field `total_tarikan`, misal 165.000).
  - Kolom 2: `Dasar Tarikan Non PPN (Rp)` -> Input manual (field `total_provider`, misal 148.500).
  - Kolom 3: `Bagi Hasil BUMDes (%)` -> Input manual / default 9% (field `bagi_hasil_bumdes`).
  - Kolom 4: `Hak BUMDes (Rp)` -> Otomatis readonly: `total_provider * (bagi_hasil_bumdes / 100)`.
  - Kolom 5: `Setor ke Provider (Rp)` -> Otomatis readonly: `total_provider - hasil_bumdes`.

### 2. Backend Pelanggan (`app/Http/Controllers/Wifi/WifiPelangganController.php`)
- `store()` dan `update()` menyimpan nilai `total_provider` manual.
- Hitung otomatis `hasil_bumdes = round($total_provider * ($pct / 100))`.

### 3. Backend Pendapatan Kotor & Laporan (`WifiPendapatanController.php` & `WifiLaporanController.php`)
- Menghitung porsi bagi hasil 9% dari Dasar Tarikan Non PPN (`total_provider`).
- Menambahkan grouping metode pembayaran: **Cash** dan **Transfer**.
- Menyiapkan rekap operasional untuk Laba Bersih Unit.

### 4. Frontend Pendapatan & Laporan (`Pendapatan.vue` & `Laporan.vue`)
- Memperbarui label kolom agar memakai istilah resmi klien:
  - `Tarif Warga`
  - `Dasar Tarikan Non PPN`
  - `Hak BUMDes (9%)`
  - `Setor Provider`
  - Rincian `Tunai / Cash` vs `Transfer`.

---

## 6. Rekomendasi Eksekusi:

1. **Gunakan istilah resmi dari klien:**
   - Gunakan label **"Dasar Tarikan Non PPN"** pada form pelanggan dan tabel laporan.
2. **Migrasi Otomatis Nilai Awal untuk 193 Pelanggan:**
   - Kita buatkan skrip migrasi agar pelanggan paket 11 Mbps otomatis terisi `total_provider = 148500` dan `hasil_bumdes = 13365` sehingga data existing langsung rapi.
3. **Pemisahan Cash vs Transfer:**
   - Ditampilkan pada rekap penerimaan kasir dan laporan berkala.
4. **Laba Bersih Unit Internet:**
   - Menghubungkan pengeluaran operasional (Gaji, Honor, ATB, Operasional) sehingga menghasilkan Laba Bersih Unit seperti format Simpan Pinjam.
