<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import WaTemplateModal from '@/Components/WaTemplateModal.vue';

const props = defineProps({
    nasabah: Object,
});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';

const tabunganReguler = computed(() => {
    return props.nasabah.tabungan?.find(t => t.jenis_tabungan === 'reguler') || null;
});

const tabunganSembako = computed(() => {
    return props.nasabah.tabungan?.find(t => t.jenis_tabungan === 'sembako') || null;
});

const pinjamanAktif   = computed(() => props.nasabah.pinjaman?.filter(p => p.status === 'aktif' || p.status === 'macet') ?? []);

const waModal = ref(null);
</script>

<template>
    <Head :title="`Detail: ${nasabah.nama}`" />
    <AuthenticatedLayout>
        <template #header>Detail Nasabah</template>

        <div class="space-y-5">
            <!-- Back -->
            <Link :href="route('nasabah.index')" class="inline-flex items-center gap-2 text-sm text-[color:var(--color-secondary)] hover:text-[color:var(--color-on-surface)]">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Kembali ke Daftar Nasabah
            </Link>

            <div class="grid gap-5 lg:grid-cols-3">
                <!-- Profil Nasabah -->
                <div class="lg:col-span-1">
                    <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-semibold text-[color:var(--color-on-surface)]">Profil Nasabah</h3>
                            <Link :href="route('nasabah.edit', nasabah.id)" class="rounded-lg p-1.5 text-blue-600 hover:bg-blue-50">
                                <span class="material-symbols-outlined text-base">edit</span>
                            </Link>
                        </div>
                        <div class="mb-4 relative h-20 w-20 overflow-hidden rounded-full border border-[color:var(--color-outline-variant)] bg-[color:var(--color-surface-container)] flex items-center justify-center">
                            <img v-if="nasabah.foto_url" :src="nasabah.foto_url" class="h-full w-full object-cover" alt="Foto Profil" />
                            <span v-else class="material-symbols-outlined text-4xl text-[color:var(--color-secondary)]">person</span>
                        </div>
                        <p class="text-lg font-bold text-[color:var(--color-on-surface)]">{{ nasabah.nama }}</p>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                            :class="nasabah.status === 'aktif' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-600'">
                            {{ nasabah.status === 'aktif' ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                        <div class="mt-4 space-y-2.5 text-sm">
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-base text-[color:var(--color-secondary)]">badge</span>
                                <div>
                                    <p class="text-[10px] uppercase text-[color:var(--color-secondary)]">No. Registrasi</p>
                                    <p class="font-mono font-semibold">{{ nasabah.nomor_registrasi }}</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-base text-[color:var(--color-secondary)]">account_balance_wallet</span>
                                <div>
                                    <p class="text-[10px] uppercase text-[color:var(--color-secondary)]">No. Rekening</p>
                                    <p class="font-mono font-semibold text-[color:var(--color-primary)]">{{ nasabah.nomor_rekening }}</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-base text-[color:var(--color-secondary)]">credit_card</span>
                                <div>
                                    <p class="text-[10px] uppercase text-[color:var(--color-secondary)]">NIK</p>
                                    <p class="font-mono">{{ nasabah.nik }}</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-base text-[color:var(--color-secondary)]">phone</span>
                                <div>
                                    <p class="text-[10px] uppercase text-[color:var(--color-secondary)]">No. WhatsApp</p>
                                    <button @click="waModal?.open()"
                                        class="flex items-center gap-1.5 mt-0.5 text-sm font-medium text-green-600 hover:text-green-700 hover:underline transition">
                                        <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.122 1.534 5.855L.054 23.272a.75.75 0 00.917.928l5.528-1.451A11.93 11.93 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.723 9.723 0 01-5.02-1.394l-.36-.213-3.73.979.997-3.645-.235-.374A9.718 9.718 0 012.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/>
                                        </svg>
                                        {{ nasabah.no_hp }}
                                    </button>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-base text-[color:var(--color-secondary)]">location_on</span>
                                <div>
                                    <p class="text-[10px] uppercase text-[color:var(--color-secondary)]">Alamat</p>
                                    <p>{{ nasabah.alamat }}</p>
                                </div>
                            </div>
                            <div class="flex gap-3" v-if="nasabah.pekerjaan">
                                <span class="material-symbols-outlined text-base text-[color:var(--color-secondary)]">work</span>
                                <div>
                                    <p class="text-[10px] uppercase text-[color:var(--color-secondary)]">Pekerjaan</p>
                                    <p>{{ nasabah.pekerjaan }}</p>
                                </div>
                            </div>
                            <div class="flex gap-3" v-if="nasabah.jaminan">
                                <span class="material-symbols-outlined text-base text-[color:var(--color-secondary)]">security</span>
                                <div>
                                    <p class="text-[10px] uppercase text-[color:var(--color-secondary)]">Jaminan</p>
                                    <p>{{ nasabah.jaminan }}</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-base text-[color:var(--color-secondary)]">calendar_today</span>
                                <div>
                                    <p class="text-[10px] uppercase text-[color:var(--color-secondary)]">Bergabung</p>
                                    <p>{{ formatDate(nasabah.tanggal_bergabung) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right column -->
                <div class="space-y-5 lg:col-span-2">
                    <!-- Tabungan cards -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <!-- Tabungan Reguler Card -->
                        <div v-if="tabunganReguler" class="rounded-xl border border-blue-200 bg-blue-50/50 p-5 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">Tabungan Reguler</p>
                                    <span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-700">Aktif</span>
                                </div>
                                <p class="mt-3 text-2xl font-bold text-blue-800">{{ formatCurrency(tabunganReguler.saldo) }}</p>
                            </div>
                            <div class="mt-5 flex gap-2">
                                <Link :href="route('tabungan.setor', nasabah.id)" class="flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:opacity-90">
                                    <span class="material-symbols-outlined text-xs">add</span> Setor
                                </Link>
                                <Link :href="route('tabungan.tarik', nasabah.id)" class="flex items-center gap-1 rounded-lg border border-blue-600 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 bg-white">
                                    <span class="material-symbols-outlined text-xs">remove</span> Tarik
                                </Link>
                            </div>
                        </div>

                        <!-- Tabungan Sembako Card -->
                        <div v-if="tabunganSembako" class="rounded-xl border border-orange-200 bg-orange-50/50 p-5 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-orange-600">Tabungan Sembako</p>
                                    <span class="inline-flex rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-bold text-orange-700">Aktif</span>
                                </div>
                                <p class="mt-3 text-2xl font-bold text-orange-800">{{ formatCurrency(tabunganSembako.saldo) }}</p>
                            </div>
                            <div class="mt-5 flex gap-2">
                                <Link :href="route('tabungan-sembako.setor', nasabah.id)" class="flex items-center gap-1 rounded-lg bg-orange-600 px-3 py-1.5 text-xs font-semibold text-white hover:opacity-90">
                                    <span class="material-symbols-outlined text-xs">add</span> Setor
                                </Link>
                                <Link :href="route('tabungan-sembako.ambil', nasabah.id)" class="flex items-center gap-1 rounded-lg border border-orange-600 px-3 py-1.5 text-xs font-semibold text-orange-700 hover:bg-orange-100 bg-white">
                                    <span class="material-symbols-outlined text-xs">remove</span> Ambil
                                </Link>
                            </div>
                        </div>

                        <!-- If none are active -->
                        <div v-if="!tabunganReguler && !tabunganSembako" class="col-span-2 rounded-xl border border-dashed border-slate-300 p-8 flex flex-col justify-center items-center text-center bg-slate-50/50">
                            <span class="material-symbols-outlined text-slate-400 text-4xl">account_balance_wallet</span>
                            <p class="text-sm font-semibold text-slate-500 mt-2">Tidak Ada Rekening Tabungan Aktif</p>
                            <p class="text-xs text-slate-400 mt-1">Gunakan Buka Buku Massal untuk mengaktifkan rekening tabungan nasabah.</p>
                        </div>
                    </div>

                    <!-- Pinjaman aktif -->
                    <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-semibold text-[color:var(--color-on-surface)]">Pinjaman Berjalan</h3>
                            <Link :href="route('pinjaman.create')" class="flex items-center gap-1 rounded-lg bg-[color:var(--color-primary)] px-3 py-1.5 text-xs font-semibold text-white hover:opacity-90">
                                <span class="material-symbols-outlined text-xs">add</span> Pinjam
                            </Link>
                        </div>
                        <div v-if="pinjamanAktif.length === 0" class="py-6 text-center text-sm text-[color:var(--color-secondary)]">
                            Tidak ada pinjaman berjalan
                        </div>
                        <div v-for="p in pinjamanAktif" :key="p.id" class="rounded-lg border border-[color:var(--color-outline-variant)] p-4 mb-3" :class="p.status === 'macet' ? 'bg-red-50/50 border-red-200' : ''">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <p class="font-semibold text-[color:var(--color-on-surface)]">Pinjaman Pokok</p>
                                        <span v-if="p.status === 'macet'" class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-700">Macet</span>
                                    </div>
                                    <p class="text-xs text-[color:var(--color-secondary)]">Tanggal Akad: {{ formatDate(p.tanggal_akad) }}</p>
                                    <p class="mt-1 text-sm font-semibold">Pokok: {{ formatCurrency(p.pinjaman_pokok) }} | Bunga {{ p.bunga }}%</p>
                                </div>
                                <Link :href="route('pinjaman.show', p.id)" class="text-[color:var(--color-primary)] hover:underline text-xs">Detail</Link>
                            </div>
                            <div class="mt-3 grid grid-cols-3 gap-2 text-center text-xs">
                                <div class="rounded-lg bg-[color:var(--color-surface-container-low)] p-2">
                                    <p class="text-[color:var(--color-secondary)]">Total Tagihan</p>
                                    <p class="font-semibold text-[color:var(--color-on-surface)]">{{ formatCurrency(p.total_tagihan) }}</p>
                                </div>
                                <div class="rounded-lg bg-red-50 p-2">
                                    <p class="text-red-600">Sisa</p>
                                    <p class="font-semibold text-red-700">{{ formatCurrency(p.sisa_pinjaman) }}</p>
                                </div>
                                <div class="rounded-lg bg-blue-50 p-2">
                                    <p class="text-blue-600">Angsuran</p>
                                    <p class="font-semibold text-blue-700">{{ p.angsuran?.length ?? 0 }}/{{ p.jumlah_angsuran }}x</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- WA Modal -->
    <WaTemplateModal
        ref="waModal"
        :nasabah="nasabah"
        :pinjaman-aktif="pinjamanAktif"
    />
</template>
