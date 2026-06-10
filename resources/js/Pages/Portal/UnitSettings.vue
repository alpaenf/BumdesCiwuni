<script setup>
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    unit: {
        type: Object,
        required: true,
    },
    settings: {
        type: Object,
        required: true,
    },
    galeri: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    hero_title: props.settings.hero_title || '',
    hero_subtitle: props.settings.hero_subtitle || '',
    hero_cta_text: props.settings.hero_cta_text || '',
    hero_cta_link: props.settings.hero_cta_link || '',
    feature_1_title: props.settings.feature_1_title || '',
    feature_1_desc: props.settings.feature_1_desc || '',
    feature_2_title: props.settings.feature_2_title || '',
    feature_2_desc: props.settings.feature_2_desc || '',
    feature_3_title: props.settings.feature_3_title || '',
    feature_3_desc: props.settings.feature_3_desc || '',
    contact_address: props.settings.contact_address || '',
    contact_phone: props.settings.contact_phone || '',
    contact_email: props.settings.contact_email || '',
    faq_items: props.settings.faq_items || [],
    news_items: props.settings.news_items || [],
    org_unit_sp_name: props.settings.org_unit_sp_name || '',
    org_unit_sp_image: props.settings.org_unit_sp_image || '',
    org_unit_sp_staff_name: props.settings.org_unit_sp_staff_name || '',
    org_unit_sp_staff_image: props.settings.org_unit_sp_staff_image || '',
});

const isSuccessMessageVisible = ref(false);
const uploadingIndices = ref({});
const isUploadingOrgChart = ref(false);
const isSidebarOpen = ref(false);

const logout = () => {
    router.post(route('logout'));
};

const addFaq = () => {
    form.faq_items.push({ question: '', answer: '' });
};

const removeFaq = (index) => {
    form.faq_items.splice(index, 1);
};

const addNews = () => {
    form.news_items.push({ title: '', date: '', content: '', image: '' });
};

const removeNews = (index) => {
    form.news_items.splice(index, 1);
};

const uploadingMembers = ref({});

const triggerMemberFileInput = (fieldKey) => {
    const el = document.getElementById(`input-image-${fieldKey}`);
    if (el) el.click();
};

