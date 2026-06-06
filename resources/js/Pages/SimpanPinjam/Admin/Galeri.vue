<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ galeri: Array });

const page = usePage();
const flash = computed(() => page.props.flash);

// Form upload foto baru
const form = useForm({
    foto: null,
    keterangan: '',
});

const previewUrl = ref(null);
const fileInput = ref(null);

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.foto = file;
    previewUrl.value = URL.createObjectURL(file);
};

const resetForm = () => {
    form.reset();
    previewUrl.value = null;
    if (fileInput.value) fileInput.value.value = '';
};

const submitUpload = () => {
    form.post(route('admin.galeri.store'), {
        forceFormData: true,
        onSuccess: () => resetForm(),
    });
};

// Edit keterangan
const editingId = ref(null);
const editKeterangan = ref('');

const startEdit = (item) => {
    editingId.value = item.id;
    editKeterangan.value = item.keterangan || '';
};

const saveEdit = (item) => {
    router.put(route('admin.galeri.update', item.id), {
        keterangan: editKeterangan.value,
        urutan: item.urutan,
    }, {
        preserveScroll: true,
        onSuccess: () => { editingId.value = null; },
    });
};

// Hapus foto
const confirmDelete = (item) => {
    if (confirm(`Hapus foto "${item.keterangan || 'ini'}"? Tindakan ini tidak dapat dibatalkan.`)) {
        router.delete(route('admin.galeri.destroy', item.id), { preserveScroll: true });
    }
};

// Drag to reorder (simple swap)
const dragItem = ref(null);

const onDragStart = (item) => { dragItem.value = item; };
const onDrop = (targetItem) => {
    if (!dragItem.value || dragItem.value.id === targetItem.id) return;
    const urutanA = dragItem.value.urutan;
    const urutanB = targetItem.urutan;
    router.put(route('admin.galeri.update', dragItem.value.id), { urutan: urutanB, keterangan: dragItem.value.keterangan }, { preserveScroll: true });
    router.put(route('admin.galeri.update', targetItem.id), { urutan: urutanA, keterangan: targetItem.keterangan }, { preserveScroll: true });
    dragItem.value = null;
};
</script>

