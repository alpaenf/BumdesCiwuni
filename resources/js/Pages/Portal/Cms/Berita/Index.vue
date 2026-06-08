<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    posts: { type: Object, required: true },
});

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { dateStyle: 'medium' }) : '-';

const deletePost = (id) => {
    if (confirm('Yakin ingin menghapus berita ini?')) {
        router.delete(route('portal.cms.berita.destroy', id));
    }
};
</script>

<template>
    <Head title="Kelola Berita" />
    <PortalLayout>
        <template #header>Kelola Berita</template>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-blue-800">Daftar Berita</h2>
                    <p class="text-sm text-slate-500">Kelola artikel dan berita website.</p>
                </div>
                <Link :href="route('portal.cms.berita.create')" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-lg shadow-sm transition">
                    <span class="material-symbols-outlined text-base">add</span>
                    Tulis Berita Baru
                </Link>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3">Judul</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="post in posts.data" :key="post.id" class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-slate-700 text-sm">{{ post.judul }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5 font-mono">/berita/{{ post.slug }}</p>
                                </td>
                                <td class="px-5 py-3">
                                    <span :class="post.status === 'published' ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-amber-100 text-amber-700 border-amber-200'" class="px-2 py-0.5 border text-[9px] font-bold uppercase tracking-wider rounded">
                                        {{ post.status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-slate-500">{{ formatDate(post.published_at || post.created_at) }}</td>
                                <td class="px-5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link :href="route('portal.cms.berita.edit', post.id)" class="p-1.5 rounded-lg hover:bg-blue-50 text-slate-500 hover:text-blue-700 transition">
                                            <span class="material-symbols-outlined text-[16px]">edit</span>
                                        </Link>
                                        <button @click="deletePost(post.id)" class="p-1.5 rounded-lg hover:bg-red-50 text-slate-500 hover:text-red-600 transition">
                                            <span class="material-symbols-outlined text-[16px]">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="posts.data.length === 0">
                                <td colspan="4" class="px-5 py-10 text-center text-slate-400">Belum ada berita.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
