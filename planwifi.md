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
1. **Total Tarikan Warga:** Nominal yang ditawarkan dan ditagihkan ke pelanggan (misal: Rp 165.000 atau Rp 750.000 untuk 100 Mbps).
2. **Dasar Setor Provider:** Nominal yang wajib BUMDes bayarkan/laporkan ke provider per pelanggan (misal: Rp 148.000 atau Rp 650.000).
3. **Persen Bagi Hasil BUMDes:** Nilai persentase komisi BUMDes (default: 9%, dapat disesuaikan manual per pelanggan/provider).

### B. Rumus Otomatis Sistem:
1. **Keuntungan / Hak BUMDes:**
   $$\text{Hak BUMDes} = \text{Dasar Setor Provider} \times \left(\frac{\text{Bagi Hasil \%}}{100}\right)$$
2. **Setor Bersih ke Provider:**
   $$\text{Setor Bersih Provider} = \text{Dasar Setor Provider} - \text{Hak BUMDes}$$
3. **Selisih Pajak / Biaya Lain (Informasional):**
   $$\text{Pajak \& Operasional Desa} = \text{Total Tarikan Warga} - \text{Dasar Setor Provider}$$

---

## 3. Simulasi Perhitungan Riil (Contoh 2 Transaksi Pelanggan)

| No | Pelanggan | Paket | Tarikan Warga | Dasar Provider | Bagi Hasil | Hak BUMDes (9%) | Setor Bersih Provider |
|:---|:---|:---|:---:|:---:|:---:|:---:|:---:|
| 1 | Bpk. Samsudin | 11 Mbps | Rp 165.000 | Rp 148.000 | 9% | Rp 13.320 | Rp 134.680 |
| 2 | Ibu Nasilah | 11 Mbps | Rp 165.000 | Rp 148.000 | 9% | Rp 13.320 | Rp 134.680 |

### Total Akumulasi di Laporan Pendapatan:
- **Total Uang Ditarik dari Warga:** Rp 165.000 + Rp 165.000 = **Rp 330.000**
- **Total Dasar yang Mau Dibayar ke Provider:** Rp 148.000 + Rp 148.000 = **Rp 296.000**
- **Total Keuntungan BUMDes (9%):** 9% x Rp 296.000 = **Rp 26.640**
- **Total yang BUMDes Transfer ke Provider:** Rp 296.000 - Rp 26.640 = **Rp 269.360**

*(Catatan: Sisa dana Rp 330.000 - Rp 296.000 = Rp 34.000 adalah alokasi PPN 11% faktur provider / cadangan pemeliharaan jaringan).*

---

## 4. Rencana Perubahan Berkas (File Changes)

### 1. Master Pelanggan Frontend (`resources/js/Pages/Wifi/Pelanggan.vue`)
- **Modal Tambah & Edit Pelanggan:**
  - Kolom 1: `Total Tarikan Warga (Rp)` -> Input manual.
  - Kolom 2: `Dasar Tagihan Provider (Rp)` -> Input manual (memakai field `total_provider`).
  - Kolom 3: `Bagi Hasil BUMDes (%)` -> Input manual / default 9% (field `bagi_hasil_bumdes`).
  - Kolom 4: `Keuntungan BUMDes (Rp)` -> Otomatis readonly dihitung dari: `total_provider * (bagi_hasil_bumdes / 100)`.
  - Kolom 5: `Setoran Bersih Provider (Rp)` -> Otomatis readonly dihitung dari: `total_provider - hasil_bumdes`.

### 2. Backend Pelanggan (`app/Http/Controllers/Wifi/WifiPelangganController.php`)
- Menyesuaikan `store()` dan `update()` agar menyimpan nilai `total_provider` manual dari form.
- Menghitung `hasil_bumdes = round($total_provider * ($pct / 100))` secara konsisten.

### 3. Backend Pendapatan Kotor (`app/Http/Controllers/Wifi/WifiPendapatanController.php`)
- Menyesuaikan kalkulasi loop pendapatan:
  - Mengambil dasar tagihan provider: `$dasarProvider = (float) ($pelanggan->total_provider > 0 ? $pelanggan->total_provider : $tarikan);`
  - Menghitung hak BUMDes: `$hakBumdes = round($dasarProvider * ($pct / 100));`
  - Menghitung hak provider: `$hakProvider = max(0, $dasarProvider - $hakBumdes);`
  - Menambahkan data `$totalDasarProvider` ke ringkasan laporan.

### 4. Frontend Pendapatan Kotor (`resources/js/Pages/Wifi/Pendapatan.vue`)
- Menyesuaikan tabel rincian Skema Persentase dengan kolom:
  `Tanggal` | `No. Struk` | `Pelanggan` | `Paket` | `Tarikan Warga` | `Dasar Provider` | `Bagi Hasil (%)` | `Hak BUMDes` | `Setor ke Provider` | `Aksi`
- Menyesuaikan ringkasan total footer:
  - Total Tarikan Warga: `rupiah(totalTarikanBruto)`
  - Total Dasar Tagihan Provider: `rupiah(totalDasarProvider)`
  - Total Keuntungan BUMDes: `rupiah(pendapatanPersentase)`
  - Total Transfer Bersih ke Provider: `rupiah(totalHakProvider)`

---

## 5. Poin Diskusi & Revisi Tipis-Tipis Bersama

Sebelum kita eksekusi penerapannya ke sistem, silakan ditinjau 3 poin berikut:

1. **Penamaan Label Kolom di Form & Tabel:**
   Apakah istilah label berikut sudah nyaman dan ramah bagi pengurus desa?
   - Tagihan ke warga: **"Tarif Pelanggan"** atau **"Total Tarikan Warga"**?
   - Tagihan modal dari provider: **"Dasar Provider"** atau **"Tagihan Modal Provider"**?
   - Keuntungan BUMDes: **"Hak BUMDes"** atau **"Keuntungan BUMDes"**?
   - Yang disetor: **"Setoran Bersih Provider"**?

2. **Migrasi / Nilai Awal untuk 193 Pelanggan yang Sudah Ada:**
   Di database saat ini terdapat sekitar 193 pelanggan terdaftar.
   - Apakah Anda ingin kita jalankan skrip pengisian otomatis satu kali (*one-time migration*) agar pelanggan yang paketnya "11 mbps" otomatis memiliki nilai Dasar Provider = Rp 148.000?
   - Tujuannya agar Anda tidak perlu mengklik dan mengedit 193 pelanggan satu per satu secara manual.

3. **Skema Admin Flat:**
   Sesuai kesepakatan awal, Skema Admin Flat (biaya admin tetap, misal Rp 5.000 per transaksi) tetap berjalan normal seperti saat ini tanpa diubah.
