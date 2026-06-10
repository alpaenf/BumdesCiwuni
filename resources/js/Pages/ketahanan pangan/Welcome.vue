<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    unit: { type: Object, required: true },
});

const foodStocks = [
    { name: 'Beras Premium Ciwuni', category: 'Pangan Utama', price: 'Rp 14.500', unit: 'kg', stock: '2.5 Ton', icon: 'grass', desc: 'Beras premium hasil panen kelompok tani Desa Ciwuni, pulen dan bebas pengawet.' },
    { name: 'Pupuk Urea Non-Subsidi', category: 'Saprotan', price: 'Rp 9.800', unit: 'kg', stock: '800 kg', icon: 'electric_bolt', desc: 'Pupuk nitrogen berkualitas tinggi untuk mempercepat pertumbuhan vegetatif tanaman.' },
    { name: 'Bibit Padi Unggul Inpari 32', category: 'Benih', price: 'Rp 75.000', unit: 'kantong (5kg)', stock: '120 kantong', icon: 'agriculture', desc: 'Benih padi bersertifikat, tahan hama WBC, potensi hasil panen melimpah.' },
    { name: 'Jagung Pipil Kering', category: 'Pangan Utama', price: 'Rp 8.000', unit: 'kg', stock: '1.2 Ton', icon: 'grain', desc: 'Jagung pakan ternak berkualitas tinggi dengan kadar air rendah (14%).' },
];

const selectedStock = ref(null);
const orderQty = ref(1);
const orderName = ref('');
const orderPhone = ref('');
const isOrderSuccess = ref(false);

const openOrder = (stock) => {
    selectedStock.value = stock;
    orderQty.value = 1;
    isOrderSuccess.value = false;
};

const closeOrder = () => {
    selectedStock.value = null;
    orderName.value = '';
    orderPhone.value = '';
};

const submitOrder = () => {
    if (orderName.value && orderPhone.value && orderQty.value > 0) {
        isOrderSuccess.value = true;
        setTimeout(() => {
            closeOrder();
        }, 3000);
    }
};
</script>

