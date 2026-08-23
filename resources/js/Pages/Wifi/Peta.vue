<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Fix Leaflet default icon path issue with Vite
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
    iconUrl:       new URL('leaflet/dist/images/marker-icon.png',   import.meta.url).href,
    shadowUrl:     new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
});

const props = defineProps({
    unit:         { type: Object,  required: true },
    user:         { type: Object,  required: true },
    pelanggan:    { type: Array,   default: () => [] },
    tanpaGps:     { type: Number,  default: 0 },
    paketOptions: { type: Array,   default: () => [] },
    rtOptions:    { type: Array,   default: () => [] },
    rwOptions:    { type: Array,   default: () => [] },
    stats:        { type: Object,  default: () => ({}) },
});

// ── Sidebar & UI state ─────────────────────────────────────────────────────
const isSidebarOpen      = ref(false);
const isFilterOpen       = ref(false);
const isMobileFilterOpen = ref(false);
const selectedPelanggan  = ref(null);
const isSideListOpen     = ref(false);
const logout = () => router.post(route('logout'));

// ── Filters ────────────────────────────────────────────────────────────────
const filterPaket  = ref('');
const filterStatus = ref('');
const filterRt     = ref('');
const filterRw     = ref('');
const searchNama   = ref('');

const filtered = computed(() => {
    return props.pelanggan.filter(p => {
        const st = p.current_status || p.status_1_15;
        if (filterPaket.value  && p.paket  !== filterPaket.value)  return false;
        if (filterStatus.value && st       !== filterStatus.value) return false;
        if (filterRt.value     && p.rt     !== filterRt.value)     return false;
        if (filterRw.value     && p.rw     !== filterRw.value)     return false;
        if (searchNama.value && !p.nama.toLowerCase().includes(searchNama.value.toLowerCase())) return false;
        return true;
    });
});

const legendStats = computed(() => {
    const counts = { AKTIF: 0, ISOLIR: 0 };
    props.pelanggan.forEach(p => {
        const st = p.current_status || p.status_1_15;
        if (st === 'ISOLIR') counts.ISOLIR++;
        else                 counts.AKTIF++;
    });
    return counts;
});

// ── Map ────────────────────────────────────────────────────────────────────
let map       = null;
let markerGroup = null;
const mapRef  = ref(null);

// Custom icon factory keyed by status_1_15
const markerIcon = (status) => {
    const isIsolir = status === 'ISOLIR';
    const c = isIsolir
        ? { bg: '#ef4444', border: '#dc2626' }
        : { bg: '#10b981', border: '#059669' };
    const svg = `
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="36" viewBox="0 0 28 36">
          <path d="M14 0C6.27 0 0 6.27 0 14c0 10.5 14 22 14 22S28 24.5 28 14C28 6.27 21.73 0 14 0z"
                fill="${c.bg}" stroke="${c.border}" stroke-width="2"/>
          <circle cx="14" cy="14" r="5" fill="white" opacity="0.9"/>
        </svg>`;
    return L.divIcon({
        html: svg,
        className: '',
        iconSize:    [28, 36],
        iconAnchor:  [14, 36],
        popupAnchor: [0, -36],
    });
};

const rupiah = (val) => {
    if (!val && val !== 0) return '-';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
};

const formatDate = (val) => {
    if (!val) return '-';
    try { return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }); }
    catch { return val; }
};

const statusClass = (s) => {
    if (s === 'ISOLIR') return 'bg-red-100 text-red-700 border-red-300';
    return 'bg-emerald-100 text-emerald-700 border-emerald-300';
};

