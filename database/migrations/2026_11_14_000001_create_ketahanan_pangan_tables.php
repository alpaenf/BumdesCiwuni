<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Data Pelanggan (Hewani)
        Schema::create('kp_customers', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->nullable();
            $table->string('nama');
            $table->enum('customer_type', ['Eceran', 'Agen'])->default('Eceran');
            $table->string('no_hp')->nullable();
            $table->text('alamat')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Data Produk Telur (Hewani)
        Schema::create('kp_products', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('kategori')->default('Telur'); // Telur Bagus, Telur Retak, dll
            $table->string('satuan')->default('kg');
            $table->decimal('harga_default', 15, 2)->default(0);
            $table->decimal('stok_tersedia', 12, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 3. Logistik Pakan (Hewani)
        Schema::create('kp_feed_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pakan')->default('Pakan Layer');
            $table->decimal('stok_tersedia_kg', 12, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 4. Pembelian Pakan (Feed Purchases)
        Schema::create('kp_feed_purchases', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nama_pakan');
            $table->decimal('jumlah_kg', 12, 2);
            $table->decimal('harga_per_kg', 15, 2);
            $table->decimal('total_biaya', 15, 2);
            $table->string('supplier')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Pemakaian Pakan Harian (Feed Usages)
        Schema::create('kp_feed_usages', function (Blueprint $table) {
            $table->id();
            $table->integer('hari_ke')->nullable();
            $table->string('hari')->nullable();
            $table->date('tanggal');
            $table->integer('umur_minggu_ke')->nullable();
            $table->integer('umur_minggu_hari_ke')->nullable();
            $table->decimal('stok_awal_kg', 12, 2)->default(0);
            $table->decimal('kebutuhan_per_ekor_gram', 8, 2)->default(115.00);
            $table->integer('populasi_ayam')->default(1000);
            $table->decimal('total_kebutuhan_kg', 12, 2)->default(0);
            $table->decimal('sisa_pakan_kg', 12, 2)->default(0);
            $table->decimal('harga_per_kg', 15, 2)->nullable()->default(0);
            $table->decimal('total_biaya_pemakaian', 15, 2)->nullable()->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Produksi Telur Harian (Egg Productions)
        Schema::create('kp_egg_productions', function (Blueprint $table) {
            $table->id();
            $table->integer('umur_ke')->nullable();
            $table->string('hari')->nullable();
            $table->date('tanggal');
            $table->integer('umur_minggu')->nullable();
            $table->integer('hari_ke')->nullable();
            $table->decimal('produksi_kg', 12, 2)->default(0); // Telur bagus masuk stok
            $table->decimal('stok_sebelum_kg', 12, 2)->default(0);
            $table->decimal('terjual_kg', 12, 2)->default(0);
            $table->decimal('sisa_stok_kg', 12, 2)->default(0);
            $table->decimal('harga_per_kg', 15, 2)->default(0);
            $table->decimal('pendapatan_estimasi', 15, 2)->default(0);
            $table->integer('telur_retak_butir')->default(0);
            $table->text('keterangan_retak')->nullable();
            $table->integer('telur_rusak_butir')->default(0);
            $table->text('keterangan_rusak')->nullable();
            $table->decimal('pendapatan_harian_real', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // 7. Transaksi Penjualan Telur (Egg Sales)
        Schema::create('kp_egg_sales', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi')->unique();
            $table->date('tanggal');
            $table->foreignId('customer_id')->nullable()->constrained('kp_customers')->nullOnDelete();
            $table->string('nama_pelanggan');
            $table->enum('customer_type', ['Eceran', 'Agen'])->default('Eceran');
            $table->decimal('jumlah_kg', 12, 2);
            $table->decimal('harga_per_kg', 15, 2);
            $table->decimal('total_harga', 15, 2);
            $table->decimal('jumlah_pembayaran', 15, 2);
            $table->decimal('kembalian', 15, 2)->default(0);
            $table->enum('metode_pembayaran', ['Tunai', 'Transfer', 'Qris', 'Hutang'])->default('Tunai');
            $table->enum('status_pembayaran', ['Lunas', 'Belum Lunas'])->default('Lunas');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 8. Pengeluaran (Expenses - Fixed & Variable Cost)
        Schema::create('kp_expenses', function (Blueprint $table) {
            $table->id();
            $table->enum('unit_kategori', ['hewani', 'nabati'])->default('hewani');
            $table->date('tanggal');
            $table->string('uraian');
            $table->enum('jenis_biaya', ['tetap', 'variabel'])->default('variabel');
            $table->string('kategori_biaya'); // Biaya Sewa, Biaya Pajak, Tenaga Kerja, Bibit, Bahan Baku Pakan, Vaksin & Obat, Operasional, Promosi, dll
            $table->decimal('nominal', 15, 2);
            $table->string('referensi_tipe')->nullable(); // e.g. feed_purchase
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 9. Pendapatan Lain-lain (Other Incomes)
        Schema::create('kp_other_incomes', function (Blueprint $table) {
            $table->id();
            $table->enum('unit_kategori', ['hewani', 'nabati'])->default('hewani');
            $table->date('tanggal');
            $table->string('uraian');
            $table->string('kategori')->default('Pendapatan Lain-Lain'); // e.g. Penjualan Kotoran/Pupuk Kandang, Karung Bekas, dll
            $table->decimal('nominal', 15, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 10. Arus Kas (Cash Flow)
        Schema::create('kp_cash_flows', function (Blueprint $table) {
            $table->id();
            $table->enum('unit_kategori', ['hewani', 'nabati'])->default('hewani');
            $table->date('tanggal');
            $table->string('uraian');
            $table->decimal('debit', 15, 2)->default(0); // Pemasukan
            $table->decimal('kredit', 15, 2)->default(0); // Pengeluaran
            $table->decimal('saldo_berjalan', 15, 2)->default(0);
            $table->string('sumber_tipe')->nullable(); // egg_sale, feed_purchase, expense, other_income, saldo_awal
            $table->unsignedBigInteger('sumber_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ================= NABATI TABLES =================
        // 11. Komoditas Nabati
        Schema::create('kp_plant_commodities', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('satuan')->default('kg');
            $table->decimal('harga_default', 15, 2)->default(0);
            $table->decimal('stok_tersedia', 12, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 12. Produksi Tanaman / Hasil Panen (Plant Productions)
        Schema::create('kp_plant_productions', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('commodity_id')->constrained('kp_plant_commodities')->cascadeOnDelete();
            $table->decimal('jumlah_panen', 12, 2);
            $table->string('satuan')->default('kg');
            $table->string('lokasi_lahan')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 13. Penjualan Hasil Panen Nabati (Plant Sales)
        Schema::create('kp_plant_sales', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi')->unique();
            $table->date('tanggal');
            $table->string('nama_pelanggan');
            $table->foreignId('commodity_id')->constrained('kp_plant_commodities')->cascadeOnDelete();
            $table->decimal('jumlah', 12, 2);
            $table->string('satuan')->default('kg');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('total_harga', 15, 2);
            $table->decimal('jumlah_pembayaran', 15, 2);
            $table->enum('metode_pembayaran', ['Tunai', 'Transfer', 'Qris', 'Hutang'])->default('Tunai');
            $table->enum('status_pembayaran', ['Lunas', 'Belum Lunas'])->default('Lunas');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kp_plant_sales');
        Schema::dropIfExists('kp_plant_productions');
        Schema::dropIfExists('kp_plant_commodities');
        Schema::dropIfExists('kp_cash_flows');
        Schema::dropIfExists('kp_other_incomes');
        Schema::dropIfExists('kp_expenses');
        Schema::dropIfExists('kp_egg_sales');
        Schema::dropIfExists('kp_egg_productions');
        Schema::dropIfExists('kp_feed_usages');
        Schema::dropIfExists('kp_feed_purchases');
        Schema::dropIfExists('kp_feed_stocks');
        Schema::dropIfExists('kp_products');
        Schema::dropIfExists('kp_customers');
    }
};

