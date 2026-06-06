<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    post: { type: Object, required: true },
    related: { type: Array, default: () => [] },
    settings: { type: Object, default: () => ({}) },
});

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { dateStyle: 'long' }) : '';
</script>

<template>
    <Head :title="post.judul + ' - Berita BUMDes'" />

    <div class="min-h-screen bg-white text-[#181d18] font-sans antialiased">
        <!-- Simple Navbar -->
        <header class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-md border-b border-[#bfc9bd]/50 shadow-sm">
            <div class="max-w-5xl mx-auto px-6 flex items-center justify-between h-14">
                <Link href="/" class="flex items-center gap-2">
                    <img src="/logo2.png" alt="Logo" class="h-8 w-8 object-contain" />
                    <span class="text-xs font-bold text-emerald-800">{{ settings.bumdes_name || 'BUMDes' }}</span>
                </Link>
                <Link :href="route('portal.berita.index')" class="text-xs font-semibold text-slate-500 hover:text-emerald-700 transition">← Semua Berita</Link>
            </div>
        </header>

        <main class="pt-24 pb-20">
            <!-- Article -->
            <article class="max-w-3xl mx-auto px-6">
                <div class="mb-6">
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">{{ formatDate(post.published_at) }}</span>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 mt-2 leading-tight">{{ post.judul }}</h1>
                    <p v-if="post.ringkasan" class="text-sm text-slate-500 mt-3">{{ post.ringkasan }}</p>
                </div>

                <div v-if="post.thumbnail" class="rounded-2xl overflow-hidden border border-slate-200 mb-8">
                    <img :src="post.thumbnail" :alt="post.judul" class="w-full h-auto object-cover" />
                </div>

                <div class="prose prose-sm prose-slate max-w-none text-sm leading-relaxed text-[#404940] whitespace-pre-wrap">
                    {{ post.konten }}
                </div>
            </article>

            <!-- Related -->
            <div v-if="related.length > 0" class="max-w-5xl mx-auto px-6 mt-16">
                <h3 class="text-lg font-bold text-slate-800 mb-6">Berita Lainnya</h3>
                <div class="grid gap-6 md:grid-cols-3">
                    <Link v-for="r in related" :key="r.id" :href="route('portal.berita.detail', r.slug)" class="bg-white border border-[#bfc9bd]/60 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all group">
                        <div class="aspect-[4/3] bg-slate-100 overflow-hidden">
                            <img v-if="r.thumbnail" :src="r.thumbnail" :alt="r.judul" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-400 bg-slate-50">
                                <span class="material-symbols-outlined text-3xl">newspaper</span>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-[10px] font-bold text-emerald-700 uppercase">{{ formatDate(r.published_at) }}</p>
                            <h4 class="text-sm font-bold text-slate-800 group-hover:text-emerald-700 transition-colors mt-1 line-clamp-2">{{ r.judul }}</h4>
                        </div>
                    </Link>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
