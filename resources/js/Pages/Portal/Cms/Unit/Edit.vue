<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ unit: { type: Object, required: true } });

const form = useForm({
    _method: 'put',
    nama_unit: props.unit.nama_unit,
    logo: props.unit.logo || null,
    thumbnail: props.unit.thumbnail || null,
    deskripsi: props.unit.deskripsi || '',
    tipe: props.unit.tipe,
    status: props.unit.status,
    api_url: props.unit.api_url || '',
    api_key: props.unit.api_key || '',
    icon: props.unit.icon || 'business',
    urutan: props.unit.urutan || 0,
});

const previewLogo = ref(props.unit.logo || null);
const previewThumbnail = ref(props.unit.thumbnail || null);

const submit = () => {
    form.post(route('portal.cms.unit.update', props.unit.id));
};
</script>

<template>
    <Head title="Edit Unit Usaha" />
    <PortalLayout>
        <template #header>Edit Unit Usaha</template>
        <div class="max-w-3xl space-y-6">
            <Link :href="route('portal.cms.unit.index')" class="text-sm text-slate-500 hover:text-emerald-700 transition">← Kembali</Link>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white border border-slate-200 rounded-xl p-6 space-y-4 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Informasi Unit</h3>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Unit *</label>
                            <input v-model="form.nama_unit" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition" />
                            <p v-if="form.errors.nama_unit" class="text-xs text-red-500 mt-1">{{ form.errors.nama_unit }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Icon (Material Symbol)</label>
                            <input v-model="form.icon" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Urutan</label>
                            <input v-model.number="form.urutan" type="number" min="0" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe *</label>
                            <select v-model="form.tipe" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                                <option value="internal">Internal</option>
                                <option value="external">External</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Status *</label>
                            <select v-model="form.status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                                <option value="aktif">Aktif</option>
                                <option value="coming_soon">Coming Soon</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi</label>
                        <textarea v-model="form.deskripsi" rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"></textarea>
                    </div>
                </div>

                <div v-if="form.tipe === 'external'" class="bg-white border border-slate-200 rounded-xl p-6 space-y-4 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Integrasi External</h3>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">API URL / Website URL</label>
                        <input v-model="form.api_url" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">API Key</label>
                        <input v-model="form.api_key" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition" />
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl p-6 space-y-4 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Media</h3>
                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Logo Upload -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-2">Logo Unit (Opsional)</label>
                            <div class="flex items-start gap-4">
                                <div class="w-16 h-16 border border-slate-200 rounded-xl overflow-hidden bg-slate-50 flex items-center justify-center shrink-0">
                                    <img v-if="previewLogo" :src="previewLogo" class="w-full h-full object-cover" />
                                    <span v-else class="material-symbols-outlined text-slate-400 text-2xl">image</span>
                                </div>
                                <div class="flex-1">
                                    <input 
                                        type="file" 
                                        accept="image/*"
                                        @change="e => { form.logo = e.target.files[0]; previewLogo = URL.createObjectURL(e.target.files[0]) }"
                                        class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition cursor-pointer"
                                    />
                                    <p class="text-[10px] text-slate-500 mt-1">Format: JPG, PNG, WebP (Maks 2MB)</p>
                                </div>
                            </div>
                            <p v-if="form.errors.logo" class="text-xs text-red-500 mt-1">{{ form.errors.logo }}</p>
                        </div>
                        
                        <!-- Thumbnail Upload -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-2">Thumbnail Unit (Opsional)</label>
                            <div class="flex items-start gap-4">
                                <div class="w-24 h-16 border border-slate-200 rounded-xl overflow-hidden bg-slate-50 flex items-center justify-center shrink-0">
                                    <img v-if="previewThumbnail" :src="previewThumbnail" class="w-full h-full object-cover" />
                                    <span v-else class="material-symbols-outlined text-slate-400 text-2xl">panorama</span>
                                </div>
                                <div class="flex-1">
                                    <input 
                                        type="file" 
                                        accept="image/*"
                                        @change="e => { form.thumbnail = e.target.files[0]; previewThumbnail = URL.createObjectURL(e.target.files[0]) }"
                                        class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition cursor-pointer"
                                    />
                                    <p class="text-[10px] text-slate-500 mt-1">Format: JPG, PNG, WebP (Maks 2MB)</p>
                                </div>
                            </div>
                            <p v-if="form.errors.thumbnail" class="text-xs text-red-500 mt-1">{{ form.errors.thumbnail }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Link :href="route('portal.cms.unit.index')" class="px-4 py-2.5 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm rounded-lg transition">Batal</Link>
                    <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-lg shadow-sm transition disabled:opacity-50">
                        <span class="material-symbols-outlined text-base">save</span>
                        {{ form.processing ? 'Menyimpan...' : 'Perbarui Unit' }}
                    </button>
                </div>
            </form>
        </div>
    </PortalLayout>
</template>
