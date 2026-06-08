<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    posts: { type: Object, required: true },
    settings: { type: Object, default: () => ({}) },
});

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { dateStyle: 'long' }) : '';
</script>

<template>
    <Head title="Berita - Portal BUMDes" />

    <div class="min-h-screen bg-white text-[#181d18] font-sans antialiased">
        <!-- Simple Navbar -->
        <header class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-md border-b border-[#bfc9bd]/50 shadow-sm">
            <div class="max-w-5xl mx-auto px-6 flex items-center justify-between h-14">
                <Link href="/" class="flex items-center gap-2">
                    <img src="/logo2.png" alt="Logo" class="h-8 w-8 object-contain" />
                    <span class="text-xs font-bold text-blue-800">{{ settings.bumdes_name || 'BUMDes' }}</span>
                </Link>
                <Link href="/" class="text-xs font-semibold text-slate-500 hover:text-blue-700 transition">← Kembali ke Beranda</Link>
            </div>
        </header>

        <main class="pt-24 pb-20 max-w-5xl mx-auto px-6">
            <div class="text-center space-y-3 mb-12">
                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Kabar Desa</span>
                <h2 class="text-2xl font-bold text-slate-800">Semua Berita & Informasi</h2>
            </div>

            <div v-if="posts.data.length === 0" class="text-center text-sm text-slate-400 py-20">
                Belum ada berita yang dipublikasikan.
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Link v-for="post in posts.data" :key="post.id" :href="route('portal.berita.detail', post.slug)" class="bg-white border border-[#bfc9bd]/60 rounded-2xl overflow-hidden shadow-sm hover:shadow-md hover:border-blue-400/45 transition-all duration-300 group h-full flex flex-col">
                    <div class="relative overflow-hidden aspect-[4/3] bg-slate-100 border-b border-slate-100">
                        <img v-if="post.thumbnail" :src="post.thumbnail" :alt="post.judul" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <div v-else class="w-full h-full flex items-center justify-center text-slate-400 bg-slate-50">
                            <span class="material-symbols-outlined text-4xl">newspaper</span>
                        </div>
                    </div>
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-2">{{ formatDate(post.published_at) }}</div>
                        <h4 class="font-bold text-slate-800 text-sm leading-snug group-hover:text-blue-700 transition-colors line-clamp-2 mb-2">{{ post.judul }}</h4>
                        <p class="text-xs text-[#5c5f61] leading-relaxed line-clamp-3 flex-1">{{ post.ringkasan }}</p>
                        <span class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-blue-700">
                            Baca Selengkapnya
                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </span>
                    </div>
                </Link>
            </div>
        </main>
    </div>
</template>

<style scoped>
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
</style>
