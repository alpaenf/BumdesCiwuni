<?php

use App\Http\Controllers\SimpanPinjam\AngsuranController;
use App\Http\Controllers\SimpanPinjam\BukuTabunganController;
use App\Http\Controllers\SimpanPinjam\DashboardController;
use App\Http\Controllers\SimpanPinjam\GaleriController;
use App\Http\Controllers\SimpanPinjam\KwitansiController;
use App\Http\Controllers\SimpanPinjam\LaporanController;
use App\Http\Controllers\SimpanPinjam\NasabahController;
use App\Http\Controllers\SimpanPinjam\PinjamanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SimpanPinjam\TabunganController;
use App\Http\Controllers\SimpanPinjam\PeriodeTabunganController;
use App\Http\Controllers\SimpanPinjam\LandingPageController;
use App\Http\Controllers\SimpanPinjam\PendapatanController;

use App\Http\Controllers\SimpanPinjam\TunggakanPinjamanController;
use App\Http\Controllers\SimpanPinjam\PengaturanTabunganController;
use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\Portal\CmsController;
use App\Http\Controllers\Portal\PostController;
use App\Http\Controllers\Portal\UnitController;
use App\Http\Controllers\Portal\ExecDashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// =====================================================
// PORTAL BUMDES — PUBLIC WEBSITE
// =====================================================
Route::get('/', [PortalController::class, 'index'])->name('portal.home');
Route::get('/berita', [PortalController::class, 'beritaIndex'])->name('portal.berita.index');
Route::get('/berita/{slug}', [PortalController::class, 'beritaDetail'])->name('portal.berita.detail');

// =====================================================
// PORTAL BUMDES — CMS & EXECUTIVE DASHBOARD (Auth Required)
// =====================================================
Route::middleware(['auth', 'role:admin'])->prefix('portal')->group(function () {

    // CMS Dashboard
    Route::get('/cms', [CmsController::class, 'dashboard'])->name('portal.cms.dashboard');

    // Profil BUMDes
    Route::get('/cms/profil', [CmsController::class, 'editProfil'])->name('portal.cms.profil.edit');
    Route::put('/cms/profil', [CmsController::class, 'updateProfil'])->name('portal.cms.profil.update');

    // Banner
    Route::get('/cms/banner', [CmsController::class, 'editBanner'])->name('portal.cms.banner.edit');
    Route::put('/cms/banner', [CmsController::class, 'updateBanner'])->name('portal.cms.banner.update');

    // Upload Image
    Route::post('/cms/upload-image', [CmsController::class, 'uploadImage'])->name('portal.cms.upload-image');

    // Berita CRUD
    Route::get('/cms/berita', [PostController::class, 'index'])->name('portal.cms.berita.index');
    Route::get('/cms/berita/buat', [PostController::class, 'create'])->name('portal.cms.berita.create');
    Route::post('/cms/berita', [PostController::class, 'store'])->name('portal.cms.berita.store');
    Route::get('/cms/berita/{post}/edit', [PostController::class, 'edit'])->name('portal.cms.berita.edit');
    Route::put('/cms/berita/{post}', [PostController::class, 'update'])->name('portal.cms.berita.update');
    Route::delete('/cms/berita/{post}', [PostController::class, 'destroy'])->name('portal.cms.berita.destroy');

    // Unit Usaha CRUD
    Route::get('/cms/unit', [UnitController::class, 'index'])->name('portal.cms.unit.index');
    Route::get('/cms/unit/buat', [UnitController::class, 'create'])->name('portal.cms.unit.create');
    Route::post('/cms/unit', [UnitController::class, 'store'])->name('portal.cms.unit.store');
    Route::get('/cms/unit/{unit}/edit', [UnitController::class, 'edit'])->name('portal.cms.unit.edit');
    Route::put('/cms/unit/{unit}', [UnitController::class, 'update'])->name('portal.cms.unit.update');
    Route::delete('/cms/unit/{unit}', [UnitController::class, 'destroy'])->name('portal.cms.unit.destroy');

    // Route PIN Keamanan
    Route::get('/cms/pin', [\App\Http\Controllers\AdminUserController::class, 'showPinForm'])->name('portal.cms.pin');
    Route::post('/cms/pin', [\App\Http\Controllers\AdminUserController::class, 'verifyPin'])->name('portal.cms.pin.verify');
    Route::post('/cms/pin/reset', [\App\Http\Controllers\AdminUserController::class, 'resetPin'])->name('portal.cms.pin.reset');

    // Kelola Pengguna / Admin (Protected by PIN)
    Route::middleware(['pin'])->group(function () {
        Route::get('/cms/users', [\App\Http\Controllers\AdminUserController::class, 'index'])->name('portal.cms.users.index');
        Route::get('/cms/users/buat', [\App\Http\Controllers\AdminUserController::class, 'create'])->name('portal.cms.users.create');
        Route::post('/cms/users', [\App\Http\Controllers\AdminUserController::class, 'store'])->name('portal.cms.users.store');
        Route::get('/cms/users/{user}/edit', [\App\Http\Controllers\AdminUserController::class, 'edit'])->name('portal.cms.users.edit');
        Route::put('/cms/users/{user}', [\App\Http\Controllers\AdminUserController::class, 'update'])->name('portal.cms.users.update');
        Route::delete('/cms/users/{user}', [\App\Http\Controllers\AdminUserController::class, 'destroy'])->name('portal.cms.users.destroy');
    });
});

