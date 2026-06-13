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

function kirimStrukWa(row) {
    const noHp = props.pinjaman.nasabah?.no_hp;
    if (!noHp) {
        alert('Nomor HP Nasabah tidak tersedia.');
        return;
    }
    let no = noHp.replace(/\D/g, '');
    if (no.startsWith('0')) no = '62' + no.slice(1);
    if (!no.startsWith('62')) no = '62' + no;

    const strukUrl = route('angsuran.struk', row.id);
    const nama = props.pinjaman.nasabah?.nama;
    
    const p = props.pinjaman;
    const t = p.total_tagihan > 0 ? p.total_tagihan : 1;
    const porsiPokok = (p.pinjaman_pokok / t) * row.jumlah_bayar;
    const porsiBunga = ((p.pinjaman_pokok * p.bunga / 100) / t) * row.jumlah_bayar;
    
    const pesan =
`Assalamu'alaikum, Bapak/Ibu *${nama}*.

Berikut informasi transaksi pembayaran angsuran Anda di *BUMDes Dammar Wulan*:

No. Transaksi : #${row.nomor_transaksi || '-'}
Tanggal       : ${formatDate(row.tanggal)}
Angsuran Ke   : ${row.angsuran_ke}
Jumlah Bayar  : ${formatCurrency(row.jumlah_bayar)}
(Pokok: ${formatCurrency(porsiPokok)}, Bunga: ${formatCurrency(porsiBunga)})
Sisa Pinjaman : ${formatCurrency(row.sisa_pinjaman)}

Lihat struk lengkap di:
${strukUrl}

Terima kasih.`;

    window.open(`https://wa.me/${no}?text=${encodeURIComponent(pesan)}`, '_blank');
}
</script>

