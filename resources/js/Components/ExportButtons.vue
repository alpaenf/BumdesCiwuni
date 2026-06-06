<script setup>
const props = defineProps({
    pdfUrl:   { type: String, required: true },
    excelUrl: { type: String, default: null },
});
</script>

<template>
    <div class="flex items-center gap-2">
        <a :href="pdfUrl" target="_blank"
            class="inline-flex items-center gap-1.5 rounded-lg border border-red-500 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 transition">
            <span class="material-symbols-outlined text-sm">picture_as_pdf</span>
            Unduh PDF
        </a>
        <a v-if="excelUrl" :href="excelUrl" target="_blank"
            class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-600 px-3 py-2 text-xs font-semibold text-slate-900 hover:bg-emerald-50 transition">
            <span class="material-symbols-outlined text-sm">table_view</span>
            Unduh Excel
        </a>
    </div>
</template>
