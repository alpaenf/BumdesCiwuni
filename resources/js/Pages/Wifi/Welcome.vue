<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    unit: { type: Object, required: true },
});

const activeTab = ref('home');
const selectedSpeed = ref(30);

const internetPlans = [
    { name: 'Lite Desa', speed: '20 Mbps', price: 'Rp 150.000', period: 'bulan', desc: 'Cocok untuk belajar online dan browsing ringan keluarga kecil.', features: ['Kuota Unlimited', 'Hingga 4 Perangkat', 'Gratis Instalasi', 'Support 24/7'] },
    { name: 'Keluarga Harmoni', speed: '50 Mbps', price: 'Rp 250.000', period: 'bulan', desc: 'Ideal untuk streaming HD, gaming casual, dan kerja dari rumah.', features: ['Kuota Unlimited', 'Hingga 8 Perangkat', 'Prioritas Bandwidth', 'Dukungan Teknisi Cepat'] },
    { name: 'Bisnis & UMKM', speed: '100 Mbps', price: 'Rp 450.000', period: 'bulan', desc: 'Didedikasikan untuk warung wifi, kantor desa, dan UMKM berkembang.', features: ['Kuota Unlimited', 'Hingga 20 Perangkat', 'IP Statis Opsional', 'SLA 99.9% Uptime', 'Prioritas Utama'] },
];

const selectedPlan = ref(null);
const checkoutName = ref('');
const checkoutPhone = ref('');
const checkoutAddress = ref('');
const isCheckoutSuccess = ref(false);

const openCheckout = (plan) => {
    selectedPlan.value = plan;
    isCheckoutSuccess.value = false;
};

const closeCheckout = () => {
    selectedPlan.value = null;
    checkoutName.value = '';
    checkoutPhone.value = '';
    checkoutAddress.value = '';
};

const submitCheckout = () => {
    if (checkoutName.value && checkoutPhone.value && checkoutAddress.value) {
        isCheckoutSuccess.value = true;
        setTimeout(() => {
            closeCheckout();
        }, 3000);
    }
};
</script>

