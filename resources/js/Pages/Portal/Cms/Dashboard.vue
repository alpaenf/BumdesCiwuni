<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: { type: Object, required: true },
    recentPosts: { type: Array, default: () => [] },
});

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { dateStyle: 'medium' });
};
</script>

<template>
    <Head title="Dashboard CMS" />

    <PortalLayout>
        <template #header>Dashboard CMS Portal</template>

        <div class="space-y-6">
            <div>
                <h2 class="text-2xl font-bold text-emerald-800">Pengelolaan Website</h2>
                <p class="text-sm text-slate-500">Kelola konten website publik BUMDes Dammar Wulan.</p>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white border border-slate-200 rounded-xl p-5 hover:border-emerald-400 transition-all shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2 bg-emerald-50 rounded-lg text-emerald-700">
                            <span class="material-symbols-outlined">business</span>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">{{ stats.totalUnits }}</p>
                    <p class="text-xs text-slate-500 mt-1">Total Unit</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-5 hover:border-emerald-400 transition-all shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2 bg-emerald-50 rounded-lg text-emerald-700">
                            <span class="material-symbols-outlined">check_circle</span>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-emerald-700">{{ stats.unitAktif }}</p>
                    <p class="text-xs text-slate-500 mt-1">Unit Aktif</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-5 hover:border-emerald-400 transition-all shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2 bg-blue-50 rounded-lg text-blue-700">
                            <span class="material-symbols-outlined">newspaper</span>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">{{ stats.totalPosts }}</p>
                    <p class="text-xs text-slate-500 mt-1">Total Berita</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-5 hover:border-emerald-400 transition-all shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2 bg-violet-50 rounded-lg text-violet-700">
                            <span class="material-symbols-outlined">publish</span>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">{{ stats.publishedPosts }}</p>
                    <p class="text-xs text-slate-500 mt-1">Berita Published</p>
                </div>
            </div>

            <!-- Quick Actions & Recent Posts -->
            <div class="grid grid-cols-12 gap-4">
                <!-- Quick Actions -->
                <div class="col-span-12 lg:col-span-5 bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                    <h4 class="text-base font-bold text-slate-800 mb-4">Aksi Cepat</h4>
                    <div class="space-y-2">
                        <Link :href="route('portal.cms.berita.create')" class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/30 transition-all group">
                            <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-all">
                                <span class="material-symbols-outlined text-lg">edit_note</span>
                            </div>
                            <span class="text-sm font-semibold text-slate-700 group-hover:text-emerald-700">Tulis Berita Baru</span>
                        </Link>
                        <Link :href="route('portal.cms.unit.create')" class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/30 transition-all group">
                            <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-all">
                                <span class="material-symbols-outlined text-lg">add_business</span>
                            </div>
                            <span class="text-sm font-semibold text-slate-700 group-hover:text-emerald-700">Tambah Unit Usaha</span>
                        </Link>
                        <Link :href="route('portal.cms.profil.edit')" class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/30 transition-all group">
                            <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-all">
                                <span class="material-symbols-outlined text-lg">apartment</span>
                            </div>
                            <span class="text-sm font-semibold text-slate-700 group-hover:text-emerald-700">Edit Profil BUMDes</span>
                        </Link>
                    </div>
                </div>

                <!-- Recent Posts -->
                <div class="col-span-12 lg:col-span-7 bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="px-5 py-4 border-b border-slate-200 flex justify-between items-center">
                        <h4 class="text-base font-bold text-slate-800">Berita Terbaru</h4>
                        <Link :href="route('portal.cms.berita.index')" class="text-xs text-emerald-700 font-semibold hover:underline">Semua</Link>
                    </div>
                    <div class="divide-y divide-slate-100">
                        <div v-for="post in recentPosts" :key="post.id" class="px-5 py-3 flex items-center justify-between hover:bg-slate-50 transition">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-700 truncate">{{ post.judul }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ formatDate(post.created_at) }}</p>
                            </div>
                            <span :class="post.status === 'published' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-amber-100 text-amber-700 border-amber-200'" class="px-2 py-0.5 border text-[9px] font-bold uppercase tracking-wider rounded ml-3 shrink-0">
                                {{ post.status }}
                            </span>
                        </div>
                        <div v-if="recentPosts.length === 0" class="px-5 py-8 text-center text-slate-400 text-xs">
                            Belum ada berita.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
