<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    transaksi: Object,       // data transaksi yang diedit
    updateUrl: String,       // URL PUT endpoint
    mode: {                  // 'angsuran' | 'tabungan' | 'sembako'
        type: String,
        default: 'angsuran'
    },
    csrfToken: String,
});

const emit = defineEmits(['close', 'saved']);

const form = ref({
    tanggal: '',
    pasaran: '',
    jumlah_bayar: '',
    nominal: '',
    keterangan: '',
});

const loading  = ref(false);
const errMsg   = ref('');
const errors   = ref({});

watch(() => props.transaksi, (trx) => {
    if (!trx) return;
    form.value = {
        tanggal:     trx.tanggal?.substring(0, 10) ?? '',
        pasaran:     trx.pasaran ?? '',
        jumlah_bayar:trx.jumlah_bayar ? String(trx.jumlah_bayar) : '',
        nominal:     trx.nominal     ? String(trx.nominal)      : '',
        keterangan:  trx.keterangan  ?? '',
    };
    errMsg.value  = '';
    errors.value  = {};
}, { immediate: true });

const formatCurrency = (v) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);

function close() {
    if (!loading.value) emit('close');
}

async function submit() {
    loading.value = true;
    errMsg.value  = '';
    errors.value  = {};

    const body = {
        _method: 'PUT',
        tanggal: form.value.tanggal,
    };

    if (props.mode === 'angsuran') {
        body.pasaran     = form.value.pasaran;
        body.jumlah_bayar = form.value.jumlah_bayar;
    } else {
        body.nominal    = form.value.nominal;
        body.keterangan = form.value.keterangan;
    }

    try {
        const res = await fetch(props.updateUrl, {
            method: 'POST',        // Laravel form method spoofing via _method=PUT
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify(body),
        });

        const data = await res.json();

        if (!res.ok) {
            if (data.errors) {
                errors.value = data.errors;
            } else {
                errMsg.value = data.message || 'Gagal menyimpan perubahan.';
            }
            return;
        }

        emit('saved', data);
        emit('close');
    } catch (e) {
        errMsg.value = 'Koneksi bermasalah. Coba lagi.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="close">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close"></div>
                <div class="relative z-10 w-full max-w-sm rounded-2xl bg-white shadow-2xl">

                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                        <div>
                            <h3 class="font-semibold text-slate-800">Edit Transaksi</h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                #{{ transaksi?.nomor_transaksi ?? transaksi?.id }}
                            </p>
                        </div>
                        <button @click="close" :disabled="loading"
                            class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 disabled:opacity-50">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="px-5 py-4 space-y-4">

                        <!-- Error Banner -->
                        <div v-if="errMsg" class="rounded-lg bg-red-50 border border-red-200 px-3 py-2 text-sm text-red-700">
                            {{ errMsg }}
                        </div>

                        <!-- Tanggal -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal</label>
                            <input v-model="form.tanggal" type="date"
                                class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="errors.tanggal ? 'border-red-400' : 'border-slate-300'" />
                            <p v-if="errors.tanggal" class="text-xs text-red-600 mt-1">{{ errors.tanggal[0] }}</p>
                        </div>

                        <!-- ANGSURAN: Jumlah Bayar + Pasaran -->
                        <template v-if="mode === 'angsuran'">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Jumlah Bayar</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">Rp</span>
                                    <input v-model="form.jumlah_bayar" type="number" min="1" step="500"
                                        class="w-full rounded-lg border pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        :class="errors.jumlah_bayar ? 'border-red-400' : 'border-slate-300'" />
                                </div>
                                <p v-if="errors.jumlah_bayar" class="text-xs text-red-600 mt-1">{{ errors.jumlah_bayar[0] }}</p>
                                <p class="text-xs text-amber-600 mt-1">
                                    Perhatian: Mengubah jumlah bayar akan menghitung ulang sisa pinjaman otomatis.
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Hari Pasaran</label>
                                <select v-model="form.pasaran"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Tidak ada --</option>
                                    <option value="legi">Legi</option>
                                    <option value="pahing">Pahing</option>
                                    <option value="pon">Pon</option>
                                    <option value="wage">Wage</option>
                                    <option value="kliwon">Kliwon</option>
                                </select>
                            </div>
                        </template>

                        <!-- TABUNGAN / SEMBAKO: Nominal + Keterangan -->
                        <template v-else>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Nominal</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">Rp</span>
                                    <input v-model="form.nominal" type="number" min="1" step="500"
                                        class="w-full rounded-lg border pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        :class="errors.nominal ? 'border-red-400' : 'border-slate-300'" />
                                </div>
                                <p v-if="errors.nominal" class="text-xs text-red-600 mt-1">{{ errors.nominal[0] }}</p>
                                <p class="text-xs text-amber-600 mt-1">
                                    Perhatian: Mengubah nominal akan menghitung ulang saldo tabungan otomatis.
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Keterangan</label>
                                <input v-model="form.keterangan" type="text" maxlength="255"
                                    placeholder="Contoh: Setoran rutin bulan ini..."
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                        </template>

                    </div>

                    <!-- Footer -->
                    <div class="flex gap-3 border-t border-slate-100 px-5 py-4">
                        <button @click="close" :disabled="loading"
                            class="flex-1 rounded-lg border border-slate-200 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 disabled:opacity-50">
                            Batal
                        </button>
                        <button @click="submit" :disabled="loading"
                            class="flex-1 rounded-lg bg-blue-600 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2">
                            <svg v-if="loading" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            <span>{{ loading ? 'Menyimpan...' : 'Simpan' }}</span>
                        </button>
                    </div>

                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity .2s, transform .2s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.modal-fade-enter-from .relative, .modal-fade-leave-to .relative { transform: scale(.95) translateY(8px); }
</style>
