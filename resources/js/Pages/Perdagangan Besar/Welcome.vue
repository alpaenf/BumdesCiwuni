<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    unit: { type: Object, required: true },
});

const wholesaleItems = [
    { id: 1, name: 'Kopi Robusta Ciwuni Grade A', category: 'Hasil Bumi', price: 'Rp 65.000', minOrder: '100 kg', pack: 'Goni 50kg', availability: 'Tersedia', desc: 'Biji kopi robusta pilihan dari dataran tinggi Ciwuni, disortir ketat, kadar air 12%.' },
    { id: 2, name: 'Cengkeh Kering Kualitas Ekspor', category: 'Rempah', price: 'Rp 125.000', minOrder: '50 kg', pack: 'Peti Kayu', availability: 'Tersedia', desc: 'Cengkeh harum hasil pengeringan matahari alami tanpa pewarna buatan.' },
    { id: 3, name: 'Minyak Kelapa Murni (VCO)', category: 'Olahan', price: 'Rp 35.000', minOrder: '200 Liter', pack: 'Jerigen / Drum', availability: 'Pre-order', desc: 'Virgin Coconut Oil kualitas premium diekstrak dengan metode cold-pressed.' },
    { id: 4, name: 'Kopra Putih Reguler', category: 'Hasil Bumi', price: 'Rp 11.500', minOrder: '1 Ton', pack: 'Karung Jumbo', availability: 'Tersedia', desc: 'Kopra putih berkualitas tinggi hasil oven untuk bahan baku minyak kelapa.' },
];

const selectedItem = ref(null);
const partnerName = ref('');
const partnerCompany = ref('');
const partnerPhone = ref('');
const enquiryMessage = ref('');
const isEnquirySuccess = ref(false);

const openEnquiry = (item) => {
    selectedItem.value = item;
    isEnquirySuccess.value = false;
};

const closeEnquiry = () => {
    selectedItem.value = null;
    partnerName.value = '';
    partnerCompany.value = '';
    partnerPhone.value = '';
    enquiryMessage.value = '';
};

const submitEnquiry = () => {
    if (partnerName.value && partnerPhone.value && partnerCompany.value) {
        isEnquirySuccess.value = true;
        setTimeout(() => {
            closeEnquiry();
        }, 3000);
    }
};
</script>

