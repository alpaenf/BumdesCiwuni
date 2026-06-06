<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    settings: {
        type: Object,
        required: true,
    },
});

const activeFaqIndex = ref(null);
const isMobileMenuOpen = ref(false);

const toggleFaq = (index) => {
    if (activeFaqIndex.value === index) {
        activeFaqIndex.value = null;
    } else {
        activeFaqIndex.value = index;
    }
};

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-show');
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    const animatedElements = document.querySelectorAll('.scroll-animate');
    animatedElements.forEach((el) => observer.observe(el));
});
</script>

<template>
    <Head title="Unit Simpan Pinjam" />

    <div class="min-h-screen bg-[#ffffff] text-[#181d18] font-sans antialiased selection:bg-emerald-600 selection:text-white">
        <!-- Header / Navbar -->
        <header :class="[
            'fixed top-4 left-1/2 -translate-x-1/2 z-50 w-[calc(100%-2rem)] max-w-5xl bg-white/90 backdrop-blur-md border border-[#bfc9bd]/70 shadow-lg transition-all duration-300',
            isMobileMenuOpen ? 'rounded-2xl py-4 px-6' : 'rounded-full py-2 px-6'
        ]">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <img :src="settings.custom_logo || '/logo.png'" alt="Logo" class="w-9 h-9 object-contain" />
                    <div>
                        <h1 class="text-xs font-extrabold text-emerald-800 leading-tight">Simpan Pinjam</h1>
                        <p class="text-[9px] text-[#5c5f61] tracking-wider font-semibold uppercase leading-none">BUMDesa Dammar Wulan</p>
                    </div>
                </div>

                <!-- Navigation Desktop -->
                <nav class="hidden md:flex items-center gap-5">
                    <a href="#tentang" class="text-xs font-bold text-[#404940] hover:text-emerald-700 transition">Tentang</a>
                    <a href="#layanan" class="text-xs font-bold text-[#404940] hover:text-emerald-700 transition">Layanan</a>
                    <a href="#struktur" class="text-xs font-bold text-[#404940] hover:text-emerald-700 transition">Struktur</a>
                    <a href="#faq" class="text-xs font-bold text-[#404940] hover:text-emerald-700 transition">FAQ</a>
                    <a href="#kontak" class="text-xs font-bold text-[#404940] hover:text-emerald-700 transition">Kontak</a>
                    
                    <div class="border-l border-[#bfc9bd] pl-4 flex items-center gap-2">
                        <!-- Back to Main Portal -->
                        <Link :href="route('portal.home')" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition mr-2 flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-sm">home</span>
                            Portal BUMDes
                        </Link>
                        
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 text-white font-semibold text-xs rounded-lg hover:bg-emerald-700 transition shadow-sm">
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link :href="route('login')" class="inline-flex items-center justify-center px-4 py-2 border border-emerald-600 text-emerald-700 font-semibold text-xs rounded-lg hover:bg-emerald-50 transition">
                                Masuk
                            </Link>
                        </template>
                    </div>
                </nav>

                <!-- Hamburger Button (Mobile) -->
                <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="md:hidden w-8 h-8 flex items-center justify-center text-[#404940] hover:text-emerald-700 transition focus:outline-none">
                    <span class="material-symbols-outlined text-[22px]">
                        {{ isMobileMenuOpen ? 'close' : 'menu' }}
                    </span>
                </button>
            </div>

            <!-- Mobile Navigation Menu -->
            <transition name="mobile-menu">
                <div v-if="isMobileMenuOpen" class="md:hidden mt-3 pt-3 border-t border-[#bfc9bd]/30 flex flex-col gap-2.5">
                    <a href="#tentang" @click="isMobileMenuOpen = false" class="text-xs font-bold text-[#404940] hover:text-emerald-700 py-1 transition">Tentang</a>
                    <a href="#layanan" @click="isMobileMenuOpen = false" class="text-xs font-bold text-[#404940] hover:text-emerald-700 py-1 transition">Layanan</a>
                    <a href="#struktur" @click="isMobileMenuOpen = false" class="text-xs font-bold text-[#404940] hover:text-emerald-700 py-1 transition">Struktur</a>
                    <a href="#faq" @click="isMobileMenuOpen = false" class="text-xs font-bold text-[#404940] hover:text-emerald-700 py-1 transition">FAQ</a>
                    <a href="#kontak" @click="isMobileMenuOpen = false" class="text-xs font-bold text-[#404940] hover:text-emerald-700 py-1 transition">Kontak</a>
                    
                    <div class="border-t border-[#bfc9bd]/30 pt-2.5 flex flex-col gap-2">
                        <Link :href="route('portal.home')" class="w-full inline-flex items-center justify-center px-4 py-2 border border-slate-200 text-slate-700 font-bold text-xs rounded-xl hover:bg-slate-50 transition">
                            <span class="material-symbols-outlined text-sm mr-1">home</span>
                            Kembali ke Portal BUMDes
                        </Link>
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="w-full inline-flex items-center justify-center px-4 py-2 bg-emerald-600 text-white font-bold text-xs rounded-xl hover:bg-emerald-700 transition shadow-sm">
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link :href="route('login')" class="w-full inline-flex items-center justify-center px-4 py-2 border border-emerald-600 text-emerald-700 font-bold text-xs rounded-xl hover:bg-emerald-50 transition">
                                Masuk
                            </Link>
                        </template>
                    </div>
                </div>
            </transition>
        </header>

        <!-- Hero Section -->
        <section class="relative pt-28 pb-24 overflow-hidden bg-gradient-to-b from-white to-slate-50">
            <!-- Decorative circles -->
            <div class="absolute top-1/4 -right-20 w-80 h-80 bg-emerald-600/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 -left-20 w-80 h-80 bg-emerald-700/5 rounded-full blur-3xl"></div>

            <div class="max-w-4xl mx-auto px-6 text-center relative z-10 space-y-6">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-55 text-emerald-800 border border-emerald-100 text-[10px] font-bold uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Unit Usaha 1 BUMDesa Dammar Wulan
                </span>
                
                <h2 class="text-3xl md:text-5xl font-extrabold text-emerald-800 leading-tight md:leading-none tracking-tight">
                    {{ settings.hero_title || 'Layanan Simpan Pinjam BUMDesa Dammar Wulan' }}
                </h2>
                
                <p class="text-sm md:text-base text-[#404940] max-w-2xl mx-auto leading-relaxed">
                    {{ settings.hero_subtitle || 'Solusi layanan keuangan mikro desa yang aman, transparan, dan terpercaya untuk meningkatkan permodalan usaha kecil dan pemberdayaan ekonomi masyarakat.' }}
                </p>

                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <Link :href="route('login')" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg shadow-sm transition">
                        Buka Aplikasi Layanan
                        <span class="material-symbols-outlined text-[16px]">login</span>
                    </Link>
                    <Link :href="route('portal.home')" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-lg shadow-sm transition">
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                        Kembali ke Portal Utama
                    </Link>
                </div>
            </div>
        </section>

        <!-- Tentang Section -->
        <section id="tentang" class="py-20 bg-white border-y border-[#bfc9bd] space-y-16 overflow-hidden">
            <div class="max-w-5xl mx-auto px-6 grid gap-8 md:grid-cols-2 items-center">
                <div class="space-y-5 scroll-animate scroll-slide-right">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Profil & Layanan</span>
                    <h3 class="text-2xl font-bold text-slate-800 leading-tight">
                        {{ settings.about_title || 'Tentang Unit Simpan Pinjam' }}
                    </h3>
                    <p class="text-xs md:text-sm text-[#404940] leading-relaxed">
                        {{ settings.about_description || 'Unit Simpan Pinjam merupakan pilar penting dalam ekosistem BUMDesa Dammar Wulan, yang fokus pada penyediaan akses keuangan terjangkau bagi seluruh warga Desa Ciwuni. Kami menyediakan berbagai macam jenis tabungan serta pembiayaan modal usaha bagi pelaku usaha mikro.' }}
                    </p>
                    <div v-if="settings.about_history" class="pt-3 border-t border-slate-100 space-y-2">
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Sejarah & Tujuan</h4>
                        <p class="text-xs text-[#5c5f61] leading-relaxed whitespace-pre-wrap">
                            {{ settings.about_history }}
                        </p>
                    </div>
                </div>

                <!-- Visual Card with Icon representation -->
                <div class="scroll-animate scroll-scale-in relative bg-gradient-to-br from-emerald-50 to-[#ffffff] p-8 border border-[#bfc9bd] rounded-2xl shadow-sm flex flex-col items-center justify-center text-center overflow-hidden">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-emerald-600/5 rounded-full"></div>
                    
                    <span class="material-symbols-outlined text-6xl text-emerald-650 mb-4 filter drop-shadow-sm">payments</span>
                    <h4 class="font-extrabold text-slate-800 text-base uppercase tracking-wider">Unit Simpan Pinjam</h4>
                    <p class="text-[10px] font-bold text-emerald-700 tracking-widest uppercase">BUMDesa Dammar Wulan</p>
                    <div class="w-full border-t border-dashed border-[#bfc9bd] my-4"></div>
                    <p class="text-xs text-[#404940] leading-relaxed max-w-sm">
                        Mendorong pertumbuhan ekonomi desa melalui pengelolaan simpanan yang aman serta penyaluran pinjaman modal yang bijak dan berkeadilan.
                    </p>
                </div>
            </div>
        </section>

        <!-- Layanan Unggulan Section -->
        <section id="layanan" class="py-20 bg-slate-50 border-b border-[#bfc9bd]">
            <div class="max-w-5xl mx-auto px-6 space-y-12">
                <div class="text-center space-y-3 scroll-animate scroll-fade-up">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Program & Fitur</span>
                    <h3 class="text-2xl font-bold text-slate-800">Layanan Keuangan Kami</h3>
                    <p class="text-xs text-[#404940] max-w-md mx-auto">Berbagai pilihan produk tabungan dan pinjaman yang disesuaikan dengan kebutuhan finansial masyarakat desa.</p>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <!-- Feature 1: Tabungan Reguler -->
                    <div class="scroll-animate scroll-scale-in bg-white border border-[#bfc9bd] p-6 rounded-xl hover:border-emerald-500 hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                        <div class="space-y-4">
                            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                                <span class="material-symbols-outlined text-[24px]">savings</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm mb-1.5">{{ settings.feature_1_title || 'Tabungan Reguler' }}</h4>
                                <p class="text-[11px] text-[#5c5f61] leading-relaxed">{{ settings.feature_1_desc || 'Tabungan harian sukarela bagi warga dengan proses setoran dan penarikan yang sangat mudah untuk melatih kebiasaan menabung sejak dini.' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 2: Tabungan Sembako -->
                    <div class="scroll-animate scroll-scale-in bg-white border border-[#bfc9bd] p-6 rounded-xl hover:border-emerald-500 hover:shadow-md transition-all duration-300 flex flex-col justify-between" style="transition-delay: 80ms">
                        <div class="space-y-4">
                            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                                <span class="material-symbols-outlined text-[24px]">shopping_basket</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm mb-1.5">{{ settings.feature_2_title || 'Tabungan Sembako Hari Raya' }}</h4>
                                <p class="text-[11px] text-[#5c5f61] leading-relaxed">{{ settings.feature_2_desc || 'Tabungan berjangka khusus persiapan hari raya keagamaan, memberikan paket sembako premium atau hasil akumulasi tabungan menjelang lebaran.' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 3: Pinjaman Modal -->
                    <div class="scroll-animate scroll-scale-in bg-white border border-[#bfc9bd] p-6 rounded-xl hover:border-emerald-500 hover:shadow-md transition-all duration-300 flex flex-col justify-between" style="transition-delay: 160ms">
                        <div class="space-y-4">
                            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                                <span class="material-symbols-outlined text-[24px]">payments</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm mb-1.5">{{ settings.feature_3_title || 'Pinjaman Modal Usaha' }}</h4>
                                <p class="text-[11px] text-[#5c5f61] leading-relaxed">{{ settings.feature_3_desc || 'Bantuan modal usaha mikro bagi pelaku UMKM di Desa Ciwuni dengan bunga ringan dan skema angsuran fleksibel sesuai kapasitas usaha.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Struktur Organisasi Simpan Pinjam Section -->
        <section id="struktur" class="py-20 bg-white border-b border-[#bfc9bd]">
            <div class="max-w-6xl mx-auto px-6">
                <div class="text-center space-y-3 mb-16 scroll-animate scroll-fade-up">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Kepengurusan Unit</span>
                    <h3 class="text-2xl font-bold text-slate-800">Struktur Organisasi Unit Simpan Pinjam</h3>
                    <p class="text-xs text-[#404940] max-w-md mx-auto">Manajemen pengelola Unit Simpan Pinjam BUMDesa Dammar Wulan yang bertanggung jawab penuh terhadap pelayanan transaksi warga.</p>
                </div>
                
                <!-- Desktop Org Chart Container (Connected Tree) -->
                <div class="hidden md:block overflow-x-auto pb-4">
                    <div class="relative min-w-[600px] max-w-3xl mx-auto">
                        <!-- Level 1: Pembina & Direktur (Sebagai Oversight) -->
                        <div class="flex justify-center gap-12 mb-0">
                            <!-- Pembina -->
                            <div class="org-node flex flex-col items-center scroll-animate scroll-fade-up">
                                <div class="relative group cursor-default">
                                    <div class="w-16 h-16 rounded-full border-4 border-slate-350 bg-gradient-to-br from-slate-400 to-slate-655 flex items-center justify-center shadow text-slate-800 font-extrabold text-sm ring-4 ring-slate-500/10 overflow-hidden">
                                        <img v-if="settings.org_pembina_image" :src="settings.org_pembina_image" class="w-full h-full object-cover" />
                                        <span v-else>SR</span>
                                    </div>
                                </div>
                                <div class="mt-2 text-center">
                                    <p class="text-xs font-bold text-slate-800">{{ settings.org_pembina_name || 'Surya Ramdhani' }}</p>
                                    <span class="mt-0.5 inline-block px-2 py-0.5 bg-slate-200 text-slate-700 text-[8px] font-bold uppercase tracking-wider rounded-full">Pembina BUMDes</span>
                                </div>
                            </div>

                            <!-- Direktur -->
                            <div class="org-node flex flex-col items-center scroll-animate scroll-fade-up" style="transition-delay: 80ms">
                                <div class="relative group cursor-default">
                                    <div class="w-16 h-16 rounded-full border-4 border-slate-350 bg-gradient-to-br from-slate-400 to-slate-655 flex items-center justify-center shadow text-slate-800 font-extrabold text-sm ring-4 ring-slate-500/10 overflow-hidden">
                                        <img v-if="settings.org_direktur_image" :src="settings.org_direktur_image" class="w-full h-full object-cover" />
                                        <span v-else>AH</span>
                                    </div>
                                </div>
                                <div class="mt-2 text-center">
                                    <p class="text-xs font-bold text-slate-800">{{ settings.org_direktur_name || 'Ahmad Hidayat' }}</p>
                                    <span class="mt-0.5 inline-block px-2 py-0.5 bg-slate-200 text-slate-700 text-[8px] font-bold uppercase tracking-wider rounded-full">Direktur BUMDes</span>
                                </div>
                            </div>
                        </div>

                        <!-- Connector down to Unit Head -->
                        <div class="flex justify-center scroll-animate scroll-fade-up" style="transition-delay: 150ms">
                            <div class="w-0.5 h-10 bg-emerald-500/60"></div>
                        </div>

                        <!-- Level 2: Kepala Unit Simpan Pinjam -->
                        <div class="flex justify-center mb-0">
                            <div class="org-node flex flex-col items-center scroll-animate scroll-fade-up" style="transition-delay: 200ms">
                                <div class="relative group cursor-default">
                                    <div class="w-20 h-20 rounded-full border-4 border-emerald-600 bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center shadow-lg text-white font-extrabold text-xl ring-4 ring-emerald-500/20 group-hover:scale-105 transition-all duration-300 overflow-hidden">
                                        <img v-if="settings.org_unit_sp_image" :src="settings.org_unit_sp_image" class="w-full h-full object-cover" />
                                        <span v-else>FN</span>
                                    </div>
                                </div>
                                <div class="mt-3 text-center">
                                    <p class="text-xs font-extrabold text-slate-800">{{ settings.org_unit_sp_name || 'Fajar Nugroho' }}</p>
                                    <span class="mt-1 inline-block px-2.5 py-0.5 bg-emerald-600 text-white text-[9px] font-bold uppercase tracking-wider rounded-full">Kepala Unit Simpan Pinjam</span>
                                </div>
                            </div>
                        </div>

                        <!-- Connector down to Staff -->
                        <div class="flex justify-center scroll-animate scroll-fade-up" style="transition-delay: 280ms">
                            <div class="w-0.5 h-10 bg-emerald-500/60"></div>
                        </div>

                        <!-- Level 3: Staff Administrasi & Pelayanan / Kasir -->
                        <div class="relative flex justify-center gap-0">
                            <div class="absolute top-0 left-1/4 right-1/4 h-0.5 bg-emerald-500/40"></div>
                            <div class="absolute top-0 left-1/4 w-0.5 h-8 bg-emerald-500/40" style="transform: translateX(-50%)"></div>
                            <div class="absolute top-0 right-1/4 w-0.5 h-8 bg-emerald-500/40" style="transform: translateX(50%)"></div>

                            <div class="grid grid-cols-2 gap-16 w-full max-w-xl pt-8">
                                <!-- Staf Administrasi & Tabungan -->
                                <div class="org-node flex flex-col items-center scroll-animate scroll-fade-up" style="transition-delay: 350ms">
                                    <div class="relative">
                                        <div class="w-14 h-14 rounded-2xl border-2 border-emerald-400 bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center shadow text-emerald-700 font-extrabold text-sm overflow-hidden">
                                            <span class="material-symbols-outlined text-2xl">manage_accounts</span>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <p class="text-[10px] font-bold text-slate-800">Staf Administrasi</p>
                                        <span class="mt-1 inline-block px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[8px] font-bold uppercase tracking-wider rounded-full">Pelayanan Nasabah & Tabungan</span>
                                    </div>
                                </div>

                                <!-- Staf Kredit & Pinjaman -->
                                <div class="org-node flex flex-col items-center scroll-animate scroll-fade-up" style="transition-delay: 420ms">
                                    <div class="relative">
                                        <div class="w-14 h-14 rounded-2xl border-2 border-emerald-400 bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center shadow text-emerald-700 font-extrabold text-sm overflow-hidden">
                                            <span class="material-symbols-outlined text-2xl">rate_review</span>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <p class="text-[10px] font-bold text-slate-800">Staf Kredit / Kasir</p>
                                        <span class="mt-1 inline-block px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[8px] font-bold uppercase tracking-wider rounded-full">Pelayanan Kredit & Kasir</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Org Chart View -->
                <div class="md:hidden space-y-6">
                    <div class="flex flex-col items-center">
                        <span class="text-[8px] font-bold uppercase tracking-widest text-slate-500 mb-2">Manajemen BUMDes</span>
                        <div class="flex gap-2">
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-center w-[130px]">
                                <h5 class="text-[10px] font-bold text-slate-800 leading-tight">{{ settings.org_pembina_name || 'Surya Ramdhani' }}</h5>
                                <p class="text-[8px] text-slate-500 mt-1">Pembina</p>
                            </div>
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-center w-[130px]">
                                <h5 class="text-[10px] font-bold text-slate-800 leading-tight">{{ settings.org_direktur_name || 'Ahmad Hidayat' }}</h5>
                                <p class="text-[8px] text-slate-500 mt-1">Direktur</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center -my-2">
                        <div class="w-0.5 h-6 bg-emerald-500/30"></div>
                    </div>

                    <div class="flex flex-col items-center">
                        <span class="text-[8px] font-bold uppercase tracking-widest text-emerald-700 mb-2">Kepala Unit</span>
                        <div class="bg-emerald-55/50 border border-emerald-100 rounded-2xl p-4 flex items-center gap-3 w-full max-w-[280px]">
                            <div class="w-12 h-12 rounded-full border-2 border-emerald-600 overflow-hidden shrink-0 shadow-sm bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white font-bold text-xs">
                                <img v-if="settings.org_unit_sp_image" :src="settings.org_unit_sp_image" class="w-full h-full object-cover" />
                                <span v-else>FN</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-extrabold text-slate-800">{{ settings.org_unit_sp_name || 'Fajar Nugroho' }}</h4>
                                <p class="text-[9px] font-bold text-emerald-700 mt-0.5">Ka. Unit Simpan Pinjam</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section id="faq" class="py-20 bg-[#ffffff] border-b border-[#bfc9bd]">
            <div class="max-w-3xl mx-auto px-6 space-y-12">
                <div class="text-center space-y-3 scroll-animate scroll-fade-up">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Tanya Jawab</span>
                    <h3 class="text-2xl font-bold text-slate-800">Frequently Asked Questions (FAQ)</h3>
                    <p class="text-xs text-[#404940] max-w-md mx-auto">Temukan jawaban atas beberapa pertanyaan umum seputar layanan Simpan Pinjam kami.</p>
                </div>

                <div v-if="!settings.faq_items || settings.faq_items.length === 0" class="text-center text-xs text-slate-400 py-10 scroll-animate scroll-fade-up">
                    Belum ada FAQ yang dirilis saat ini.
                </div>

                <div class="space-y-3">
                    <div v-for="(faq, idx) in settings.faq_items" :key="idx" 
                         class="scroll-animate scroll-fade-up"
                         :style="{ transitionDelay: `${idx * 60}ms` }">
                        <div class="bg-white border border-[#bfc9bd] rounded-xl overflow-hidden shadow-sm transition-all">
                            <button @click="toggleFaq(idx)" class="w-full px-5 py-4 flex items-center justify-between text-left font-bold text-xs md:text-sm text-slate-800 hover:bg-slate-50/50 transition">
                                <span>{{ faq.question }}</span>
                                <span class="material-symbols-outlined text-slate-400 transition-transform" :class="{ 'rotate-180 text-emerald-700': activeFaqIndex === idx }">
                                    keyboard_arrow_down
                                </span>
                            </button>
                            <transition name="collapse">
                                <div v-if="activeFaqIndex === idx" class="px-5 pb-5 pt-1 text-xs text-[#5c5f61] leading-relaxed border-t border-slate-100 bg-slate-50/20">
                                    {{ faq.answer }}
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Kontak Section -->
        <section id="kontak" class="py-20 bg-slate-900 text-slate-105 relative overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.1),transparent_50%)]"></div>
            
            <div class="max-w-4xl mx-auto px-6 relative z-10 grid gap-8 md:grid-cols-2">
                <div class="space-y-4 scroll-animate scroll-slide-right">
                    <h3 class="text-xl font-bold text-white">Hubungi Unit Simpan Pinjam</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Punya pertanyaan seputar keanggotaan nasabah, persyaratan pinjaman modal, atau penarikan tabungan sembako? Tim operasional kami siap membantu Anda.</p>
                    
                    <div class="space-y-3 pt-2 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-500 text-[18px]">location_on</span>
                            <span class="text-slate-300">{{ settings.contact_address || 'Kantor BUMDesa Dammar Wulan, Desa Ciwuni' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-500 text-[18px]">call</span>
                            <span class="text-slate-300">{{ settings.contact_phone || '0812-0000-0000' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-500 text-[18px]">mail</span>
                            <span class="text-slate-300">{{ settings.contact_email || 'simpanpinjam@ciwuni.desa.id' }}</span>
                        </div>
                    </div>
                </div>

                <div class="scroll-animate scroll-slide-left bg-white/5 border border-white/10 p-6 rounded-xl flex flex-col justify-between">
                    <div>
                        <h4 class="font-bold text-sm text-white mb-2">Jam Operasional Kantor Pelayanan</h4>
                        <p class="text-xs text-slate-400">Senin - Jumat: 08.00 - 15.00 WIB</p>
                        <p class="text-xs text-slate-400 mt-1">Sabtu: 08.00 - 12.00 WIB</p>
                    </div>
                    <div class="mt-6">
                        <a v-if="settings.contact_phone" :href="`https://wa.me/${settings.contact_phone.replace(/[^0-9]/g, '')}`" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-650 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg transition shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">chat</span>
                            Hubungi Pelayanan via WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-8 bg-slate-950 text-center text-slate-550 border-t border-slate-900 text-xs">
            <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <p>&copy; 2026 BUMDes Dammar Wulan &bull; Unit Simpan Pinjam. Hak Cipta Dilindungi.</p>
                <p class="text-[10px] text-slate-600">Sistem Informasi Keuangan Mikro &bull; Desa Ciwuni</p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Scroll Premium Animations */
.scroll-animate {
    opacity: 0;
    will-change: transform, opacity;
}

/* Slide Up and Fade In */
.scroll-fade-up {
    transform: translateY(30px);
    transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}
.scroll-fade-up.animate-show {
    opacity: 1;
    transform: translateY(0);
}

/* Scale In */
.scroll-scale-in {
    transform: scale(0.94);
    transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}
.scroll-scale-in.animate-show {
    opacity: 1;
    transform: scale(1);
}

/* Slide Left (comes from right to left) */
.scroll-slide-left {
    transform: translateX(35px);
    transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}
.scroll-slide-left.animate-show {
    opacity: 1;
    transform: translateX(0);
}

/* Slide Right (comes from left to right) */
.scroll-slide-right {
    transform: translateX(-35px);
    transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}
.scroll-slide-right.animate-show {
    opacity: 1;
    transform: translateX(0);
}

/* Mobile Menu Collapsible Transition */
.mobile-menu-enter-active,
.mobile-menu-leave-active {
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    max-height: 400px;
    overflow: hidden;
}
.mobile-menu-enter-from,
.mobile-menu-leave-to {
    opacity: 0;
    max-height: 0;
    margin-top: 0;
    padding-top: 0;
}
</style>