// Executive Dashboard (admin + admin_unit + manager + manager_pusat)
Route::middleware(['auth', 'role:admin_unit,manager,manager_pusat'])->group(function () {
    Route::get('/portal/dashboard', [ExecDashboardController::class, 'index'])->name('portal.exec.dashboard');
});

// =====================================================
// UNIT SIMPAN PINJAM — Prefixed under /unit/simpan-pinjam
// All existing routes preserved with same names
// =====================================================
Route::get('/unit/simpan-pinjam', [LandingPageController::class, 'showLandingPage'])->name('welcome');

Route::middleware(['auth', 'role:admin_unit,manager,manager_pusat'])->prefix('unit/simpan-pinjam')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Struk Publik (Bisa diakses tanpa login dari link WhatsApp)
    Route::get('/tabungan/struk/{transaksi}', [TabunganController::class, 'struk'])->name('tabungan.struk')->withoutMiddleware(['auth', 'role:admin_unit,manager,manager_pusat']);
    Route::get('/tabungan-sembako/struk/{transaksi}', [\App\Http\Controllers\SimpanPinjam\TabunganSembakoController::class, 'struk'])->name('tabungan-sembako.struk')->withoutMiddleware(['auth', 'role:admin_unit,manager,manager_pusat']);
    Route::get('/angsuran/struk/{angsuran}', [AngsuranController::class, 'struk'])->name('angsuran.struk')->withoutMiddleware(['auth', 'role:admin_unit,manager,manager_pusat']);

    // PDF Struk Thermal — Server-side PDF Generation (80mm)
    Route::get('/tabungan/struk/{transaksi}/pdf', [TabunganController::class, 'pdf'])->name('tabungan.struk.pdf')->withoutMiddleware(['auth', 'role:admin_unit,manager,manager_pusat']);
    Route::get('/tabungan-sembako/struk/{transaksi}/pdf', [\App\Http\Controllers\SimpanPinjam\TabunganSembakoController::class, 'pdf'])->name('tabungan-sembako.struk.pdf')->withoutMiddleware(['auth', 'role:admin_unit,manager,manager_pusat']);
    Route::get('/angsuran/struk/{angsuran}/pdf', [AngsuranController::class, 'pdf'])->name('angsuran.struk.pdf')->withoutMiddleware(['auth', 'role:admin_unit,manager,manager_pusat']);

    // Pengaturan Tabungan (admin only)
    Route::get('/pengaturan/tabungan', [PengaturanTabunganController::class, 'index'])->name('pengaturan.tabungan')->middleware('role:admin_unit');
    Route::put('/pengaturan/tabungan', [PengaturanTabunganController::class, 'update'])->name('pengaturan.tabungan.update')->middleware('role:admin_unit');
    Route::get('/tabungan/tutup-buku-masal', [PengaturanTabunganController::class, 'tutupBukuMasalIndex'])->name('tabungan.tutup-buku-masal.index')->middleware('role:admin_unit');
    Route::post('/tabungan/tutup-buku-masal', [PengaturanTabunganController::class, 'tutupBukuMasalStore'])->name('tabungan.tutup-buku-masal.store')->middleware('role:admin_unit');
    Route::post('/tabungan/mulai-buku-masal', [PengaturanTabunganController::class, 'mulaiBukuMasalStore'])->name('tabungan.mulai-buku-masal.store')->middleware('role:admin_unit');

    // Nasabah
    Route::get('/nasabah', [NasabahController::class, 'index'])->name('nasabah.index');
    Route::get('/nasabah/tambah', [NasabahController::class, 'create'])->name('nasabah.create')->middleware('role:admin_unit');
    Route::post('/nasabah', [NasabahController::class, 'store'])->name('nasabah.store')->middleware('role:admin_unit');
    Route::get('/nasabah/{nasabah}', [NasabahController::class, 'show'])->name('nasabah.show');
    Route::get('/nasabah/{nasabah}/edit', [NasabahController::class, 'edit'])->name('nasabah.edit')->middleware('role:admin_unit');
    Route::put('/nasabah/{nasabah}', [NasabahController::class, 'update'])->name('nasabah.update')->middleware('role:admin_unit');
    Route::delete('/nasabah/{nasabah}', [NasabahController::class, 'destroy'])->name('nasabah.destroy')->middleware('role:admin_unit');
    Route::get('/nasabah/{nasabah}/print', [NasabahController::class, 'print'])->name('nasabah.print');
    Route::get('/nasabah/{nasabah}/pinjaman-aktif', [NasabahController::class, 'pinjamanAktif'])->name('nasabah.pinjaman-aktif');



    // Tabungan — static routes FIRST, then dynamic {nasabah}
    Route::get('/tabungan', [TabunganController::class, 'index'])->name('tabungan.index');
    Route::get('/tabungan/{nasabah}/setor', [TabunganController::class, 'setor'])->name('tabungan.setor')->middleware('role:admin_unit');
    Route::post('/tabungan/{nasabah}/setor', [TabunganController::class, 'storeSetor'])->name('tabungan.setor.store')->middleware('role:admin_unit');
    Route::get('/tabungan/{nasabah}/tarik', [TabunganController::class, 'tarik'])->name('tabungan.tarik')->middleware('role:admin_unit');
    Route::post('/tabungan/{nasabah}/tarik', [TabunganController::class, 'storeTarik'])->name('tabungan.tarik.store')->middleware('role:admin_unit');
    Route::get('/tabungan/{nasabah}/riwayat', [TabunganController::class, 'riwayat'])->name('tabungan.riwayat');
    Route::put('/tabungan/transaksi/{transaksi}/edit', [TabunganController::class, 'updateTransaksi'])->name('tabungan.transaksi.update')->middleware('role:admin_unit');


    // Tabungan Sembako
    Route::get('/tabungan-sembako', [\App\Http\Controllers\SimpanPinjam\TabunganSembakoController::class, 'index'])->name('tabungan-sembako.index');
    Route::get('/tabungan-sembako/{nasabah}/setor', [\App\Http\Controllers\SimpanPinjam\TabunganSembakoController::class, 'setor'])->name('tabungan-sembako.setor')->middleware('role:admin_unit');
    Route::post('/tabungan-sembako/{nasabah}/setor', [\App\Http\Controllers\SimpanPinjam\TabunganSembakoController::class, 'storeSetor'])->name('tabungan-sembako.setor.store')->middleware('role:admin_unit');
    Route::get('/tabungan-sembako/{nasabah}/ambil', [\App\Http\Controllers\SimpanPinjam\TabunganSembakoController::class, 'ambil'])->name('tabungan-sembako.ambil')->middleware('role:admin_unit');
    Route::post('/tabungan-sembako/{nasabah}/ambil', [\App\Http\Controllers\SimpanPinjam\TabunganSembakoController::class, 'storeAmbil'])->name('tabungan-sembako.ambil.store')->middleware('role:admin_unit');
    Route::get('/tabungan-sembako/{nasabah}/riwayat', [\App\Http\Controllers\SimpanPinjam\TabunganSembakoController::class, 'riwayat'])->name('tabungan-sembako.riwayat');
    Route::put('/tabungan-sembako/transaksi/{transaksi}/edit', [\App\Http\Controllers\SimpanPinjam\TabunganSembakoController::class, 'updateTransaksi'])->name('tabungan-sembako.transaksi.update')->middleware('role:admin_unit');

    // Pinjaman — static routes FIRST, then dynamic {pinjaman}
    Route::get('/pinjaman', [PinjamanController::class, 'index'])->name('pinjaman.index');
    Route::get('/pinjaman/tambah', [PinjamanController::class, 'create'])->name('pinjaman.create')->middleware('role:admin_unit');
    Route::post('/pinjaman/kalkulasi', [PinjamanController::class, 'kalkulasi'])->name('pinjaman.kalkulasi');
    Route::post('/pinjaman', [PinjamanController::class, 'store'])->name('pinjaman.store')->middleware('role:admin_unit');
    Route::get('/pinjaman/{pinjaman}', [PinjamanController::class, 'show'])->name('pinjaman.show');

    // Angsuran — static routes FIRST, then dynamic {angsuran}
    Route::get('/angsuran', [AngsuranController::class, 'index'])->name('angsuran.index');
    Route::get('/angsuran/bayar', [AngsuranController::class, 'create'])->name('angsuran.create')->middleware('role:admin_unit');
    Route::post('/angsuran', [AngsuranController::class, 'store'])->name('angsuran.store')->middleware('role:admin_unit');
    Route::put('/angsuran/{angsuran}/edit', [AngsuranController::class, 'update'])->name('angsuran.update')->middleware('role:admin_unit');

    // Buku Tabungan
    Route::get('/buku-tabungan', [BukuTabunganController::class, 'index'])->name('buku-tabungan.index');
    Route::get('/buku-tabungan/print', [BukuTabunganController::class, 'print'])->name('buku-tabungan.print');
    Route::get('/buku-tabungan/pdf', [BukuTabunganController::class, 'pdf'])->name('buku-tabungan.pdf');
    Route::get('/buku-tabungan/excel', [BukuTabunganController::class, 'excel'])->name('buku-tabungan.excel');

    // Tunggakan
    Route::get('/tunggakan', [TunggakanPinjamanController::class, 'index'])->name('tunggakan.index');
    Route::get('/tunggakan/print', [TunggakanPinjamanController::class, 'print'])->name('tunggakan.print');
    Route::get('/tunggakan/pdf', [TunggakanPinjamanController::class, 'pdf'])->name('tunggakan.pdf');
    Route::get('/tunggakan/excel', [TunggakanPinjamanController::class, 'excel'])->name('tunggakan.excel');

    // Kwitansi
    Route::get('/kwitansi', [KwitansiController::class, 'index'])->name('kwitansi.index');
    Route::get('/kwitansi/buat', [KwitansiController::class, 'create'])->name('kwitansi.create')->middleware('role:admin_unit');
    Route::post('/kwitansi', [KwitansiController::class, 'store'])->name('kwitansi.store')->middleware('role:admin_unit');
    Route::get('/kwitansi/{kwitansi}/print', [KwitansiController::class, 'print'])->name('kwitansi.print');

    // Pendapatan Kotor
    Route::get('/pendapatan', [PendapatanController::class, 'index'])->name('pendapatan.index');
    Route::get('/pendapatan/pdf', [PendapatanController::class, 'pdf'])->name('pendapatan.pdf');
    Route::get('/pendapatan/excel', [PendapatanController::class, 'excel'])->name('pendapatan.excel');

    // Laporan
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/nasabah', [LaporanController::class, 'nasabah'])->name('laporan.nasabah');
    Route::get('/laporan/tabungan', [LaporanController::class, 'tabungan'])->name('laporan.tabungan');
    Route::get('/laporan/pinjaman', [LaporanController::class, 'pinjaman'])->name('laporan.pinjaman');
    Route::get('/laporan/angsuran', [LaporanController::class, 'angsuran'])->name('laporan.angsuran');
    Route::get('/laporan/kas', [LaporanController::class, 'kas'])->name('laporan.kas');

    // Laporan — Ekspor PDF
    Route::get('/laporan/nasabah/pdf',  [LaporanController::class, 'nasabahPdf'])->name('laporan.nasabah.pdf');
    Route::get('/laporan/tabungan/pdf', [LaporanController::class, 'tabunganPdf'])->name('laporan.tabungan.pdf');
    Route::get('/laporan/pinjaman/pdf', [LaporanController::class, 'pinjamanPdf'])->name('laporan.pinjaman.pdf');
    Route::get('/laporan/angsuran/pdf', [LaporanController::class, 'angsuranPdf'])->name('laporan.angsuran.pdf');
    Route::get('/laporan/kas/pdf',      [LaporanController::class, 'kasPdf'])->name('laporan.kas.pdf');

    // Laporan — Ekspor Excel
    Route::get('/laporan/nasabah/excel',  [LaporanController::class, 'nasabahExcel'])->name('laporan.nasabah.excel');
    Route::get('/laporan/tabungan/excel', [LaporanController::class, 'tabunganExcel'])->name('laporan.tabungan.excel');
    Route::get('/laporan/pinjaman/excel', [LaporanController::class, 'pinjamanExcel'])->name('laporan.pinjaman.excel');
    Route::get('/laporan/angsuran/excel', [LaporanController::class, 'angsuranExcel'])->name('laporan.angsuran.excel');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Landing Page Settings (Admin Unit & Admin)
    Route::middleware('role:admin_unit')->group(function () {
        Route::get('/admin/landing-page', [LandingPageController::class, 'editSettings'])->name('admin.landing-page.edit');
        Route::post('/admin/landing-page', [LandingPageController::class, 'updateSettings'])->name('admin.landing-page.update');
        Route::post('/admin/landing-page/upload-image', [LandingPageController::class, 'uploadImage'])->name('admin.landing-page.upload-image');

        // Galeri Unit
        Route::get('/admin/galeri', [GaleriController::class, 'index'])->name('admin.galeri.index');
        Route::post('/admin/galeri', [GaleriController::class, 'store'])->name('admin.galeri.store');
        Route::put('/admin/galeri/{galeri}', [GaleriController::class, 'update'])->name('admin.galeri.update');
        Route::delete('/admin/galeri/{galeri}', [GaleriController::class, 'destroy'])->name('admin.galeri.destroy');
    });
});

