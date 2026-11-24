<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    settings: { type: Object, required: true },
});

const form = useForm({ 
    ...props.settings,
    _method: 'put' 
});
const saving = ref(false);
const previewLogo = ref(props.settings.bumdes_logo || null);

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

const uploadingMembers = ref({});

const triggerMemberFileInput = (fieldKey) => {
    const el = document.getElementById(`input-image-${fieldKey}`);
    if (el) el.click();
};

const handleMemberImageUpload = (event, fieldKey) => {
    const file = event.target.files[0];
    if (!file) return;

    uploadingMembers.value[fieldKey] = true;

    compressImage(file, async (compressedFile) => {
        const formData = new FormData();
        formData.append('image', compressedFile);

        try {
            const response = await axios.post(route('portal.cms.upload-image'), formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            form[fieldKey] = response.data.url;
        } catch (error) {
            console.error('Member image upload failed:', error);
            alert('Gagal mengunggah foto. Pastikan file berupa gambar (JPG, PNG, WebP) dan berukuran kurang dari 5MB.');
        } finally {
            uploadingMembers.value[fieldKey] = false;
        }
    });
};

const removeMemberImage = (fieldKey) => {
    form[fieldKey] = '';
};

const customMembers = ref(
    Array.isArray(props.settings.org_bumdes_custom_members)
        ? JSON.parse(JSON.stringify(props.settings.org_bumdes_custom_members))
        : []
);

const isAddMemberModalOpen = ref(false);
const newMemberForm = ref({
    role: 'Pengawas BUMDes',
    name: '',
    image: '',
});
const previewNewMemberImage = ref(null);
const uploadingNewMember = ref(false);

const quickRoles = ['Pengawas BUMDes', 'Pengawas', 'Staff Operasional', 'Anggota Unit', 'Penasihat'];

const openAddMemberModal = (prefillRole = 'Pengawas BUMDes') => {
    newMemberForm.value = {
        role: prefillRole,
        name: '',
        image: '',
    };
    previewNewMemberImage.value = null;
    isAddMemberModalOpen.value = true;
};

const handleNewMemberImageUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    uploadingNewMember.value = true;
    compressImage(file, async (compressedFile) => {
        const formData = new FormData();
        formData.append('image', compressedFile);

        try {
            const response = await axios.post(route('portal.cms.upload-image'), formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            newMemberForm.value.image = response.data.url;
            previewNewMemberImage.value = response.data.url;
        } catch (error) {
            console.error('Member image upload failed:', error);
            alert('Gagal mengunggah foto. Pastikan format gambar sesuai.');
        } finally {
            uploadingNewMember.value = false;
        }
    });
};

const saveNewMember = () => {
    if (!newMemberForm.value.name.trim()) {
        alert('Silakan masukkan nama anggota / pengurus.');
        return;
    }
    customMembers.value.push({
        id: 'member_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
        role: newMemberForm.value.role.trim() || 'Anggota',
        name: newMemberForm.value.name.trim(),
        image: newMemberForm.value.image || '',
    });
    isAddMemberModalOpen.value = false;
};

const removeCustomMember = (index) => {
    if (confirm('Yakin ingin menghapus anggota ini dari struktur?')) {
        customMembers.value.splice(index, 1);
    }
};

const triggerCustomMemberFileInput = (memberId) => {
    const el = document.getElementById(`input-custom-image-${memberId}`);
    if (el) el.click();
};

const handleCustomMemberImageUpload = (event, member) => {
    const file = event.target.files[0];
    if (!file) return;

    uploadingMembers.value[member.id] = true;
    compressImage(file, async (compressedFile) => {
        const formData = new FormData();
        formData.append('image', compressedFile);

        try {
            const response = await axios.post(route('portal.cms.upload-image'), formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            member.image = response.data.url;
        } catch (error) {
            console.error('Upload failed:', error);
            alert('Gagal mengunggah foto.');
        } finally {
            uploadingMembers.value[member.id] = false;
        }
    });
};

const removeCustomMemberImage = (member) => {
    member.image = '';
};

const standardRoles = [
    { label: 'Komisaris (Kepala Desa)', nameKey: 'org_bumdes_pembina_name', imgKey: 'org_bumdes_pembina_image' },
    { label: 'Pengawas BUMDes', nameKey: 'org_bumdes_pengawas_name', imgKey: 'org_bumdes_pengawas_image' },
    { label: 'Direktur BUMDes', nameKey: 'org_bumdes_direktur_name', imgKey: 'org_bumdes_direktur_image' },
    { label: 'Sekretaris', nameKey: 'org_bumdes_sekretaris_name', imgKey: 'org_bumdes_sekretaris_image' },
    { label: 'Bendahara', nameKey: 'org_bumdes_bendahara_name', imgKey: 'org_bumdes_bendahara_image' },
    { label: 'Ka. Unit Ketahanan Pangan', nameKey: 'org_bumdes_unit_pangan_name', imgKey: 'org_bumdes_unit_pangan_image' },
    { label: 'Ka. Unit Perdagangan', nameKey: 'org_bumdes_unit_pasar_name', imgKey: 'org_bumdes_unit_pasar_image' },
    { label: 'Ka. Unit Simpan Pinjam', nameKey: 'org_bumdes_unit_sp_name', imgKey: 'org_bumdes_unit_sp_image' },
    { label: 'Ka. Unit Internet (Wifi)', nameKey: 'org_bumdes_unit_wifi_name', imgKey: 'org_bumdes_unit_wifi_image' },
];

const submit = () => {
    saving.value = true;
    form.org_bumdes_custom_members = customMembers.value;
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
                <div class="bg-white border border-slate-200 rounded-xl p-6 space-y-6 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] text-blue-600">group</span>
                                Struktur Organisasi / Pengurus BUMDes
                            </h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">Ubah nama dan foto pengurus BUMDes, atau tambahkan anggota/pengawas baru.</p>
                        </div>
                        <button
                            type="button"
                            @click="openAddMemberModal('Pengawas BUMDes')"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition self-start sm:self-auto cursor-pointer"
                        >
                            <span class="material-symbols-outlined text-sm font-bold">person_add</span>
                            <span>Tambah Anggota</span>
                        </button>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <!-- Standard Roles Cards -->
                        <div v-for="role in standardRoles" :key="role.nameKey" class="p-4 bg-white border border-slate-200 rounded-xl flex gap-4 items-center flex-col text-center hover:border-slate-300 transition relative">
                            <!-- Foto Upload Preview -->
                            <div class="w-24 h-24 border-2 border-blue-100 rounded-full overflow-hidden bg-slate-50 flex-shrink-0 flex items-center justify-center relative group cursor-pointer shadow-sm hover:shadow transition" @click="triggerMemberFileInput(role.imgKey)">
                                <input
                                    :id="`input-image-${role.imgKey}`"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="handleMemberImageUpload($event, role.imgKey)"
                                />
                                
                                <div v-if="uploadingMembers[role.imgKey]" class="flex items-center justify-center text-slate-500">
                                    <span class="animate-spin material-symbols-outlined text-sm">progress_activity</span>
                                </div>
                                <template v-else>
                                    <img v-if="form[role.imgKey]" :src="form[role.imgKey]" class="w-full h-full object-cover" />
                                    <div v-else class="text-center flex flex-col items-center justify-center text-slate-400 group-hover:text-blue-500 transition">
                                        <span class="material-symbols-outlined text-xl">add_a_photo</span>
                                        <span class="text-[9px] uppercase font-bold tracking-wider block mt-1">Upload</span>
                                    </div>
                                </template>
                            </div>

                            <!-- Name input & Delete Image -->
                            <div class="w-full space-y-2">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">{{ role.label }}</label>
                                    <input v-model="form[role.nameKey]" type="text" class="w-full rounded-lg border-slate-200 text-xs text-center focus:ring-blue-500 focus:border-blue-500 bg-white py-1.5 px-2.5" placeholder="Nama Lengkap" />
                                </div>
                                <div v-if="form[role.imgKey]" class="flex items-center justify-center pt-1">
                                    <button type="button" @click.stop="removeMemberImage(role.imgKey)" class="text-[10px] font-bold text-rose-600 hover:text-rose-800 transition bg-rose-50 px-2 py-1 rounded-md">Hapus Foto</button>
                                </div>
                            </div>
                        </div>

                        <!-- Custom / Dynamic Members Cards -->
                        <div v-for="(member, idx) in customMembers" :key="member.id" class="p-4 bg-blue-50/40 border border-blue-200 rounded-xl flex gap-4 items-center flex-col text-center relative hover:shadow-md transition">
                            <!-- Badge custom -->
                            <div class="w-full flex items-center justify-between">
                                <span class="text-[9px] font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded-full uppercase tracking-wider">Anggota Tambahan</span>
                                <button type="button" @click="removeCustomMember(idx)" class="text-rose-600 hover:text-rose-800 p-1 rounded hover:bg-rose-50 transition" title="Hapus Anggota Ini">
                                    <span class="material-symbols-outlined text-base">delete</span>
                                </button>
                            </div>

                            <!-- Foto Upload Preview -->
                            <div class="w-24 h-24 border-2 border-blue-200 rounded-full overflow-hidden bg-white flex-shrink-0 flex items-center justify-center relative group cursor-pointer shadow-sm hover:shadow transition" @click="triggerCustomMemberFileInput(member.id)">
                                <input
                                    :id="`input-custom-image-${member.id}`"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="handleCustomMemberImageUpload($event, member)"
                                />
                                <div v-if="uploadingMembers[member.id]" class="flex items-center justify-center text-slate-500">
                                    <span class="animate-spin material-symbols-outlined text-sm">progress_activity</span>
                                </div>
                                <template v-else>
                                    <img v-if="member.image" :src="member.image" class="w-full h-full object-cover" />
                                    <div v-else class="text-center flex flex-col items-center justify-center text-slate-400 group-hover:text-blue-500 transition">
                                        <span class="material-symbols-outlined text-xl">add_a_photo</span>
                                        <span class="text-[9px] uppercase font-bold tracking-wider block mt-1">Upload</span>
                                    </div>
                                </template>
                            </div>

                            <!-- Role & Name input -->
                            <div class="w-full space-y-2">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Jabatan / Posisi</label>
                                    <input v-model="member.role" type="text" class="w-full rounded-lg border-blue-200 text-xs text-center font-bold text-blue-800 focus:ring-blue-500 focus:border-blue-500 bg-white py-1 px-2.5" placeholder="Contoh: Pengawas" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Nama Lengkap</label>
                                    <input v-model="member.name" type="text" class="w-full rounded-lg border-slate-200 text-xs text-center focus:ring-blue-500 focus:border-blue-500 bg-white py-1.5 px-2.5" placeholder="Nama Lengkap" />
                                </div>
                                <div v-if="member.image" class="flex items-center justify-center pt-1">
                                    <button type="button" @click.stop="removeCustomMemberImage(member)" class="text-[10px] font-bold text-rose-600 hover:text-rose-800 transition bg-rose-50 px-2 py-1 rounded-md">Hapus Foto</button>
                                </div>
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

        <!-- Modal Tambah Anggota / Pengawas Baru -->
        <div v-if="isAddMemberModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600 text-lg">person_add</span>
                            Tambah Anggota / Pengurus
                        </h3>
                        <p class="text-xs text-slate-500">Masukkan data jabatan dan nama pengurus baru</p>
                    </div>
                    <button @click="isAddMemberModalOpen = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>

                <div class="p-5 space-y-4 text-xs">
                    <!-- Quick Role Suggestion Pills -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Pilihan Cepat Jabatan:</label>
                        <div class="flex flex-wrap gap-1.5">
                            <button
                                v-for="q in quickRoles"
                                :key="q"
                                type="button"
                                @click="newMemberForm.role = q"
                                :class="newMemberForm.role === q ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-semibold transition"
                            >
                                {{ q }}
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jabatan / Posisi *</label>
                        <input
                            v-model="newMemberForm.role"
                            type="text"
                            placeholder="Contoh: Pengawas, Pengawas BUMDes, Staff..."
                            required
                            class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:border-blue-500 focus:bg-white focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                        <input
                            v-model="newMemberForm.name"
                            type="text"
                            placeholder="Masukkan nama lengkap..."
                            required
                            class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:border-blue-500 focus:bg-white focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Foto (Opsional)</label>
                        <div class="flex items-center gap-3">
                            <div class="w-16 h-16 rounded-full border border-slate-200 overflow-hidden bg-slate-50 flex items-center justify-center shrink-0">
                                <img v-if="previewNewMemberImage" :src="previewNewMemberImage" class="w-full h-full object-cover" />
                                <span v-else class="material-symbols-outlined text-slate-400 text-xl">person</span>
                            </div>
                            <div class="flex-1">
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="handleNewMemberImageUpload"
                                    class="w-full text-xs text-slate-500 file:mr-2.5 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer"
                                />
                                <p v-if="uploadingNewMember" class="text-[10px] text-blue-600 mt-1 font-semibold flex items-center gap-1">
                                    <span class="animate-spin material-symbols-outlined text-xs">progress_activity</span>
                                    Sedang mengunggah foto...
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        @click="isAddMemberModalOpen = false"
                        class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-white text-xs"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="saveNewMember"
                        :disabled="uploadingNewMember"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-md shadow-blue-600/20 text-xs transition cursor-pointer"
                    >
                        Tambahkan ke Struktur
                    </button>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
