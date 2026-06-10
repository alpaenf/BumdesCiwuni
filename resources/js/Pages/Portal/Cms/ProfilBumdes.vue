<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    settings: { type: Object, required: true },
});

const form = useForm({ 
    ...props.settings,
    _method: 'put' 
});
const saving = ref(false);
const previewLogo = ref(props.settings.bumdes_logo || null);
const previewStruktur = ref(props.settings.struktur_organisasi || null);

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

const handleLogoUpload = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    previewLogo.value = URL.createObjectURL(file);
    
    compressImage(file, (compressedFile) => {
        form.bumdes_logo = compressedFile;
    });
};

const handleStrukturUpload = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    previewStruktur.value = URL.createObjectURL(file);
    
    compressImage(file, (compressedFile) => {
        form.struktur_organisasi = compressedFile;
    });
};

const submit = () => {
    saving.value = true;
    form.post(route('portal.cms.profil.update'), {
        onFinish: () => saving.value = false,
    });
};
</script>

<template>
    <Head title="Profil BUMDes" />

    <PortalLayout>
        <template #header>Kelola Profil BUMDes</template>

        <div class="max-w-3xl space-y-6">
            <div>
                <h2 class="text-xl font-bold text-blue-800">Profil BUMDes</h2>
                <p class="text-sm text-slate-500">Kelola informasi umum tentang BUMDes Dammar Wulan.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Identitas -->
                <div class="bg-white border border-slate-200 rounded-xl p-6 space-y-4 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Identitas</h3>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama BUMDes</label>
                            <input v-model="form.bumdes_name" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                            <p v-if="form.errors.bumdes_name" class="text-xs text-red-500 mt-1">{{ form.errors.bumdes_name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tagline</label>
                            <input v-model="form.bumdes_tagline" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">Logo BUMDes (Auto Compress)</label>
                        <div class="flex items-start gap-4">
                            <div class="w-20 h-20 border border-slate-200 rounded-xl overflow-hidden bg-slate-50 flex items-center justify-center shrink-0">
                                <img v-if="previewLogo" :src="previewLogo" class="w-full h-full object-contain p-2" />
                                <span v-else class="material-symbols-outlined text-slate-400 text-2xl">verified</span>
                            </div>
                            <div class="flex-1">
                                <input 
                                    type="file" 
                                    accept="image/*"
                                    @change="handleLogoUpload"
                                    class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer"
                                />
                                <p class="text-[10px] text-slate-500 mt-1.5">Gambar akan dikompres otomatis ke resolusi maksimal 1200px dan format JPG 80% untuk menghemat penyimpanan server BUMDes.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tentang -->
                <div class="bg-white border border-slate-200 rounded-xl p-6 space-y-4 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Tentang Kami</h3>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Tentang</label>
                        <input v-model="form.about_title" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi</label>
                        <textarea v-model="form.about_description" rows="4" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Sejarah Singkat</label>
                        <textarea v-model="form.about_history" rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"></textarea>
                    </div>
                </div>

                <!-- Visi Misi -->
                <div class="bg-white border border-slate-200 rounded-xl p-6 space-y-4 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Visi & Misi</h3>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Visi</label>
                        <textarea v-model="form.visi" rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Misi</label>
                        <textarea v-model="form.misi" rows="4" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="1. Misi pertama&#10;2. Misi kedua"></textarea>
                    </div>
                </div>

                <!-- Struktur Organisasi -->
                <div class="bg-white border border-slate-200 rounded-xl p-6 space-y-4 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Struktur Organisasi</h3>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">Bagan / Struktur Pengurus (Auto Compress)</label>
                        <div class="flex flex-col md:flex-row items-start gap-4">
                            <div class="w-full md:w-64 border border-slate-200 rounded-xl overflow-hidden bg-slate-50 flex items-center justify-center shrink-0 min-h-32">
                                <img v-if="previewStruktur" :src="previewStruktur" class="w-full h-auto object-contain" />
                                <span v-else class="material-symbols-outlined text-slate-400 text-3xl">account_tree</span>
                            </div>
                            <div class="flex-1 w-full">
                                <input 
                                    type="file" 
                                    accept="image/*"
                                    @change="handleStrukturUpload"
                                    class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer"
                                />
                                <p class="text-[10px] text-slate-500 mt-1.5">Unggah gambar struktur organisasi. Gambar akan dikompres otomatis ke resolusi maksimal 1200px (format JPG 80%).</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kontak -->
                <div class="bg-white border border-slate-200 rounded-xl p-6 space-y-4 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Kontak</h3>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat</label>
                        <textarea v-model="form.contact_address" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"></textarea>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Telepon</label>
                            <input v-model="form.contact_phone" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
                            <input v-model="form.contact_email" type="email" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Google Maps Embed (iframe)</label>
                        <textarea v-model="form.google_maps_embed" rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder='<iframe src="..." ...></iframe>'></textarea>
                    </div>
                </div>

                <!-- Footer & Social -->
                <div class="bg-white border border-slate-200 rounded-xl p-6 space-y-4 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Footer & Sosial Media</h3>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Footer Text</label>
                        <input v-model="form.footer_text" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                    </div>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Facebook</label>
                            <input v-model="form.social_facebook" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Instagram</label>
                            <input v-model="form.social_instagram" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">YouTube</label>
                            <input v-model="form.social_youtube" type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-lg shadow-sm transition disabled:opacity-50">
                        <span class="material-symbols-outlined text-base">save</span>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </div>
            </form>
        </div>
    </PortalLayout>
</template>
