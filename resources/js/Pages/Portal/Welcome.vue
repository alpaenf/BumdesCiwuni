<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    settings: { type: Object, required: true },
    orgSettings: { type: Object, default: () => ({}) },
    units: { type: Array, default: () => [] },
    posts: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
});

const rotatingUnits = [
    { name: 'Portal Simpan Pinjam', href: route('welcome'), icon: 'account_balance' },
    { name: 'Portal Unit WiFi', href: route('unit.welcome', { slug: 'wifi' }), icon: 'wifi' },
    { name: 'Portal Ketahanan Pangan', href: route('unit.welcome', { slug: 'ketahanan-pangan' }), icon: 'agriculture' },
    { name: 'Portal Perdagangan Besar', href: route('unit.welcome', { slug: 'perdagangan-besar' }), icon: 'local_shipping' }
];

const currentUnitIndex = ref(0);
let unitRotateTimer = null;

const fullText = "Portal Terpadu untuk Transparansi dan Kemajuan Ekonomi Desa Dammar Wulan.";
const displayText = ref("");
const isScrolled = ref(false);

const isMobileMenuOpen = ref(false);

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return new Intl.DateTimeFormat('id-ID', { dateStyle: 'long' }).format(date);
};

const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num || 0);
};

const getInitials = (name) => {
    if (!name) return '??';
    return name.split(' ').filter(Boolean).map(n => n[0]).join('').slice(0, 2).toUpperCase();
};

const getStatusBadge = (status) => {
    const map = {
        aktif: { text: 'Aktif', class: 'bg-blue-100 text-blue-800 border-blue-200' },
        coming_soon: { text: 'Coming Soon', class: 'bg-amber-100 text-amber-800 border-amber-200' },
        nonaktif: { text: 'Nonaktif', class: 'bg-slate-100 text-slate-600 border-slate-200' },
    };
    return map[status] || map.nonaktif;
};

const getTypeBadge = (tipe) => {
    return tipe === 'internal'
        ? { text: 'Aplikasi Internal', class: 'bg-blue-100 text-blue-800 border-blue-200' }
        : { text: 'Website Unit', class: 'bg-blue-100 text-blue-800 border-blue-200' };
};

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('portal-animate-show');
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.portal-animate').forEach((el) => observer.observe(el));

    // Typewriter effect logic
    const fullText = props.settings.hero_subtitle || 'Lembaga penggerak ekonomi desa yang berdedikasi mengelola layanan keuangan mikro, penyediaan jaringan internet, program ketahanan pangan, hingga pusat perdagangan terpadu.';
    let isTyping = true;
    let textIndex = 0;

    window.addEventListener('scroll', () => {
        isScrolled.value = window.scrollY > 50;
    });

    const startTypewriter = () => {
        if (isTyping) {
            if (textIndex < fullText.length) {
                displayText.value += fullText.charAt(textIndex);
                textIndex++;
                setTimeout(startTypewriter, 60); // Ngetik pelan (60ms per huruf)
            } else {
                isTyping = false;
                setTimeout(startTypewriter, 3000); // Tunggu 3 detik saat teks sudah utuh
            }
        } else {
            if (textIndex > 0) {
                displayText.value = fullText.substring(0, textIndex - 1);
                textIndex--;
                setTimeout(startTypewriter, 30); // Hapus sedikit lebih cepat (30ms per huruf)
            } else {
                isTyping = true;
                setTimeout(startTypewriter, 1000); // Tunggu 1 detik sebelum ngetik ulang
            }
        }
    };

    setTimeout(startTypewriter, 1000); // Mulai efek setelah halaman dimuat 1 detik

    unitRotateTimer = setInterval(() => {
        currentUnitIndex.value = (currentUnitIndex.value + 1) % rotatingUnits.length;
    }, 5000);
});

onUnmounted(() => {
    if (unitRotateTimer) clearInterval(unitRotateTimer);
});
</script>

