<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: { type: Object, required: true },
});

const form = useForm({ ...props.settings });

const submit = () => {
    form.put(route('portal.cms.banner.update'));
};
</script>

<template>
    <Head title="Kelola Banner" />

    <PortalLayout>
        <template #header>Kelola Banner</template>

        <div class="max-w-3xl space-y-6">
            <div>
                <h2 class="text-xl font-bold text-emerald-800">Banner Hero</h2>
                <p class="text-sm text-slate-500">Kelola tampilan hero section di halaman utama website.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white border border-slate-200 rounded-xl p-6 space-y-4 shadow-sm">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Hero</label>
                        <input v-model="form.hero_title" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition" />
                        <p v-if="form.errors.hero_title" class="text-xs text-red-500 mt-1">{{ form.errors.hero_title }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Subjudul Hero</label>
                        <textarea v-model="form.hero_subtitle" rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"></textarea>
                        <p v-if="form.errors.hero_subtitle" class="text-xs text-red-500 mt-1">{{ form.errors.hero_subtitle }}</p>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Teks Tombol CTA</label>
                            <input v-model="form.hero_cta_text" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Link Tombol CTA</label>
                            <input v-model="form.hero_cta_link" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition" />
                        </div>
                    </div>
                </div>

                <!-- Preview -->
                <div class="bg-gradient-to-br from-emerald-50 to-white border border-slate-200 rounded-xl p-8 text-center space-y-3 shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Preview Hero</p>
                    <h3 class="text-xl font-extrabold text-emerald-800">{{ form.hero_title || 'Judul Hero...' }}</h3>
                    <p class="text-sm text-[#404940] max-w-lg mx-auto">{{ form.hero_subtitle || 'Subjudul hero...' }}</p>
                    <button type="button" class="inline-flex items-center gap-1 px-4 py-2 bg-emerald-600 text-white font-semibold text-xs rounded-lg">
                        {{ form.hero_cta_text || 'CTA' }}
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </button>
                </div>

                <div class="flex justify-end">
                    <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-lg shadow-sm transition disabled:opacity-50">
                        <span class="material-symbols-outlined text-base">save</span>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Banner' }}
                    </button>
                </div>
            </form>
        </div>
    </PortalLayout>
</template>
