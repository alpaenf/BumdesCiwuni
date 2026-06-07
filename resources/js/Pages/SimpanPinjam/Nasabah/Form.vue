<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    nasabah: { type: Object, default: null },
});

const isEdit = computed(() => !!props.nasabah);

const form = useForm({
    _method: isEdit.value ? 'PUT' : undefined,
    nama:              props.nasabah?.nama ?? '',
    nik:               props.nasabah?.nik ?? '',
    nomor_rekening:    props.nasabah?.nomor_rekening ?? '',
    alamat:            props.nasabah?.alamat ?? '',
    no_hp:             props.nasabah?.no_hp ?? '',
    pekerjaan:         props.nasabah?.pekerjaan ?? '',
    jaminan:           props.nasabah?.jaminan ?? '',
    tanggal_bergabung: props.nasabah?.tanggal_bergabung ?? '',
    status:            props.nasabah?.status ?? 'aktif',
    kategori:          props.nasabah?.kategori ?? [],
    foto:              null,
    foto_jaminan:      null,
});

const imagePreview = ref(props.nasabah?.foto_url ?? null);
const jaminanPreview = ref(props.nasabah?.foto_jaminan_url ?? null);

const compressImage = (file, callback) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = (event) => {
        const img = new Image();
        img.src = event.target.result;
        img.onload = () => {
            const canvas = document.createElement('canvas');
            const MAX_WIDTH = 800;
            const MAX_HEIGHT = 800;
            let width = img.width;
            let height = img.height;

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
                    lastModified: Date.now(),
                });
                callback(newFile);
            }, 'image/jpeg', 0.7);
        };
    };
};

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        compressImage(file, (compressedFile) => {
            form.foto = compressedFile;
            imagePreview.value = URL.createObjectURL(compressedFile);
        });
    }
};

const handleJaminanChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        compressImage(file, (compressedFile) => {
            form.foto_jaminan = compressedFile;
            jaminanPreview.value = URL.createObjectURL(compressedFile);
        });
    }
};

