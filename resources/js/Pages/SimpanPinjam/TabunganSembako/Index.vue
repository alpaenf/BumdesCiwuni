<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';

const props = defineProps({ nasabah: Object, filters: Object, flash: Object });

// Auto-open struk in new tab after submit
onMounted(() => {
    if (props.flash?.struk_url) {
        window.open(props.flash.struk_url, '_blank');
    }
});

const search = ref(props.filters?.search ?? '');
let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('tabungan-sembako.index'), { search: search.value }, { preserveState: true, replace: true });
    }, 400);
});
const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);
const getSembako = (nasabah) => nasabah.tabungan?.find(t => t.jenis_tabungan === 'sembako');

// Struk modal state
const showModal = ref(false);
const modalNasabah = ref(null);
const modalTransaksi = ref([]);
const modalLoading = ref(false);
const modalError = ref('');

async function openStrukModal(nasabah) {
    modalNasabah.value = nasabah;
    modalTransaksi.value = [];
    modalError.value = '';
    modalLoading.value = true;
    showModal.value = true;

    try {
        const res = await fetch(route('tabungan-sembako.riwayat', nasabah.id));
        if (!res.ok) throw new Error('Gagal memuat riwayat');
        modalTransaksi.value = await res.json();
    } catch (e) {
        modalError.value = 'Gagal memuat riwayat transaksi.';
    } finally {
        modalLoading.value = false;
    }
}

function closeModal() {
    showModal.value = false;
    modalNasabah.value = null;
    modalTransaksi.value = [];
}

function openStruk(transaksiId) {
    window.open(route('tabungan-sembako.struk', transaksiId), '_blank');
}

function sendWaStruk(trx) {
    if (!modalNasabah.value?.no_hp) return;
    let no = (modalNasabah.value.no_hp || '').replace(/\D/g, '');
    if (no.startsWith('0')) no = '62' + no.slice(1);
    if (!no.startsWith('62')) no = '62' + no;

    const strukUrl = route('tabungan-sembako.struk', trx.id);
    const jenis = trx.jenis_transaksi === 'setor' ? 'Setoran' : 'Pengambilan';
    const pesan =
`Assalamu'alaikum, Bapak/Ibu *${modalNasabah.value.nama}*.

Berikut informasi transaksi tabungan sembako Anda di *BUMDes Dammar Wulan*:

No. Transaksi : #${trx.nomor_transaksi}
Tanggal       : ${formatTanggal(trx.tanggal)}
Jenis         : ${jenis}
Nominal       : ${formatCurrency(trx.nominal)}

Lihat struk lengkap di:
${strukUrl}

Terima kasih.`;

    window.open(`https://wa.me/${no}?text=${encodeURIComponent(pesan)}`, '_blank');
}