// =====================================================
// NEW UNITS — WIFI, KETAHANAN PANGAN, PERDAGANGAN BESAR
// =====================================================
Route::get('/unit/{slug}', [App\Http\Controllers\Portal\UnitLandingController::class, 'welcome'])->name('unit.welcome');
Route::get('/unit/{slug}/login', [App\Http\Controllers\Portal\UnitLandingController::class, 'login'])->name('unit.login');
Route::post('/unit/{slug}/login', [App\Http\Controllers\Portal\UnitLandingController::class, 'loginStore']);

Route::middleware(['auth'])->group(function () {
    Route::get('/unit/{slug}/dashboard', [App\Http\Controllers\Portal\UnitLandingController::class, 'dashboard'])->name('unit.dashboard');
    
    // Unit Settings
    Route::get('/unit/{slug}/settings', [App\Http\Controllers\Portal\UnitLandingController::class, 'editSettings'])->name('unit.settings.edit');
    Route::post('/unit/{slug}/settings', [App\Http\Controllers\Portal\UnitLandingController::class, 'updateSettings'])->name('unit.settings.update');
    Route::post('/unit/{slug}/settings/upload-image', [App\Http\Controllers\Portal\UnitLandingController::class, 'uploadImage'])->name('unit.settings.upload-image');
    
    // Unit Galeri
    Route::post('/unit/{slug}/galeri', [App\Http\Controllers\Portal\UnitLandingController::class, 'galeriStore'])->name('unit.galeri.store');
    Route::delete('/unit/{slug}/galeri/{id}', [App\Http\Controllers\Portal\UnitLandingController::class, 'galeriDestroy'])->name('unit.galeri.destroy');
});

require __DIR__.'/auth.php';
