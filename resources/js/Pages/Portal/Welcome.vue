<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    settings: { type: Object, required: true },
    orgSettings: { type: Object, default: () => ({}) },
    units: { type: Array, default: () => [] },
    posts: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
});

const isMobileMenuOpen = ref(false);

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return new Intl.DateTimeFormat('id-ID', { dateStyle: 'long' }).format(date);
};

const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num || 0);
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
});
</script>

<template>
    <Head title="Portal BUMDes Dammar Wulan" />

    <div class="min-h-screen bg-white text-[#181d18] font-sans antialiased selection:bg-blue-600 selection:text-white">
        <!-- Header / Navbar -->
        <header :class="[
            'fixed top-4 left-1/2 -translate-x-1/2 z-50 w-[calc(100%-2rem)] max-w-5xl bg-white/90 backdrop-blur-md border border-[#bfc9bd]/70 shadow-lg transition-all duration-300',
            'rounded-full py-2 px-6'
        ]">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <img src="/logo2.png" alt="Logo" class="h-9 w-9 object-contain" />
                    <div>
                        <h1 class="text-xs font-extrabold text-blue-800 leading-tight">{{ settings.bumdes_name || 'BUMDes Dammar Wulan' }}</h1>
                        <p class="text-[9px] text-[#5c5f61] tracking-wider font-semibold uppercase leading-none">Portal Terintegrasi</p>
                    </div>
                </div>

                <nav class="hidden md:flex items-center gap-5">
                    <a href="#tentang" class="text-xs font-bold text-[#404940] hover:text-blue-700 transition">Tentang</a>
                    <a href="#unit-usaha" class="text-xs font-bold text-[#404940] hover:text-blue-700 transition">Unit Usaha</a>
                    <a href="#struktur" class="text-xs font-bold text-[#404940] hover:text-blue-700 transition">Struktur</a>
                    <a href="#berita" class="text-xs font-bold text-[#404940] hover:text-blue-700 transition">Berita</a>
                    <a href="#kontak" class="text-xs font-bold text-[#404940] hover:text-blue-700 transition">Kontak</a>
                    <div class="border-l border-[#bfc9bd] pl-4 flex items-center gap-2">
                        <Link v-if="$page.props.auth?.user" :href="route('portal.cms.dashboard')" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white font-semibold text-xs rounded-lg hover:bg-blue-700 transition shadow-sm">
                            Dashboard CMS
                        </Link>
                        <Link v-else :href="route('portal.login')" class="inline-flex items-center justify-center px-4 py-2 border border-blue-600 text-blue-700 font-semibold text-xs rounded-lg hover:bg-blue-50 transition">
                            Masuk Portal
                        </Link>
                    </div>
                </nav>

                <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="md:hidden w-8 h-8 flex items-center justify-center text-[#404940] hover:text-blue-700 transition">
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
        <section class="relative pt-28 pb-16 overflow-hidden">
            <div class="absolute top-1/4 -right-20 w-80 h-80 bg-blue-500/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 -left-20 w-80 h-80 bg-blue-700/5 rounded-full blur-3xl"></div>

            <div class="max-w-4xl mx-auto px-6 text-center relative z-10 space-y-6">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-800 border border-blue-100 text-[10px] font-bold uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                    Portal Terintegrasi BUMDes
                </span>

                <h2 class="text-3xl md:text-5xl font-extrabold text-blue-800 leading-tight md:leading-none tracking-tight">
                    {{ settings.hero_title || 'Portal Resmi BUMDesa Dammar Wulan' }}
                </h2>

                <p class="text-sm md:text-base text-[#404940] max-w-2xl mx-auto leading-relaxed">
                    {{ settings.hero_subtitle || 'Lembaga ekonomi desa yang berdedikasi mengelola berbagai unit bisnis dan layanan masyarakat.' }}
                </p>

                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a :href="settings.hero_cta_link || '#unit-usaha'" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-lg shadow-sm transition">
                        {{ settings.hero_cta_text || 'Lihat Unit Usaha' }}
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                    <Link :href="route('welcome')" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-lg shadow-sm transition">
                        Portal Simpan Pinjam
                    </Link>
                </div>
            </div>
        </section>

        <!-- Tentang Section -->
        <section id="tentang" class="pt-16 pb-20 bg-white border-b border-[#bfc9bd]">
            <div class="max-w-5xl mx-auto px-6 grid gap-10 md:grid-cols-2 items-center">
                <div class="space-y-5 portal-animate">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Tentang Kami</span>
                    <h3 class="text-2xl font-bold text-slate-800 leading-tight">
                        {{ settings.about_title || 'Mengenal BUMDesa Dammar Wulan' }}
                    </h3>
                    <p class="text-sm text-[#404940] leading-relaxed">
                        {{ settings.about_description || '' }}
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

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
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

        <!-- Struktur Organisasi Section -->
        <section id="struktur" class="py-20 bg-white border-b border-[#bfc9bd]">
            <div class="max-w-6xl mx-auto px-6">
                <div class="text-center space-y-3 mb-16 portal-animate">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Kepengurusan</span>
                    <h3 class="text-2xl font-bold text-slate-800">Struktur Organisasi BUMDesa</h3>
                    <p class="text-xs text-[#404940] max-w-md mx-auto">Jajaran pengurus BUMDesa Dammar Wulan Desa Ciwuni periode 2024–2028 yang berkomitmen melayani masyarakat.</p>
                </div>
                
                <!-- Desktop Org Chart Container (Connected Tree) -->
                <div class="hidden md:block overflow-x-auto pb-4">
                    <div class="relative min-w-[700px] max-w-4xl mx-auto">
                        <!-- Level 1: Pembina -->
                        <div class="flex justify-center mb-0">
                            <div class="flex flex-col items-center portal-animate">
                                <div class="relative group cursor-default">
                                    <div class="w-20 h-20 rounded-full border-4 border-blue-600 bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-lg text-white font-extrabold text-xl ring-4 ring-blue-500/20 group-hover:scale-105 transition-all duration-300 overflow-hidden">
                                        <img v-if="orgSettings.org_pembina_image" :src="orgSettings.org_pembina_image" class="w-full h-full object-cover" />
                                        <span v-else>SR</span>
                                    </div>
                                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-blue-500 rounded-full border-2 border-white flex items-center justify-center">
                                        <span class="material-symbols-outlined text-white text-[10px]" style="font-size:10px">verified</span>
                                    </span>
                                </div>
                                <div class="mt-3 text-center">
                                    <p class="text-xs font-extrabold text-slate-800">{{ orgSettings.org_pembina_name || 'Surya Ramdhani' }}</p>
                                    <span class="mt-1 inline-block px-2.5 py-0.5 bg-blue-600 text-white text-[9px] font-bold uppercase tracking-wider rounded-full">Pembina</span>
                                </div>
                            </div>
                        </div>

                        <!-- Connector: L1 → L2 -->
                        <div class="flex justify-center portal-animate" style="transition-delay: 80ms">
                            <div class="w-0.5 h-10 bg-blue-500/60"></div>
                        </div>

                        <!-- Level 2: Direktur -->
                        <div class="flex justify-center mb-0">
                            <div class="flex flex-col items-center portal-animate" style="transition-delay: 150ms">
                                <div class="relative group cursor-default">
                                    <div class="w-20 h-20 rounded-full border-4 border-blue-600 bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-lg text-white font-extrabold text-xl ring-4 ring-blue-500/20 group-hover:scale-105 transition-all duration-300 overflow-hidden">
                                        <img v-if="orgSettings.org_direktur_image" :src="orgSettings.org_direktur_image" class="w-full h-full object-cover" />
                                        <span v-else>AH</span>
                                    </div>
                                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-blue-500 rounded-full border-2 border-white flex items-center justify-center">
                                        <span class="material-symbols-outlined text-white" style="font-size:10px">star</span>
                                    </span>
                                </div>
                                <div class="mt-3 text-center">
                                    <p class="text-xs font-extrabold text-slate-800">{{ orgSettings.org_direktur_name || 'Ahmad Hidayat' }}</p>
                                    <span class="mt-1 inline-block px-2.5 py-0.5 bg-blue-600 text-white text-[9px] font-bold uppercase tracking-wider rounded-full">Direktur</span>
                                </div>
                            </div>
                        </div>

                        <!-- Connector: L2 → L3 (horizontal rail) -->
                        <div class="relative flex justify-center mt-0 portal-animate" style="transition-delay: 220ms">
                            <div class="w-0.5 h-10 bg-blue-500/60"></div>
                        </div>

                        <!-- Level 3: Sekretaris + Bendahara + Pengawas -->
                        <div class="relative flex justify-center gap-0">
                            <!-- Horizontal line spanning all 3 children -->
                            <div class="absolute top-0 left-1/4 right-1/4 h-0.5 bg-blue-500/40"></div>

                            <!-- Left branch vertical -->
                            <div class="absolute top-0 left-1/4 w-0.5 h-10 bg-blue-500/40" style="transform: translateX(-50%)"></div>
                            <!-- Right branch vertical -->
                            <div class="absolute top-0 right-1/4 w-0.5 h-10 bg-blue-500/40" style="transform: translateX(50%)"></div>
                            <!-- Center branch vertical -->
                            <div class="absolute top-0 left-1/2 w-0.5 h-10 bg-blue-500/40" style="transform: translateX(-50%)"></div>

                            <div class="grid grid-cols-3 gap-10 w-full max-w-2xl pt-10">
                                <!-- Sekretaris -->
                                <div class="flex flex-col items-center portal-animate" style="transition-delay: 300ms">
                                    <div class="relative group cursor-default">
                                        <div class="w-16 h-16 rounded-full border-4 border-blue-600 bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-md text-white font-extrabold text-base ring-4 ring-blue-50/20 group-hover:scale-105 transition-all duration-300 overflow-hidden">
                                            <img v-if="orgSettings.org_sekretaris_image" :src="orgSettings.org_sekretaris_image" class="w-full h-full object-cover" />
                                            <span v-else>DP</span>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <p class="text-[10px] font-extrabold text-slate-800">{{ orgSettings.org_sekretaris_name || 'Dewi Puspitasari' }}</p>
                                        <span class="mt-1 inline-block px-2 py-0.5 bg-blue-600 text-white text-[8px] font-bold uppercase tracking-wider rounded-full">Sekretaris</span>
                                    </div>
                                </div>

                                <!-- Bendahara -->
                                <div class="flex flex-col items-center portal-animate" style="transition-delay: 380ms">
                                    <div class="relative group cursor-default">
                                        <div class="w-16 h-16 rounded-full border-4 border-blue-600 bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-md text-white font-extrabold text-base ring-4 ring-blue-50/20 group-hover:scale-105 transition-all duration-300 overflow-hidden">
                                            <img v-if="orgSettings.org_bendahara_image" :src="orgSettings.org_bendahara_image" class="w-full h-full object-cover" />
                                            <span v-else>RS</span>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <p class="text-[10px] font-extrabold text-slate-800">{{ orgSettings.org_bendahara_name || 'Rini Setiawati' }}</p>
                                        <span class="mt-1 inline-block px-2 py-0.5 bg-blue-600 text-white text-[8px] font-bold uppercase tracking-wider rounded-full">Bendahara</span>
                                    </div>
                                </div>

                                <!-- Pengawas -->
                                <div class="flex flex-col items-center portal-animate" style="transition-delay: 460ms">
                                    <div class="relative group cursor-default">
                                        <div class="w-16 h-16 rounded-full border-4 border-blue-600 bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-md text-white font-extrabold text-base ring-4 ring-blue-50/20 group-hover:scale-105 transition-all duration-300 overflow-hidden">
                                            <img v-if="orgSettings.org_pengawas_image" :src="orgSettings.org_pengawas_image" class="w-full h-full object-cover" />
                                            <span v-else>BW</span>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <p class="text-[10px] font-extrabold text-slate-800">{{ orgSettings.org_pengawas_name || 'Bambang Wibowo' }}</p>
                                        <span class="mt-1 inline-block px-2 py-0.5 bg-blue-600 text-white text-[8px] font-bold uppercase tracking-wider rounded-full">Pengawas</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Connector to unit heads -->
                        <div class="flex justify-center mt-6 portal-animate" style="transition-delay: 520ms">
                            <div class="w-0.5 h-10 bg-blue-500/40"></div>
                        </div>

                        <!-- Level 4: Kepala Unit -->
                        <div class="relative">
                            <!-- Horizontal bar spanning columns -->
                            <div class="absolute top-0 left-[5%] right-[5%] h-0.5 bg-blue-500/30"></div>

                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 pt-10">
                                <!-- Unit Simpan Pinjam -->
                                <div class="flex flex-col items-center group relative portal-animate" style="transition-delay: 600ms">
                                    <div class="absolute -top-10 left-1/2 w-0.5 h-10 bg-blue-500/30" style="transform:translateX(-50%)"></div>
                                    <div class="relative cursor-default">
                                        <div class="w-14 h-14 rounded-2xl border-2 border-blue-400 bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center shadow text-blue-700 font-extrabold text-sm group-hover:scale-105 transition-all duration-300 overflow-hidden">
                                            <img v-if="orgSettings.org_unit_sp_image" :src="orgSettings.org_unit_sp_image" class="w-full h-full object-cover" />
                                            <span v-else class="material-symbols-outlined text-2xl">savings</span>
                                        </div>
                                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-blue-500 rounded-full border-2 border-white"></div>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <p class="text-[9px] font-extrabold text-slate-800">{{ orgSettings.org_unit_sp_name || 'Fajar Nugroho' }}</p>
                                        <p class="text-[8px] text-slate-500 mt-0.5">Ka. Unit</p>
                                        <span class="mt-1 inline-block px-2 py-0.5 bg-blue-100 text-blue-700 text-[7px] font-bold uppercase tracking-wider rounded-full border border-blue-200">Simpan Pinjam</span>
                                    </div>
                                </div>

                                <!-- Unit KP-SPAMS -->
                                <div class="flex flex-col items-center group relative portal-animate" style="transition-delay: 680ms">
                                    <div class="absolute -top-10 left-1/2 w-0.5 h-10 bg-blue-500/30" style="transform:translateX(-50%)"></div>
                                    <div class="relative cursor-default">
                                        <div class="w-14 h-14 rounded-2xl border-2 border-blue-400 bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center shadow text-blue-700 font-extrabold text-sm group-hover:scale-105 transition-all duration-300 overflow-hidden">
                                            <img v-if="orgSettings.org_unit_kpspams_image" :src="orgSettings.org_unit_kpspams_image" class="w-full h-full object-cover" />
                                            <span v-else class="material-symbols-outlined text-2xl">water_drop</span>
                                        </div>
                                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-blue-500 rounded-full border-2 border-white"></div>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <p class="text-[9px] font-extrabold text-slate-800">{{ orgSettings.org_unit_kpspams_name || 'Mulyadi' }}</p>
                                        <p class="text-[8px] text-slate-500 mt-0.5">Ka. Unit</p>
                                        <span class="mt-1 inline-block px-2 py-0.5 bg-blue-100 text-blue-700 text-[7px] font-bold uppercase tracking-wider rounded-full border border-blue-200">KP-SPAMS</span>
                                    </div>
                                </div>

                                <!-- Unit Toko / Sembako -->
                                <div class="flex flex-col items-center group relative portal-animate" style="transition-delay: 760ms">
                                    <div class="absolute -top-10 left-1/2 w-0.5 h-10 bg-blue-500/30" style="transform:translateX(-50%)"></div>
                                    <div class="relative cursor-default">
                                        <div class="w-14 h-14 rounded-2xl border-2 border-blue-400 bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center shadow text-blue-700 font-extrabold text-sm group-hover:scale-105 transition-all duration-300 overflow-hidden">
                                            <img v-if="orgSettings.org_unit_toko_image" :src="orgSettings.org_unit_toko_image" class="w-full h-full object-cover" />
                                            <span v-else class="material-symbols-outlined text-2xl">storefront</span>
                                        </div>
                                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-blue-500 rounded-full border-2 border-white"></div>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <p class="text-[9px] font-extrabold text-slate-800">{{ orgSettings.org_unit_toko_name || 'Siti Aminah' }}</p>
                                        <p class="text-[8px] text-slate-500 mt-0.5">Ka. Unit</p>
                                        <span class="mt-1 inline-block px-2 py-0.5 bg-blue-100 text-blue-700 text-[7px] font-bold uppercase tracking-wider rounded-full border border-blue-200">Toko Desa</span>
                                    </div>
                                </div>

                                <!-- Unit Wisata -->
                                <div class="flex flex-col items-center group relative portal-animate" style="transition-delay: 840ms">
                                    <div class="absolute -top-10 left-1/2 w-0.5 h-10 bg-blue-500/30" style="transform:translateX(-50%)"></div>
                                    <div class="relative cursor-default">
                                        <div class="w-14 h-14 rounded-2xl border-2 border-blue-400 bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center shadow text-blue-700 font-extrabold text-sm group-hover:scale-105 transition-all duration-300 overflow-hidden">
                                            <img v-if="orgSettings.org_unit_wisata_image" :src="orgSettings.org_unit_wisata_image" class="w-full h-full object-cover" />
                                            <span v-else class="material-symbols-outlined text-2xl">forest</span>
                                        </div>
                                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-blue-500 rounded-full border-2 border-white"></div>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <p class="text-[9px] font-extrabold text-slate-800">{{ orgSettings.org_unit_wisata_name || 'Eko Prasetyo' }}</p>
                                        <p class="text-[8px] text-slate-500 mt-0.5">Ka. Unit</p>
                                        <span class="mt-1 inline-block px-2 py-0.5 bg-blue-100 text-blue-700 text-[7px] font-bold uppercase tracking-wider rounded-full border border-blue-200">Wisata</span>
                                    </div>
                                </div>

                                <!-- Unit TPS3R -->
                                <div class="flex flex-col items-center group relative portal-animate col-span-2 md:col-span-1" style="transition-delay: 920ms">
                                    <div class="absolute -top-10 left-1/2 w-0.5 h-10 bg-blue-500/30" style="transform:translateX(-50%)"></div>
                                    <div class="relative cursor-default">
                                        <div class="w-14 h-14 rounded-2xl border-2 border-blue-400 bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center shadow text-blue-700 font-extrabold text-sm group-hover:scale-105 transition-all duration-300 overflow-hidden">
                                            <img v-if="orgSettings.org_unit_tps3r_image" :src="orgSettings.org_unit_tps3r_image" class="w-full h-full object-cover" />
                                            <span v-else class="material-symbols-outlined text-2xl">delete_sweep</span>
                                        </div>
                                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-blue-500 rounded-full border-2 border-white"></div>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <p class="text-[9px] font-extrabold text-slate-800">{{ orgSettings.org_unit_tps3r_name || 'Budi Santoso' }}</p>
                                        <p class="text-[8px] text-slate-500 mt-0.5">Ka. Unit</p>
                                        <span class="mt-1 inline-block px-2 py-0.5 bg-blue-100 text-blue-700 text-[7px] font-bold uppercase tracking-wider rounded-full border border-blue-200">TPS3R</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Note -->
                        <p class="text-center text-[10px] text-slate-400 mt-12 portal-animate" style="transition-delay: 1000ms">
                            * Nama-nama di atas dapat diperbarui sesuai kepengurusan aktif BUMDesa Dammar Wulan.
                        </p>
                    </div>
                </div>

                <!-- Mobile Org Chart View -->
                <div class="md:hidden space-y-8">
                    <!-- Pembina -->
                    <div class="flex flex-col items-center">
                        <span class="text-[9px] font-bold uppercase tracking-widest text-blue-700 mb-2">Pembina</span>
                        <div class="w-full max-w-[280px] bg-blue-50/50 border border-blue-100 rounded-2xl p-4 flex items-center gap-4 portal-animate">
                            <div class="w-14 h-14 rounded-full border-2 border-blue-600 overflow-hidden shrink-0 shadow-sm bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white font-bold">
                                <img v-if="orgSettings.org_pembina_image" :src="orgSettings.org_pembina_image" class="w-full h-full object-cover" />
                                <span v-else>SR</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-extrabold text-slate-800">{{ orgSettings.org_pembina_name || 'Surya Ramdhani' }}</h4>
                                <p class="text-[10px] font-bold text-blue-700 mt-0.5">Pembina Utama</p>
                            </div>
                        </div>
                    </div>

                    <!-- Line separator -->
                    <div class="flex justify-center -my-2 portal-animate">
                        <div class="w-0.5 h-6 bg-blue-500/30"></div>
                    </div>

                    <!-- Direktur -->
                    <div class="flex flex-col items-center">
                        <span class="text-[9px] font-bold uppercase tracking-widest text-blue-700 mb-2">Direksi Utama</span>
                        <div class="w-full max-w-[280px] bg-blue-50/50 border border-blue-100 rounded-2xl p-4 flex items-center gap-4 portal-animate">
                            <div class="w-14 h-14 rounded-full border-2 border-blue-600 overflow-hidden shrink-0 shadow-sm bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white font-bold">
                                <img v-if="orgSettings.org_direktur_image" :src="orgSettings.org_direktur_image" class="w-full h-full object-cover" />
                                <span v-else>AH</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-extrabold text-slate-800">{{ orgSettings.org_direktur_name || 'Ahmad Hidayat' }}</h4>
                                <p class="text-[10px] font-bold text-blue-700 mt-0.5">Direktur</p>
                            </div>
                        </div>
                    </div>

                    <!-- Line separator -->
                    <div class="flex justify-center -my-2 portal-animate">
                        <div class="w-0.5 h-6 bg-blue-500/30"></div>
                    </div>

                    <!-- Sekretaris, Bendahara, Pengawas Grid -->
                    <div class="space-y-3">
                        <div class="text-center portal-animate">
                            <span class="text-[9px] font-bold uppercase tracking-widest text-blue-700">Manajemen & Pengawas</span>
                        </div>
                        <div class="grid grid-cols-1 gap-3 max-w-[280px] mx-auto">
                            <!-- Sekretaris -->
                            <div class="bg-white border border-[#bfc9bd] rounded-2xl p-3 flex items-center gap-3 portal-animate">
                                <div class="w-12 h-12 rounded-full border-2 border-blue-600 overflow-hidden shrink-0 bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white font-bold text-xs">
                                    <img v-if="orgSettings.org_sekretaris_image" :src="orgSettings.org_sekretaris_image" class="w-full h-full object-cover" />
                                    <span v-else>DP</span>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800 leading-tight">{{ orgSettings.org_sekretaris_name || 'Dewi Puspitasari' }}</h4>
                                    <p class="text-[9px] text-slate-500 font-semibold uppercase tracking-wider mt-0.5">Sekretaris</p>
                                </div>
                            </div>
                            
                            <!-- Bendahara -->
                            <div class="bg-white border border-[#bfc9bd] rounded-2xl p-3 flex items-center gap-3 portal-animate">
                                <div class="w-12 h-12 rounded-full border-2 border-blue-600 overflow-hidden shrink-0 bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white font-bold text-xs">
                                    <img v-if="orgSettings.org_bendahara_image" :src="orgSettings.org_bendahara_image" class="w-full h-full object-cover" />
                                    <span v-else>RS</span>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800 leading-tight">{{ orgSettings.org_bendahara_name || 'Rini Setiawati' }}</h4>
                                    <p class="text-[9px] text-slate-500 font-semibold uppercase tracking-wider mt-0.5">Bendahara</p>
                                </div>
                            </div>

                            <!-- Pengawas -->
                            <div class="bg-white border border-[#bfc9bd] rounded-2xl p-3 flex items-center gap-3 portal-animate">
                                <div class="w-12 h-12 rounded-full border-2 border-blue-600 overflow-hidden shrink-0 bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white font-bold text-xs">
                                    <img v-if="orgSettings.org_pengawas_image" :src="orgSettings.org_pengawas_image" class="w-full h-full object-cover" />
                                    <span v-else>BW</span>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800 leading-tight">{{ orgSettings.org_pengawas_name || 'Bambang Wibowo' }}</h4>
                                    <p class="text-[9px] text-slate-500 font-semibold uppercase tracking-wider mt-0.5">Pengawas</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Line separator -->
                    <div class="flex justify-center -my-2 portal-animate">
                        <div class="w-0.5 h-6 bg-blue-500/30"></div>
                    </div>

                    <!-- Kepala Unit -->
                    <div class="space-y-3">
                        <div class="text-center portal-animate">
                            <span class="text-[9px] font-bold uppercase tracking-widest text-blue-700">Kepala Unit Kerja</span>
                        </div>
                        <div class="grid grid-cols-1 gap-3 max-w-[280px] mx-auto">
                            <!-- Simpan Pinjam -->
                            <div class="bg-white border border-[#bfc9bd] rounded-2xl p-3 flex items-center gap-3 portal-animate">
                                <div class="w-12 h-12 rounded-xl border border-blue-400 overflow-hidden shrink-0 bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center text-blue-700 font-bold">
                                    <img v-if="orgSettings.org_unit_sp_image" :src="orgSettings.org_unit_sp_image" class="w-full h-full object-cover" />
                                    <span v-else class="material-symbols-outlined text-xl">savings</span>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800 leading-tight">{{ orgSettings.org_unit_sp_name || 'Fajar Nugroho' }}</h4>
                                    <p class="text-[9px] text-blue-600 font-bold mt-0.5">Ka. Unit Simpan Pinjam</p>
                                </div>
                            </div>

                            <!-- KP-SPAMS -->
                            <div class="bg-white border border-[#bfc9bd] rounded-2xl p-3 flex items-center gap-3 portal-animate">
                                <div class="w-12 h-12 rounded-xl border border-blue-400 overflow-hidden shrink-0 bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center text-blue-700 font-bold">
                                    <img v-if="orgSettings.org_unit_kpspams_image" :src="orgSettings.org_unit_kpspams_image" class="w-full h-full object-cover" />
                                    <span v-else class="material-symbols-outlined text-xl">water_drop</span>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800 leading-tight">{{ orgSettings.org_unit_kpspams_name || 'Mulyadi' }}</h4>
                                    <p class="text-[9px] text-blue-600 font-bold mt-0.5">Ka. Unit KP-SPAMS</p>
                                </div>
                            </div>

                            <!-- Toko Desa -->
                            <div class="bg-white border border-[#bfc9bd] rounded-2xl p-3 flex items-center gap-3 portal-animate">
                                <div class="w-12 h-12 rounded-xl border border-blue-400 overflow-hidden shrink-0 bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center text-blue-700 font-bold">
                                    <img v-if="orgSettings.org_unit_toko_image" :src="orgSettings.org_unit_toko_image" class="w-full h-full object-cover" />
                                    <span v-else class="material-symbols-outlined text-xl">storefront</span>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800 leading-tight">{{ orgSettings.org_unit_toko_name || 'Siti Aminah' }}</h4>
                                    <p class="text-[9px] text-blue-600 font-bold mt-0.5">Ka. Unit Toko Desa</p>
                                </div>
                            </div>

                            <!-- Wisata -->
                            <div class="bg-white border border-[#bfc9bd] rounded-2xl p-3 flex items-center gap-3 portal-animate">
                                <div class="w-12 h-12 rounded-xl border border-blue-400 overflow-hidden shrink-0 bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center text-blue-700 font-bold">
                                    <img v-if="orgSettings.org_unit_wisata_image" :src="orgSettings.org_unit_wisata_image" class="w-full h-full object-cover" />
                                    <span v-else class="material-symbols-outlined text-xl">forest</span>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800 leading-tight">{{ orgSettings.org_unit_wisata_name || 'Eko Prasetyo' }}</h4>
                                    <p class="text-[9px] text-blue-600 font-bold mt-0.5">Ka. Unit Wisata Desa</p>
                                </div>
                            </div>

                            <!-- TPS3R -->
                            <div class="bg-white border border-[#bfc9bd] rounded-2xl p-3 flex items-center gap-3 portal-animate">
                                <div class="w-12 h-12 rounded-xl border border-blue-400 overflow-hidden shrink-0 bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center text-blue-700 font-bold">
                                    <img v-if="orgSettings.org_unit_tps3r_image" :src="orgSettings.org_unit_tps3r_image" class="w-full h-full object-cover" />
                                    <span v-else class="material-symbols-outlined text-xl">delete_sweep</span>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800 leading-tight">{{ orgSettings.org_unit_tps3r_name || 'Budi Santoso' }}</h4>
                                    <p class="text-[9px] text-blue-600 font-bold mt-0.5">Ka. Unit TPS3R</p>
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
                    <p class="text-xs text-blue-200">{{ settings.footer_text || '© 2024 BUMDesa Dammar Wulan. Hak Cipta Dilindungi.' }}</p>
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
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