<template>
    <Head title="BUMDes Ciwuni - Unit Wifi" />

    <div class="min-h-screen bg-slate-950 text-slate-100 font-sans antialiased selection:bg-indigo-500 selection:text-white overflow-hidden">
        <!-- Floating Ambient Gradients -->
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] bg-indigo-500/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] bg-purple-500/10 rounded-full blur-[120px] pointer-events-none"></div>

        <!-- Header -->
        <header class="sticky top-0 z-40 bg-slate-950/80 backdrop-blur-md border-b border-slate-900 px-6 py-4">
            <div class="max-w-6xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-600/15 rounded-xl border border-indigo-500/30">
                        <span class="material-symbols-outlined text-indigo-400 text-2xl font-bold">wifi</span>
                    </div>
                    <div>
                        <h1 class="text-sm font-extrabold text-white leading-tight">BUMDes Ciwuni</h1>
                        <p class="text-[10px] text-indigo-400 uppercase tracking-widest font-semibold">Unit Wifi Desa</p>
                    </div>
                </div>

                <nav class="hidden md:flex items-center gap-6">
                    <a href="#hero" class="text-xs font-semibold text-slate-350 hover:text-white transition">Beranda</a>
                    <a href="#plans" class="text-xs font-semibold text-slate-350 hover:text-white transition">Paket Internet</a>
                    <a href="#features" class="text-xs font-semibold text-slate-350 hover:text-white transition">Keunggulan</a>
                    <a href="#contact" class="text-xs font-semibold text-slate-350 hover:text-white transition">Kontak</a>
                </nav>

                <div class="flex items-center gap-3">
                    <Link :href="route('portal.home')" class="text-xs font-semibold text-slate-400 hover:text-white transition px-3 py-1.5 rounded-lg border border-slate-800">
                        Portal Utama
                    </Link>
                    <Link :href="route('unit.login', { slug: 'wifi' })" class="text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition px-4 py-2 rounded-lg shadow-lg shadow-indigo-600/20">
                        Kelola Unit
                    </Link>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section id="hero" class="relative py-20 md:py-28 px-6 border-b border-slate-900">
            <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-12 items-center">
                <div class="space-y-6 text-left">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 text-[10px] font-bold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                        Koneksi Tanpa Batas Desa Ciwuni
                    </span>
                    <h2 class="text-4xl md:text-5xl font-extrabold text-white leading-tight tracking-tight">
                        Internet Super Cepat, <br />
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400">Membangun Ekonomi Digital Desa</span>
                    </h2>
                    <p class="text-slate-400 text-sm md:text-base leading-relaxed">
                        Nikmati koneksi internet fiber optic unlimited dengan harga ramah kantong. Diinisiasi oleh BUMDes Ciwuni untuk mendukung digitalisasi pendidikan, administrasi warga, dan kemajuan UMKM lokal.
                    </p>
                    <div class="pt-4 flex flex-wrap gap-4">
                        <a href="#plans" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs rounded-lg transition shadow-lg shadow-indigo-600/30">
                            Pilih Paket Sekarang
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                        <a href="#coverage" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 hover:bg-slate-800 text-slate-300 font-semibold text-xs rounded-lg border border-slate-800 transition">
                            Cek Jangkauan
                        </a>
                    </div>
                </div>

                <!-- Interactive Card: Simulated Speedometer -->
                <div class="flex items-center justify-center relative">
                    <div class="absolute inset-0 bg-indigo-600/10 rounded-full blur-[60px] pointer-events-none"></div>
                    <div class="w-full max-w-sm bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-3xl p-6 space-y-6 shadow-2xl relative">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Simulasi Kecepatan</span>
                            <span class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                        </div>
                        
                        <div class="flex flex-col items-center py-6">
                            <div class="relative flex items-center justify-center">
                                <svg class="w-48 h-48 transform -rotate-90">
                                    <circle cx="96" cy="96" r="80" stroke="#1e293b" stroke-width="12" fill="transparent" />
                                    <circle cx="96" cy="96" r="80" stroke="#4f46e5" stroke-width="12" fill="transparent" 
                                            :stroke-dasharray="2 * Math.PI * 80" 
                                            :stroke-dashoffset="2 * Math.PI * 80 * (1 - (selectedSpeed / 100))" 
                                            class="transition-all duration-500 ease-out" />
                                </svg>
                                <div class="absolute flex flex-col items-center justify-center">
                                    <span class="text-4xl font-black text-white tracking-tight">{{ selectedSpeed }}</span>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-widest font-semibold mt-1">Mbps</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="text-xs font-bold text-slate-450 block">Geser Kecepatan Paket:</label>
                            <input type="range" min="10" max="100" v-model="selectedSpeed" class="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-indigo-500" />
                            <div class="flex justify-between text-[10px] text-slate-500 font-semibold uppercase">
                                <span>10 Mbps</span>
                                <span>50 Mbps</span>
                                <span>100 Mbps</span>
                            </div>
                        </div>

                        <div class="bg-indigo-950/40 border border-indigo-900/30 rounded-xl p-3.5 flex items-center justify-between">
                            <div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase">Estimasi Biaya</p>
                                <p class="text-sm font-extrabold text-white mt-0.5">Rp {{ selectedSpeed * 5000 + 50000 }} / bln</p>
                            </div>
                            <a href="#plans" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-[10px] rounded-lg transition">
                                Pesan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-12 bg-slate-950/40 border-b border-slate-900">
            <div class="max-w-6xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="space-y-1">
                    <p class="text-3xl font-black text-white">450+</p>
                    <p class="text-xs text-indigo-400 font-bold uppercase tracking-wider">Rumah Terkoneksi</p>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-black text-white">99.8%</p>
                    <p class="text-xs text-indigo-400 font-bold uppercase tracking-wider">Uptime Rata-Rata</p>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-black text-white">24 / 7</p>
                    <p class="text-xs text-indigo-400 font-bold uppercase tracking-wider">Dukungan Teknis</p>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-black text-white">10 Gbps</p>
                    <p class="text-xs text-indigo-400 font-bold uppercase tracking-wider">Total Bandwidth Desa</p>
                </div>
            </div>
        </section>

        <!-- Internet Plans Section -->
        <section id="plans" class="py-20 px-6 max-w-6xl mx-auto">
            <div class="text-center space-y-3 mb-16">
                <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest">PILIHAN TERBAIK</span>
                <h3 class="text-3xl font-black text-white">Pilih Paket Internet Anda</h3>
                <p class="text-slate-400 text-xs md:text-sm max-w-md mx-auto">Semua paket bersifat flat-rate, tanpa FUP, dan dikelola langsung oleh tim teknis desa yang andal.</p>
            </div>

            <div class="grid gap-8 md:grid-cols-3">
                <div v-for="plan in internetPlans" :key="plan.name" class="bg-slate-900/40 border border-slate-800 hover:border-indigo-500/50 hover:shadow-2xl hover:shadow-indigo-600/5 rounded-2xl p-6 flex flex-col justify-between transition-all duration-300 relative group">
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <h4 class="text-base font-extrabold text-indigo-300 uppercase tracking-wider">{{ plan.name }}</h4>
                            <div class="flex items-baseline gap-1">
                                <span class="text-3xl font-black text-white">{{ plan.price }}</span>
                                <span class="text-xs text-slate-500">/{{ plan.period }}</span>
                            </div>
                            <span class="inline-block px-2.5 py-0.5 bg-indigo-600/10 text-indigo-400 border border-indigo-600/20 text-[10px] font-bold rounded">Kecepatan: {{ plan.speed }}</span>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">{{ plan.desc }}</p>
                        
                        <ul class="space-y-3 border-t border-slate-800 pt-6">
                            <li v-for="feat in plan.features" :key="feat" class="flex items-center gap-2 text-xs text-slate-350">
                                <span class="material-symbols-outlined text-indigo-400 text-sm">check_circle</span>
                                {{ feat }}
                            </li>
                        </ul>
                    </div>
                    <div class="mt-8">
                        <button @click="openCheckout(plan)" class="w-full py-3 bg-slate-800 hover:bg-indigo-600 group-hover:bg-indigo-600 text-white font-bold text-xs rounded-xl transition shadow-lg">
                            Daftar Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 px-6 bg-slate-900/20 border-t border-b border-slate-900">
            <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-12 items-center">
                <div class="space-y-6">
                    <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest">KEUNGGULAN KAMI</span>
                    <h3 class="text-3xl font-black text-white">Mengapa Memilih Wifi BUMDes Ciwuni?</h3>
                    <p class="text-slate-450 text-xs md:text-sm leading-relaxed">
                        Kami tidak sekadar menjual paket internet, melainkan membangun infrastruktur digital berkelanjutan untuk kesejahteraan warga Desa Ciwuni. Keuntungan yang didapat disalurkan kembali untuk pembangunan desa.
                    </p>
                    
                    <div class="space-y-4 pt-4">
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600/10 border border-indigo-650/20 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-indigo-400">speed</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white uppercase tracking-wider">Koneksi Fiber Optic Murni</h4>
                                <p class="text-[11px] text-slate-400 mt-1">Menggunakan kabel serat optik langsung ke dalam rumah Anda untuk kestabilan prima di segala cuaca.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600/10 border border-indigo-650/20 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-indigo-400">currency_exchange</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white uppercase tracking-wider">Harga Flat-rate & Tanpa FUP</h4>
                                <p class="text-[11px] text-slate-400 mt-1">Biaya bulanan tetap, kuota 100% unlimited sepuasnya tanpa ada penurunan kecepatan di tengah bulan.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600/10 border border-indigo-650/20 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-indigo-400">diversity_3</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white uppercase tracking-wider">Mendukung Pembangunan Desa</h4>
                                <p class="text-[11px] text-slate-400 mt-1">Sebagian pendapatan unit dialokasikan ke Pendapatan Asli Desa (PAD) untuk membiayai fasilitas publik desa.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-indigo-900/20 to-purple-900/10 border border-slate-800 rounded-3xl p-8 space-y-6">
                    <h4 class="text-sm font-extrabold text-white uppercase tracking-widest text-center">Jadwal Operasional Pelayanan</h4>
                    <div class="space-y-4 text-xs">
                        <div class="flex justify-between border-b border-slate-800 pb-2">
                            <span class="text-slate-400">Pendaftaran & Pemasangan</span>
                            <span class="font-semibold text-white">Senin - Sabtu (08.00 - 16.00)</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-800 pb-2">
                            <span class="text-slate-400">Pengaduan Gangguan</span>
                            <span class="font-semibold text-white">Setiap Hari (24 Jam Via Whatsapp)</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-800 pb-2">
                            <span class="text-slate-400">Kantor Layanan BUMDes</span>
                            <span class="font-semibold text-white">Senin - Jumat (08.00 - 15.00)</span>
                        </div>
                    </div>
                    <div class="bg-indigo-600/10 border border-indigo-500/20 rounded-2xl p-4 text-center">
                        <p class="text-[11px] text-indigo-300 font-bold leading-relaxed">
                            Butuh Pasang Cepat? Hubungi CS BUMDes Wifi via WhatsApp di bawah.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-20 px-6 max-w-4xl mx-auto text-center space-y-8">
            <h3 class="text-2xl font-black text-white">Hubungi Kami & Ajukan Pemasangan</h3>
            <p class="text-slate-400 text-xs md:text-sm max-w-md mx-auto">Untuk konsultasi gratis lokasi, informasi promo pemasangan baru, dan registrasi layanan, hubungi petugas administrasi unit Wifi kami.</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="https://wa.me/628120000000" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs rounded-xl shadow-lg transition">
                    <span class="material-symbols-outlined text-lg">chat</span>
                    Chat WhatsApp CS Wifi
                </a>
                <a href="mailto:wifi@ciwuni.desa.id" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 hover:bg-slate-800 text-slate-350 font-semibold text-xs rounded-xl border border-slate-800 transition">
                    <span class="material-symbols-outlined text-lg">mail</span>
                    Email Admin Unit
                </a>
            </div>
        </section>

        <!-- Checkout Dialog -->
        <div v-if="selectedPlan" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-6 shadow-2xl relative">
                <button @click="closeCheckout" class="absolute top-4 right-4 text-slate-400 hover:text-white transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
                
                <div v-if="!isCheckoutSuccess">
                    <h3 class="text-lg font-black text-white uppercase tracking-wider mb-2">Formulir Pendaftaran</h3>
                    <p class="text-xs text-slate-450 mb-4">Anda memilih paket <span class="text-indigo-400 font-bold">{{ selectedPlan.name }}</span> ({{ selectedPlan.speed }} - {{ selectedPlan.price }}/bln).</p>
                    
                    <form @submit.prevent="submitCheckout" class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nama Lengkap</label>
                            <input v-model="checkoutName" type="text" placeholder="Masukkan nama lengkap" class="w-full rounded-xl border border-slate-850 bg-slate-950 px-4 py-3 text-xs text-slate-200 focus:border-indigo-500 focus:outline-none" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">No. WhatsApp / HP</label>
                            <input v-model="checkoutPhone" type="tel" placeholder="Contoh: 08123456789" class="w-full rounded-xl border border-slate-850 bg-slate-950 px-4 py-3 text-xs text-slate-200 focus:border-indigo-500 focus:outline-none" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Alamat Pemasangan</label>
                            <textarea v-model="checkoutAddress" rows="3" placeholder="Masukkan alamat lengkap RT/RW/Dusun" class="w-full rounded-xl border border-slate-850 bg-slate-950 px-4 py-3 text-xs text-slate-200 focus:border-indigo-500 focus:outline-none" required></textarea>
                        </div>
                        
                        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl transition">
                            Kirim Pengajuan Pasang
                        </button>
                    </form>
                </div>
                
                <div v-else class="text-center py-8 space-y-4">
                    <div class="w-16 h-16 bg-emerald-500/10 border border-emerald-500/30 text-emerald-450 rounded-full flex items-center justify-center mx-auto animate-bounce">
                        <span class="material-symbols-outlined text-3xl">check</span>
                    </div>
                    <h3 class="text-lg font-black text-white">Pengajuan Berhasil Dikirim!</h3>
                    <p class="text-xs text-slate-400 max-w-xs mx-auto leading-relaxed">Terima kasih {{ checkoutName }}, tim teknisi Wifi BUMDes Ciwuni akan menghubungi Anda dalam waktu 1x24 jam untuk melakukan survei lokasi.</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="py-10 bg-slate-950 border-t border-slate-900 text-center text-xs text-slate-500">
            <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-400 text-lg">wifi</span>
                    <p class="font-extrabold text-white">BUMDes Ciwuni Unit Wifi</p>
                </div>
                <p>© 2026 BUMDesa Dammar Wulan • Desa Ciwuni. Hak Cipta Dilindungi.</p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Range slider custom styling */
input[type="range"]::-webkit-slider-thumb {
    box-shadow: 0 0 10px rgba(79, 70, 229, 0.5);
}
</style>