const handleMemberImageUpload = async (event, fieldKey) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('image', file);

    uploadingMembers.value[fieldKey] = true;
    try {
        const response = await axios.post(route('unit.settings.upload-image', { slug: props.unit.slug }), formData, {
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
};

const removeMemberImage = (fieldKey) => {
    form[fieldKey] = '';
};

const uploadingNewsImage = ref({});

const triggerNewsFileInput = (index) => {
    const el = document.getElementById(`input-news-image-${index}`);
    if (el) el.click();
};

const handleNewsImageUpload = async (event, index) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('image', file);

    uploadingNewsImage.value[index] = true;
    try {
        const response = await axios.post(route('unit.settings.upload-image', { slug: props.unit.slug }), formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        form.news_items[index].image = response.data.url;
    } catch (error) {
        console.error('News image upload failed:', error);
        alert('Gagal mengunggah foto. Pastikan file berupa gambar (JPG, PNG, WebP) dan berukuran kurang dari 5MB.');
    } finally {
        uploadingNewsImage.value[index] = false;
    }
};

const removeNewsImage = (index) => {
    form.news_items[index].image = '';
};

const submit = () => {
    form.post(route('unit.settings.update', { slug: props.unit.slug }), {
        onSuccess: () => {
            isSuccessMessageVisible.value = true;
            setTimeout(() => {
                isSuccessMessageVisible.value = false;
            }, 3000);
        },
    });
};

// === Galeri Management ===
const galeriUploadForm = useForm({ foto: null, keterangan: '' });
const isUploadingGaleri = ref(false);

const triggerGaleriUpload = () => {
    document.getElementById('galeri-file-input')?.click();
};

const handleGaleriFile = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    galeriUploadForm.foto = file;
    isUploadingGaleri.value = true;
    galeriUploadForm.post(route('unit.galeri.store', { slug: props.unit.slug }), {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => {
            isUploadingGaleri.value = false;
            galeriUploadForm.reset();
            e.target.value = '';
        },
    });
};

const deleteGaleri = (id) => {
    if (!confirm('Hapus foto ini dari galeri?')) return;
    const delForm = useForm({});
    delForm.delete(route('unit.galeri.destroy', { slug: props.unit.slug, id: id }), { preserveScroll: true });
};
</script>

<template>
    <Head title="Pengaturan Landing Page" />

    <div class="h-screen flex overflow-hidden bg-[#f1f5f9] font-sans">
        <!-- Mobile Sidebar Overlay -->
        <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-slate-900/50 z-40 md:hidden backdrop-blur-sm"></div>

        <!-- Sidebar -->
        <aside :class="['fixed md:static inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-300 shrink-0 flex flex-col justify-between p-6 transition-transform duration-300', isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0']">
            <div class="space-y-8">
                <!-- Branding -->
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <img :src="settings.custom_logo || '/logo.png'" alt="Logo Unit" class="w-10 h-10 object-contain drop-shadow-sm" />
                        <div>
                            <h2 class="text-xs font-black text-slate-900 leading-tight">Admin Unit</h2>
                            <p class="text-[9px] text-slate-500 uppercase tracking-widest font-semibold mt-0.5">{{ unit.nama_unit }}</p>
                        </div>
                    </div>
                    <button @click="isSidebarOpen = false" class="md:hidden text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Nav -->
                <nav class="space-y-1">
                    <Link :href="route('unit.dashboard', { slug: unit.slug })" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">dashboard</span>
                        Dashboard
                    </Link>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 bg-blue-600/10 text-blue-600 font-bold text-xs rounded-xl border border-blue-600/20">
                        <span class="material-symbols-outlined text-lg">web</span>
                        Pengaturan Landing Page
                    </a>
                </nav>
            </div>

            <!-- User Info & Logout -->
            <div class="border-t border-slate-300 pt-5 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                        {{ $page.props.auth.user.nama.charAt(0) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-bold text-slate-900 truncate">{{ $page.props.auth.user.nama }}</p>
                        <p class="text-[9px] text-slate-500 truncate">{{ $page.props.auth.user.email }}</p>
                    </div>
                </div>
                <button @click="logout" class="w-full flex items-center justify-center gap-2 py-2.5 bg-slate-50 border border-slate-200 hover:bg-red-950/20 hover:border-red-900/30 text-slate-500 hover:text-red-400 text-xs font-bold rounded-xl transition">
                    Keluar Aplikasi
                    <span class="material-symbols-outlined text-sm">logout</span>
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col min-w-0 h-screen overflow-y-auto">
            <!-- Top Nav -->
            <header class="sticky top-0 z-30 h-16 border-b border-slate-300 bg-white/80 backdrop-blur-md px-4 sm:px-6 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <button @click="isSidebarOpen = true" class="md:hidden w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-300 text-slate-600 shadow-sm">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <div>
                        <h1 class="text-sm font-bold text-slate-900 leading-tight">Pengaturan Landing Page</h1>
                        <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest hidden sm:block">Manajemen Konten Publik</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <a :href="route('unit.welcome', { slug: unit.slug })" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] rounded-lg border border-slate-300 transition">
                        <span class="material-symbols-outlined text-[14px]">public</span>
                        Lihat Website
                    </a>
                </div>
            </header>

            <!-- Content Container -->
            <div class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-5xl mx-auto w-full">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Konten Landing Page {{ unit.nama_unit }}</h2>
                    <p class="text-xs text-slate-500 mt-1">Kelola teks banner, layanan, struktur organisasi, dan kontak khusus {{ unit.nama_unit }}.</p>
                </div>
                <a :href="route('unit.welcome', { slug: unit.slug })" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-slate-200 hover:border-slate-300 text-xs font-semibold text-slate-700 bg-white rounded-lg shadow-sm transition hover:bg-slate-50">
                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                    Lihat Landing Page
                </a>
            </div>

            <!-- Toast Success -->
            <transition name="fade">
                <div v-if="isSuccessMessageVisible" class="flex items-center gap-2 p-3 bg-blue-50 text-blue-800 border border-blue-100 rounded-lg text-sm font-semibold shadow-sm">
                    <span class="material-symbols-outlined text-[20px] text-blue-600">check_circle</span>
                    Konten Landing Page berhasil disimpan!
                </div>
            </transition>

            <form @submit.prevent="submit" class="space-y-6">


                <!-- HERO SECTION -->
                <div class="bg-white border border-outline-variant rounded-xl p-5 shadow-sm space-y-4">
                    <h3 class="text-sm font-bold text-primary flex items-center gap-2 border-b border-slate-100 pb-2">
                        <span class="material-symbols-outlined text-[18px]">photo_library</span>
                        Section Hero (Banner Utama)
                    </h3>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Judul Utama (Hero Title)</label>
                            <input v-model="form.hero_title" type="text" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary" />
                            <span v-if="form.errors.hero_title" class="text-[10px] text-red-600 font-bold mt-1 block">{{ form.errors.hero_title }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Sub-judul / Penjelasan Singkat</label>
                            <textarea v-model="form.hero_subtitle" rows="3" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary"></textarea>
                            <span v-if="form.errors.hero_subtitle" class="text-[10px] text-red-600 font-bold mt-1 block">{{ form.errors.hero_subtitle }}</span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Teks Tombol Aksi (CTA)</label>
                            <input v-model="form.hero_cta_text" type="text" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary" />
                            <span v-if="form.errors.hero_cta_text" class="text-[10px] text-red-600 font-bold mt-1 block">{{ form.errors.hero_cta_text }}</span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Link / Rute Tombol Aksi</label>
                            <input v-model="form.hero_cta_link" type="text" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary" />
                            <span v-if="form.errors.hero_cta_link" class="text-[10px] text-red-600 font-bold mt-1 block">{{ form.errors.hero_cta_link }}</span>
                        </div>
                    </div>
                </div>



                <!-- FEATURES / SERVICES SECTION -->
                <div class="bg-white border border-outline-variant rounded-xl p-5 shadow-sm space-y-5">
                    <h3 class="text-sm font-bold text-primary flex items-center gap-2 border-b border-slate-100 pb-2">
                        <span class="material-symbols-outlined text-[18px]">list_alt</span>
                        Section Fitur & Layanan Utama
                    </h3>

                    <!-- Fitur 1 -->
                    <div class="p-4 bg-white rounded-lg space-y-3 border border-outline-variant">
                        <h4 class="text-xs font-bold text-slate-700">Layanan 1</h4>
                        <div class="grid gap-3 md:grid-cols-3">
                            <div class="md:col-span-1">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Nama Layanan</label>
                                <input v-model="form.feature_1_title" type="text" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary bg-white" />
                                <span v-if="form.errors.feature_1_title" class="text-[10px] text-red-600 font-bold mt-1 block">{{ form.errors.feature_1_title }}</span>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Penjelasan Singkat</label>
                                <input v-model="form.feature_1_desc" type="text" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary bg-white" />
                                <span v-if="form.errors.feature_1_desc" class="text-[10px] text-red-600 font-bold mt-1 block">{{ form.errors.feature_1_desc }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Fitur 2 -->
                    <div class="p-4 bg-white rounded-lg space-y-3 border border-outline-variant">
                        <h4 class="text-xs font-bold text-slate-700">Layanan 2</h4>
                        <div class="grid gap-3 md:grid-cols-3">
                            <div class="md:col-span-1">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Nama Layanan</label>
                                <input v-model="form.feature_2_title" type="text" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary bg-white" />
                                <span v-if="form.errors.feature_2_title" class="text-[10px] text-red-600 font-bold mt-1 block">{{ form.errors.feature_2_title }}</span>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Penjelasan Singkat</label>
                                <input v-model="form.feature_2_desc" type="text" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary bg-white" />
                                <span v-if="form.errors.feature_2_desc" class="text-[10px] text-red-600 font-bold mt-1 block">{{ form.errors.feature_2_desc }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Fitur 3 -->
                    <div class="p-4 bg-white rounded-lg space-y-3 border border-outline-variant">
                        <h4 class="text-xs font-bold text-slate-700">Layanan 3</h4>
                        <div class="grid gap-3 md:grid-cols-3">
                            <div class="md:col-span-1">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Nama Layanan</label>
                                <input v-model="form.feature_3_title" type="text" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary bg-white" />
                                <span v-if="form.errors.feature_3_title" class="text-[10px] text-red-600 font-bold mt-1 block">{{ form.errors.feature_3_title }}</span>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Penjelasan Singkat</label>
                                <input v-model="form.feature_3_desc" type="text" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary bg-white" />
                                <span v-if="form.errors.feature_3_desc" class="text-[10px] text-red-600 font-bold mt-1 block">{{ form.errors.feature_3_desc }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STRUKTUR ORGANISASI SECTION -->
                <div class="bg-white border border-outline-variant rounded-xl p-5 shadow-sm space-y-6">
                    <h3 class="text-sm font-bold text-primary flex items-center gap-2 border-b border-slate-100 pb-2">
                        <span class="material-symbols-outlined text-[18px]">group</span>
                        Anggota & Foto Struktur Organisasi
                    </h3>
                    <p class="text-[11px] text-slate-500">Ubah nama dan unggah foto untuk masing-masing pengurus BUMDes. Jika foto kosong, inisial nama dengan gradien hijau premium akan ditampilkan sebagai gantinya.</p>

                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Helper component generator inside form -->
                        <div v-for="role in [
                            { label: 'Kepala Unit / Manager', nameKey: 'org_unit_sp_name', imgKey: 'org_unit_sp_image' },
                            { label: 'Staf Unit', nameKey: 'org_unit_sp_staff_name', imgKey: 'org_unit_sp_staff_image' },
                        ]" :key="role.nameKey" class="p-4 bg-white border border-outline-variant rounded-xl flex gap-4 items-center">
                            
                            <!-- Foto Upload Preview -->
                            <div class="w-20 h-20 border border-slate-200 rounded-full overflow-hidden bg-slate-50 flex-shrink-0 flex items-center justify-center relative group cursor-pointer" @click="triggerMemberFileInput(role.imgKey)">
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
                                    <div v-else class="text-center flex flex-col items-center justify-center text-slate-400">
                                        <span class="material-symbols-outlined text-lg">add_a_photo</span>
                                        <span class="text-[8px] uppercase font-bold tracking-wider block mt-0.5">Foto</span>
                                    </div>
                                </template>
                            </div>

                            <!-- Name input & Delete Image -->
                            <div class="flex-grow space-y-2">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">{{ role.label }}</label>
                                    <input v-model="form[role.nameKey]" type="text" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary bg-white py-1 px-2.5" placeholder="Nama Lengkap" />
                                </div>
                                <div v-if="form[role.imgKey]" class="flex items-center justify-between">
                                    <span class="text-[9px] text-blue-600 font-semibold flex items-center gap-0.5"><span class="material-symbols-outlined text-[12px]">check_circle</span> Terunggah</span>
                                    <button type="button" @click.stop="removeMemberImage(role.imgKey)" class="text-[9px] font-bold text-rose-600 hover:underline">Hapus Foto</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- BERITA SECTION -->
                <div class="bg-white border border-outline-variant rounded-xl p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-sm font-bold text-primary flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">newspaper</span>
                            Section Berita & Pengumuman
                        </h3>
                        <button type="button" @click="addNews" class="inline-flex items-center gap-1 text-[11px] font-bold text-primary hover:underline">
                            <span class="material-symbols-outlined text-[14px]">add</span> Tambah Berita
                        </button>
                    </div>

                    <div v-if="form.news_items.length === 0" class="text-xs text-slate-400 py-4 text-center">
                        Belum ada berita. Klik "Tambah Berita" untuk menulis pengumuman baru.
                    </div>

                    <div class="space-y-4">
                        <div v-for="(item, index) in form.news_items" :key="index" class="p-4 bg-white border border-outline-variant rounded-lg space-y-3 relative group">
                            <button type="button" @click="removeNews(index)" class="absolute top-2 right-2 text-rose-600 hover:text-rose-800 opacity-50 group-hover:opacity-100 transition">
                                <span class="material-symbols-outlined text-base">delete</span>
                            </button>
                            
                            <div class="grid gap-4 md:grid-cols-4">
                                <div class="md:col-span-1">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Gambar/Thumbnail</label>
                                    <div class="w-full aspect-video border border-slate-200 rounded-lg overflow-hidden bg-slate-50 flex flex-col items-center justify-center relative group cursor-pointer" @click="triggerNewsFileInput(index)">
                                        <input
                                            :id="`input-news-image-${index}`"
                                            type="file"
                                            accept="image/*"
                                            class="hidden"
                                            @change="handleNewsImageUpload($event, index)"
                                        />
                                        <div v-if="uploadingNewsImage[index]" class="flex items-center justify-center text-slate-500">
                                            <span class="animate-spin material-symbols-outlined text-sm">progress_activity</span>
                                        </div>
                                        <template v-else>
                                            <img v-if="item.image" :src="item.image" class="w-full h-full object-cover" />
                                            <div v-else class="text-center text-slate-400">
                                                <span class="material-symbols-outlined text-lg">add_a_photo</span>
                                                <span class="text-[8px] uppercase tracking-wider block mt-0.5">Upload</span>
                                            </div>
                                        </template>
                                    </div>
                                    <button v-if="item.image" type="button" @click.stop="removeNewsImage(index)" class="text-[9px] font-bold text-rose-600 hover:underline mt-1 block w-full text-center">Hapus Gambar</button>
                                </div>
                                <div class="md:col-span-3 space-y-3">
                                    <div class="grid gap-3 grid-cols-2">
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Judul Berita</label>
                                            <input v-model="item.title" type="text" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary bg-white" placeholder="Misal: Penyaluran Dana 2026..." required />
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Tanggal</label>
                                            <input v-model="item.date" type="text" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary bg-white" placeholder="Misal: 12 April 2026" />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Isi Berita</label>
                                        <textarea v-model="item.content" rows="3" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary bg-white" placeholder="Masukkan isi pengumuman atau berita di sini..." required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ SECTION -->
                <div class="bg-white border border-outline-variant rounded-xl p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-sm font-bold text-primary flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">quiz</span>
                            Section FAQ (Tanya Jawab)
                        </h3>
                        <button type="button" @click="addFaq" class="inline-flex items-center gap-1 text-[11px] font-bold text-primary hover:underline">
                            <span class="material-symbols-outlined text-[14px]">add</span> Tambah Pertanyaan
                        </button>
                    </div>

                    <div v-if="form.faq_items.length === 0" class="text-xs text-slate-400 py-4 text-center">
                        Belum ada pertanyaan FAQ. Klik "Tambah Pertanyaan" untuk memulai.
                    </div>

                    <div class="space-y-4">
                        <div v-for="(item, index) in form.faq_items" :key="index" class="p-4 bg-white border border-outline-variant rounded-lg space-y-3 relative group">
                            <button type="button" @click="removeFaq(index)" class="absolute top-2 right-2 text-rose-600 hover:text-rose-800 opacity-50 group-hover:opacity-100 transition">
                                <span class="material-symbols-outlined text-base">delete</span>
                            </button>
                            
                            <div class="grid gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Pertanyaan #{{ index + 1 }}</label>
                                    <input v-model="item.question" type="text" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary bg-white" placeholder="Misal: Berapa setoran awal minimal?" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Jawaban</label>
                                    <textarea v-model="item.answer" rows="2" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary bg-white" placeholder="Masukkan jawaban penjelasan di sini..." required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- CONTACT & ADDRESS SECTION -->
                <div class="bg-white border border-outline-variant rounded-xl p-5 shadow-sm space-y-4">
                    <h3 class="text-sm font-bold text-primary flex items-center gap-2 border-b border-slate-100 pb-2">
                        <span class="material-symbols-outlined text-[18px]">contact_support</span>
                        Section Informasi Kontak & Alamat
                    </h3>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Alamat Lengkap Kantor Unit</label>
                            <input v-model="form.contact_address" type="text" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary" />
                            <span v-if="form.errors.contact_address" class="text-[10px] text-red-600 font-bold mt-1 block">{{ form.errors.contact_address }}</span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nomor Telepon / WhatsApp Unit</label>
                            <input v-model="form.contact_phone" type="text" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary" />
                            <span v-if="form.errors.contact_phone" class="text-[10px] text-red-600 font-bold mt-1 block">{{ form.errors.contact_phone }}</span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Email Kantor Unit</label>
                            <input v-model="form.contact_email" type="email" class="w-full rounded-lg border-slate-200 text-xs focus:ring-primary focus:border-primary" />
                            <span v-if="form.errors.contact_email" class="text-[10px] text-red-600 font-bold mt-1 block">{{ form.errors.contact_email }}</span>
                        </div>
                    </div>
                </div>

                <!-- GALERI SECTION -->
                <div class="bg-white border border-outline-variant rounded-xl p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-sm font-bold text-primary flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">photo_library</span>
                            Galeri Unit
                        </h3>
                        <button type="button" @click="triggerGaleriUpload" :disabled="isUploadingGaleri" class="inline-flex items-center gap-1 text-[11px] font-bold text-primary hover:underline disabled:opacity-50">
                            <span class="material-symbols-outlined text-[14px]">add_photo_alternate</span>
                            {{ isUploadingGaleri ? 'Mengupload...' : 'Upload Foto' }}
                        </button>
                        <input id="galeri-file-input" type="file" accept="image/*" class="hidden" @change="handleGaleriFile" />
                    </div>
                    <div v-if="galeri.length === 0" class="text-xs text-slate-400 py-6 text-center">
                        Belum ada foto di galeri. Klik "Upload Foto" untuk menambahkan.
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <div v-for="item in galeri" :key="item.id" class="relative group rounded-lg overflow-hidden border border-slate-200">
                            <img :src="item.foto" class="w-full h-32 object-cover" />
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                <button type="button" @click="deleteGaleri(item.id)" class="flex items-center gap-1 bg-red-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold">
                                    <span class="material-symbols-outlined text-xs">delete</span> Hapus
                                </button>
                            </div>
                            <p v-if="item.keterangan" class="text-[10px] text-slate-600 px-2 py-1 truncate">{{ item.keterangan }}</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center gap-3">
                    <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-blue-800 text-white font-semibold text-xs rounded-lg shadow transition disabled:opacity-50">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Semua Konten' }}
                    </button>
                </div>
            </form>
            </div>
        </main>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
