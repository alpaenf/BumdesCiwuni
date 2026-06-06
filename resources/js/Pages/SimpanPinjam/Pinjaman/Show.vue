<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ pinjaman: Object });

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';

const progress = computed(() => {
    const total = Number(props.pinjaman.total_tagihan);
    const sisa  = Number(props.pinjaman.sisa_pinjaman);
    if (!total) return 0;
    return Math.round(((total - sisa) / total) * 100);
});

const pasaranLabel = { legi: 'Legi', pahing: 'Pahing', pon: 'Pon', wage: 'Wage', kliwon: 'Kliwon' };
</script>

<template>
    <Head :title="`Pinjaman — ${pinjaman.nasabah?.nama}`" />
    <AuthenticatedLayout>
        <template #header>Detail Pinjaman</template>
        <div class="space-y-5">
            <Link :href="route('pinjaman.index')" class="inline-flex items-center gap-2 text-sm text-[color:var(--color-secondary)] hover:text-[color:var(--color-on-surface)]">
                <span class="material-symbols-outlined text-base">arrow_back</span> Kembali ke Daftar Pinjaman
            </Link>

            <div class="grid gap-5 lg:grid-cols-3">
                <!-- Info Pinjaman -->
                <div class="lg:col-span-1 space-y-4">
                    <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Nasabah</p>
                        <p class="mt-2 text-lg font-bold">{{ pinjaman.nasabah?.nama }}</p>
                        <p class="font-mono text-sm text-[color:var(--color-primary)]">{{ pinjaman.nasabah?.nomor_rekening }}</p>
                        <span class="mt-2 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                            :class="pinjaman.status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600'">
                            {{ pinjaman.status === 'aktif' ? 'Aktif' : 'Lunas' }}
                        </span>
                    </div>

                    <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-[color:var(--color-secondary)]">Tanggal Akad</span><span class="font-semibold">{{ formatDate(pinjaman.tanggal_akad) }}</span></div>
                        <div class="flex justify-between"><span class="text-[color:var(--color-secondary)]">Pinjaman Pokok</span><span class="font-semibold">{{ formatCurrency(pinjaman.pinjaman_pokok) }}</span></div>
                        <div class="flex justify-between"><span class="text-[color:var(--color-secondary)]">Bunga</span><span class="font-semibold">{{ pinjaman.bunga }}%</span></div>
                        <div class="flex justify-between border-t pt-3"><span class="text-[color:var(--color-secondary)]">Total Tagihan</span><span class="font-bold text-[color:var(--color-on-surface)]">{{ formatCurrency(pinjaman.total_tagihan) }}</span></div>
                        <div class="flex justify-between"><span class="text-[color:var(--color-secondary)]">Setoran / Angsuran</span><span class="font-semibold">{{ formatCurrency(pinjaman.nominal_setoran) }}</span></div>
                        <div class="flex justify-between"><span class="text-[color:var(--color-secondary)]">Jumlah Angsuran</span><span class="font-semibold">{{ pinjaman.jumlah_angsuran }} kali</span></div>
                        <div class="flex justify-between"><span class="text-[color:var(--color-secondary)]">Terbayar</span><span class="font-semibold text-emerald-600">{{ pinjaman.angsuran?.length ?? 0 }} kali</span></div>
                        <div class="flex justify-between border-t pt-3"><span class="text-[color:var(--color-secondary)]">Sisa Pinjaman</span><span class="font-bold text-red-600">{{ formatCurrency(pinjaman.sisa_pinjaman) }}</span></div>
                    </div>

                    <!-- Progress -->
                    <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-5 shadow-sm">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="font-semibold">Progress Pelunasan</span>
                            <span class="text-[color:var(--color-primary)] font-bold">{{ progress }}%</span>
                        </div>
                        <div class="h-3 w-full rounded-full bg-[color:var(--color-surface-container)]">
                            <div class="h-3 rounded-full bg-[color:var(--color-primary)] transition-all" :style="{ width: progress + '%' }"></div>
                        </div>
                    </div>

                    <!-- Dokumen Perjanjian -->
                    <div v-if="pinjaman.foto_perjanjian_url" class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-5 shadow-sm">
                        <p class="font-semibold text-sm mb-3">Dokumen Perjanjian</p>
                        <a :href="pinjaman.foto_perjanjian_url" target="_blank" class="block overflow-hidden rounded-lg border hover:opacity-90">
                            <img :src="pinjaman.foto_perjanjian_url" class="w-full object-cover max-h-48" alt="Dokumen Perjanjian" />
                        </a>
                        <p class="mt-2 text-center text-xs text-[color:var(--color-secondary)]">Klik gambar untuk melihat ukuran penuh</p>
                    </div>

                    <Link v-if="pinjaman.status === 'aktif'" :href="route('angsuran.create', { nasabah_id: pinjaman.nasabah?.id })"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-[color:var(--color-primary)] px-4 py-3 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                        <span class="material-symbols-outlined text-base">payments</span> Bayar Angsuran
                    </Link>
                </div>

                <!-- Riwayat Angsuran -->
                <div class="lg:col-span-2">
                    <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white shadow-sm">
                        <div class="border-b border-[color:var(--color-outline-variant)] px-6 py-4">
                            <h3 class="font-semibold">Riwayat Angsuran</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-[color:var(--color-outline-variant)] bg-[color:var(--color-surface-container-low)]">
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Ke-</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Tanggal</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Hari Pasaran</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Jumlah Bayar</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Sisa Pinjaman</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Struk</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[color:var(--color-outline-variant)]">
                                    <tr v-if="!pinjaman.angsuran || pinjaman.angsuran.length === 0">
                                        <td colspan="6" class="px-4 py-8 text-center text-[color:var(--color-secondary)]">Belum ada angsuran</td>
                                    </tr>
                                    <tr v-for="a in pinjaman.angsuran" :key="a.id" class="hover:bg-[color:var(--color-surface-container-low)]">
                                        <td class="px-4 py-3 font-semibold text-center">{{ a.angsuran_ke }}</td>
                                        <td class="px-4 py-3 text-[color:var(--color-secondary)]">{{ formatDate(a.tanggal) }}</td>
                                        <td class="px-4 py-3 capitalize text-[color:var(--color-secondary)]">{{ pasaranLabel[a.pasaran] ?? a.pasaran }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-emerald-600">{{ formatCurrency(a.jumlah_bayar) }}</td>
                                        <td class="px-4 py-3 text-right" :class="Number(a.sisa_pinjaman) <= 0 ? 'text-emerald-600 font-semibold' : 'text-red-600'">{{ formatCurrency(a.sisa_pinjaman) }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <a :href="route('angsuran.struk', a.id)" target="_blank" class="inline-flex items-center gap-1 rounded-lg bg-[color:var(--color-surface-container)] px-2.5 py-1 text-xs text-[color:var(--color-on-surface-variant)] hover:bg-[color:var(--color-surface-container-high)]">
                                                <span class="material-symbols-outlined text-xs">print</span> Print
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
