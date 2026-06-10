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
    galeri: {
        type: Array,
        default: () => [],
    },
});

const activeFaqIndex = ref(null);
const isMobileMenuOpen = ref(false);
const lightboxFoto = ref(null);
const lightboxKeterangan = ref(null);

const openLightbox = (foto, keterangan) => {
    lightboxFoto.value = foto;
    lightboxKeterangan.value = keterangan;
};
const closeLightbox = () => {
    lightboxFoto.value = null;
    lightboxKeterangan.value = null;
};

const getInitials = (name) => {
    if (!name) return 'WF';
    return name.split(' ').filter(Boolean).map(n => n[0]).join('').slice(0, 2).toUpperCase();
};

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
    <Head title="Unit Wifi" />

    <div class="min-h-screen bg-[#ffffff] text-[#181d18] font-sans antialiased selection:bg-blue-600 selection:text-white">
        <!-- Header / Navbar -->
        <header :class="[
            'fixed top-4 left-1/2 -translate-x-1/2 z-50 w-[calc(100%-2rem)] max-w-5xl bg-white/90 backdrop-blur-md border border-[#bfc9bd]/70 shadow-lg transition-all duration-300',
            'rounded-full py-2 px-6'
        ]">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <img :src="settings.custom_logo || '/logo.png'" alt="Logo" class="w-9 h-9 object-contain" />
                    <div>
                        <h1 class="text-xs font-extrabold text-blue-800 leading-tight">Unit Wifi</h1>
                        <p class="text-[9px] text-[#5c5f61] tracking-wider font-semibold uppercase leading-none">BUMDES Dammar Wulan</p>
                    </div>
                </div>

                <!-- Navigation Desktop -->
                <nav class="hidden md:flex items-center gap-5">
                    <a href="#tentang" class="text-xs font-bold text-[#404940] hover:text-blue-700 transition">Tentang</a>
                    <a href="#layanan" class="text-xs font-bold text-[#404940] hover:text-blue-700 transition">Layanan</a>
                    <a href="#struktur" class="text-xs font-bold text-[#404940] hover:text-blue-700 transition">Struktur</a>
                    <a href="#galeri" class="text-xs font-bold text-[#404940] hover:text-blue-700 transition">Galeri</a>
                    <a href="#berita" class="text-xs font-bold text-[#404940] hover:text-blue-700 transition">Berita</a>
                    <a href="#faq" class="text-xs font-bold text-[#404940] hover:text-blue-700 transition">FAQ</a>
                    <a href="#kontak" class="text-xs font-bold text-[#404940] hover:text-blue-700 transition">Kontak</a>
                    
                    <div class="border-l border-[#bfc9bd] pl-4 flex items-center gap-2">
                        <!-- Back to Main Portal -->
                        <Link :href="route('portal.home')" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition mr-2 flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-sm">home</span>
                            Portal BUMDes
                        </Link>
                        
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white font-semibold text-xs rounded-lg hover:bg-blue-700 transition shadow-sm">
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link :href="route('unit.login', { slug: 'wifi' })" class="inline-flex items-center justify-center px-4 py-2 border border-blue-600 text-blue-700 font-semibold text-xs rounded-lg hover:bg-blue-50 transition">
                                Masuk
                            </Link>
                        </template>
                    </div>
                </nav>

                <!-- Hamburger Button (Mobile) -->
                <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="md:hidden w-8 h-8 flex items-center justify-center text-[#404940] hover:text-blue-700 transition focus:outline-none">
                    <span class="material-symbols-outlined text-[22px]">
                        {{ isMobileMenuOpen ? 'close' : 'menu' }}
                    </span>
                </button>
            </div>

            <!-- Mobile Navigation Menu -->
            <transition 
                enter-active-class="transition ease-out duration-200 origin-top" 
                enter-from-class="opacity-0 scale-y-95 -translate-y-2" 
                enter-to-class="opacity-100 scale-y-100 translate-y-0" 
                leave-active-class="transition ease-in duration-150 origin-top" 
                leave-from-class="opacity-100 scale-y-100 translate-y-0" 
                leave-to-class="opacity-0 scale-y-95 -translate-y-2"
            >
                <div v-if="isMobileMenuOpen" class="md:hidden absolute top-[115%] left-0 right-0 bg-white/95 backdrop-blur-md border border-[#bfc9bd]/70 rounded-2xl shadow-xl p-4 flex flex-col gap-2.5">
                    <a href="#tentang" @click="isMobileMenuOpen = false" class="text-xs font-bold text-[#404940] hover:text-blue-700 py-1 transition">Tentang</a>
                    <a href="#layanan" @click="isMobileMenuOpen = false" class="text-xs font-bold text-[#404940] hover:text-blue-700 py-1 transition">Layanan</a>
                    <a href="#struktur" @click="isMobileMenuOpen = false" class="text-xs font-bold text-[#404940] hover:text-blue-700 py-1 transition">Struktur</a>
                    <a href="#berita" @click="isMobileMenuOpen = false" class="text-xs font-bold text-[#404940] hover:text-blue-700 py-1 transition">Berita</a>
                    <a href="#faq" @click="isMobileMenuOpen = false" class="text-xs font-bold text-[#404940] hover:text-blue-700 py-1 transition">FAQ</a>
                    <a href="#kontak" @click="isMobileMenuOpen = false" class="text-xs font-bold text-[#404940] hover:text-blue-700 py-1 transition">Kontak</a>
                    
                    <div class="border-t border-[#bfc9bd]/30 pt-2.5 flex flex-col gap-2">
                        <Link :href="route('portal.home')" class="w-full inline-flex items-center justify-center px-4 py-2 border border-slate-200 text-slate-700 font-bold text-xs rounded-xl hover:bg-slate-50 transition">
                            <span class="material-symbols-outlined text-sm mr-1">home</span>
                            Kembali ke Portal BUMDes
                        </Link>
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white font-bold text-xs rounded-xl hover:bg-blue-700 transition shadow-sm">
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link :href="route('unit.login', { slug: 'wifi' })" class="w-full inline-flex items-center justify-center px-4 py-2 border border-blue-600 text-blue-700 font-bold text-xs rounded-xl hover:bg-blue-50 transition">
                                Masuk
                            </Link>
                        </template>
                    </div>
                </div>
            </transition>
        </header>

        <!-- Hero Section -->
        <section class="relative pt-32 pb-24 md:pb-32 overflow-hidden min-h-screen flex items-center justify-center">
            <!-- Video Background -->
            <video autoplay loop muted playsinline class="absolute inset-0 w-full h-full object-cover z-0">
                <source src="/bumdes.mp4" type="video/mp4" />
            </video>
            
            <!-- Blue Tint Overlay for Readability -->
            <div class="absolute inset-0 bg-blue-950/60 z-0 backdrop-blur-[2px]"></div>

            <!-- Bottom Gradient Fade to White (Pembatas Bawah) -->
            <div class="absolute bottom-0 left-0 w-full h-40 md:h-56 bg-gradient-to-t from-white via-white/60 to-transparent z-0"></div>

            <div class="max-w-4xl mx-auto px-6 text-center relative z-10 space-y-4 md:space-y-6 -mt-4 md:-mt-8">
                <h2 class="text-3xl md:text-5xl lg:text-6xl font-extrabold text-white leading-tight md:leading-tight tracking-tight drop-shadow-lg">
                    {{ (settings.hero_title || 'Layanan Unit Wifi BUMDES Dammar Wulan').replace(/BUMDesa|BUMDes/ig, 'BUMDES') }}
                </h2>
                
                <div class="flex items-start justify-center">
                    <p class="text-sm md:text-lg text-slate-200 max-w-2xl mx-auto leading-relaxed drop-shadow-md">
                        {{ settings.hero_subtitle || 'Solusi layanan internet desa yang super cepat, aman, transparan, dan terjangkau untuk pemberdayaan masyarakat desa.' }}
                    </p>
                </div>

                <div class="pt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link :href="route('unit.login', { slug: 'wifi' })" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-lg hover:shadow-blue-600/30 hover:-translate-y-0.5 transition-all">
                        Buka Aplikasi Layanan
                        <span class="material-symbols-outlined text-[18px]">login</span>
                    </Link>
                    <Link :href="route('portal.home')" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 border border-white/20 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white font-bold text-sm rounded-xl transition-all">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Kembali ke Portal Utama
                    </Link>
                </div>
            </div>
        </section>

        <!-- Tentang Section -->
        <section id="tentang" class="py-20 bg-white border-y border-[#bfc9bd] space-y-16 overflow-hidden">
            <div class="max-w-5xl mx-auto px-6 grid gap-8 md:grid-cols-2 items-center">
                <div class="space-y-5 scroll-animate scroll-slide-right">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Profil & Layanan</span>
                    <h3 class="text-2xl font-bold text-slate-800 leading-tight">
                        {{ settings.about_title || 'Tentang Unit Wifi' }}
                    </h3>
                    <p class="text-xs md:text-sm text-[#404940] leading-relaxed">
                        {{ (settings.about_description || 'Unit Wifi merupakan pilar penting dalam ekosistem BUMDES Dammar Wulan, yang fokus pada penyediaan akses internet terjangkau bagi seluruh warga desa. Kami menyediakan berbagai macam jenis paket internet untuk pelaku usaha dan warga.').replace(/BUMDesa|BUMDes/ig, 'BUMDES') }}
                    </p>
                    <div v-if="settings.about_history" class="pt-3 border-t border-slate-100 space-y-2">
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Sejarah & Tujuan</h4>
                        <p class="text-xs text-[#5c5f61] leading-relaxed whitespace-pre-wrap">
                            {{ settings.about_history }}
                        </p>
                    </div>
                </div>

                <!-- Visual Card with Icon representation -->
                <div class="scroll-animate scroll-scale-in relative bg-gradient-to-br from-blue-50 to-[#ffffff] p-8 border border-[#bfc9bd] rounded-2xl shadow-sm flex flex-col items-center justify-center text-center overflow-hidden">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-600/5 rounded-full"></div>
                    
                    <img src="/logo.png" alt="Logo Wifi" class="w-16 h-16 object-contain mb-4 filter drop-shadow-sm" />
                    <h4 class="font-extrabold text-slate-800 text-base uppercase tracking-wider">Unit Wifi</h4>
                    <p class="text-[10px] font-bold text-blue-700 tracking-widest uppercase">BUMDES Dammar Wulan</p>
                    <div class="w-full border-t border-dashed border-[#bfc9bd] my-4"></div>
                    <p class="text-xs text-[#404940] leading-relaxed max-w-sm">
                        Mendorong konektivitas dan digitalisasi desa dengan internet berkualitas.
                    </p>
                </div>
            </div>
        </section>

        <!-- Layanan Unggulan Section -->
        <section id="layanan" class="py-20 bg-slate-50 border-b border-[#bfc9bd]">
            <div class="max-w-5xl mx-auto px-6 space-y-12">
                <div class="text-center space-y-3 scroll-animate scroll-fade-up">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Program & Fitur</span>
                    <h3 class="text-2xl font-bold text-slate-800">Layanan Unit Kami</h3>
                    <p class="text-xs text-[#404940] max-w-md mx-auto">Berbagai pilihan layanan yang disesuaikan dengan kebutuhan masyarakat desa.</p>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <!-- Feature 1 -->
                    <div class="scroll-animate scroll-scale-in bg-white border border-[#bfc9bd] p-6 rounded-xl hover:border-blue-500 hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                        <div class="space-y-4">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
                                <span class="material-symbols-outlined text-[24px]">wifi</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm mb-1.5">{{ settings.feature_1_title || 'Internet Rumahan' }}</h4>
                                <p class="text-[11px] text-[#5c5f61] leading-relaxed">{{ settings.feature_1_desc || 'Koneksi tanpa batas untuk menunjang aktivitas harian keluarga.' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="scroll-animate scroll-scale-in bg-white border border-[#bfc9bd] p-6 rounded-xl hover:border-blue-500 hover:shadow-md transition-all duration-300 flex flex-col justify-between" style="transition-delay: 80ms">
                        <div class="space-y-4">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
                                <span class="material-symbols-outlined text-[24px]">router</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm mb-1.5">{{ settings.feature_2_title || 'Pemasangan Jaringan' }}</h4>
                                <p class="text-[11px] text-[#5c5f61] leading-relaxed">{{ settings.feature_2_desc || 'Instalasi perangkat jaringan cepat dan handal.' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="scroll-animate scroll-scale-in bg-white border border-[#bfc9bd] p-6 rounded-xl hover:border-blue-500 hover:shadow-md transition-all duration-300 flex flex-col justify-between" style="transition-delay: 160ms">
                        <div class="space-y-4">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
                                <span class="material-symbols-outlined text-[24px]">support_agent</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm mb-1.5">{{ settings.feature_3_title || 'Dukungan Teknis' }}</h4>
                                <p class="text-[11px] text-[#5c5f61] leading-relaxed">{{ settings.feature_3_desc || 'Pelayanan responsif untuk menjaga kualitas koneksi Anda 24 jam.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Struktur Organisasi Section -->
        <section id="struktur" class="py-20 bg-white border-b border-[#bfc9bd]">
            <div class="max-w-6xl mx-auto px-6">
                <div class="text-center space-y-3 mb-16 scroll-animate scroll-fade-up">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Kepengurusan Unit</span>
                    <h3 class="text-2xl font-bold text-slate-800">Struktur Organisasi Unit Wifi</h3>
                    <p class="text-xs text-[#404940] max-w-md mx-auto">Manajemen pengelola Unit Wifi BUMDES Dammar Wulan.</p>
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
                                        <span v-else>{{ getInitials(settings.org_pembina_name || 'Surya Ramdhani') }}</span>
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
                                        <span v-else>{{ getInitials(settings.org_direktur_name || 'Ahmad Hidayat') }}</span>
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
                            <div class="w-0.5 h-10 bg-blue-500/60"></div>
                        </div>

                        <!-- Level 2: Kepala Unit Wifi -->
                        <div class="flex justify-center mb-0">
                            <div class="org-node flex flex-col items-center scroll-animate scroll-fade-up" style="transition-delay: 200ms">
                                <div class="relative group cursor-default">
                                    <div class="w-20 h-20 rounded-full border-4 border-blue-600 bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-lg text-white font-extrabold text-xl ring-4 ring-blue-500/20 group-hover:scale-105 transition-all duration-300 overflow-hidden">
                                        <img v-if="settings.org_unit_wifi_image" :src="settings.org_unit_wifi_image" class="w-full h-full object-cover" />
                                        <span v-else>{{ getInitials(settings.org_unit_wifi_name || 'Kepala Unit') }}</span>
                                    </div>
                                </div>
                                <div class="mt-3 text-center">
                                    <p class="text-xs font-extrabold text-slate-800">{{ settings.org_unit_wifi_name || 'Kepala Unit' }}</p>
                                    <span class="mt-1 inline-block px-2.5 py-0.5 bg-blue-600 text-white text-[9px] font-bold uppercase tracking-wider rounded-full">Kepala Unit Wifi</span>
                                </div>
                            </div>
                        </div>

                        <!-- Connector down to Staff -->
                        <div class="flex justify-center scroll-animate scroll-fade-up" style="transition-delay: 280ms">
                            <div class="w-0.5 h-10 bg-blue-500/60"></div>
                        </div>

                        <!-- Level 3: Staff Unit Wifi -->
                        <div class="flex justify-center">
                            <div class="org-node flex flex-col items-center scroll-animate scroll-fade-up" style="transition-delay: 350ms">
                                <div class="relative group cursor-default">
                                    <div class="w-16 h-16 rounded-full border-4 border-blue-500 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow text-white font-extrabold text-lg ring-4 ring-blue-500/10 group-hover:scale-105 transition-all duration-300 overflow-hidden">
                                        <img v-if="settings.org_unit_wifi_staff_image" :src="settings.org_unit_wifi_staff_image" class="w-full h-full object-cover" />
                                        <span v-else>{{ getInitials(settings.org_unit_wifi_staff_name || 'Staf WF') }}</span>
                                    </div>
                                </div>
                                <div class="mt-2 text-center">
                                    <p class="text-xs font-bold text-slate-800">{{ settings.org_unit_wifi_staff_name || 'Nama Staf' }}</p>
                                    <span class="mt-0.5 inline-block px-2 py-0.5 bg-blue-100 text-blue-700 text-[8px] font-bold uppercase tracking-wider rounded-full">Staf Unit Wifi</span>
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
                        <div class="w-0.5 h-6 bg-blue-500/30"></div>
                    </div>

                    <div class="flex flex-col items-center">
                        <span class="text-[8px] font-bold uppercase tracking-widest text-blue-700 mb-2">Kepala Unit</span>
                        <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-4 flex items-center gap-3 w-full max-w-[280px]">
                            <div class="w-12 h-12 rounded-full border-2 border-blue-600 overflow-hidden shrink-0 shadow-sm bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white font-bold text-xs">
                                <img v-if="settings.org_unit_wifi_image" :src="settings.org_unit_wifi_image" class="w-full h-full object-cover" />
                                <span v-else>{{ getInitials(settings.org_unit_wifi_name || 'Kepala Unit') }}</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-extrabold text-slate-800">{{ settings.org_unit_wifi_name || 'Kepala Unit' }}</h4>
                                <p class="text-[9px] font-bold text-blue-700 mt-0.5">Ka. Unit Wifi</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center -my-2">
                        <div class="w-0.5 h-6 bg-blue-500/30"></div>
                    </div>

                    <div class="flex flex-col items-center">
                        <span class="text-[8px] font-bold uppercase tracking-widest text-blue-700 mb-2">Staf Unit</span>
                        <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-4 flex items-center gap-3 w-full max-w-[280px]">
                            <div class="w-12 h-12 rounded-full border-2 border-blue-500 overflow-hidden shrink-0 shadow-sm bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-xs">
                                <img v-if="settings.org_unit_wifi_staff_image" :src="settings.org_unit_wifi_staff_image" class="w-full h-full object-cover" />
                                <span v-else>{{ getInitials(settings.org_unit_wifi_staff_name || 'Staf WF') }}</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-extrabold text-slate-800">{{ settings.org_unit_wifi_staff_name || 'Nama Staf' }}</h4>
                                <p class="text-[9px] font-bold text-blue-700 mt-0.5">Staf Unit Wifi</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Galeri Section -->
        <section id="galeri" class="py-20 bg-slate-50 border-b border-[#bfc9bd]">
            <div class="max-w-6xl mx-auto px-6 space-y-12">
                <div class="text-center space-y-3 scroll-animate scroll-fade-up">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Dokumentasi</span>
                    <h3 class="text-2xl font-bold text-slate-800">Galeri Unit Wifi</h3>
                    <p class="text-xs text-[#404940] max-w-md mx-auto">Dokumentasi kegiatan dan aktivitas pelayanan Unit Wifi BUMDES Dammar Wulan.</p>
                </div>

                <!-- Grid Galeri -->
                <div v-if="galeri.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div
                        v-for="(item, idx) in galeri" :key="item.id"
                        class="scroll-animate scroll-scale-in group relative rounded-xl overflow-hidden aspect-square cursor-pointer shadow-sm border border-[#bfc9bd] hover:border-blue-400 hover:shadow-md transition-all duration-300"
                        :style="{ transitionDelay: `${(idx % 8) * 60}ms` }"
                        @click="openLightbox(item.foto, item.keterangan)"
                    >
                        <img :src="item.foto" :alt="item.keterangan || 'Galeri'" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3">
                            <p v-if="item.keterangan" class="text-white text-[10px] font-semibold leading-tight line-clamp-2">{{ item.keterangan }}</p>
                            <span v-else class="text-white/70 text-[10px]">Lihat foto</span>
                        </div>
                        <div class="absolute top-2 right-2 w-7 h-7 bg-white/80 backdrop-blur-sm rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="material-symbols-outlined text-sm text-slate-700">zoom_in</span>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-16 scroll-animate scroll-fade-up">
                    <span class="material-symbols-outlined text-5xl text-slate-300">photo_library</span>
                    <p class="mt-3 text-sm text-slate-400">Belum ada foto di galeri.</p>
                </div>
            </div>
        </section>

        <!-- Lightbox -->
        <Teleport to="body">
            <div v-if="lightboxFoto" class="fixed inset-0 z-[9999] bg-black/90 flex items-center justify-center p-4" @click.self="closeLightbox">
                <button @click="closeLightbox" class="absolute top-4 right-4 w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition">
                    <span class="material-symbols-outlined text-white">close</span>
                </button>
                <div class="max-w-4xl w-full">
                    <img :src="lightboxFoto" alt="Galeri" class="w-full max-h-[80vh] object-contain rounded-xl" />
                    <p v-if="lightboxKeterangan" class="mt-3 text-center text-sm text-white/80">{{ lightboxKeterangan }}</p>
                </div>
            </div>
        </Teleport>

        <!-- Berita & Pengumuman Section -->
        <section id="berita" class="py-20 bg-[#f8fafc] border-b border-[#bfc9bd]">
            <div class="max-w-5xl mx-auto px-6 space-y-12">
                <div class="text-center space-y-3 scroll-animate scroll-fade-up">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Berita Terkini</span>
                    <h3 class="text-2xl font-bold text-slate-800">Pengumuman & Berita Unit</h3>
                    <p class="text-xs text-[#404940] max-w-md mx-auto">Informasi terbaru seputar layanan dan kegiatan Unit Wifi.</p>
                </div>

                <div v-if="!settings.news_items || settings.news_items.length === 0" class="text-center text-xs text-slate-400 py-10 scroll-animate scroll-fade-up">
                    Belum ada berita atau pengumuman saat ini.
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div v-for="(news, idx) in settings.news_items" :key="idx" class="scroll-animate scroll-fade-up bg-white rounded-xl overflow-hidden border border-[#bfc9bd] shadow-sm hover:shadow-md transition-shadow group flex flex-col" :style="{ transitionDelay: `${idx * 100}ms` }">
                        <div class="w-full h-44 overflow-hidden relative">
                            <img v-if="news.image" :src="news.image" alt="Berita" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                            <div v-else class="w-full h-full bg-slate-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-4xl text-slate-300">newspaper</span>
                            </div>
                        </div>
                        <div class="p-5 flex flex-col flex-grow">
                            <div class="flex items-center text-[10px] text-slate-500 mb-2 font-semibold tracking-wide uppercase">
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px]">calendar_today</span>
                                    {{ news.date || 'Terbaru' }}
                                </span>
                            </div>
                            <h4 class="font-bold text-slate-800 text-sm mb-2 leading-snug">{{ news.title }}</h4>
                            <p class="text-[11px] text-slate-600 line-clamp-3 leading-relaxed mb-4">{{ news.content }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section id="faq" class="py-20 bg-[#ffffff] border-b border-[#bfc9bd]">
            <div class="max-w-3xl mx-auto px-6 space-y-12">
                <div class="text-center space-y-3 scroll-animate scroll-fade-up">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Tanya Jawab</span>
                    <h3 class="text-2xl font-bold text-slate-800">Frequently Asked Questions (FAQ)</h3>
                    <p class="text-xs text-[#404940] max-w-md mx-auto">Temukan jawaban atas beberapa pertanyaan umum seputar layanan Wifi kami.</p>
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
                                <span class="material-symbols-outlined text-slate-400 transition-transform" :class="{ 'rotate-180 text-blue-700': activeFaqIndex === idx }">
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
                    <h3 class="text-xl font-bold text-white">Hubungi Unit Wifi</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Punya pertanyaan seputar pemasangan baru atau masalah teknis? Tim operasional kami siap membantu Anda.</p>
                    
                    <div class="space-y-3 pt-2 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-500 text-[18px]">location_on</span>
                            <span class="text-slate-300">{{ (settings.contact_address || 'Kantor BUMDES Dammar Wulan, Desa Ciwuni').replace(/BUMDesa|BUMDes/ig, 'BUMDES') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-500 text-[18px]">call</span>
                            <span class="text-slate-300">{{ settings.contact_phone || '0812-0000-0000' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-500 text-[18px]">mail</span>
                            <span class="text-slate-300">{{ settings.contact_email || 'wifi@ciwuni.desa.id' }}</span>
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
                        <a v-if="settings.contact_phone" :href="`https://wa.me/${settings.contact_phone.replace(/[^0-9]/g, '')}`" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-lg transition shadow-sm">
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
                <p>&copy; 2026 BUMDes Dammar Wulan &bull; Unit Wifi. Hak Cipta Dilindungi.</p>
                <p class="text-[10px] text-slate-600">Sistem Informasi Layanan Internet &bull; Desa Ciwuni</p>
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