<template>
    <Head title="Kelola Galeri" />
    <AuthenticatedLayout>
        <template #header>Kelola Galeri Unit</template>
        <div class="space-y-6">

            <!-- Flash Message -->
            <div v-if="flash?.success" class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700 font-medium flex items-center gap-2">
                <span class="material-symbols-outlined text-base">check_circle</span>
                {{ flash.success }}
            </div>

            <!-- Upload Form -->
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white shadow-sm p-6">
                <h2 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[color:var(--color-primary)]">add_photo_alternate</span>
                    Tambah Foto Galeri
                </h2>

                <form @submit.prevent="submitUpload" class="flex flex-col gap-4 sm:flex-row sm:items-start">
                    <!-- Preview & File Input -->
                    <div class="flex-shrink-0">
                        <div
                            class="w-32 h-32 rounded-xl border-2 border-dashed border-[color:var(--color-outline-variant)] bg-slate-50 flex items-center justify-center overflow-hidden cursor-pointer hover:border-[color:var(--color-primary)] transition relative"
                            @click="fileInput?.click()"
                        >
                            <img v-if="previewUrl" :src="previewUrl" class="w-full h-full object-cover" />
                            <div v-else class="text-center text-slate-400">
                                <span class="material-symbols-outlined text-3xl">image</span>
                                <p class="text-[10px] mt-1">Klik pilih foto</p>
                            </div>
                        </div>
                        <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onFileChange" />
                        <p v-if="form.errors.foto" class="mt-1 text-xs text-red-500">{{ form.errors.foto }}</p>
                    </div>

                    <!-- Fields -->
                    <div class="flex-1 space-y-3">
                        <div>
                            <label class="text-xs font-semibold text-slate-700 mb-1 block">Keterangan Foto <span class="font-normal text-slate-400">(opsional)</span></label>
                            <input
                                v-model="form.keterangan"
                                type="text"
                                placeholder="Contoh: Kegiatan pelayanan nasabah, Juni 2026"
                                class="w-full rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:border-[color:var(--color-primary)] focus:outline-none"
                            />
                        </div>
                        <div class="flex gap-2">
                            <button
                                type="submit"
                                :disabled="!form.foto || form.processing"
                                class="inline-flex items-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed transition"
                            >
                                <span class="material-symbols-outlined text-base">upload</span>
                                {{ form.processing ? 'Mengupload...' : 'Upload Foto' }}
                            </button>
                            <button v-if="previewUrl" type="button" @click="resetForm" class="rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                                Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Galeri Grid -->
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[color:var(--color-primary)]">photo_library</span>
                        Foto Galeri ({{ galeri.length }})
                    </h2>
                    <p class="text-xs text-slate-400">Drag &amp; drop untuk mengatur urutan tampil</p>
                </div>

                <!-- Empty State -->
                <div v-if="galeri.length === 0" class="text-center py-16">
                    <span class="material-symbols-outlined text-5xl text-slate-300">photo_library</span>
                    <p class="mt-3 text-sm text-slate-400">Belum ada foto. Upload foto pertama di atas!</p>
                </div>

                <!-- Grid -->
                <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <div
                        v-for="item in galeri" :key="item.id"
                        class="group relative rounded-xl overflow-hidden border border-[color:var(--color-outline-variant)] bg-slate-50 cursor-grab active:cursor-grabbing hover:border-[color:var(--color-primary)] hover:shadow-md transition-all aspect-square"
                        draggable="true"
                        @dragstart="onDragStart(item)"
                        @dragover.prevent
                        @drop="onDrop(item)"
                    >
                        <!-- Foto -->
                        <img :src="item.foto" :alt="item.keterangan || 'Galeri'" class="w-full h-full object-cover" />

                        <!-- Overlay -->
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors duration-200 flex flex-col items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                            <button
                                @click.stop="startEdit(item)"
                                class="w-8 h-8 bg-white rounded-full flex items-center justify-center hover:bg-emerald-50 transition shadow"
                                title="Edit keterangan"
                            >
                                <span class="material-symbols-outlined text-sm text-slate-700">edit</span>
                            </button>
                            <button
                                @click.stop="confirmDelete(item)"
                                class="w-8 h-8 bg-white rounded-full flex items-center justify-center hover:bg-red-50 transition shadow"
                                title="Hapus foto"
                            >
                                <span class="material-symbols-outlined text-sm text-red-500">delete</span>
                            </button>
                        </div>

                        <!-- Keterangan badge -->
                        <div v-if="item.keterangan" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent px-2 py-2">
                            <p class="text-white text-[9px] font-medium leading-tight truncate">{{ item.keterangan }}</p>
                        </div>

                        <!-- Urutan badge -->
                        <div class="absolute top-1.5 left-1.5 w-5 h-5 bg-black/50 text-white text-[8px] font-bold rounded flex items-center justify-center">
                            {{ item.urutan || '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div v-if="editingId" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" @click.self="editingId = null">
                <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
                    <h3 class="font-bold text-slate-800 mb-4">Edit Keterangan Foto</h3>
                    <input
                        v-model="editKeterangan"
                        type="text"
                        placeholder="Keterangan foto..."
                        class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-3 py-2.5 text-sm focus:outline-none focus:border-[color:var(--color-primary)]"
                    />
                    <div class="flex gap-2 mt-4">
                        <button
                            @click="saveEdit(galeri.find(g => g.id === editingId))"
                            class="flex-1 rounded-lg bg-[color:var(--color-primary)] px-4 py-2.5 text-sm font-semibold text-white hover:opacity-90 transition"
                        >
                            Simpan
                        </button>
                        <button @click="editingId = null" class="flex-1 rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