const buildPopup = (p) => {
    const fotoHtml = p.foto_rumah
        ? `<div style="margin:-12px -12px 8px -12px;height:100px;overflow:hidden;background:#f1f5f9"><img src="/uploads/pelanggan_wifi/${p.foto_rumah}" style="width:100%;height:100%;object-fit:cover" /></div>`
        : '';
    const statusVal = p.current_status || p.status_1_15 || '-';
    const isIsolir = statusVal === 'ISOLIR';
    const statusBg = isIsolir ? '#fef2f2' : '#ecfdf5';
    const statusColor = isIsolir ? '#b91c1c' : '#047857';
    const statusBorder = isIsolir ? '#fca5a5' : '#6ee7b7';
    const dotBg = isIsolir ? '#ef4444' : '#10b981';

    return `
        <div style="padding:12px;font-family:system-ui,-apple-system,sans-serif;color:#334155">
            ${fotoHtml}
            <div style="margin-bottom:6px">
                <h4 style="font-size:13px;font-weight:800;color:#0f172a;margin:0;line-height:1.3">${p.nama}</h4>
                <p style="font-size:10px;color:#64748b;margin:2px 0 0;font-family:monospace">No. ${p.no ?? '-'} &bull; ID: ${p.no_id_pel ?? p.id}</p>
            </div>

            <div style="display:flex;align-items:center;gap:4px;margin-bottom:8px;flex-wrap:wrap">
                ${p.paket ? `<span style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;padding:1px 6px;border-radius:6px;font-size:10px;font-weight:700;font-family:monospace">${p.paket}</span>` : ''}
                <span style="display:inline-flex;align-items:center;gap:4px;padding:1px 6px;border-radius:9999px;font-size:10px;font-weight:800;background:${statusBg};color:${statusColor};border:1px solid ${statusBorder}">
                    <span style="width:6px;height:6px;border-radius:50%;background:${dotBg};display:inline-block"></span>
                    ${isIsolir ? 'ISOLIR' : 'AKTIF'}
                </span>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:6px 8px;margin-bottom:8px">
                <div>
                    <span style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;display:block">RT/RW</span>
                    <span style="font-size:10px;font-weight:600;color:#1e293b">${p.rt ?? '-'}/${p.rw ?? '-'}</span>
                </div>
                <div>
                    <span style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;display:block">Masa Bayar</span>
                    <span style="font-size:10px;font-weight:700;color:#2563eb">Tgl 1 - 10</span>
                </div>
                <div style="grid-column:1/-1;margin-top:2px">
                    <span style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;display:block">Total Tarikan</span>
                    <span style="font-size:11px;font-weight:800;color:#0f172a;font-family:monospace">${rupiah(p.total_tarikan)}</span>
                </div>
            </div>

            <div style="display:flex;gap:4px">
                <a href="https://www.google.com/maps?q=${p.gps_lat},${p.gps_long}" target="_blank"
                   style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:4px;padding:5px 8px;background:#2563eb;color:#ffffff;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none">
                   Google Maps
                </a>
            </div>
        </div>`;
};

const renderMarkers = () => {
    if (!map || !markerGroup) return;
    markerGroup.clearLayers();

    filtered.value.forEach(p => {
        if (!p.gps_lat || !p.gps_long) return;
        const marker = L.marker([p.gps_lat, p.gps_long], { icon: markerIcon(p.current_status || p.status_1_15) });
        marker.bindPopup(buildPopup(p), { maxWidth: 280, minWidth: 220, autoPan: true, autoPanPadding: [20, 20] });
        marker.on('click', () => { selectedPelanggan.value = p; });
        markerGroup.addLayer(marker);
    });

    // Fit bounds if we have markers
    if (markerGroup.getLayers().length > 0) {
        map.fitBounds(markerGroup.getBounds(), { padding: [40, 40], maxZoom: 16 });
    }
};

// Watch filter changes → re-render markers
watch(filtered, () => renderMarkers());

onMounted(async () => {
    await nextTick();

    // Center default: Jawa Tengah (fallback)
    const defaultCenter = props.pelanggan.length > 0
        ? [props.pelanggan[0].gps_lat ?? -7.3305694, props.pelanggan[0].gps_long ?? 109.2296654]
        : [-7.3305694, 109.2296654];

    map = L.map(mapRef.value, {
        center:  defaultCenter,
        zoom:    14,
        zoomControl: true,
    });

    // OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);

    // Marker group
    markerGroup = L.featureGroup().addTo(map);

    renderMarkers();
});

watch([filterPaket, filterStatus, filterRt, filterRw, searchNama], () => {
    renderMarkers();
});

onUnmounted(() => {
    if (map) { map.remove(); map = null; }
});

const flyToMarker = (p) => {
    if (!map || !p.gps_lat || !p.gps_long) return;
    map.flyTo([p.gps_lat, p.gps_long], 17, { duration: 1 });
    selectedPelanggan.value = p;
    isSideListOpen.value = false;
};

const activeFilterCount = computed(() =>
    [filterPaket.value, filterStatus.value, filterRt.value, filterRw.value].filter(Boolean).length
);
const resetFilters = () => {
    filterPaket.value = ''; filterStatus.value = '';
    filterRt.value = ''; filterRw.value = ''; searchNama.value = '';
};
</script>

<template>
    <Head title="Peta Pelanggan WiFi" />

    <div class="min-h-screen bg-slate-50 text-slate-800 flex font-sans">

        <!-- Mobile Sidebar Backdrop -->
        <div v-if="isSidebarOpen" @click="isSidebarOpen = false"
             class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 md:hidden"></div>

        <!-- ══ SIDEBAR ════════════════════════════════════════════════════════ -->
        <aside :class="['fixed md:sticky top-0 h-screen z-50 w-64 bg-white border-r border-slate-300 shrink-0 flex flex-col transition-transform duration-300',
                        isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0']">
            <!-- Branding (Fixed top) -->
            <div class="p-6 pb-4 shrink-0 flex items-center justify-between gap-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <img src="/logowifi.png" alt="Logo WiFi" class="w-10 h-10 object-contain drop-shadow-sm" />
                    <div>
                        <h2 class="text-xs font-black text-slate-900 leading-tight">Admin Unit WiFi</h2>
                        <p class="text-[9px] text-slate-500 uppercase tracking-widest font-semibold mt-0.5">BUMDes Ciwuni</p>
                    </div>
                </div>
                <button @click="isSidebarOpen = false" aria-label="Tutup sidebar"
                        class="md:hidden text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Nav (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                <nav class="space-y-1">
                    <Link :href="route('unit.dashboard', { slug: unit.slug })"
                          class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">dashboard</span>
                        Dashboard
                    </Link>
                    <Link :href="route('wifi.pelanggan.index')"
                          class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">group</span>
                        Pelanggan
                    </Link>
                    <a href="#"
                       class="flex items-center gap-3 px-4 py-3 bg-blue-600/10 text-blue-600 font-bold text-xs rounded-xl border border-blue-600/20">
                        <span class="material-symbols-outlined text-lg">map</span>
                        Peta Pelanggan
                    </a>
                    <Link :href="route('wifi.pembayaran.index')"
                          class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">payments</span>
                        Kasir &amp; Pembayaran
                    </Link>
                    <Link :href="route('wifi.provider.index')"
                          class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">cell_tower</span>
                        Master Provider
                    </Link>
                    <Link :href="route('wifi.laporan.index')"
                          class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">summarize</span>
                        Laporan WiFi
                    </Link>
                    <Link :href="route('unit.settings.edit', { slug: unit.slug })"
                          class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">web</span>
                        Pengaturan Landing Page
                    </Link>

                    <!-- Section Portal BUMDes (Hanya tampil untuk Super Admin / Manager Pusat) -->
                    <div v-if="(user?.role || $page?.props?.auth?.user?.role) === 'admin' || (user?.role || $page?.props?.auth?.user?.role) === 'manager_pusat'" class="pt-4 mt-4 border-t border-slate-200 space-y-1">
                        <p class="px-4 text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Portal BUMDes</p>
                        <Link :href="route('portal.exec.dashboard')"
                              class="flex items-center gap-3 px-4 py-2.5 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                            <span class="material-symbols-outlined text-lg">hub</span>
                            Dashboard Portal
                        </Link>
                        <Link v-if="(user?.role || $page?.props?.auth?.user?.role) === 'admin'" :href="route('portal.cms.dashboard')"
                              class="flex items-center gap-3 px-4 py-2.5 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                            <span class="material-symbols-outlined text-lg">edit_note</span>
                            Kelola Website
                        </Link>
                        <Link :href="route('dashboard')"
                              class="flex items-center gap-3 px-4 py-2.5 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                            <span class="material-symbols-outlined text-lg">account_balance</span>
                            Unit Simpan Pinjam
                        </Link>
                        <a :href="route('unit.welcome', { slug: 'ketahanan-pangan' })" target="_blank"
                           class="flex items-center gap-3 px-4 py-2.5 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                            <span class="material-symbols-outlined text-lg">agriculture</span>
                            Unit Ketahanan Pangan
                        </a>
                        <a :href="route('unit.welcome', { slug: 'perdagangan-besar' })" target="_blank"
                           class="flex items-center gap-3 px-4 py-2.5 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                            <span class="material-symbols-outlined text-lg">local_shipping</span>
                            Unit Perdagangan Besar
                        </a>
                    </div>
                </nav>
            </div>

            <!-- User Info & Logout (Fixed bottom) -->
            <div class="p-6 border-t border-slate-300 shrink-0 space-y-4 bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                        {{ user.nama.charAt(0) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-bold text-slate-900 truncate">{{ user.nama }}</p>
                        <p class="text-[9px] text-slate-500 truncate">{{ user.email }}</p>
                    </div>
                </div>
                <button @click="logout" aria-label="Keluar"
                        class="w-full flex items-center justify-center gap-2 py-2.5 bg-slate-50 border border-slate-200 hover:bg-red-950/20 hover:border-red-900/30 text-slate-500 hover:text-red-400 text-xs font-bold rounded-xl transition">
                    Keluar Aplikasi
                    <span class="material-symbols-outlined text-sm">logout</span>
                </button>
            </div>
        </aside>

        <!-- ══ MAIN ═══════════════════════════════════════════════════════════ -->
        <main class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">

            <!-- Top Nav -->
            <header class="sticky top-0 z-30 h-16 border-b border-slate-300 bg-white/80 backdrop-blur-md px-4 sm:px-6 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <button @click="isSidebarOpen = true" aria-label="Buka sidebar"
                            class="md:hidden p-1.5 -ml-2 text-slate-500 hover:text-slate-800 rounded-lg transition mr-1">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <span class="material-symbols-outlined text-blue-400 text-lg hidden sm:block">map</span>
                    <span class="text-xs font-bold text-slate-600">Peta Sebaran Pelanggan WiFi</span>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Stat chips -->
                    <span class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 border border-blue-200 text-blue-700 rounded-full text-[10px] font-bold">
                        <span class="material-symbols-outlined text-xs">location_on</span>
                        {{ filtered.length }} titik tampil
                    </span>
                    <span v-if="tanpaGps > 0"
                          class="hidden md:inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 border border-slate-200 text-slate-500 rounded-full text-[10px] font-semibold"
                          :title="`${tanpaGps} pelanggan tanpa koordinat GPS`">
                        <span class="material-symbols-outlined text-xs">location_off</span>
                        {{ tanpaGps }} tanpa GPS
                    </span>
                </div>
            </header>

            <!-- MAP AREA: full height minus header -->
            <div class="flex-1 relative overflow-hidden">

                <!-- ── MAP ────────────────────────────────────────────────── -->
                <div ref="mapRef" class="absolute inset-0 z-0"></div>

                <!-- ── FLOATING FILTER TOOLBAR ───────────────────────────── -->
                <div class="absolute top-3 left-3 right-3 z-[400] pointer-events-none">
                    <!-- Desktop Filter Bar (sm+) -->
                    <div class="hidden sm:flex flex-wrap gap-2 pointer-events-auto">

                        <!-- Search nama -->
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                            <input v-model="searchNama" type="search" placeholder="Cari nama..."
                                   class="pl-8 pr-3 py-2 text-xs bg-white border border-slate-200 rounded-xl shadow-md focus:border-blue-500 focus:outline-none w-44" />
                        </div>

                        <!-- Filter Paket -->
                        <select v-model="filterPaket"
                                class="px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl shadow-md focus:border-blue-500 focus:outline-none">
                            <option value="">Semua Paket</option>
                            <option v-for="p in paketOptions" :key="p" :value="p">{{ p }}</option>
                        </select>

                        <!-- Filter Status -->
                        <select v-model="filterStatus"
                                class="px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl shadow-md focus:border-blue-500 focus:outline-none">
                            <option value="">Semua Status</option>
                            <option value="AKTIF">Aktif</option>
                            <option value="ISOLIR">Isolir</option>
                        </select>

                        <!-- Filter RT -->
                        <select v-model="filterRt"
                                class="px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl shadow-md focus:border-blue-500 focus:outline-none">
                            <option value="">Semua RT</option>
                            <option v-for="r in rtOptions" :key="r" :value="r">RT {{ r }}</option>
                        </select>

                        <!-- Filter RW -->
                        <select v-model="filterRw"
                                class="px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl shadow-md focus:border-blue-500 focus:outline-none">
                            <option value="">Semua RW</option>
                            <option v-for="r in rwOptions" :key="r" :value="r">RW {{ r }}</option>
                        </select>

                        <!-- Reset -->
                        <button v-if="activeFilterCount > 0" @click="resetFilters"
                                aria-label="Reset filter" title="Reset semua filter"
                                class="px-3 py-2 text-xs bg-white border border-red-200 text-red-500 rounded-xl shadow-md hover:bg-red-50 transition flex items-center gap-1 font-semibold">
                            <span class="material-symbols-outlined text-sm">filter_list_off</span>
                            Reset
                        </button>

                        <!-- Daftar Pelanggan toggle -->
                        <button @click="isSideListOpen = !isSideListOpen"
                                aria-label="Toggle daftar pelanggan"
                                class="ml-auto px-3 py-2 text-xs bg-white border border-slate-200 text-slate-700 rounded-xl shadow-md hover:border-blue-400 hover:text-blue-600 transition flex items-center gap-1.5 font-semibold">
                            <span class="material-symbols-outlined text-sm">format_list_bulleted</span>
                            Daftar ({{ filtered.length }})
                        </button>
                    </div>

                    <!-- Mobile Compact Top Control Bar (< sm) -->
                    <div class="sm:hidden flex items-center gap-1.5 pointer-events-auto">
                        <div class="relative flex-1">
                            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                            <input v-model="searchNama" type="search" placeholder="Cari nama..."
                                   class="w-full pl-8 pr-2 py-2 text-xs bg-white border border-slate-200 rounded-xl shadow-lg focus:border-blue-500 focus:outline-none" />
                        </div>
                        <button @click="isMobileFilterOpen = !isMobileFilterOpen"
                                :class="['px-2.5 py-2 text-xs border rounded-xl shadow-lg transition font-bold flex items-center gap-1 shrink-0',
                                         isMobileFilterOpen || activeFilterCount > 0 ? 'bg-blue-600 border-blue-600 text-white' : 'bg-white border-slate-200 text-slate-700']">
                            <span class="material-symbols-outlined text-sm">filter_list</span>
                            <span v-if="activeFilterCount > 0" class="w-4 h-4 rounded-full bg-white text-blue-600 text-[10px] font-black flex items-center justify-center">{{ activeFilterCount }}</span>
                        </button>
                        <button @click="isSideListOpen = !isSideListOpen"
                                class="px-2.5 py-2 text-xs bg-white border border-slate-200 text-slate-700 rounded-xl shadow-lg font-bold flex items-center gap-1 shrink-0">
                            <span class="material-symbols-outlined text-sm">format_list_bulleted</span>
                            ({{ filtered.length }})
                        </button>
                    </div>

                    <!-- Mobile Collapsible Filter Panel -->
                    <div v-if="isMobileFilterOpen" class="sm:hidden mt-2 p-3 bg-white/95 backdrop-blur-md border border-slate-200 rounded-2xl shadow-xl pointer-events-auto grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[9px] font-extrabold text-slate-400 uppercase mb-1">Paket</label>
                            <select v-model="filterPaket" class="w-full px-2 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg">
                                <option value="">Semua Paket</option>
                                <option v-for="p in paketOptions" :key="p" :value="p">{{ p }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[9px] font-extrabold text-slate-400 uppercase mb-1">Status</label>
                            <select v-model="filterStatus" class="w-full px-2 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg">
                                <option value="">Semua Status</option>
                                <option value="AKTIF">Aktif</option>
                                <option value="ISOLIR">Isolir</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[9px] font-extrabold text-slate-400 uppercase mb-1">RT</label>
                            <select v-model="filterRt" class="w-full px-2 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg">
                                <option value="">Semua RT</option>
                                <option v-for="r in rtOptions" :key="r" :value="r">RT {{ r }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[9px] font-extrabold text-slate-400 uppercase mb-1">RW</label>
                            <select v-model="filterRw" class="w-full px-2 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg">
                                <option value="">Semua RW</option>
                                <option v-for="r in rwOptions" :key="r" :value="r">RW {{ r }}</option>
                            </select>
                        </div>
                        <div class="col-span-2 flex justify-end pt-1">
                            <button v-if="activeFilterCount > 0" @click="resetFilters" class="text-xs text-red-500 font-bold flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">filter_list_off</span> Reset Filter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ── LEGEND ─────────────────────────────────────────────── -->
                <div :class="[selectedPelanggan ? 'hidden sm:block' : '']"
                     class="absolute bottom-4 left-3 z-[390] bg-white/95 backdrop-blur-sm border border-slate-200 rounded-xl shadow-lg px-3 py-2.5 space-y-1.5 min-w-[150px] sm:min-w-[170px]">
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Legenda Status Tagihan</p>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 shrink-0"></span>
                            <span class="text-[11px] font-semibold text-slate-700">Aktif</span>
                        </div>
                        <span class="text-[10px] text-slate-700 font-bold font-mono">{{ legendStats.AKTIF }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded-full bg-red-500 shrink-0"></span>
                            <span class="text-[11px] font-semibold text-slate-700">Isolir</span>
                        </div>
                        <span class="text-[10px] text-slate-700 font-bold font-mono">{{ legendStats.ISOLIR }}</span>
                    </div>
                    <div class="border-t border-slate-100 pt-1.5 mt-1">
                        <p class="text-[10px] text-slate-400">
                            <span class="font-bold text-slate-600">{{ stats.ada_gps ?? 0 }}</span> dari
                            <span class="font-bold text-slate-600">{{ stats.total ?? 0 }}</span> pelanggan dipetakan
                        </p>
                    </div>
                </div>

                <!-- ── SELECTED PELANGGAN CARD ────────────────────────────── -->
                <Transition enter-active-class="transition-all duration-200 ease-out"
                            enter-from-class="opacity-0 translate-y-2 sm:translate-y-2"
                            leave-active-class="transition-all duration-150 ease-in"
                            leave-to-class="opacity-0 translate-y-2 sm:translate-y-2">
                    <div v-if="selectedPelanggan"
                         class="fixed sm:absolute bottom-0 sm:bottom-4 left-0 right-0 sm:left-auto sm:right-3 z-[450] w-full sm:w-72 bg-white border-t sm:border border-slate-200 rounded-t-2xl sm:rounded-2xl shadow-2xl overflow-hidden max-h-[85vh] overflow-y-auto">
                        <!-- Foto header -->
                        <div class="relative h-28 bg-slate-100">
                            <img v-if="selectedPelanggan.foto_rumah"
                                 :src="`/uploads/pelanggan_wifi/${selectedPelanggan.foto_rumah}`"
                                 alt="Foto Rumah"
                                 class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <span class="material-symbols-outlined text-slate-300 text-4xl">home</span>
                            </div>
                            <!-- Status badge overlay -->
                            <div class="absolute top-2 left-2 flex gap-1">
                                <span v-if="selectedPelanggan.status_1_15"
                                      :class="statusClass(selectedPelanggan.status_1_15)"
                                      class="px-2 py-0.5 rounded-full text-[10px] font-extrabold border uppercase backdrop-blur-sm">
                                    1-15: {{ selectedPelanggan.status_1_15 }}
                                </span>
                            </div>
                            <button @click="selectedPelanggan = null" aria-label="Tutup detail"
                                    class="absolute top-2 right-2 w-6 h-6 bg-white/90 rounded-full flex items-center justify-center text-slate-500 hover:text-slate-900 shadow">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>

                        <!-- Info -->
                        <div class="p-3 space-y-2">
                            <div>
                                <p class="font-extrabold text-slate-900 text-sm leading-tight">{{ selectedPelanggan.nama }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">
                                    No. {{ selectedPelanggan.no ?? '-' }}
                                    <span v-if="selectedPelanggan.paket"
                                          class="ml-1 px-1.5 py-0.5 bg-blue-100 text-blue-700 border border-blue-200 rounded text-[10px] font-semibold">
                                        {{ selectedPelanggan.paket }}
                                    </span>
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-[11px]">
                                <div>
                                    <span class="text-slate-400 text-[10px] block">Alamat</span>
                                    <span class="font-semibold text-slate-700 leading-tight block">
                                        {{ selectedPelanggan.alamat || '-' }} RT {{ selectedPelanggan.rt ?? '-' }}/{{ selectedPelanggan.rw ?? '-' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-slate-400 text-[10px] block">Masa Bayar</span>
                                    <span class="font-bold text-blue-700 text-[11px]">
                                        Tanggal 1 - 10
                                    </span>
                                </div>
                                <div>
                                    <span class="text-slate-400 text-[10px] block">Status Tagihan</span>
                                    <span :class="statusClass(selectedPelanggan.current_status || selectedPelanggan.status_1_15)"
                                          class="inline-flex px-1.5 py-0.5 rounded-full text-[10px] font-extrabold border uppercase">
                                        {{ selectedPelanggan.current_status || selectedPelanggan.status_1_15 || 'Belum bayar' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-slate-400 text-[10px] block">Total Tarikan</span>
                                    <span class="font-bold text-slate-800 text-[11px]">
                                        {{ selectedPelanggan.total_tarikan
                                            ? new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(selectedPelanggan.total_tarikan)
                                            : '-' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Action buttons -->
                            <div class="flex gap-2 pt-1">
                                <a :href="`https://www.google.com/maps?q=${selectedPelanggan.gps_lat},${selectedPelanggan.gps_long}`"
                                   target="_blank" aria-label="Buka di Google Maps"
                                   class="flex-1 flex items-center justify-center gap-1 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-[11px] font-bold rounded-lg transition">
                                    <span class="material-symbols-outlined text-sm">map</span>
                                    Google Maps
                                </a>
                                <Link :href="route('wifi.pelanggan.index') + `?search=${selectedPelanggan.nama}`"
                                      aria-label="Edit data pelanggan"
                                      class="flex items-center justify-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-[11px] font-bold rounded-lg transition">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </Transition>

                <!-- ── SIDE LIST PANEL ─────────────────────────────────────── -->
                <Transition enter-active-class="transition-all duration-200 ease-out"
                            enter-from-class="opacity-0 translate-x-4"
                            leave-active-class="transition-all duration-150 ease-in"
                            leave-to-class="opacity-0 translate-x-4">
                    <div v-if="isSideListOpen"
                         class="absolute top-14 left-3 right-3 sm:left-auto sm:right-3 bottom-3 z-[450] w-auto sm:w-80 bg-white/98 backdrop-blur-sm border border-slate-200 rounded-2xl shadow-2xl flex flex-col overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50 shrink-0">
                            <p class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">
                                Daftar Titik ({{ filtered.length }})
                            </p>
                            <button @click="isSideListOpen = false" aria-label="Tutup daftar"
                                    class="p-1 text-slate-400 hover:text-slate-700 rounded-lg">
                                <span class="material-symbols-outlined text-base">close</span>
                            </button>
                        </div>

                        <!-- Empty -->
                        <div v-if="filtered.length === 0"
                             class="flex-1 flex flex-col items-center justify-center py-8 text-slate-400">
                            <span class="material-symbols-outlined text-4xl mb-2">location_off</span>
                            <p class="text-xs font-semibold">Tidak ada titik yang ditampilkan</p>
                            <p class="text-[11px] mt-1">Ubah filter untuk melihat lebih banyak</p>
                        </div>

                        <div v-else class="flex-1 overflow-y-auto divide-y divide-slate-100">
                            <button v-for="p in filtered" :key="p.id" @click="flyToMarker(p)"
                                    class="w-full text-left px-4 py-3 hover:bg-blue-50 transition flex items-start gap-3">
                                <!-- Status dot -->
                                <span :class="(p.current_status || p.status_1_15) === 'ISOLIR' ? 'bg-red-500' : 'bg-emerald-500'" class="w-2.5 h-2.5 rounded-full mt-0.5 shrink-0"></span>

                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-slate-900 text-xs truncate">{{ p.nama }}</p>
                                    <p class="text-[10px] text-slate-500 truncate">
                                        RT {{ p.rt ?? '-' }}/{{ p.rw ?? '-' }}
                                        <span v-if="p.paket" class="ml-1 text-blue-600">· {{ p.paket }}</span>
                                    </p>
                                </div>
                                <span class="material-symbols-outlined text-slate-300 text-sm shrink-0">chevron_right</span>
                            </button>
                        </div>
                    </div>
                </Transition>

                <!-- Empty State Overlay (no GPS data at all) -->
                <div v-if="pelanggan.length === 0"
                     class="absolute inset-0 z-[500] flex flex-col items-center justify-center bg-white/90 backdrop-blur-sm">
                    <span class="material-symbols-outlined text-slate-300 text-6xl mb-4">location_off</span>
                    <h2 class="text-base font-extrabold text-slate-700 mb-1">Belum ada data koordinat GPS</h2>
                    <p class="text-xs text-slate-400 text-center max-w-xs">
                        Tambahkan koordinat GPS Longitude dan Latitude pada data pelanggan agar lokasinya muncul di peta ini.
                    </p>
                    <Link :href="route('wifi.pelanggan.index')"
                          class="mt-5 inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow transition">
                        <span class="material-symbols-outlined text-base">group</span>
                        Ke Halaman Pelanggan
                    </Link>
                </div>

            </div><!-- /map area -->
        </main>
    </div>
</template>

<style>
/* Leaflet Popup Responsive Styling Override */
.leaflet-popup-content-wrapper {
    padding: 0 !important;
    border-radius: 16px !important;
    box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.25), 0 8px 10px -6px rgba(15, 23, 42, 0.15) !important;
    overflow: hidden !important;
    border: 1px solid #e2e8f0 !important;
    background: #ffffff !important;
}
.leaflet-popup-content {
    margin: 0 !important;
    width: 250px !important;
    max-width: 80vw !important;
    line-height: 1.4 !important;
}
.leaflet-container a.leaflet-popup-close-button {
    top: 8px !important;
    right: 8px !important;
    color: #64748b !important;
    padding: 4px !important;
    width: 24px !important;
    height: 24px !important;
    border-radius: 9999px !important;
    background: rgba(255, 255, 255, 0.85) !important;
    backdrop-filter: blur(4px) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
    z-index: 10 !important;
}
.leaflet-container a.leaflet-popup-close-button:hover {
    color: #0f172a !important;
    background: #ffffff !important;
}
</style>
