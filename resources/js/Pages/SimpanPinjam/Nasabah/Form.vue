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
    alamat:            props.nasabah?.alamat ?? '',
    no_hp:             props.nasabah?.no_hp ?? '',
    pekerjaan:         props.nasabah?.pekerjaan ?? '',
    jaminan:           props.nasabah?.jaminan ?? '',
    tanggal_bergabung: props.nasabah?.tanggal_bergabung ?? '',
    status:            props.nasabah?.status ?? 'aktif',
    foto:              null,
});

const imagePreview = ref(props.nasabah?.foto_url ?? null);

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.foto = file;
        imagePreview.value = URL.createObjectURL(file);
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

                    <!-- Jaminan & Tanggal -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-[color:var(--color-on-surface)]">Jaminan</label>
                            <input
                                v-model="form.jaminan"
                                type="text"
                                placeholder="Misal: BPKB Motor, Sertifikat"
                                class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2.5 text-sm focus:border-[color:var(--color-primary)] focus:outline-none"
                            />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-[color:var(--color-on-surface)]">Tanggal Bergabung</label>
                            <input
                                v-model="form.tanggal_bergabung"
                                type="date"
                                class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2.5 text-sm focus:border-[color:var(--color-primary)] focus:outline-none"
                            />
                        </div>
                    </div>

                    <!-- Status -->
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
