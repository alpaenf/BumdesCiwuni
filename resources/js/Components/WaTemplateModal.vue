<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    nasabah: { type: Object, required: true },
    pinjamanAktif: { type: Array, default: () => [] },
});

const show = ref(false);
const selectedTemplate = ref(null);
const customPesan = ref('');

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);

const waNumber = computed(() => {
    let no = (props.nasabah?.no_hp || '').replace(/\D/g, '');
    if (no.startsWith('0')) no = '62' + no.slice(1);
    if (!no.startsWith('62')) no = '62' + no;
    return no;
});

const sisaPinjaman = computed(() => {
    if (!props.pinjamanAktif?.length) return 0;
    return props.pinjamanAktif.reduce((sum, p) => sum + Number(p.sisa_pinjaman || 0), 0);
});

const templates = computed(() => [
    {
        id: 'penagihan_angsuran',
        label: 'Penagihan Angsuran',
        icon: 'payments',
        color: 'text-red-600',
        bgColor: 'bg-red-50 border-red-200',
        pesan: `Assalamu'alaikum, Bapak/Ibu *${props.nasabah?.nama}*.\n\nKami dari *BUMDes Dammar Wulan* ingin mengingatkan bahwa Anda memiliki kewajiban angsuran yang belum diselesaikan.\n\nSisa Pinjaman : ${formatCurrency(sisaPinjaman.value)}\n\nMohon segera melakukan pembayaran atau hubungi kami untuk informasi lebih lanjut.\n\nTerima kasih atas perhatiannya.`,
    },
    {
        id: 'penagihan_tabungan',
        label: 'Pengingat Setoran Tabungan',
        icon: 'savings',
        color: 'text-blue-600',
        bgColor: 'bg-blue-50 border-blue-200',
        pesan: `Assalamu'alaikum, Bapak/Ibu *${props.nasabah?.nama}*.\n\nKami dari *BUMDes Dammar Wulan* ingin mengingatkan untuk melakukan setoran tabungan rutin Anda.\n\nTabungan rutin sangat membantu untuk masa depan yang lebih baik.\n\nSilakan datang ke kantor kami atau hubungi kami untuk informasi lebih lanjut.\n\nTerima kasih.`,
    },
    {
        id: 'info_struk',
        label: 'Info / Kirim Struk',
        icon: 'receipt_long',
        color: 'text-blue-600',
        bgColor: 'bg-blue-50 border-blue-200',
        pesan: `Assalamu'alaikum, Bapak/Ibu *${props.nasabah?.nama}*.\n\nBerikut kami sampaikan informasi terkait transaksi Anda di *BUMDes Dammar Wulan*.\n\nUntuk melihat struk transaksi lengkap, silakan hubungi petugas kami langsung di kantor.\n\nTerima kasih telah menjadi nasabah setia kami.`,
    },
    {
        id: 'ucapan',
        label: 'Ucapan / Informasi Umum',
        icon: 'campaign',
        color: 'text-purple-600',
        bgColor: 'bg-purple-50 border-purple-200',
        pesan: `Assalamu'alaikum, Bapak/Ibu *${props.nasabah?.nama}*.\n\nKami dari *BUMDes Dammar Wulan* ingin menyampaikan informasi penting terkait layanan kami.\n\nUntuk informasi lebih lanjut, silakan hubungi kami.\n\nTerima kasih.`,
    },
]);

function selectTemplate(tpl) {
    selectedTemplate.value = tpl;
    customPesan.value = tpl.pesan;
}

function openWa() {
    if (!customPesan.value.trim()) return;
    const url = `https://wa.me/${waNumber.value}?text=${encodeURIComponent(customPesan.value)}`;
    window.open(url, '_blank');
}

function open() {
    selectedTemplate.value = null;
    customPesan.value = '';
    show.value = true;
}

function close() {
    show.value = false;
}

defineExpose({ open });
</script>

<template>
    <slot :open="open" />

    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="close">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
            <div class="relative z-10 w-full max-w-lg rounded-2xl bg-white shadow-2xl flex flex-col max-h-[90vh]">

                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-green-100">
                            <svg class="h-5 w-5 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                <path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.122 1.534 5.855L.054 23.272a.75.75 0 00.917.928l5.528-1.451A11.93 11.93 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.723 9.723 0 01-5.02-1.394l-.36-.213-3.73.979.997-3.645-.235-.374A9.718 9.718 0 012.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-800">Kirim WhatsApp</h3>
                            <p class="text-xs text-slate-500">{{ nasabah.nama }} · {{ nasabah.no_hp }}</p>
                        </div>
                    </div>
                    <button @click="close" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <!-- Body -->
                <div class="overflow-y-auto px-5 py-4 space-y-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Pilih Template Pesan</p>
                        <div class="grid grid-cols-2 gap-2">
                            <button v-for="tpl in templates" :key="tpl.id"
                                @click="selectTemplate(tpl)"
                                :class="[
                                    'flex items-center gap-2 rounded-xl border px-3 py-2.5 text-left text-xs font-medium transition',
                                    selectedTemplate?.id === tpl.id
                                        ? tpl.bgColor + ' ' + tpl.color + ' ring-2 ring-offset-1 ring-current'
                                        : 'border-slate-200 text-slate-600 hover:bg-slate-50'
                                ]">
                                <span class="material-symbols-outlined text-base shrink-0" :class="selectedTemplate?.id === tpl.id ? tpl.color : 'text-slate-400'">{{ tpl.icon }}</span>
                                {{ tpl.label }}
                            </button>
                        </div>
                    </div>

                    <div v-if="selectedTemplate">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Edit Pesan</p>
                            <button @click="customPesan = selectedTemplate.pesan" class="text-xs text-blue-500 hover:underline">Reset ke default</button>
                        </div>
                        <textarea v-model="customPesan" rows="8"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm leading-relaxed focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100 resize-none font-mono">
                        </textarea>
                        <p class="mt-1 text-xs text-slate-400">Teks tebal gunakan *tanda bintang*, misal: *nama*</p>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center py-6 text-slate-400">
                        <span class="material-symbols-outlined text-4xl mb-2">chat_bubble</span>
                        <p class="text-sm">Pilih template pesan di atas untuk memulai</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-slate-100 px-5 py-3 flex items-center justify-between gap-3 shrink-0">
                    <button @click="close" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button @click="openWa" :disabled="!customPesan.trim()"
                        class="flex items-center gap-2 rounded-lg bg-green-600 px-5 py-2 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-40 disabled:cursor-not-allowed transition">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.122 1.534 5.855L.054 23.272a.75.75 0 00.917.928l5.528-1.451A11.93 11.93 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.723 9.723 0 01-5.02-1.394l-.36-.213-3.73.979.997-3.645-.235-.374A9.718 9.718 0 012.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/>
                        </svg>
                        Buka WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
