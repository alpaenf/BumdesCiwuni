<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    judul: '',
    thumbnail: null,
    ringkasan: '',
    konten: '',
    status: 'draft',
    published_at: '',
});

const previewThumbnail = ref(null);

const compressImage = (file, callback) => {
    if (!file.type.startsWith('image/')) return callback(file);
    
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = event => {
        const img = new Image();
        img.src = event.target.result;
        img.onload = () => {
            const canvas = document.createElement('canvas');
            let width = img.width;
            let height = img.height;
            const MAX_WIDTH = 1200;
            const MAX_HEIGHT = 1200;

            if (width > height) {
                if (width > MAX_WIDTH) {
                    height *= MAX_WIDTH / width;
                    width = MAX_WIDTH;
                }
            } else {
                if (height > MAX_HEIGHT) {
                    width *= MAX_HEIGHT / height;
                    height = MAX_HEIGHT;
                }
            }
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);
            
            canvas.toBlob((blob) => {
                const newFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                });
                callback(newFile);
            }, 'image/jpeg', 0.8);
        };
    };
};

const handleThumbnailUpload = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    previewThumbnail.value = URL.createObjectURL(file);
    
    compressImage(file, (compressedFile) => {
        form.thumbnail = compressedFile;
    });
};

const submit = () => {
    form.post(route('portal.cms.berita.store'));
};
</script>

<template>
    <Head title="Tulis Berita" />
    <PortalLayout>
        <template #header>Tulis Berita Baru</template>
        <div class="max-w-3xl space-y-6">
            <div class="flex items-center gap-2">
                <Link :href="route('portal.cms.berita.index')" class="text-sm text-slate-500 hover:text-blue-700 transition">← Kembali</Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white border border-slate-200 rounded-xl p-6 space-y-4 shadow-sm">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Berita *</label>
                        <input v-model="form.judul" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                        <p v-if="form.errors.judul" class="text-xs text-red-500 mt-1">{{ form.errors.judul }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">Thumbnail Berita (Opsional)</label>
                        <div class="flex items-start gap-4">
                            <div class="w-24 h-16 border border-slate-200 rounded-xl overflow-hidden bg-slate-50 flex items-center justify-center shrink-0">
                                <img v-if="previewThumbnail" :src="previewThumbnail" class="w-full h-full object-cover" />
                                <span v-else class="material-symbols-outlined text-slate-400 text-2xl">panorama</span>
                            </div>
                            <div class="flex-1">
                                <input 
                                    type="file" 
                                    accept="image/*"
                                    @change="handleThumbnailUpload"
                                    class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer"
                                />
                                <p class="text-[10px] text-slate-500 mt-1">Format: JPG, PNG, WebP (Maks 2MB)</p>
                            </div>
                        </div>
                        <p v-if="form.errors.thumbnail" class="text-xs text-red-500 mt-1">{{ form.errors.thumbnail }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Ringkasan</label>
                        <textarea v-model="form.ringkasan" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Ringkasan singkat berita..."></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Konten *</label>
                        <textarea v-model="form.konten" rows="10" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Tulis konten berita di sini..."></textarea>
                        <p v-if="form.errors.konten" class="text-xs text-red-500 mt-1">{{ form.errors.konten }}</p>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
                            <select v-model="form.status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Publish</label>
                            <input v-model="form.published_at" type="datetime-local" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Link :href="route('portal.cms.berita.index')" class="px-4 py-2.5 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm rounded-lg transition">Batal</Link>
                    <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-lg shadow-sm transition disabled:opacity-50">
                        <span class="material-symbols-outlined text-base">save</span>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Berita' }}
                    </button>
                </div>
            </form>
        </div>
    </PortalLayout>
</template>
