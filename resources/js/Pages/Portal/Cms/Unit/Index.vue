<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({ units: { type: Object, required: true } });

const statusBadge = (s) => ({
    aktif: { text: 'Aktif', class: 'bg-emerald-100 text-emerald-700 border-emerald-200' },
    coming_soon: { text: 'Coming Soon', class: 'bg-amber-100 text-amber-700 border-amber-200' },
    nonaktif: { text: 'Nonaktif', class: 'bg-slate-100 text-slate-500 border-slate-200' },
}[s] || { text: s, class: 'bg-slate-100 text-slate-500 border-slate-200' });

const deleteUnit = (id) => {
    if (confirm('Yakin ingin menghapus unit ini?')) {
        router.delete(route('portal.cms.unit.destroy', id));
    }
};
</script>

<template>
    <Head title="Kelola Unit Usaha" />
    <PortalLayout>
        <template #header>Kelola Unit Usaha</template>
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-emerald-800">Daftar Unit Usaha</h2>
                    <p class="text-sm text-slate-500">Kelola unit usaha BUMDes.</p>
                </div>
                <Link :href="route('portal.cms.unit.create')" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg shadow-sm transition">
                    <span class="material-symbols-outlined text-base">add_business</span>
                    Tambah Unit Baru
                </Link>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3">#</th>
                                <th class="px-5 py-3">Unit</th>
                                <th class="px-5 py-3">Tipe</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="unit in units.data" :key="unit.id" class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3 text-slate-400 font-mono">{{ unit.urutan }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-[18px]">{{ unit.icon || 'business' }}</span>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-700 text-sm">{{ unit.nama_unit }}</p>
                                            <p class="text-[10px] text-slate-400 font-mono">/{{ unit.slug }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <span :class="unit.tipe === 'internal' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-blue-100 text-blue-700 border-blue-200'" class="px-2 py-0.5 border text-[9px] font-bold uppercase tracking-wider rounded">
                                        {{ unit.tipe }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <span :class="statusBadge(unit.status).class" class="px-2 py-0.5 border text-[9px] font-bold uppercase tracking-wider rounded">
                                        {{ statusBadge(unit.status).text }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link :href="route('portal.cms.unit.edit', unit.id)" class="p-1.5 rounded-lg hover:bg-emerald-50 text-slate-500 hover:text-emerald-700 transition">
                                            <span class="material-symbols-outlined text-[16px]">edit</span>
                                        </Link>
                                        <button @click="deleteUnit(unit.id)" class="p-1.5 rounded-lg hover:bg-red-50 text-slate-500 hover:text-red-600 transition">
                                            <span class="material-symbols-outlined text-[16px]">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="units.data.length === 0">
                                <td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada unit usaha.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