<template>
    <Head title="Portal BUMDes Dammar Wulan" />

    <div class="min-h-screen bg-white text-[#181d18] font-sans antialiased selection:bg-blue-600 selection:text-white overflow-x-hidden w-full max-w-[100vw]">
        <!-- Header / Navbar -->
        <header :class="[
            'nav-animate fixed top-4 left-1/2 z-50 w-[calc(100%-2rem)] max-w-5xl transition-all duration-300 rounded-full py-2 px-6',
            isScrolled 
                ? 'bg-white/95 backdrop-blur-md border border-[#bfc9bd]/70 shadow-lg' 
                : 'bg-white/10 backdrop-blur-md border border-white/20 shadow-[0_8px_32px_0_rgba(0,0,0,0.05)]'
        ]">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <img src="/logo2.png" alt="Logo" class="h-9 w-9 object-contain" />
                    <div>
                        <h1 :class="['text-xs font-extrabold leading-tight transition-colors', isScrolled ? 'text-blue-800' : 'text-white']">{{ (settings.bumdes_name || 'BUMDES Dammar Wulan').replace(/BUMDesa|BUMDes/ig, 'BUMDES') }}</h1>
                        <p :class="['text-[9px] tracking-wider font-semibold uppercase leading-none transition-colors', isScrolled ? 'text-[#5c5f61]' : 'text-slate-300']">Portal Terintegrasi</p>
                    </div>
                </div>

                <nav class="hidden md:flex items-center gap-5">
                    <a href="#tentang" :class="['text-xs font-bold transition-colors', isScrolled ? 'text-[#404940] hover:text-blue-700' : 'text-slate-200 hover:text-white']">Tentang</a>
                    <a href="#unit-usaha" :class="['text-xs font-bold transition-colors', isScrolled ? 'text-[#404940] hover:text-blue-700' : 'text-slate-200 hover:text-white']">Unit Usaha</a>
                    <a href="#struktur" :class="['text-xs font-bold transition-colors', isScrolled ? 'text-[#404940] hover:text-blue-700' : 'text-slate-200 hover:text-white']">Struktur</a>
                    <a href="#berita" :class="['text-xs font-bold transition-colors', isScrolled ? 'text-[#404940] hover:text-blue-700' : 'text-slate-200 hover:text-white']">Berita</a>
                    <a href="#kontak" :class="['text-xs font-bold transition-colors', isScrolled ? 'text-[#404940] hover:text-blue-700' : 'text-slate-200 hover:text-white']">Kontak</a>
                    
                    <div :class="['border-l pl-4 flex items-center gap-2 transition-colors', isScrolled ? 'border-[#bfc9bd]' : 'border-white/30']">
                        <Link v-if="$page.props.auth?.user" :href="route('portal.cms.dashboard')" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white font-semibold text-xs rounded-lg hover:bg-blue-700 transition shadow-sm">
                            Dashboard CMS
                        </Link>
                        <Link v-else :href="route('portal.login')" :class="['inline-flex items-center justify-center px-4 py-2 font-semibold text-xs rounded-lg transition-colors border', isScrolled ? 'border-blue-600 text-blue-700 hover:bg-blue-50' : 'border-white/50 text-white hover:bg-white/20']">
                            Masuk Portal
                        </Link>
                    </div>
                </nav>

                <button @click="isMobileMenuOpen = !isMobileMenuOpen" :class="['md:hidden w-8 h-8 flex items-center justify-center transition-colors', isScrolled ? 'text-[#404940] hover:text-blue-700' : 'text-white hover:text-slate-200']">
                    <span class="material-symbols-outlined text-[22px]">{{ isMobileMenuOpen ? 'close' : 'menu' }}</span>
                </button>
            </div>

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
                    <a href="#unit-usaha" @click="isMobileMenuOpen = false" class="text-xs font-bold text-[#404940] hover:text-blue-700 py-1 transition">Unit Usaha</a>
                    <a href="#struktur" @click="isMobileMenuOpen = false" class="text-xs font-bold text-[#404940] hover:text-blue-700 py-1 transition">Struktur</a>
                    <a href="#berita" @click="isMobileMenuOpen = false" class="text-xs font-bold text-[#404940] hover:text-blue-700 py-1 transition">Berita</a>
                    <a href="#kontak" @click="isMobileMenuOpen = false" class="text-xs font-bold text-[#404940] hover:text-blue-700 py-1 transition">Kontak</a>
                    <div class="border-t border-[#bfc9bd]/30 pt-2.5">
                        <Link v-if="$page.props.auth?.user" :href="route('portal.cms.dashboard')" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white font-bold text-xs rounded-xl hover:bg-blue-700 transition shadow-sm">
                            Dashboard CMS
                        </Link>
                        <Link v-else :href="route('portal.login')" class="w-full inline-flex items-center justify-center px-4 py-2 border border-blue-600 text-blue-700 font-bold text-xs rounded-xl hover:bg-blue-50 transition">
                            Masuk Portal
                        </Link>
                    </div>
                </div>
            </transition>
        </header>

        <!-- Hero Section -->
        <section class="relative pt-32 pb-24 md:pb-32 overflow-hidden min-h-[85vh] flex items-center justify-center">
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
                    {{ (settings.hero_title || 'Portal Resmi BUMDES Dammar Wulan').replace(/BUMDesa|BUMDes/ig, 'BUMDES') }}
                </h2>

                <div class="h-20 md:h-24 flex items-start justify-center">
                    <p class="text-sm md:text-lg text-slate-200 max-w-2xl mx-auto leading-relaxed drop-shadow-md">
                        {{ displayText }}<span class="animate-pulse inline-block w-[2px] h-[1em] bg-slate-200 ml-1 align-middle -mt-1"></span>
                    </p>
                </div>

                <div class="pt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a :href="settings.hero_cta_link || '#unit-usaha'" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-lg hover:shadow-blue-600/30 hover:-translate-y-0.5 transition-all">
                        {{ settings.hero_cta_text || 'Lihat Unit Usaha' }}
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </a>
                    
                    <!-- Animated Rotating Unit Button -->
                    <Transition name="fade-unit" mode="out-in">
                        <Link :key="rotatingUnits[currentUnitIndex].href"
                              :href="rotatingUnits[currentUnitIndex].href"
                              class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 border border-white/40 bg-white/15 backdrop-blur-md hover:bg-white/30 text-white font-extrabold text-sm rounded-xl shadow-xl hover:-translate-y-0.5 transition-all">
                            {{ rotatingUnits[currentUnitIndex].name }}
                        </Link>
                    </Transition>
                </div>
            </div>
        </section>

        <!-- Tentang Section -->
        <section id="tentang" class="pt-16 pb-20 bg-white border-b border-[#bfc9bd]">
            <div class="max-w-5xl mx-auto px-6 grid gap-10 md:grid-cols-2 items-center">
                <div class="space-y-5 portal-animate">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Tentang Kami</span>
                    <h3 class="text-2xl font-bold text-slate-800 leading-tight">
                        {{ (settings.about_title || 'Mengenal BUMDES Dammar Wulan').replace(/BUMDesa|BUMDes/ig, 'BUMDES') }}
                    </h3>
                    <p class="text-sm text-[#404940] leading-relaxed">
                        {{ (settings.about_description || '').replace(/BUMDesa|BUMDes/ig, 'BUMDES') }}
                    </p>
                    <div v-if="settings.about_history" class="pt-3 border-t border-slate-100 space-y-2">
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Sejarah Singkat</h4>
                        <p class="text-xs text-[#5c5f61] leading-relaxed whitespace-pre-wrap">{{ settings.about_history }}</p>
                    </div>
                </div>

                <div class="portal-animate space-y-6">
                    <!-- Visi -->
                    <div v-if="settings.visi" class="bg-blue-50 border border-blue-100 rounded-xl p-5">
                        <h4 class="text-xs font-bold text-blue-800 uppercase tracking-wider mb-2">Visi</h4>
                        <p class="text-sm text-blue-900 leading-relaxed">{{ settings.visi }}</p>
                    </div>
                    <!-- Misi -->
                    <div v-if="settings.misi" class="bg-white border border-[#bfc9bd] rounded-xl p-5">
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">Misi</h4>
                        <p class="text-sm text-[#404940] leading-relaxed whitespace-pre-wrap">{{ settings.misi }}</p>
                    </div>
                </div>
            </div>

            <!-- Struktur Organisasi / Pengurus -->
            <section id="struktur" class="py-20 bg-white border-y border-slate-200">
                <div class="max-w-6xl mx-auto px-6">
                    <div class="text-center space-y-3 mb-16 portal-animate">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Manajemen Kami</span>
                        <h3 class="text-2xl font-bold text-slate-800">Struktur Pengurus BUMDes</h3>
                        <p class="text-xs text-[#404940] max-w-md mx-auto">Susunan kepengurusan BUMDES Dammar Wulan yang menaungi 4 unit usaha produktif.</p>
                    </div>
                    
                    <!-- Desktop Org Chart Container (Connected Tree matching official banner) -->
                    <div class="hidden md:block overflow-x-auto pb-8">
                        <div class="relative min-w-[860px] max-w-5xl mx-auto flex flex-col items-center">
                            
                            <!-- Root: MUSYAWARAH DESA -->
                            <div class="flex flex-col items-center portal-animate z-20">
                                <div class="px-6 py-2.5 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white rounded-xl shadow-lg border border-slate-700/80 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <span class="text-xs font-black uppercase tracking-widest text-slate-100">MUSYAWARAH DESA</span>
                                </div>
                                <!-- Vertical stem from Musyawarah Desa -->
                                <div class="w-0.5 h-10 bg-slate-300"></div>
                            </div>

                            <!-- Level 1: Komisaris (Left) - Direktur (Center) - Pengawas (Right) -->
                            <div class="relative w-full max-w-4xl px-4">
                                <!-- Horizontal branch line from Musyawarah Desa stem to Komisaris & Pengawas -->
                                <div class="absolute -top-5 left-1/2 -translate-x-1/2 w-[620px] h-0.5 bg-slate-300 -z-10"></div>
                                <!-- Vertical drops from horizontal branch down to Komisaris and Pengawas -->
                                <div class="absolute -top-5 left-[calc(50%-310px)] w-0.5 h-5 bg-slate-300 -z-10"></div>
                                <div class="absolute -top-5 right-[calc(50%-310px)] w-0.5 h-5 bg-slate-300 -z-10"></div>

                                <!-- Horizontal dashed coordinator/supervisory line between Komisaris <-> Direktur <-> Pengawas -->
                                <div class="absolute top-1/2 -translate-y-1/2 left-1/2 -translate-x-1/2 w-[560px] border-t-2 border-dashed border-rose-300/80 -z-10"></div>

                                <div class="grid grid-cols-3 items-center gap-6">
                                    <!-- Left: Komisaris / KEPALA DESA -->
                                    <div class="flex flex-col items-center portal-animate">
                                        <div class="w-20 h-20 rounded-full border-4 border-rose-500 bg-white flex items-center justify-center shadow-lg overflow-hidden z-10 ring-4 ring-rose-500/10">
                                            <img v-if="settings.org_bumdes_pembina_image" :src="settings.org_bumdes_pembina_image" class="w-full h-full object-cover" />
                                            <span v-else class="text-rose-700 font-extrabold text-xl">{{ getInitials(settings.org_bumdes_pembina_name || 'Komisaris') }}</span>
                                        </div>
                                        <div class="mt-3 bg-white border border-rose-200/80 rounded-xl p-2.5 w-48 text-center shadow-sm">
                                            <span class="inline-block px-2.5 py-0.5 bg-rose-500 text-white text-[9px] font-extrabold uppercase tracking-wider rounded-full shadow-xs mb-1">KOMISARIS</span>
                                            <p class="text-xs font-black text-slate-800 leading-tight uppercase">{{ settings.org_bumdes_pembina_name || 'NGADIMAN' }}</p>
                                            <p class="text-[9px] font-bold text-rose-600 mt-0.5 tracking-wide">KEPALA DESA</p>
                                        </div>
                                    </div>

                                    <!-- Center: Direktur -->
                                    <div class="flex flex-col items-center portal-animate">
                                        <div class="w-22 h-22 rounded-full border-4 border-blue-600 bg-white flex items-center justify-center shadow-xl overflow-hidden z-10 ring-4 ring-blue-500/15 scale-105">
                                            <img v-if="settings.org_bumdes_direktur_image" :src="settings.org_bumdes_direktur_image" class="w-full h-full object-cover" />
                                            <span v-else class="text-blue-700 font-extrabold text-2xl">{{ getInitials(settings.org_bumdes_direktur_name || 'Direktur') }}</span>
                                        </div>
                                        <div class="mt-3 bg-white border-2 border-blue-500/80 rounded-xl p-2.5 w-52 text-center shadow-md">
                                            <span class="inline-block px-3 py-0.5 bg-blue-600 text-white text-[9px] font-black uppercase tracking-wider rounded-full shadow-xs mb-1">DIREKTUR</span>
                                            <p class="text-xs font-black text-slate-900 leading-tight">{{ settings.org_bumdes_direktur_name || 'P. YULIANTORO, S.E.' }}</p>
                                        </div>
                                    </div>

                                    <!-- Right: Pengawas -->
                                    <div class="flex flex-col items-center portal-animate">
                                        <div class="w-20 h-20 rounded-full border-4 border-rose-500 bg-white flex items-center justify-center shadow-lg overflow-hidden z-10 ring-4 ring-rose-500/10">
                                            <img v-if="settings.org_bumdes_pengawas_image" :src="settings.org_bumdes_pengawas_image" class="w-full h-full object-cover" />
                                            <span v-else class="text-rose-700 font-extrabold text-xl">{{ getInitials(settings.org_bumdes_pengawas_name || 'Pengawas') }}</span>
                                        </div>
                                        <div class="mt-3 bg-white border border-rose-200/80 rounded-xl p-2.5 w-48 text-center shadow-sm">
                                            <span class="inline-block px-2.5 py-0.5 bg-rose-500 text-white text-[9px] font-extrabold uppercase tracking-wider rounded-full shadow-xs mb-1">PENGAWAS</span>
                                            <p class="text-xs font-black text-slate-800 leading-tight uppercase">{{ settings.org_bumdes_pengawas_name || 'SUBARKAH' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vertical Connector down from Direktur to Level 2 (Sekretaris & Bendahara) -->
                            <div class="w-0.5 h-10 bg-slate-300"></div>

                            <!-- Level 2: Sekretaris (Left) & Bendahara (Right) -->
                            <div class="relative w-full max-w-md px-4">
                                <!-- Horizontal branch for Sek/Ben -->
                                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-64 h-0.5 bg-slate-300 -z-10"></div>
                                <div class="absolute top-0 left-[calc(50%-128px)] w-0.5 h-5 bg-slate-300 -z-10"></div>
                                <div class="absolute top-0 right-[calc(50%-128px)] w-0.5 h-5 bg-slate-300 -z-10"></div>

                                <div class="flex justify-between items-center pt-5">
                                    <!-- Sekretaris -->
                                    <div class="flex flex-col items-center portal-animate w-40">
                                        <div class="w-16 h-16 rounded-full border-3 border-rose-400 bg-white flex items-center justify-center shadow-md overflow-hidden z-10 ring-3 ring-rose-400/15">
                                            <img v-if="settings.org_bumdes_sekretaris_image" :src="settings.org_bumdes_sekretaris_image" class="w-full h-full object-cover" />
                                            <span v-else class="text-rose-700 font-bold text-sm">{{ getInitials(settings.org_bumdes_sekretaris_name || 'Sekretaris') }}</span>
                                        </div>
                                        <div class="mt-2.5 bg-white border border-rose-200 rounded-xl p-2 w-full text-center shadow-sm">
                                            <span class="inline-block px-2 py-0.5 bg-rose-50 text-rose-600 text-[8px] font-bold uppercase tracking-wider rounded-full mb-0.5">SEKRETARIS</span>
                                            <p class="text-[11px] font-bold text-slate-800 leading-tight">{{ settings.org_bumdes_sekretaris_name || 'SALSA DETRIANI' }}</p>
                                        </div>
                                    </div>

                                    <!-- Bendahara -->
                                    <div class="flex flex-col items-center portal-animate w-40">
                                        <div class="w-16 h-16 rounded-full border-3 border-rose-400 bg-white flex items-center justify-center shadow-md overflow-hidden z-10 ring-3 ring-rose-400/15">
                                            <img v-if="settings.org_bumdes_bendahara_image" :src="settings.org_bumdes_bendahara_image" class="w-full h-full object-cover" />
                                            <span v-else class="text-rose-700 font-bold text-sm">{{ getInitials(settings.org_bumdes_bendahara_name || 'Bendahara') }}</span>
                                        </div>
                                        <div class="mt-2.5 bg-white border border-rose-200 rounded-xl p-2 w-full text-center shadow-sm">
                                            <span class="inline-block px-2 py-0.5 bg-rose-50 text-rose-600 text-[8px] font-bold uppercase tracking-wider rounded-full mb-0.5">BENDAHARA</span>
                                            <p class="text-[11px] font-bold text-slate-800 leading-tight">{{ settings.org_bumdes_bendahara_name || 'DWI GUNAWAN' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vertical stem down from Level 2 to Level 3 (The 4 Unit Heads) -->
                            <div class="w-0.5 h-12 bg-slate-300"></div>

                            <!-- Level 3: 4 Business Unit Heads (Left to Right matching physical banner) -->
                            <div class="relative w-full max-w-4xl px-4">
                                <!-- Horizontal branch line across the 4 units -->
                                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[720px] h-0.5 bg-slate-300 -z-10"></div>
                                
                                <div class="grid grid-cols-4 gap-4 pt-5">
                                    <div v-for="unit in [
                                        { 
                                            label: 'Ka. Unit Ketahanan Pangan', 
                                            name: settings.org_bumdes_unit_pangan_name || 'BUDIMAN', 
                                            image: settings.org_bumdes_unit_pangan_image,
                                            borderCol: 'border-emerald-500',
                                            badgeCol: 'bg-emerald-600 text-white',
                                            bgCard: 'border-emerald-200'
                                        },
                                        { 
                                            label: 'Ka. Unit Perdagangan', 
                                            name: settings.org_bumdes_unit_pasar_name || 'SUSANTI', 
                                            image: settings.org_bumdes_unit_pasar_image,
                                            borderCol: 'border-amber-500',
                                            badgeCol: 'bg-amber-500 text-white',
                                            bgCard: 'border-amber-200'
                                        },
                                        { 
                                            label: 'Ka. Unit Simpan Pinjam', 
                                            name: settings.org_bumdes_unit_sp_name || 'PIPIT DAIKHOTUL KH.', 
                                            image: settings.org_bumdes_unit_sp_image,
                                            borderCol: 'border-sky-500',
                                            badgeCol: 'bg-sky-600 text-white',
                                            bgCard: 'border-sky-200'
                                        },
                                        { 
                                            label: 'Ka. Unit Internet (Wifi)', 
                                            name: settings.org_bumdes_unit_wifi_name || 'SITI MARFUAH', 
                                            image: settings.org_bumdes_unit_wifi_image,
                                            borderCol: 'border-slate-600',
                                            badgeCol: 'bg-slate-700 text-white',
                                            bgCard: 'border-slate-200'
                                        },
                                    ]" :key="unit.label" class="flex flex-col items-center relative portal-animate">
                                        <!-- Vertical drop from horizontal line -->
                                        <div class="w-0.5 h-5 bg-slate-300 absolute -top-5 left-1/2 -translate-x-1/2 -z-10"></div>

                                        <div class="w-16 h-16 rounded-full border-3 bg-white flex items-center justify-center shadow-md overflow-hidden z-10" :class="unit.borderCol">
                                            <img v-if="unit.image" :src="unit.image" class="w-full h-full object-cover" />
                                            <span v-else class="font-extrabold text-sm text-slate-700">{{ getInitials(unit.name) }}</span>
                                        </div>
                                        <div class="mt-2.5 bg-white border rounded-xl p-2.5 w-full text-center shadow-sm" :class="unit.bgCard">
                                            <span class="inline-block px-2 py-0.5 text-[8px] font-bold uppercase tracking-wider rounded-md mb-1 shadow-xs" :class="unit.badgeCol">{{ unit.label }}</span>
                                            <p class="text-xs font-bold text-slate-800 leading-tight">{{ unit.name }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Anggota & Pengurus Tambahan (Desktop View) -->
                            <div v-if="settings.org_bumdes_custom_members && settings.org_bumdes_custom_members.length > 0" class="mt-12 w-full portal-animate border-t border-slate-200/80 pt-8">
                                <div class="text-center mb-6">
                                    <span class="text-[9px] font-bold uppercase tracking-widest text-slate-600 bg-slate-100 px-3 py-1 rounded-full border border-slate-200">Pengurus & Anggota Tambahan</span>
                                </div>
                                <div class="flex flex-wrap justify-center gap-6">
                                    <div v-for="member in settings.org_bumdes_custom_members" :key="member.id || member.name" class="flex flex-col items-center w-40">
                                        <div class="w-14 h-14 rounded-full border-2 border-slate-300 bg-white flex items-center justify-center shadow-sm text-slate-700 font-bold text-sm overflow-hidden">
                                            <img v-if="member.image" :src="member.image" class="w-full h-full object-cover" />
                                            <span v-else>{{ getInitials(member.name || member.role) }}</span>
                                        </div>
                                        <div class="mt-2 bg-white border border-slate-200 rounded-xl p-2 w-full text-center shadow-sm">
                                            <p class="text-xs font-bold text-slate-800 leading-tight">{{ member.name }}</p>
                                            <p class="text-[8px] font-bold text-blue-600 uppercase tracking-wider mt-0.5">{{ member.role }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Org Chart View -->
                    <div class="md:hidden space-y-6 portal-animate">
                        <!-- Musyawarah Desa -->
                        <div class="flex justify-center">
                            <div class="px-5 py-2 bg-gradient-to-r from-slate-900 to-slate-800 text-white rounded-xl shadow border border-slate-700 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <span class="text-[11px] font-black uppercase tracking-widest text-slate-100">MUSYAWARAH DESA</span>
                            </div>
                        </div>

                        <!-- Level 1: Komisaris, Direktur, Pengawas -->
                        <div class="grid grid-cols-3 gap-2">
                            <!-- Komisaris -->
                            <div class="bg-white border border-rose-200 rounded-xl p-2.5 text-center shadow-xs flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full border-2 border-rose-500 overflow-hidden bg-white mb-2 flex items-center justify-center text-rose-700 font-bold shadow-xs">
                                    <img v-if="settings.org_bumdes_pembina_image" :src="settings.org_bumdes_pembina_image" class="w-full h-full object-cover" />
                                    <span v-else>{{ getInitials(settings.org_bumdes_pembina_name || 'Komisaris') }}</span>
                                </div>
                                <span class="text-[8px] font-extrabold uppercase px-1.5 py-0.5 bg-rose-500 text-white rounded mb-1">KOMISARIS</span>
                                <h5 class="text-[11px] font-bold text-slate-800 leading-tight uppercase">{{ settings.org_bumdes_pembina_name || 'NGADIMAN' }}</h5>
                                <span class="text-[8px] text-rose-600 font-semibold">KEPALA DESA</span>
                            </div>

                            <!-- Direktur -->
                            <div class="bg-white border-2 border-blue-500 rounded-xl p-2.5 text-center shadow-xs flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full border-2 border-blue-600 overflow-hidden bg-white mb-2 flex items-center justify-center text-blue-700 font-bold shadow-xs">
                                    <img v-if="settings.org_bumdes_direktur_image" :src="settings.org_bumdes_direktur_image" class="w-full h-full object-cover" />
                                    <span v-else>{{ getInitials(settings.org_bumdes_direktur_name || 'Direktur') }}</span>
                                </div>
                                <span class="text-[8px] font-extrabold uppercase px-1.5 py-0.5 bg-blue-600 text-white rounded mb-1">DIREKTUR</span>
                                <h5 class="text-[11px] font-bold text-slate-900 leading-tight">{{ settings.org_bumdes_direktur_name || 'P. YULIANTORO, S.E.' }}</h5>
                            </div>

                            <!-- Pengawas -->
                            <div class="bg-white border border-rose-200 rounded-xl p-2.5 text-center shadow-xs flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full border-2 border-rose-500 overflow-hidden bg-white mb-2 flex items-center justify-center text-rose-700 font-bold shadow-xs">
                                    <img v-if="settings.org_bumdes_pengawas_image" :src="settings.org_bumdes_pengawas_image" class="w-full h-full object-cover" />
                                    <span v-else>{{ getInitials(settings.org_bumdes_pengawas_name || 'Pengawas') }}</span>
                                </div>
                                <span class="text-[8px] font-extrabold uppercase px-1.5 py-0.5 bg-rose-500 text-white rounded mb-1">PENGAWAS</span>
                                <h5 class="text-[11px] font-bold text-slate-800 leading-tight uppercase">{{ settings.org_bumdes_pengawas_name || 'SUBARKAH' }}</h5>
                            </div>
                        </div>

                        <!-- Level 2: Sekretaris & Bendahara -->
                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <div class="bg-white border border-slate-200 rounded-xl p-2.5 flex items-center gap-2.5 shadow-xs">
                                <div class="w-11 h-11 shrink-0 rounded-full border-2 border-rose-400 overflow-hidden bg-white flex items-center justify-center text-rose-700 text-xs font-bold">
                                    <img v-if="settings.org_bumdes_sekretaris_image" :src="settings.org_bumdes_sekretaris_image" class="w-full h-full object-cover" />
                                    <span v-else>{{ getInitials(settings.org_bumdes_sekretaris_name || 'Sekretaris') }}</span>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[8px] font-bold uppercase text-rose-600">SEKRETARIS</span>
                                    <h5 class="text-[11px] font-bold text-slate-800 leading-tight truncate">{{ settings.org_bumdes_sekretaris_name || 'SALSA DETRIANI' }}</h5>
                                </div>
                            </div>
                            <div class="bg-white border border-slate-200 rounded-xl p-2.5 flex items-center gap-2.5 shadow-xs">
                                <div class="w-11 h-11 shrink-0 rounded-full border-2 border-rose-400 overflow-hidden bg-white flex items-center justify-center text-rose-700 text-xs font-bold">
                                    <img v-if="settings.org_bumdes_bendahara_image" :src="settings.org_bumdes_bendahara_image" class="w-full h-full object-cover" />
                                    <span v-else>{{ getInitials(settings.org_bumdes_bendahara_name || 'Bendahara') }}</span>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[8px] font-bold uppercase text-rose-600">BENDAHARA</span>
                                    <h5 class="text-[11px] font-bold text-slate-800 leading-tight truncate">{{ settings.org_bumdes_bendahara_name || 'DWI GUNAWAN' }}</h5>
                                </div>
                            </div>
                        </div>

                        <!-- Level 3: 4 Business Units -->
                        <div class="space-y-2.5 pt-2">
                            <h5 class="text-center text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-2">Kepala Unit Usaha</h5>
                            <div v-for="role in [
                                { label: 'Ka. Unit Ketahanan Pangan', name: settings.org_bumdes_unit_pangan_name || 'BUDIMAN', image: settings.org_bumdes_unit_pangan_image, badge: 'bg-emerald-600 text-white', border: 'border-emerald-200' },
                                { label: 'Ka. Unit Perdagangan', name: settings.org_bumdes_unit_pasar_name || 'SUSANTI', image: settings.org_bumdes_unit_pasar_image, badge: 'bg-amber-500 text-white', border: 'border-amber-200' },
                                { label: 'Ka. Unit Simpan Pinjam', name: settings.org_bumdes_unit_sp_name || 'PIPIT DAIKHOTUL KH.', image: settings.org_bumdes_unit_sp_image, badge: 'bg-sky-600 text-white', border: 'border-sky-200' },
                                { label: 'Ka. Unit Internet (Wifi)', name: settings.org_bumdes_unit_wifi_name || 'SITI MARFUAH', image: settings.org_bumdes_unit_wifi_image, badge: 'bg-slate-700 text-white', border: 'border-slate-200' },
                            ]" :key="role.label" class="bg-white border rounded-xl p-3 flex items-center gap-3 shadow-xs" :class="role.border">
                                <div class="w-12 h-12 shrink-0 rounded-full border-2 overflow-hidden bg-slate-50 flex items-center justify-center font-bold text-slate-700">
                                    <img v-if="role.image" :src="role.image" class="w-full h-full object-cover" />
                                    <span v-else>{{ getInitials(role.name) }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <span class="inline-block px-2 py-0.5 text-[8px] font-bold uppercase rounded" :class="role.badge">{{ role.label }}</span>
                                    <h6 class="text-xs font-bold text-slate-800 mt-1 leading-tight">{{ role.name }}</h6>
                                </div>
                            </div>
                        </div>

                        <!-- Anggota & Pengurus Tambahan (Mobile View) -->
                        <div v-if="settings.org_bumdes_custom_members && settings.org_bumdes_custom_members.length > 0" class="space-y-2.5 pt-4 border-t border-slate-200">
                            <h5 class="text-center text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-2">Pengurus & Anggota Tambahan</h5>
                            <div v-for="member in settings.org_bumdes_custom_members" :key="member.id || member.name" class="bg-white border border-slate-200 rounded-xl p-3 flex items-center gap-3 shadow-xs">
                                <div class="w-11 h-11 shrink-0 rounded-full border border-slate-300 overflow-hidden bg-slate-50 flex items-center justify-center font-bold text-slate-700 text-xs">
                                    <img v-if="member.image" :src="member.image" class="w-full h-full object-cover" />
                                    <span v-else>{{ getInitials(member.name || member.role) }}</span>
                                </div>
                                <div>
                                    <h6 class="text-xs font-bold text-slate-800">{{ member.name }}</h6>
                                    <p class="text-[9px] text-blue-600 font-bold uppercase mt-0.5">{{ member.role }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>

        <!-- Profil Unit Bisnis Section (Alternating detail) -->
        <section class="py-20 bg-slate-50 border-b border-[#bfc9bd]">
            <div class="max-w-5xl mx-auto px-6 space-y-16">
                <div class="text-center space-y-2 mb-12 portal-animate">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Profil Unit Bisnis</span>
                    <h3 class="text-xl font-bold text-slate-800">Eksplorasi Detail Unit Kerja BUMDes</h3>
                    <p class="text-xs text-[#5c5f61] max-w-md mx-auto">Mengenal lebih dekat bidang usaha yang dikelola secara profesional demi memajukan perekonomian desa.</p>
                </div>

                <div class="space-y-20">
                    <div v-for="(unit, idx) in units" :key="unit.id" class="grid md:grid-cols-2 gap-8 items-center">
                        <div :class="idx % 2 === 1 ? 'order-1 md:order-2' : ''" class="space-y-4 portal-animate">
                            <span class="inline-block px-2.5 py-0.5 bg-blue-50 text-blue-700 text-[9px] font-bold uppercase rounded-full border border-blue-200">Unit {{ idx + 1 }}</span>
                            <h4 class="text-lg font-bold text-slate-800">{{ unit.nama_unit }}</h4>
                            <p class="text-xs md:text-sm text-[#5c5f61] leading-relaxed">
                                {{ unit.deskripsi }}
                            </p>
                        </div>
                        <div :class="idx % 2 === 1 ? 'order-2 md:order-1' : ''" class="flex items-center justify-center p-2 portal-animate">
                            <div class="w-40 h-40 md:w-52 md:h-52 flex items-center justify-center bg-transparent shrink-0">
                                <img v-if="unit.thumbnail || unit.logo" :src="unit.thumbnail || unit.logo" class="w-full h-full object-contain drop-shadow-sm" />
                                <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-350">
                                    <span class="material-symbols-outlined text-[64px] text-blue-600">{{ unit.icon || 'business' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Unit Usaha Section -->
        <section id="unit-usaha" class="py-20 bg-gradient-to-b from-white to-slate-50 border-b border-[#bfc9bd]">
            <div class="max-w-6xl mx-auto px-6 space-y-12">
                <div class="text-center space-y-3 portal-animate">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">PORTAL INTEGRASI</span>
                    <h3 class="text-2xl font-bold text-slate-800">Unit Usaha BUMDes</h3>
                    <p class="text-xs text-[#404940] max-w-md mx-auto">Akses cepat ke portal aplikasi internal dan website resmi dari berbagai divisi bisnis kami.</p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="(unit, idx) in units" :key="unit.id || idx"
                         class="portal-animate"
                         :style="{ transitionDelay: `${idx * 80}ms` }">
                        <div class="bg-white border border-[#bfc9bd] p-6 rounded-xl hover:border-blue-500 hover:shadow-md transition-all duration-300 flex flex-col justify-between h-full">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="w-10 h-10 flex items-center justify-center shrink-0">
                                        <img v-if="unit.logo || unit.thumbnail" :src="unit.logo || unit.thumbnail" class="w-full h-full object-contain" />
                                        <div v-else class="w-full h-full rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[20px]">{{ unit.icon || 'business' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span :class="getStatusBadge(unit.status).class" class="px-2 py-0.5 border text-[9px] font-bold uppercase tracking-wider rounded">
                                            {{ getStatusBadge(unit.status).text }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-sm mb-1.5">{{ unit.nama_unit }}</h4>
                                    <p class="text-[11px] text-[#5c5f61] leading-relaxed">{{ unit.deskripsi }}</p>
                                </div>
                            </div>
                            <div class="mt-6 pt-4 border-t border-slate-100">
                                <template v-if="unit.status === 'aktif'">
                                    <a v-if="unit.tipe === 'external'" :href="unit.api_url" target="_blank" class="w-full inline-flex items-center justify-center gap-1 px-3 py-2 border border-slate-200 hover:border-blue-500 text-slate-700 hover:text-blue-700 font-bold text-[11px] rounded-lg transition">
                                        Kunjungi Website
                                        <span class="material-symbols-outlined text-[12px]">open_in_new</span>
                                    </a>
                                    <Link v-else :href="unit.slug === 'simpan-pinjam' ? route('welcome') : route('unit.welcome', { slug: unit.slug })" class="w-full inline-flex items-center justify-center gap-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-[11px] rounded-lg transition shadow-sm">
                                        Kunjungi Unit
                                        <span class="material-symbols-outlined text-[12px]">arrow_forward</span>
                                    </Link>
                                </template>
                                <div v-else class="w-full inline-flex items-center justify-center gap-1 px-3 py-2 bg-slate-100 text-slate-400 font-bold text-[11px] rounded-lg cursor-not-allowed">
                                    <span class="material-symbols-outlined text-[12px]">schedule</span>
                                    Segera Hadir
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        <!-- Berita Section -->
        <section id="berita" class="py-20 bg-white border-b border-[#bfc9bd]">
            <div class="max-w-5xl mx-auto px-6 space-y-12">
                <div class="text-center space-y-3 portal-animate">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Kabar Desa</span>
                    <h3 class="text-2xl font-bold text-slate-800">Berita & Informasi Terkini</h3>
                    <p class="text-xs text-[#404940] max-w-md mx-auto">Ikuti perkembangan terbaru dari BUMDes Dammar Wulan.</p>
                </div>

                <div v-if="!posts || posts.length === 0" class="text-center text-xs text-slate-400 py-10 portal-animate">
                    Belum ada berita yang dipublikasikan.
                </div>

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="(post, idx) in posts" :key="post.id"
                         class="portal-animate"
                         :style="{ transitionDelay: `${idx * 100}ms` }">
                        <Link :href="route('portal.berita.detail', post.slug)" class="bg-white border border-[#bfc9bd]/60 rounded-2xl overflow-hidden shadow-sm flex flex-col justify-between hover:shadow-md hover:border-blue-400/45 transition-all duration-300 group cursor-pointer h-full">
                            <div>
                                <div class="relative overflow-hidden aspect-[4/3] bg-slate-100 border-b border-slate-100">
                                    <img v-if="post.thumbnail" :src="post.thumbnail" :alt="post.judul" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                    <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-400 bg-slate-50">
                                        <span class="material-symbols-outlined text-4xl">newspaper</span>
                                    </div>
                                </div>
                                <div class="p-5 space-y-3">
                                    <div class="text-xs font-bold text-blue-700 uppercase tracking-wide">
                                        {{ formatDate(post.published_at) }}
                                    </div>
                                    <h4 class="font-bold text-slate-800 text-sm leading-snug group-hover:text-blue-700 transition-colors line-clamp-2">
                                        {{ post.judul }}
                                    </h4>
                                    <p class="text-xs text-[#5c5f61] leading-relaxed line-clamp-3">
                                        {{ post.ringkasan }}
                                    </p>
                                </div>
                            </div>
                            <div class="px-5 pb-5 pt-0">
                                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 group-hover:text-blue-800 transition">
                                    Baca Selengkapnya
                                    <span class="material-symbols-outlined text-xs font-bold transition-transform group-hover:translate-x-0.5">arrow_forward</span>
                                </span>
                            </div>
                        </Link>
                    </div>
                </div>

                <div v-if="posts && posts.length > 0" class="text-center portal-animate">
                    <Link :href="route('portal.berita.index')" class="inline-flex items-center gap-2 px-6 py-3 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-lg transition">
                        Lihat Semua Berita
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Kontak Section -->
        <section id="kontak" class="py-20 bg-slate-50 border-b border-[#bfc9bd]">
            <div class="max-w-5xl mx-auto px-6">
                <div class="text-center space-y-3 mb-12 portal-animate">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Hubungi Kami</span>
                    <h3 class="text-2xl font-bold text-slate-800">Kontak & Lokasi</h3>
                </div>

                <div class="grid gap-8 md:grid-cols-2">
                    <div class="space-y-6 portal-animate">
                        <div v-if="settings.contact_address" class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">location_on</span>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-800 uppercase tracking-wider">Alamat</p>
                                <p class="text-sm text-[#404940] mt-1">{{ settings.contact_address }}</p>
                            </div>
                        </div>
                        <div v-if="settings.contact_phone" class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">phone</span>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-800 uppercase tracking-wider">Telepon</p>
                                <p class="text-sm text-[#404940] mt-1">{{ settings.contact_phone }}</p>
                            </div>
                        </div>
                        <div v-if="settings.contact_email" class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">email</span>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-800 uppercase tracking-wider">Email</p>
                                <p class="text-sm text-[#404940] mt-1">{{ settings.contact_email }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="portal-animate">
                        <div v-if="settings.google_maps_embed" class="rounded-xl overflow-hidden border border-[#bfc9bd] h-[280px]" v-html="settings.google_maps_embed"></div>
                        <div v-else class="rounded-xl border border-[#bfc9bd] h-[280px] bg-white flex items-center justify-center text-slate-400">
                            <div class="text-center">
                                <span class="material-symbols-outlined text-4xl">map</span>
                                <p class="text-xs mt-2">Lokasi Google Maps</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-10 bg-blue-800 text-white">
            <div class="max-w-5xl mx-auto px-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <img src="/logo2.png" alt="Logo" class="h-10 w-10 object-contain" />
                        <div>
                            <p class="text-sm font-bold">{{ settings.bumdes_name || 'BUMDes Dammar Wulan' }}</p>
                            <p class="text-[10px] text-blue-200">Portal Terintegrasi</p>
                        </div>
                    </div>
                    <p class="text-xs text-blue-200">{{ (settings.footer_text || '© 2024 BUMDES Dammar Wulan. Hak Cipta Dilindungi.').replace(/BUMDesa|BUMDes/ig, 'BUMDES') }}</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.portal-animate {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.portal-animate-show {
    opacity: 1;
    transform: translateY(0);
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@keyframes navSlideDown {
    from {
        transform: translate(-50%, -150%);
        opacity: 0;
    }
    to {
        transform: translate(-50%, 0);
        opacity: 1;
    }
}
.nav-animate {
    animation: navSlideDown 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.fade-unit-enter-active,
.fade-unit-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.fade-unit-enter-from {
    opacity: 0;
    transform: translateY(10px) scale(0.97);
}
.fade-unit-leave-to {
    opacity: 0;
    transform: translateY(-10px) scale(0.97);
}
</style>