<template>
    <Head title="BUMDes Ciwuni - Unit Perdagangan Besar" />

    <div class="min-h-screen bg-slate-900 text-slate-105 font-sans antialiased selection:bg-amber-500 selection:text-slate-950 overflow-hidden">
        <!-- Background Grid Pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#1e293b_1px,transparent_1px),linear-gradient(to_bottom,#1e293b_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)] pointer-events-none opacity-40"></div>

        <!-- Header -->
        <header class="sticky top-0 z-40 bg-slate-900/90 backdrop-blur-md border-b border-slate-800 px-6 py-4">
            <div class="max-w-6xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-amber-500/10 rounded-xl border border-amber-500/35">
                        <span class="material-symbols-outlined text-amber-450 text-2xl font-bold">local_shipping</span>
                    </div>
                    <div>
                        <h1 class="text-sm font-extrabold text-white leading-tight">BUMDes Ciwuni</h1>
                        <p class="text-[10px] text-amber-450 uppercase tracking-widest font-semibold">Unit Perdagangan Besar</p>
                    </div>
                </div>

                <nav class="hidden md:flex items-center gap-6">
                    <a href="#hero" class="text-xs font-semibold text-slate-300 hover:text-white transition">Beranda</a>
                    <a href="#catalog" class="text-xs font-semibold text-slate-300 hover:text-white transition">Katalog Grosir</a>
                    <a href="#logistics" class="text-xs font-semibold text-slate-300 hover:text-white transition">Layanan Kemitraan</a>
                    <a href="#contact" class="text-xs font-semibold text-slate-300 hover:text-white transition">Hubungi</a>
                </nav>

                <div class="flex items-center gap-3">
                    <Link :href="route('portal.home')" class="text-xs font-semibold text-slate-400 hover:text-white transition px-3 py-1.5 rounded-lg border border-slate-800">
                        Portal Utama
                    </Link>
                    <Link :href="route('unit.login', { slug: 'perdagangan-besar' })" class="text-xs font-bold text-slate-950 bg-amber-500 hover:bg-amber-400 transition px-4 py-2 rounded-lg shadow-lg shadow-amber-500/15">
                        Kelola Unit
                    </Link>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section id="hero" class="relative py-20 md:py-28 px-6 border-b border-slate-850">
            <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-12 items-center">
                <div class="space-y-6 text-left relative z-10">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 text-amber-300 border border-amber-500/20 text-[10px] font-bold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-450 animate-pulse"></span>
                        Hub Logistik & Distributor Komoditas Desa
                    </span>
                    <h2 class="text-4xl md:text-5xl font-extrabold text-white leading-tight tracking-tight">
                        Penyedia Hasil Bumi <br />
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-amber-200">Skala Besar & Ekspor</span>
                    </h2>
                    <p class="text-slate-400 text-sm md:text-base leading-relaxed">
                        Kami menjalin kemitraan dengan industri manufaktur, eksportir, dan jaringan retail nasional untuk mendistribusikan komoditas pertanian unggulan hasil olahan petani Desa Ciwuni secara efisien dan konsisten.
                    </p>
                    <div class="pt-4 flex flex-wrap gap-4">
                        <a href="#catalog" class="inline-flex items-center gap-2 px-6 py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-lg transition shadow-lg shadow-amber-500/15">
                            Lihat Katalog Komoditas
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                        <a href="#logistics" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-800 hover:bg-slate-755 text-slate-200 font-semibold text-xs rounded-lg border border-slate-700 transition">
                            Layanan Logistik
                        </a>
                    </div>
                </div>

                <!-- Right Side: Logistics Visual Mockup -->
                <div class="flex justify-center relative">
                    <div class="absolute -top-10 -right-10 w-72 h-72 bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="w-full max-w-sm bg-slate-950/70 border border-slate-850 rounded-3xl p-6 space-y-6 shadow-2xl relative">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Layanan Distribusi Aktif</span>
                            <span class="px-2 py-0.5 bg-amber-500/10 text-amber-400 border border-amber-500/25 rounded text-[9px] font-bold">ON TIME</span>
                        </div>
                        
                        <!-- Track Container -->
                        <div class="space-y-4">
                            <div class="relative pl-6 border-l-2 border-amber-500/30 space-y-4">
                                <div class="relative">
                                    <div class="absolute -left-[31px] top-0.5 w-4 h-4 bg-amber-500 rounded-full border-4 border-slate-950 shadow-md"></div>
                                    <h5 class="text-xs font-bold text-white">Hub Cargo BUMDes Ciwuni</h5>
                                    <p class="text-[10px] text-slate-500 mt-0.5">Muatan: 5.2 Ton Kopi Robusta (Dalam Transit)</p>
                                </div>
                                <div class="relative">
                                    <div class="absolute -left-[31px] top-0.5 w-4 h-4 bg-amber-500/40 rounded-full border-4 border-slate-950 shadow-md"></div>
                                    <h5 class="text-xs font-bold text-slate-350">Gudang Penyortiran</h5>
                                    <p class="text-[10px] text-slate-500 mt-0.5">Quality control kelembapan & kemasan karung</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex items-center justify-between text-xs">
                            <div>
                                <p class="text-[9px] text-slate-500 font-bold uppercase">Kemitraan Bulan Ini</p>
                                <p class="text-sm font-extrabold text-white mt-0.5">18 Korporasi</p>
                            </div>
                            <span class="material-symbols-outlined text-amber-450 text-2xl">handshake</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-12 bg-slate-950/40 border-b border-slate-850">
            <div class="max-w-6xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="space-y-1">
                    <p class="text-3xl font-black text-amber-450">142 Ton</p>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Komoditas Terjual</p>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-black text-amber-450">98.6%</p>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Tingkat Kepuasan Mitra</p>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-black text-amber-450">24 Jam</p>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Sistem Pelacakan Kargo</p>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-black text-amber-450">5 Wilayah</p>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Hub Distribusi Utama</p>
                </div>
            </div>
        </section>

        <!-- Catalog Section -->
        <section id="catalog" class="py-20 px-6 max-w-6xl mx-auto">
            <div class="text-center space-y-3 mb-16">
                <span class="text-xs font-bold text-amber-400 uppercase tracking-widest">KATALOG PRODUK</span>
                <h3 class="text-3xl font-black text-white">Komoditas Skala Grosir</h3>
                <p class="text-slate-400 text-xs md:text-sm max-w-md mx-auto">Kami melayani pengiriman partai besar dengan syarat minimum order terjangkau untuk menjaga harga jual grosir tetap kompetitif.</p>
            </div>

            <div class="grid gap-8 md:grid-cols-2">
                <div v-for="item in wholesaleItems" :key="item.id" class="bg-slate-950/50 border border-slate-800 hover:border-amber-500/50 rounded-3xl p-6 flex flex-col sm:flex-row gap-6 justify-between transition-all duration-300 group">
                    <div class="space-y-4 flex-1">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-0.5 bg-amber-500/10 text-amber-400 border border-amber-500/25 rounded text-[9px] font-bold uppercase tracking-wider">{{ item.category }}</span>
                            <span v-if="item.availability === 'Tersedia'" class="text-[10px] text-emerald-450 font-bold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Ready Stock
                            </span>
                            <span v-else class="text-[10px] text-amber-500 font-bold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Indent / PO
                            </span>
                        </div>
                        
                        <div class="space-y-1">
                            <h4 class="font-extrabold text-white text-sm leading-snug group-hover:text-amber-400 transition-colors">{{ item.name }}</h4>
                            <p class="text-xs text-slate-500">Kemasan: <span class="font-bold text-slate-350">{{ item.pack }}</span></p>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">{{ item.desc }}</p>
                    </div>
                    
                    <div class="sm:w-48 sm:border-l sm:border-slate-800 sm:pl-6 flex flex-col justify-between pt-6 sm:pt-0">
                        <div class="space-y-2">
                            <div>
                                <span class="text-[9px] font-bold text-slate-500 uppercase">Min. Order</span>
                                <p class="text-xs font-bold text-slate-200">{{ item.minOrder }}</p>
                            </div>
                            <div>
                                <span class="text-[9px] font-bold text-slate-500 uppercase">Harga Acuan</span>
                                <p class="text-base font-black text-amber-450">{{ item.price }} <span class="text-[10px] text-slate-500 font-semibold">/kg</span></p>
                            </div>
                        </div>
                        <button @click="openEnquiry(item)" class="w-full mt-4 py-2.5 bg-slate-900 border border-slate-800 hover:bg-amber-500 hover:border-amber-400 hover:text-slate-950 text-slate-300 font-bold text-xs rounded-xl transition">
                            Ajukan Pemesanan
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Kemitraan & Layanan Section -->
        <section id="logistics" class="py-20 px-6 bg-slate-950/20 border-t border-b border-slate-850">
            <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-12 items-center">
                <div class="space-y-6">
                    <span class="text-xs font-bold text-amber-400 uppercase tracking-widest">ALUR LOGISTIK</span>
                    <h3 class="text-3xl font-black text-white">Sistem Logistik BUMDes</h3>
                    <p class="text-slate-450 text-xs md:text-sm leading-relaxed">
                        Kami menjamin standar mutu kualitas komoditas melalui jaminan uji laboratorium kelayakan, proses packing profesional antimikroba, dan pengiriman tepat waktu menggunakan armada truk BUMDes Ciwuni.
                    </p>
                    
                    <div class="space-y-4 pt-4">
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-amber-450">lab_research</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white uppercase tracking-wider">Quality Assurance Terpadu</h4>
                                <p class="text-[11px] text-slate-400 mt-1">Setiap biji kopi dan rempah disortir menggunakan mesin pemisah otomatis dan diuji kadar airnya untuk menjamin kesegaran maksimal.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-amber-450">local_shipping</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white uppercase tracking-wider">Armada & Pelacakan Kargo</h4>
                                <p class="text-[11px] text-slate-400 mt-1">Pengiriman cargo didukung oleh armada truk boks berpendingin, serta dilengkapi GPS real-time untuk pemantauan posisi pengiriman.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800 rounded-3xl p-8 space-y-6">
                    <h4 class="text-sm font-extrabold text-white uppercase tracking-widest text-center">Standar Pengiriman Cargo</h4>
                    <div class="space-y-4 text-xs">
                        <div class="flex justify-between border-b border-slate-800 pb-2">
                            <span class="text-slate-400">Jangkauan Distribusi</span>
                            <span class="font-semibold text-white">Nasional (Jawa & Bali)</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-800 pb-2">
                            <span class="text-slate-400">Waktu Kirim Reguler</span>
                            <span class="font-semibold text-white">1 - 3 Hari Kerja</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-800 pb-2">
                            <span class="text-slate-400">Asuransi Kehilangan</span>
                            <span class="font-semibold text-white">Ditanggung Penuh (BUMDes Cover)</span>
                        </div>
                    </div>
                    <div class="bg-amber-500/5 border border-amber-500/20 rounded-2xl p-4 text-center">
                        <p class="text-[11px] text-amber-300 font-bold leading-relaxed">
                            Butuh negosiasi kontrak supply tahunan? Unduh Company Profile & Hubungi Direktur Perdagangan BUMDes.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-20 px-6 max-w-4xl mx-auto text-center space-y-8">
            <h3 class="text-2xl font-black text-white">Mulai Kerja Sama Distribusi</h3>
            <p class="text-slate-400 text-xs md:text-sm max-w-md mx-auto">Kami menyambut baik kerja sama jangka panjang dengan distributor retail, pabrik olahan makanan, dan eksportir komoditas.</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="https://wa.me/628120000000" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition">
                    <span class="material-symbols-outlined text-lg">chat</span>
                    Hubungi WhatsApp Direktur Dagang
                </a>
                <a href="mailto:perdagangan@ciwuni.desa.id" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-950 hover:bg-slate-850 text-slate-350 font-semibold text-xs rounded-xl border border-slate-800 transition">
                    <span class="material-symbols-outlined text-lg">mail</span>
                    Email Penawaran Harga
                </a>
            </div>
        </section>

        <!-- Enquiry Dialog -->
        <div v-if="selectedItem" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-6 shadow-2xl relative">
                <button @click="closeEnquiry" class="absolute top-4 right-4 text-slate-400 hover:text-white transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
                
                <div v-if="!isEnquirySuccess">
                    <h3 class="text-lg font-black text-white uppercase tracking-wider mb-2">Permintaan Penawaran</h3>
                    <p class="text-xs text-slate-450 mb-4">Ajukan negosiasi harga & spesifikasi untuk komoditas <span class="text-amber-400 font-bold">{{ selectedItem.name }}</span>.</p>
                    
                    <form @submit.prevent="submitEnquiry" class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nama Kontak</label>
                                <input v-model="partnerName" type="text" placeholder="Nama Anda" class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-xs text-slate-200 focus:border-amber-500 focus:outline-none" required />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nama Perusahaan</label>
                                <input v-model="partnerCompany" type="text" placeholder="CV / PT / Perorangan" class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-xs text-slate-200 focus:border-amber-500 focus:outline-none" required />
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">No. WhatsApp / HP</label>
                            <input v-model="partnerPhone" type="tel" placeholder="08..." class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-xs text-slate-200 focus:border-amber-500 focus:outline-none" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Detail Kebutuhan (Volume & Tanggal Kirim)</label>
                            <textarea v-model="enquiryMessage" rows="3" placeholder="Contoh: Butuh 2 Ton Kopi Robusta dikirim akhir bulan ini ke gudang kami di Surabaya." class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-xs text-slate-200 focus:border-amber-500 focus:outline-none" required></textarea>
                        </div>
                        
                        <button type="submit" class="w-full py-3 bg-amber-500 hover:bg-amber-400 text-slate-955 font-bold text-xs rounded-xl transition">
                            Kirim Permintaan
                        </button>
                    </form>
                </div>
                
                <div v-else class="text-center py-8 space-y-4">
                    <div class="w-16 h-16 bg-amber-500/10 border border-amber-500/30 text-amber-450 rounded-full flex items-center justify-center mx-auto animate-bounce">
                        <span class="material-symbols-outlined text-3xl">check</span>
                    </div>
                    <h3 class="text-lg font-black text-white">Penawaran Berhasil Dikirim!</h3>
                    <p class="text-xs text-slate-400 max-w-xs mx-auto leading-relaxed">Terima kasih {{ partnerName }} dari {{ partnerCompany }}. Admin unit Perdagangan Besar BUMDes Ciwuni akan mengirimkan draft surat penawaran resmi (Quotation) dalam waktu 2 jam kerja.</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="py-10 bg-slate-950 border-t border-slate-850 text-center text-xs text-slate-500">
            <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-450 text-lg">local_shipping</span>
                    <p class="font-extrabold text-white">BUMDes Ciwuni Unit Perdagangan Besar</p>
                </div>
                <p>© 2026 BUMDesa Dammar Wulan • Desa Ciwuni. Hak Cipta Dilindungi.</p>
            </div>
        </footer>
    </div>
</template>