const submit = () => {
    if (isEdit.value) {
        form.post(route('nasabah.update', props.nasabah.id));
    } else {
        form.post(route('nasabah.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Nasabah' : 'Tambah Nasabah'" />
    <AuthenticatedLayout>
        <template #header>{{ isEdit ? 'Edit Nasabah' : 'Tambah Nasabah' }}</template>

        <div class="mx-auto max-w-2xl">
            <!-- Back link -->
            <Link :href="route('nasabah.index')" class="mb-6 inline-flex items-center gap-2 text-sm text-[color:var(--color-secondary)] hover:text-[color:var(--color-on-surface)]">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Kembali ke Daftar Nasabah
            </Link>

            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                <h2 class="mb-6 text-base font-semibold text-[color:var(--color-on-surface)]">
                    {{ isEdit ? `Edit Data: ${nasabah.nama}` : 'Data Nasabah Baru' }}
                </h2>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Foto Profil -->
                    <div class="flex flex-col items-center gap-4 sm:flex-row sm:gap-6 border-b border-[color:var(--color-outline-variant)] pb-5">
                        <div class="relative h-24 w-24 overflow-hidden rounded-full border border-[color:var(--color-outline-variant)] bg-[color:var(--color-surface-container)] flex items-center justify-center">
                            <img v-if="imagePreview" :src="imagePreview" class="h-full w-full object-cover" alt="Foto Profil" />
                            <span v-else class="material-symbols-outlined text-4xl text-[color:var(--color-secondary)]">person</span>
                        </div>
                        <div class="flex-1 text-center sm:text-left">
                            <label class="mb-1.5 block text-sm font-medium text-[color:var(--color-on-surface)]">Foto Profil</label>
                            <input
                                type="file"
                                accept="image/*"
                                @change="handleFileChange"
                                class="hidden"
                                id="foto-input"
                            />
                            <label
                                for="foto-input"
                                class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2 text-sm font-medium text-[color:var(--color-on-surface-variant)] transition hover:bg-[color:var(--color-surface-container)]"
                            >
                                <span class="material-symbols-outlined text-base">upload_file</span>
                                Pilih Foto
                            </label>
                            <p class="mt-1.5 text-xs text-[color:var(--color-secondary)]">Format: JPG, JPEG, PNG, GIF. Maksimal 2MB.</p>
                            <p v-if="form.errors.foto" class="mt-1 text-xs text-red-500">{{ form.errors.foto }}</p>
                        </div>
                    </div>

                    <!-- Nama -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[color:var(--color-on-surface)]">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input
                            v-model="form.nama"
                            type="text"
                            placeholder="Masukkan nama lengkap"
                            class="w-full rounded-lg border px-4 py-2.5 text-sm focus:border-[color:var(--color-primary)] focus:outline-none focus:ring-2 focus:ring-[color:var(--color-primary)]/20"
                            :class="form.errors.nama ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]'"
                        />
                        <p v-if="form.errors.nama" class="mt-1 text-xs text-red-500">{{ form.errors.nama }}</p>
                    </div>

                    <!-- Nomor Rekening -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[color:var(--color-on-surface)]">Nomor Rekening</label>
                        <input
                            v-model="form.nomor_rekening"
                            type="text"
                            :disabled="isUpdate"
                            placeholder="Masukkan nomor rekening (opsional, kosongkan untuk otomatis)"
                            class="w-full rounded-lg border px-4 py-2.5 text-sm font-mono focus:border-[color:var(--color-primary)] focus:outline-none focus:ring-2 focus:ring-[color:var(--color-primary)]/20"
                            :class="[
                                form.errors.nomor_rekening ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]',
                                isUpdate ? 'bg-slate-100 cursor-not-allowed opacity-70' : 'bg-white'
                            ]"
                        />
                        <p v-if="!isUpdate" class="mt-1 text-xs text-[color:var(--color-secondary)]">
                            Kosongkan saat pendaftaran untuk membuat nomor rekening otomatis (Format: 00001.YYYY).
                        </p>
                        <p v-else class="mt-1 text-xs text-orange-500 font-medium">
                            <span class="material-symbols-outlined text-[10px] align-middle">lock</span> Nomor rekening sudah terkunci dan tidak dapat diubah.
                        </p>
                        <p v-if="form.errors.nomor_rekening" class="mt-1 text-xs text-red-500">{{ form.errors.nomor_rekening }}</p>
                    </div>

                    <!-- NIK -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[color:var(--color-on-surface)]">NIK <span class="text-red-500">*</span></label>
                        <input
                            v-model="form.nik"
                            type="text"
                            maxlength="16"
                            placeholder="16 digit NIK"
                            class="w-full rounded-lg border px-4 py-2.5 text-sm font-mono focus:border-[color:var(--color-primary)] focus:outline-none focus:ring-2 focus:ring-[color:var(--color-primary)]/20"
                            :class="form.errors.nik ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]'"
                        />
                        <p v-if="form.errors.nik" class="mt-1 text-xs text-red-500">{{ form.errors.nik }}</p>
                    </div>

                    <!-- Alamat -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[color:var(--color-on-surface)]">Alamat <span class="text-red-500">*</span></label>
                        <textarea
                            v-model="form.alamat"
                            rows="3"
                            placeholder="Alamat lengkap nasabah"
                            class="w-full rounded-lg border px-4 py-2.5 text-sm focus:border-[color:var(--color-primary)] focus:outline-none focus:ring-2 focus:ring-[color:var(--color-primary)]/20"
                            :class="form.errors.alamat ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]'"
                        ></textarea>
                        <p v-if="form.errors.alamat" class="mt-1 text-xs text-red-500">{{ form.errors.alamat }}</p>
                    </div>

                    <!-- No HP & Pekerjaan -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-[color:var(--color-on-surface)]">Nomor WhatsApp <span class="text-red-500">*</span></label>
                            <input
                                v-model="form.no_hp"
                                type="text"
                                placeholder="08xxxxxxxxxx"
                                class="w-full rounded-lg border px-4 py-2.5 text-sm focus:border-[color:var(--color-primary)] focus:outline-none focus:ring-2 focus:ring-[color:var(--color-primary)]/20"
                                :class="form.errors.no_hp ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]'"
                            />
                            <p v-if="form.errors.no_hp" class="mt-1 text-xs text-red-500">{{ form.errors.no_hp }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-[color:var(--color-on-surface)]">Pekerjaan</label>
                            <input
                                v-model="form.pekerjaan"
                                type="text"
                                placeholder="Misal: Petani, Pedagang"
                                class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2.5 text-sm focus:border-[color:var(--color-primary)] focus:outline-none"
                            />
                        </div>
                    </div>

                    <!-- Kategori Nasabah -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[color:var(--color-on-surface)]">Program / Kategori yang Diikuti</label>
                        <div class="flex flex-wrap gap-4 mt-2">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" value="tabungan" v-model="form.kategori" class="rounded border-gray-300 text-[color:var(--color-primary)] focus:ring-[color:var(--color-primary)]">
                                <span class="text-sm">Tabungan</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" value="sembako" v-model="form.kategori" class="rounded border-gray-300 text-[color:var(--color-primary)] focus:ring-[color:var(--color-primary)]">
                                <span class="text-sm">Tabungan Sembako</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" value="pinjaman" v-model="form.kategori" class="rounded border-gray-300 text-[color:var(--color-primary)] focus:ring-[color:var(--color-primary)]">
                                <span class="text-sm">Pinjaman</span>
                            </label>
                        </div>
                    </div>

                    <!-- Jaminan & Foto Jaminan (Khusus Pinjaman) -->
                    <div v-if="form.kategori.includes('pinjaman')" class="bg-amber-50 p-4 rounded-xl border border-amber-200">
                        <h4 class="font-bold text-amber-800 mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-base">warning</span> Dokumen Jaminan Pinjaman</h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-[color:var(--color-on-surface)]">
                                    Deskripsi Jaminan <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.jaminan"
                                    type="text"
                                    placeholder="Misal: BPKB Motor, Sertifikat"
                                    class="w-full rounded-lg border px-4 py-2.5 text-sm focus:border-amber-500 focus:outline-none"
                                    :class="form.errors.jaminan ? 'border-red-400' : 'border-amber-300 bg-white'"
                                    required
                                />
                                <p v-if="form.errors.jaminan" class="mt-1 text-xs text-red-500">{{ form.errors.jaminan }}</p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-[color:var(--color-on-surface)]">Foto Barang Jaminan</label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="handleJaminanChange"
                                    class="hidden"
                                    id="jaminan-input"
                                />
                                <label
                                    for="jaminan-input"
                                    class="inline-flex w-full justify-center cursor-pointer items-center gap-2 rounded-lg border border-amber-300 bg-white px-4 py-2.5 text-sm font-medium text-amber-800 transition hover:bg-amber-100"
                                >
                                    <span class="material-symbols-outlined text-base">add_a_photo</span>
                                    Upload Foto (Auto-Compress)
                                </label>
                                <p class="mt-1.5 text-xs text-[color:var(--color-secondary)]">Otomatis kompres.</p>
                                <p v-if="form.errors.foto_jaminan" class="mt-1 text-xs text-red-500">{{ form.errors.foto_jaminan }}</p>
                                
                                <div v-if="jaminanPreview" class="mt-3 relative h-32 w-full overflow-hidden rounded-lg border border-amber-200">
                                    <img :src="jaminanPreview" class="h-full w-full object-cover" alt="Preview Jaminan" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tanggal Bergabung & Status -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-[color:var(--color-on-surface)]">Tanggal Bergabung</label>
                            <input
                                v-model="form.tanggal_bergabung"
                                type="date"
                                class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2.5 text-sm focus:border-[color:var(--color-primary)] focus:outline-none"
                            />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-[color:var(--color-on-surface)]">Status</label>
                            <select
                                v-model="form.status"
                                class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2.5 text-sm focus:border-[color:var(--color-primary)] focus:outline-none"
                            >
                                <option value="aktif">Aktif</option>
                                <option value="tidak aktif">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-3 border-t border-[color:var(--color-outline-variant)] pt-5">
                        <Link :href="route('nasabah.index')" class="rounded-lg border border-[color:var(--color-outline-variant)] px-5 py-2.5 text-sm font-medium text-[color:var(--color-on-surface-variant)] transition hover:bg-[color:var(--color-surface-container)]">
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex items-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 disabled:opacity-60"
                        >
                            <span class="material-symbols-outlined text-base">{{ isEdit ? 'save' : 'person_add' }}</span>
                            {{ isEdit ? 'Simpan Perubahan' : 'Tambah Nasabah' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