<template>
    <Head title="BUMDes Ciwuni - Unit Ketahanan Pangan" />

    <div class="min-h-screen bg-stone-50 text-stone-900 font-sans antialiased selection:bg-emerald-600 selection:text-white overflow-hidden">
        <!-- Header -->
        <header class="sticky top-0 z-40 bg-emerald-800 text-white border-b border-emerald-900 px-6 py-4 shadow-md">
            <div class="max-w-6xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-700 rounded-xl border border-emerald-600">
                        <span class="material-symbols-outlined text-amber-300 text-2xl font-bold">agriculture</span>
                    </div>
                    <div>
                        <h1 class="text-sm font-extrabold text-white leading-tight">BUMDes Ciwuni</h1>
                        <p class="text-[10px] text-emerald-250 uppercase tracking-widest font-semibold">Unit Ketahanan Pangan</p>
                    </div>
                </div>

                <nav class="hidden md:flex items-center gap-6">
                    <a href="#hero" class="text-xs font-semibold text-emerald-100 hover:text-white transition">Beranda</a>
                    <a href="#stocks" class="text-xs font-semibold text-emerald-100 hover:text-white transition">Komoditas & Saprotan</a>
                    <a href="#about" class="text-xs font-semibold text-emerald-100 hover:text-white transition">Tentang Lumbung</a>
                    <a href="#contact" class="text-xs font-semibold text-emerald-100 hover:text-white transition">Kontak</a>
                </nav>

                <div class="flex items-center gap-3">
                    <Link :href="route('portal.home')" class="text-xs font-semibold text-emerald-100 hover:text-white transition px-3 py-1.5 rounded-lg border border-emerald-700">
                        Portal Utama
                    </Link>
                    <Link :href="route('unit.login', { slug: 'ketahanan-pangan' })" class="text-xs font-bold text-emerald-900 bg-amber-300 hover:bg-amber-400 transition px-4 py-2 rounded-lg shadow-lg">
                        Kelola Unit
                    </Link>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section id="hero" class="relative py-20 md:py-28 px-6 bg-gradient-to-b from-emerald-800 to-emerald-900 text-white overflow-hidden">
            <!-- Decorative circle -->
            <div class="absolute -top-20 -left-20 w-80 h-80 bg-emerald-700/40 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-12 items-center relative z-10">
                <div class="space-y-6 text-left">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 border border-amber-400/30 text-[10px] font-bold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                        Kedaulatan Pangan & Kesejahteraan Petani
                    </span>
                    <h2 class="text-4xl md:text-5xl font-extrabold leading-tight tracking-tight">
                        Pilar Ketahanan Pangan <br />
                        <span class="text-amber-300">Desa Ciwuni Mandiri</span>
                    </h2>
                    <p class="text-emerald-100 text-sm md:text-base leading-relaxed">
                        Mengelola lumbung pangan desa terpadu, menyediakan sarana produksi pertanian (saprotan) dengan harga bersubsidi, dan menjamin serapan hasil panen padi petani dengan harga yang adil guna menjaga stabilitas pangan warga.
                    </p>
                    <div class="pt-4 flex flex-wrap gap-4">
                        <a href="#stocks" class="inline-flex items-center gap-2 px-6 py-3 bg-amber-400 hover:bg-amber-500 text-emerald-950 font-bold text-xs rounded-lg transition shadow-lg shadow-amber-500/20">
                            Cek Stok Lumbung
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                        <a href="#about" class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-800/60 hover:bg-emerald-700 text-emerald-100 font-semibold text-xs rounded-lg border border-emerald-600 transition">
                            Pelajari Program
                        </a>
                    </div>
                </div>

                <!-- Right Side: Graphic or Key Highlight Card -->
                <div class="flex justify-center">
                    <div class="w-full max-w-sm bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-6 space-y-6 shadow-2xl text-white">
                        <h4 class="text-xs font-bold text-amber-300 uppercase tracking-widest">Informasi Lumbung Desa</h4>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-b border-emerald-800 pb-2">
                                <span class="text-xs text-emerald-100">Total Cadangan Beras</span>
                                <span class="text-sm font-extrabold">12.8 Ton</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-emerald-800 pb-2">
                                <span class="text-xs text-emerald-100">Petani Binaan BUMDes</span>
                                <span class="text-sm font-extrabold">85 KK</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-emerald-800 pb-2">
                                <span class="text-xs text-emerald-100">Luas Lahan Terintegrasi</span>
                                <span class="text-sm font-extrabold">24 Hektar</span>
                            </div>
                        </div>
                        <div class="p-3.5 bg-emerald-950/40 border border-emerald-800/40 rounded-2xl text-[11px] text-emerald-200 leading-relaxed">
                            <p class="font-bold text-amber-300 mb-1">📢 Distribusi Pupuk Subsidi Periode Ini:</p>
                            Penyaluran pupuk bersubsidi tahap II sedang berjalan di Balai Dusun Karanganyar. Harap bawa Kartu Tani Anda.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-12 bg-white border-b border-stone-200">
            <div class="max-w-6xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="space-y-1">
                    <p class="text-3xl font-black text-emerald-750">85+</p>
                    <p class="text-xs text-stone-500 font-bold uppercase tracking-wider">Mitra Petani Aktif</p>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-black text-emerald-750">15 Ton</p>
                    <p class="text-xs text-stone-500 font-bold uppercase tracking-wider">Kapasitas Gudang</p>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-black text-emerald-750">Rp 12M</p>
                    <p class="text-xs text-stone-500 font-bold uppercase tracking-wider">Subsidi Tersalurkan</p>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-black text-emerald-750">3 Dusun</p>
                    <p class="text-xs text-stone-500 font-bold uppercase tracking-wider">Cakupan Wilayah</p>
                </div>
            </div>
        </section>

        <!-- Commodities & Saprotan Section -->
        <section id="stocks" class="py-20 px-6 max-w-6xl mx-auto">
            <div class="text-center space-y-3 mb-16">
                <span class="text-xs font-bold text-emerald-750 uppercase tracking-widest">KATEGORI PRODUK</span>
                <h3 class="text-3xl font-black text-stone-800">Komoditas & Saprotan Tersedia</h3>
                <p class="text-stone-550 text-xs md:text-sm max-w-md mx-auto">BUMDes menyediakan bahan pangan berkualitas dan sarana produksi pertanian (saprotan) dengan harga terjangkau bagi warga.</p>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                <div v-for="stock in foodStocks" :key="stock.name" class="bg-white border border-stone-200 hover:border-emerald-500 hover:shadow-xl rounded-2xl p-5 flex flex-col justify-between transition-all duration-300 group">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded text-[9px] font-bold uppercase tracking-wider">{{ stock.category }}</span>
                            <span class="material-symbols-outlined text-emerald-600 text-xl">{{ stock.icon }}</span>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-extrabold text-stone-800 text-sm leading-snug group-hover:text-emerald-750 transition-colors">{{ stock.name }}</h4>
                            <p class="text-xs text-stone-500">Stok: <span class="font-bold text-stone-700">{{ stock.stock }}</span></p>
                        </div>
                        <p class="text-[11px] text-stone-500 leading-relaxed">{{ stock.desc }}</p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-stone-100 flex items-center justify-between">
                        <div>
                            <span class="text-[9px] font-bold text-stone-400 uppercase">Harga</span>
                            <p class="text-sm font-black text-emerald-700">{{ stock.price }} <span class="text-[10px] text-stone-400 font-semibold">/{{ stock.unit }}</span></p>
                        </div>
                        <button @click="openOrder(stock)" class="px-3.5 py-1.5 bg-emerald-700 hover:bg-emerald-650 text-white font-bold text-[10px] rounded-lg transition shadow-md">
                            Pesan
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Lumbung Section -->
        <section id="about" class="py-20 px-6 bg-emerald-50 border-t border-b border-emerald-100">
            <div class="max-w-5xl mx-auto grid md:grid-cols-2 gap-12 items-center">
                <div class="space-y-6">
                    <span class="text-xs font-bold text-emerald-750 uppercase tracking-widest">PROFIL PROGRAM</span>
                    <h3 class="text-3xl font-black text-emerald-900">Peran Strategis Lumbung Pangan</h3>
                    <p class="text-emerald-950 text-xs md:text-sm leading-relaxed">
                        Lumbung pangan Desa Ciwuni dikelola secara kolektif untuk menimbun hasil panen raya sebagai cadangan pangan darurat saat paceklik, mengendalikan fluktuasi harga beras di pasar, serta memberikan pembiayaan bibit tunda bayar bagi petani.
                    </p>
                    
                    <div class="space-y-4 pt-4">
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white border border-emerald-150 flex items-center justify-center shrink-0 shadow-sm">
                                <span class="material-symbols-outlined text-emerald-650">currency_exchange</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-emerald-900 uppercase tracking-wider">Skema Yarnen (Bayar Setelah Panen)</h4>
                                <p class="text-[11px] text-emerald-800 mt-1">Petani mitra dapat mengambil bibit dan pupuk terlebih dahulu, lalu membayarnya menggunakan hasil panen padi.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white border border-emerald-150 flex items-center justify-center shrink-0 shadow-sm">
                                <span class="material-symbols-outlined text-emerald-650">restore</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-emerald-900 uppercase tracking-wider">Cadangan Logistik Darurat</h4>
                                <p class="text-[11px] text-emerald-800 mt-1">Menjamin ketersediaan beras bagi warga prasejahtera Desa Ciwuni secara gratis atau bersubsidi saat terjadi bencana atau paceklik.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white border border-emerald-100 rounded-3xl p-6 space-y-6 shadow-xl">
                    <h4 class="text-sm font-extrabold text-emerald-950 uppercase tracking-widest text-center">Simulasi Hasil Yarnen Petani</h4>
                    <div class="space-y-4 text-xs">
                        <p class="text-[11px] text-stone-500 leading-relaxed text-center">Model tunda bayar input pertanian untuk luas lahan 0.5 Hektar:</p>
                        <div class="flex justify-between border-b border-stone-100 pb-2">
                            <span class="text-stone-500">Kebutuhan Benih & Pupuk</span>
                            <span class="font-bold text-stone-800">Rp 1.800.000</span>
                        </div>
                        <div class="flex justify-between border-b border-stone-100 pb-2">
                            <span class="text-stone-500">Estimasi Hasil Panen</span>
                            <span class="font-bold text-stone-800">3.2 Ton Gabah</span>
                        </div>
                        <div class="flex justify-between border-b border-stone-100 pb-2">
                            <span class="text-stone-500">Nilai Jual Panen ke BUMDes</span>
                            <span class="font-bold text-emerald-700">Rp 19.200.000</span>
                        </div>
                        <div class="flex justify-between border-b border-stone-100 pb-2">
                            <span class="text-stone-500">Potongan Saprotan</span>
                            <span class="font-bold text-red-600">- Rp 1.800.000</span>
                        </div>
                        <div class="flex justify-between font-bold text-sm text-emerald-900 bg-emerald-50 p-2.5 rounded-lg">
                            <span>Pendapatan Bersih Petani</span>
                            <span>Rp 17.400.000</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-20 px-6 max-w-4xl mx-auto text-center space-y-8">
            <h3 class="text-2xl font-black text-stone-800">Hubungi Pengurus Lumbung Desa</h3>
            <p class="text-stone-550 text-xs md:text-sm max-w-md mx-auto">Ingin menjadi mitra petani binaan BUMDes, membeli gabah dalam jumlah besar, atau melakukan pengaduan penyaluran pupuk? Hubungi kami.</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="https://wa.me/628120000000" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-700 hover:bg-emerald-650 text-white font-semibold text-xs rounded-xl shadow-lg transition">
                    <span class="material-symbols-outlined text-lg">chat</span>
                    Chat WhatsApp Pengurus
                </a>
                <a href="mailto:pangan@ciwuni.desa.id" class="inline-flex items-center gap-2 px-6 py-3 bg-white hover:bg-stone-50 text-stone-700 font-semibold text-xs rounded-xl border border-stone-250 transition shadow-sm">
                    <span class="material-symbols-outlined text-lg">mail</span>
                    Email Unit Pangan
                </a>
            </div>
        </section>

        <!-- Order Dialog -->
        <div v-if="selectedStock" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/80 backdrop-blur-sm">
            <div class="w-full max-w-md bg-white border border-stone-200 rounded-3xl p-6 space-y-6 shadow-2xl relative">
                <button @click="closeOrder" class="absolute top-4 right-4 text-stone-400 hover:text-stone-700 transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
                
                <div v-if="!isOrderSuccess">
                    <h3 class="text-lg font-black text-stone-800 uppercase tracking-wider mb-2">Formulir Pemesanan</h3>
                    <p class="text-xs text-stone-500 mb-4">Anda memesan <span class="text-emerald-700 font-bold">{{ selectedStock.name }}</span> ({{ selectedStock.price }}/{{ selectedStock.unit }}).</p>
                    
                    <form @submit.prevent="submitOrder" class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-stone-500 uppercase mb-1">Jumlah Pemesanan ({{ selectedStock.unit }})</label>
                            <input v-model="orderQty" type="number" min="1" class="w-full rounded-xl border border-stone-300 bg-stone-50 px-4 py-3 text-xs text-stone-800 focus:border-emerald-500 focus:outline-none" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-stone-500 uppercase mb-1">Nama Pemesan</label>
                            <input v-model="orderName" type="text" placeholder="Masukkan nama Anda" class="w-full rounded-xl border border-stone-300 bg-stone-50 px-4 py-3 text-xs text-stone-800 focus:border-emerald-500 focus:outline-none" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-stone-500 uppercase mb-1">No. WhatsApp / HP</label>
                            <input v-model="orderPhone" type="tel" placeholder="Contoh: 08123456789" class="w-full rounded-xl border border-stone-300 bg-stone-50 px-4 py-3 text-xs text-stone-800 focus:border-emerald-500 focus:outline-none" required />
                        </div>
                        
                        <div class="bg-emerald-50 p-3.5 rounded-2xl flex items-center justify-between text-xs">
                            <span class="font-bold text-emerald-900">Total Perkiraan</span>
                            <span class="font-black text-emerald-700">Rp {{ new Intl.NumberFormat('id-ID').format(orderQty * parseInt(selectedStock.price.replace(/[^\d]/g, ''))) }}</span>
                        </div>
                        
                        <button type="submit" class="w-full py-3 bg-emerald-700 hover:bg-emerald-650 text-white font-bold text-xs rounded-xl transition">
                            Kirim Pengajuan Pemesanan
                        </button>
                    </form>
                </div>
                
                <div v-else class="text-center py-8 space-y-4">
                    <div class="w-16 h-16 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center mx-auto animate-bounce border border-emerald-200">
                        <span class="material-symbols-outlined text-3xl">check</span>
                    </div>
                    <h3 class="text-lg font-black text-stone-800">Pemesanan Berhasil Dikirim!</h3>
                    <p class="text-xs text-stone-500 max-w-xs mx-auto leading-relaxed">Terima kasih {{ orderName }}, admin unit Ketahanan Pangan akan menghubungi Anda untuk konfirmasi stok dan pengiriman barang.</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="py-10 bg-emerald-900 text-white text-center text-xs">
            <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-300 text-lg">agriculture</span>
                    <p class="font-extrabold text-white">BUMDes Ciwuni Unit Ketahanan Pangan</p>
                </div>
                <p class="text-emerald-200">© 2026 BUMDesa Dammar Wulan • Desa Ciwuni. Hak Cipta Dilindungi.</p>
            </div>
        </footer>
    </div>
</template>