function formatTanggal(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>

<template>
    <Head title="Tabungan Sembako" />
    <AuthenticatedLayout>
        <template #header>Tabungan Sembako</template>
        <div class="space-y-5">
            <!-- Flash Success Banner -->
            <div v-if="flash?.success" class="flex items-center justify-between gap-4 rounded-xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-base text-orange-600">check_circle</span>
                    <span>{{ flash.success }} — Struk dibuka di tab baru.</span>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Daftar Tabungan Sembako</h2>
                    <p class="text-sm text-[color:var(--color-secondary)]">Total {{ nasabah.total }} nasabah</p>
                </div>
            </div>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-base text-[color:var(--color-on-surface-variant)]">search</span>
                <input v-model="search" type="text" placeholder="Cari nama atau nomor rekening..."
                    class="w-full rounded-lg border border-[color:var(--color-outline-variant)] bg-white py-2.5 pl-10 pr-4 text-sm focus:border-[color:var(--color-primary)] focus:outline-none" />
            </div>
            <div class="overflow-x-auto rounded-xl border border-[color:var(--color-outline-variant)] bg-white shadow-sm">
                <table class="w-full text-sm min-w-[800px]">
                    <thead>
                        <tr class="border-b border-[color:var(--color-outline-variant)] bg-[color:var(--color-surface-container-low)]">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">No. Rekening</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Nama Nasabah</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Saldo Sembako</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[color:var(--color-outline-variant)]">
                        <tr v-if="nasabah.data.length === 0">
                            <td colspan="4" class="px-4 py-12 text-center text-[color:var(--color-secondary)]">Tidak ada data</td>
                        </tr>
                        <tr v-for="row in nasabah.data" :key="row.id" class="hover:bg-[color:var(--color-surface-container-low)]">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-[color:var(--color-primary)]">{{ row.nomor_rekening }}</td>
                            <td class="px-4 py-3 font-medium">{{ row.nama }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ formatCurrency(getSembako(row)?.saldo ?? 0) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <Link :href="route('tabungan-sembako.setor', row.id)"
                                        class="flex items-center gap-1 rounded-lg bg-orange-500 px-3 py-1.5 text-xs font-semibold text-white hover:opacity-90">
                                        <span class="material-symbols-outlined text-xs">add</span> Setor
                                    </Link>
                                    <Link :href="route('tabungan-sembako.ambil', row.id)"
                                        class="flex items-center gap-1 rounded-lg border border-[color:var(--color-outline-variant)] px-3 py-1.5 text-xs font-semibold text-[color:var(--color-on-surface-variant)] hover:bg-[color:var(--color-surface-container)]">
                                        <span class="material-symbols-outlined text-xs">shopping_basket</span> Ambil
                                    </Link>
                                    <button @click="openStrukModal(row)"
                                        class="flex items-center gap-1 rounded-lg bg-blue-50 border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                        <span class="material-symbols-outlined text-xs">receipt_long</span> Struk
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Struk Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeModal">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
                <div class="relative z-10 w-full max-w-md rounded-2xl bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                        <div>
                            <h3 class="font-semibold text-slate-800">Cetak Struk Sembako</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ modalNasabah?.nama }} · {{ modalNasabah?.nomor_rekening }}</p>
                        </div>
                        <button @click="closeModal" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>

                    <div class="px-5 py-4 max-h-80 overflow-y-auto">
                        <div v-if="modalLoading" class="flex flex-col items-center justify-center py-10 gap-3">
                            <div class="h-8 w-8 animate-spin rounded-full border-4 border-orange-200 border-t-orange-500"></div>
                            <p class="text-xs text-slate-500">Memuat riwayat transaksi...</p>
                        </div>
                        <div v-else-if="modalError" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">{{ modalError }}</div>
                        <div v-else-if="modalTransaksi.length === 0" class="flex flex-col items-center justify-center py-10 text-slate-400">
                            <span class="material-symbols-outlined text-4xl mb-2">receipt_long</span>
                            <p class="text-sm">Belum ada riwayat transaksi</p>
                        </div>
                        <ul v-else class="divide-y divide-slate-100">
                            <li v-for="trx in modalTransaksi" :key="trx.id" class="flex items-center justify-between py-3 gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs font-mono font-semibold text-slate-700 truncate">#{{ trx.nomor_transaksi }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ formatTanggal(trx.tanggal) }}</p>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <span :class="trx.jenis_transaksi === 'setor' ? 'text-blue-600' : 'text-red-500'"
                                        class="text-xs font-semibold">
                                        {{ trx.jenis_transaksi === 'setor' ? '+' : '-' }} {{ formatCurrency(trx.nominal) }}
                                    </span>
                                    <button @click="openStruk(trx.id)"
                                        class="flex items-center gap-1 rounded-lg bg-orange-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-orange-600">
                                        <span class="material-symbols-outlined text-xs">print</span> Cetak
                                    </button>
                                    <button @click="sendWaStruk(trx)"
                                        class="flex items-center gap-1 rounded-lg bg-green-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-600">
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.122 1.534 5.855L.054 23.272a.75.75 0 00.917.928l5.528-1.451A11.93 11.93 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.723 9.723 0 01-5.02-1.394l-.36-.213-3.73.979.997-3.645-.235-.374A9.718 9.718 0 012.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/></svg> WA
                                    </button>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="border-t border-slate-100 px-5 py-3 flex justify-end">
                        <button @click="closeModal" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