<template>
    <Head :title="`Pinjaman — ${pinjaman.nasabah?.nama}`" />
    <AuthenticatedLayout>
        <template #header>Detail Pinjaman</template>
        <div class="space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <Link :href="route('pinjaman.index')" class="inline-flex items-center gap-2 text-sm text-[color:var(--color-secondary)] hover:text-[color:var(--color-on-surface)]">
                    <span class="material-symbols-outlined text-base">arrow_back</span> Kembali ke Daftar Pinjaman
                </Link>
                <Link :href="route('pinjaman.edit', pinjaman.id)" class="inline-flex items-center gap-1 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600 transition shadow-sm">
                    <span class="material-symbols-outlined text-xs">edit</span> Edit Pinjaman
                </Link>
            </div>

            <div class="grid gap-5 lg:grid-cols-3 min-w-0">
                <!-- Info Pinjaman -->
                <div class="lg:col-span-1 space-y-4 min-w-0">
                    <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4 sm:p-6 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Nasabah</p>
                        <p class="mt-2 text-lg font-bold">{{ pinjaman.nasabah?.nama }}</p>
                        <p class="font-mono text-sm text-[color:var(--color-primary)]">{{ pinjaman.nasabah?.nomor_rekening }}</p>
                        <span class="mt-2 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                            :class="{
                                'bg-blue-50 text-blue-700': pinjaman.status === 'aktif',
                                'bg-red-50 text-red-700': pinjaman.status === 'macet',
                                'bg-gray-100 text-gray-600': pinjaman.status === 'lunas'
                            }">
                            {{ pinjaman.status }}
                        </span>
                    </div>

                    <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4 sm:p-6 shadow-sm space-y-3 text-sm">
                        <div class="flex justify-between gap-2"><span class="text-[color:var(--color-secondary)]">Tanggal Akad</span><span class="font-semibold text-right">{{ formatDate(pinjaman.tanggal_akad) }}</span></div>
                        
                        <div v-if="pinjaman.jenis_pinjaman !== 'uang'" class="flex flex-col gap-1 border-y border-amber-100 bg-amber-50/50 py-2">
                            <div class="flex justify-between gap-2"><span class="text-amber-700">Jenis Pinjaman</span><span class="font-semibold capitalize text-amber-900 text-right">{{ pinjaman.jenis_pinjaman }}</span></div>
                            <div class="flex justify-between gap-2"><span class="text-amber-700">Barang</span><span class="font-semibold text-amber-900 text-right">{{ pinjaman.keterangan || '-' }}</span></div>
                            <div v-if="pinjaman.foto_barang" class="flex justify-between gap-2"><span class="text-amber-700">Foto Barang</span>
                                <a :href="'/uploads/pinjaman/' + pinjaman.foto_barang" target="_blank" class="text-blue-600 hover:underline">Lihat Foto</a>
                            </div>
                        </div>

                        <div class="flex justify-between gap-2"><span class="text-[color:var(--color-secondary)]">Pinjaman Pokok</span><span class="font-semibold text-right">{{ formatCurrency(pinjaman.pinjaman_pokok) }}</span></div>
                        <div v-if="pinjaman.biaya_tambahan > 0" class="flex justify-between gap-2 text-blue-700 bg-blue-50 -mx-2 px-2 py-1 rounded">
                            <span>Biaya Tambahan (Materai dll)</span><span class="font-semibold text-right">+{{ formatCurrency(pinjaman.biaya_tambahan) }}</span>
                        </div>
                        <div class="flex justify-between gap-2"><span class="text-[color:var(--color-secondary)]">Bunga</span><span class="font-semibold text-right">{{ pinjaman.bunga }}%</span></div>
                        <div class="flex justify-between gap-2 border-t pt-3"><span class="text-[color:var(--color-secondary)]">Total Tagihan (Utang)</span><span class="font-bold text-[color:var(--color-on-surface)] text-right">{{ formatCurrency(pinjaman.total_tagihan) }}</span></div>
                        <div class="flex flex-col gap-1 mt-1 bg-[color:var(--color-surface-container-low)] p-2 rounded-lg text-xs">
                            <div class="flex justify-between gap-2"><span class="text-[color:var(--color-secondary)]">Setoran / Angsuran</span><span class="font-semibold text-right">{{ formatCurrency(pinjaman.nominal_setoran) }}</span></div>
                            <div class="flex justify-between gap-2 border-t border-dashed border-[color:var(--color-outline-variant)] pt-1"><span class="text-[color:var(--color-secondary)]">- Pokok</span><span class="font-medium text-right">{{ formatCurrency((pinjaman.pinjaman_pokok / (pinjaman.total_tagihan || 1)) * pinjaman.nominal_setoran) }}</span></div>
                            <div class="flex justify-between gap-2"><span class="text-[color:var(--color-secondary)]">- Bunga</span><span class="font-medium text-right">{{ formatCurrency(((pinjaman.pinjaman_pokok * pinjaman.bunga / 100) / (pinjaman.total_tagihan || 1)) * pinjaman.nominal_setoran) }}</span></div>
                        </div>
                        <div class="flex justify-between gap-2 mt-2"><span class="text-[color:var(--color-secondary)]">Jumlah Angsuran</span><span class="font-semibold text-right">{{ pinjaman.jumlah_angsuran }} kali</span></div>
                        <div class="flex justify-between gap-2"><span class="text-[color:var(--color-secondary)]">Terbayar</span><span class="font-semibold text-blue-600 text-right">{{ pinjaman.angsuran?.length ?? 0 }} kali</span></div>
                        <div class="flex justify-between gap-2 border-t pt-3"><span class="text-[color:var(--color-secondary)]">Sisa Pinjaman</span><span class="font-bold text-red-600 text-right">{{ formatCurrency(pinjaman.sisa_pinjaman) }}</span></div>
                    </div>

                    <!-- Progress -->
                    <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4 sm:p-5 shadow-sm">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="font-semibold">Progress Pelunasan</span>
                            <span class="text-[color:var(--color-primary)] font-bold">{{ progress }}%</span>
                        </div>
                        <div class="h-3 w-full rounded-full bg-[color:var(--color-surface-container)]">
                            <div class="h-3 rounded-full bg-[color:var(--color-primary)] transition-all" :style="{ width: progress + '%' }"></div>
                        </div>
                    </div>

                    <!-- Dokumen Perjanjian -->
                    <div v-if="pinjaman.foto_perjanjian_url" class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4 sm:p-5 shadow-sm">
                        <p class="font-semibold text-sm mb-3">Dokumen Perjanjian</p>
                        <a :href="pinjaman.foto_perjanjian_url" target="_blank" class="block overflow-hidden rounded-lg border hover:opacity-90">
                            <img :src="pinjaman.foto_perjanjian_url" class="w-full object-cover max-h-48" alt="Dokumen Perjanjian" />
                        </a>
                        <p class="mt-2 text-center text-xs text-[color:var(--color-secondary)]">Klik gambar untuk melihat ukuran penuh</p>
                    </div>

                    <Link v-if="pinjaman.status === 'aktif' || pinjaman.status === 'macet'" :href="route('angsuran.create', { nasabah_id: pinjaman.nasabah?.id })"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-[color:var(--color-primary)] px-4 py-3 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                        <span class="material-symbols-outlined text-base">payments</span> Bayar Angsuran
                    </Link>
                </div>

                <!-- Riwayat Angsuran -->
                <div class="lg:col-span-2 min-w-0">
                    <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white shadow-sm">
                        <div class="border-b border-[color:var(--color-outline-variant)] px-4 sm:px-6 py-4">
                            <h3 class="font-semibold">Riwayat Angsuran</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-[color:var(--color-outline-variant)] bg-[color:var(--color-surface-container-low)]">
                                        <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Ke-</th>
                                        <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Tanggal</th>
                                        <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)] hidden sm:table-cell">Hari Pasaran</th>
                                        <th class="px-2 sm:px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Jumlah Bayar</th>
                                        <th class="px-2 sm:px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Sisa Pinjaman</th>
                                        <th class="px-2 sm:px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Struk</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[color:var(--color-outline-variant)]">
                                    <tr v-if="!pinjaman.angsuran || pinjaman.angsuran.length === 0">
                                        <td colspan="6" class="px-2 sm:px-4 py-8 text-center text-[color:var(--color-secondary)]">Belum ada angsuran</td>
                                    </tr>
                                    <tr v-for="a in pinjaman.angsuran" :key="a.id" class="hover:bg-[color:var(--color-surface-container-low)]">
                                        <td class="px-2 sm:px-4 py-3 font-semibold text-center">{{ a.angsuran_ke }}</td>
                                        <td class="px-2 sm:px-4 py-3 text-[color:var(--color-secondary)]">{{ formatDate(a.tanggal) }}</td>
                                        <td class="px-2 sm:px-4 py-3 capitalize text-[color:var(--color-secondary)] hidden sm:table-cell">{{ pasaranLabel[a.pasaran] ?? a.pasaran }}</td>
                                        <td class="px-2 sm:px-4 py-3 text-right">
                                            <div class="font-semibold text-blue-600">{{ formatCurrency(a.jumlah_bayar) }}</div>
                                            <div class="text-[10px] text-gray-500 mt-0.5">
                                                Pokok: {{ formatCurrency((pinjaman.pinjaman_pokok / (pinjaman.total_tagihan || 1)) * a.jumlah_bayar) }}
                                            </div>
                                            <div class="text-[10px] text-gray-500">
                                                Bunga: {{ formatCurrency(((pinjaman.pinjaman_pokok * pinjaman.bunga / 100) / (pinjaman.total_tagihan || 1)) * a.jumlah_bayar) }}
                                            </div>
                                        </td>
                                        <td class="px-2 sm:px-4 py-3 text-right" :class="Number(a.sisa_pinjaman) <= 0 ? 'text-blue-600 font-semibold' : 'text-red-600'">{{ formatCurrency(a.sisa_pinjaman) }}</td>
                                        <td class="px-2 sm:px-4 py-3">
                                            <div class="flex items-center justify-center gap-2">
                                                <a :href="route('angsuran.struk', a.id)" target="_blank" class="inline-flex items-center gap-1 rounded-lg bg-[color:var(--color-surface-container)] px-2 py-1 text-xs text-[color:var(--color-on-surface-variant)] hover:bg-[color:var(--color-surface-container-high)] sm:px-2.5">
                                                    <span class="material-symbols-outlined text-xs">print</span> <span class="hidden sm:inline">Print</span>
                                                </a>
                                                <button @click="kirimStrukWa(a)" class="inline-flex items-center gap-1 rounded-lg bg-green-500 px-2 py-1 text-xs font-semibold text-white hover:bg-green-600 transition sm:px-2.5">
                                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.122 1.534 5.855L.054 23.272a.75.75 0 00.917.928l5.528-1.451A11.93 11.93 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.723 9.723 0 01-5.02-1.394l-.36-.213-3.73.979.997-3.645-.235-.374A9.718 9.718 0 012.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/></svg> <span class="hidden sm:inline">WA</span>
                                                </button>
                                            </div>
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
