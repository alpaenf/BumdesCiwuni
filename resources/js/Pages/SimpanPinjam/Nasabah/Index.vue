<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import WaTemplateModal from '@/Components/WaTemplateModal.vue';

const props = defineProps({
    nasabah: Object,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');
const status = ref(props.filters?.status ?? '');

let searchTimeout;
watch([search, status], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('nasabah.index'), { search: search.value, status: status.value }, { preserveState: true, replace: true });
    }, 400);
});

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';

const deleteNasabah = (id, nama) => {
    if (!confirm(`Hapus nasabah "${nama}"? Tindakan ini tidak bisa dibatalkan.`)) return;
    router.delete(route('nasabah.destroy', id));
};

// WA modal
const waModal = ref(null);
const selectedNasabahWa = ref(null);

function openWaModal(row) {
    selectedNasabahWa.value = row;
    waModal.value?.open();
}
</script>

<template>
    <Head title="Data Nasabah" />
    <AuthenticatedLayout>
        <template #header>Data Nasabah</template>

        <div class="space-y-5">
            <!-- Header bar -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-[color:var(--color-on-surface)]">Daftar Nasabah</h2>
                    <p class="text-sm text-[color:var(--color-secondary)]">Total {{ nasabah.total }} nasabah terdaftar</p>
                </div>
                <Link
                    :href="route('nasabah.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 active:scale-95"
                >
                    <span class="material-symbols-outlined text-base">person_add</span>
                    Tambah Nasabah
                </Link>
            </div>

            <!-- Filters -->
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-base text-[color:var(--color-on-surface-variant)]">search</span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama, NIK, nomor rekening..."
                        class="w-full rounded-lg border border-[color:var(--color-outline-variant)] bg-white py-2.5 pl-10 pr-4 text-sm focus:border-[color:var(--color-primary)] focus:outline-none focus:ring-2 focus:ring-[color:var(--color-primary)]/20"
                    />
                </div>
                <select
                    v-model="status"
                    class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:border-[color:var(--color-primary)] focus:outline-none"
                >
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="tidak aktif">Tidak Aktif</option>
                </select>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-[color:var(--color-outline-variant)] bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-[color:var(--color-outline-variant)] bg-[color:var(--color-surface-container-low)]">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">No. Reg</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">No. Rekening</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Nama Lengkap</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">NIK</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">No. WA</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Bergabung</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[color:var(--color-outline-variant)]">
                            <tr v-if="nasabah.data.length === 0">
                                <td colspan="8" class="px-4 py-12 text-center text-[color:var(--color-secondary)]">
                                    <span class="material-symbols-outlined mb-2 block text-4xl text-[color:var(--color-outline-variant)]">person_off</span>
                                    Tidak ada data nasabah
                                </td>
                            </tr>
                            <tr
                                v-for="row in nasabah.data"
                                :key="row.id"
                                class="transition hover:bg-[color:var(--color-surface-container-low)]"
                            >
                                <td class="px-4 py-3 font-mono text-xs text-[color:var(--color-secondary)]">{{ row.nomor_registrasi }}</td>
                                <td class="px-4 py-3 font-mono text-xs font-semibold text-[color:var(--color-primary)]">{{ row.nomor_rekening }}</td>
                                <td class="px-4 py-3 font-semibold text-[color:var(--color-on-surface)]">{{ row.nama }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-[color:var(--color-secondary)]">{{ row.nik }}</td>
                                <td class="px-4 py-3">
                                    <button @click="openWaModal(row)"
                                        class="flex items-center gap-1 text-green-600 hover:text-green-700 hover:underline text-xs font-medium transition">
                                        <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.122 1.534 5.855L.054 23.272a.75.75 0 00.917.928l5.528-1.451A11.93 11.93 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.723 9.723 0 01-5.02-1.394l-.36-.213-3.73.979.997-3.645-.235-.374A9.718 9.718 0 012.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/>
                                        </svg>
                                        {{ row.no_hp }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-[color:var(--color-secondary)]">{{ formatDate(row.tanggal_bergabung) }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        :class="row.status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600'"
                                    >
                                        {{ row.status === 'aktif' ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link :href="route('nasabah.show', row.id)" class="rounded-lg p-1.5 text-[color:var(--color-on-surface-variant)] transition hover:bg-[color:var(--color-surface-container)]" title="Detail">
                                            <span class="material-symbols-outlined text-base">visibility</span>
                                        </Link>
                                        <Link :href="route('nasabah.edit', row.id)" class="rounded-lg p-1.5 text-blue-600 transition hover:bg-blue-50" title="Edit">
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </Link>
                                        <button @click="deleteNasabah(row.id, row.nama)" class="rounded-lg p-1.5 text-red-600 transition hover:bg-red-50" title="Hapus">
                                            <span class="material-symbols-outlined text-base">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="nasabah.last_page > 1" class="flex items-center justify-between border-t border-[color:var(--color-outline-variant)] px-4 py-3">
                    <p class="text-sm text-[color:var(--color-secondary)]">
                        Menampilkan {{ nasabah.from }}–{{ nasabah.to }} dari {{ nasabah.total }}
                    </p>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in nasabah.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            v-html="link.label"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
                            :class="link.active
                                ? 'bg-[color:var(--color-primary)] text-white'
                                : link.url ? 'text-[color:var(--color-on-surface-variant)] hover:bg-[color:var(--color-surface-container)]' : 'cursor-not-allowed text-[color:var(--color-outline-variant)]'"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- WA Modal -->
    <WaTemplateModal
        v-if="selectedNasabahWa"
        ref="waModal"
        :nasabah="selectedNasabahWa"
        :pinjaman-aktif="selectedNasabahWa?.pinjaman?.filter(p => p.status === 'aktif') ?? []"
    />
</template>
