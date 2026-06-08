<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: { type: Object, required: true },
});

const form = useForm({});
const userToDelete = ref(null);

const confirmDelete = (user) => {
    userToDelete.value = user;
};

const deleteUser = () => {
    if (userToDelete.value) {
        form.delete(route('portal.cms.users.destroy', userToDelete.value.id), {
            onSuccess: () => {
                userToDelete.value = null;
            },
        });
    }
};

const roleMap = {
    admin:         { label: 'Super Admin',   color: 'bg-indigo-100 text-indigo-700' },
    manager_pusat: { label: 'Manager Pusat', color: 'bg-purple-100 text-purple-700' },
    admin_unit:    { label: 'Admin Unit',    color: 'bg-blue-100 text-blue-700' },
    manager:       { label: 'Manager Unit',  color: 'bg-blue-100 text-blue-700' },
};
const formatRole = (role) => roleMap[role]?.label ?? role;
const roleBadgeClass = (role) => roleMap[role]?.color ?? 'bg-slate-100 text-slate-700';
</script>

<template>
    <Head title="Kelola Akses Admin" />

    <PortalLayout>
        <template #header>Kelola Akses Admin</template>

        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Daftar Admin Unit</h2>
                    <p class="text-xs sm:text-sm text-slate-500">Kelola akun Admin dan Manager yang mengakses unit usaha BUMDes.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <Link
                        :href="route('portal.cms.pin.reset')"
                        method="post"
                        as="button"
                        class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 border border-slate-300 text-slate-600 font-semibold rounded-lg hover:bg-slate-50 transition text-xs sm:text-sm w-full sm:w-auto justify-center"
                        title="Kunci sesi untuk memaksa verifikasi PIN ulang"
                    >
                        <span class="material-symbols-outlined text-[16px] sm:text-[18px]">lock</span>
                        Kunci Sesi
                    </Link>
                    <Link :href="route('portal.cms.users.create')" class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-sm hover:bg-blue-700 transition text-xs sm:text-sm w-full sm:w-auto justify-center">
                        <span class="material-symbols-outlined text-[16px] sm:text-[18px]">person_add</span>
                        Tambah Pengurus
                    </Link>
                </div>
            </div>

            <!-- Flash Message -->
            <div v-if="$page.props.flash.success" class="flex items-center gap-3 p-4 bg-blue-50 text-blue-800 border border-blue-200 rounded-xl">
                <span class="material-symbols-outlined">check_circle</span>
                <span class="text-sm font-medium">{{ $page.props.flash.success }}</span>
            </div>
            
            <div v-if="$page.props.flash.error" class="flex items-center gap-3 p-4 bg-red-50 text-red-800 border border-red-200 rounded-xl">
                <span class="material-symbols-outlined">error</span>
                <span class="text-sm font-medium">{{ $page.props.flash.error }}</span>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Nama</th>
                                <th class="px-6 py-4 font-semibold">Alamat Email</th>
                                <th class="px-6 py-4 font-semibold">Peran (Role)</th>
                                <th class="px-6 py-4 font-semibold">Tugas Unit</th>
                                <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-medium text-slate-800">{{ user.nama }}</td>
                                <td class="px-6 py-4">{{ user.email }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"
                                        :class="roleBadgeClass(user.role)">
                                        {{ formatRole(user.role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="user.unit" class="inline-flex items-center gap-1.5 font-semibold text-slate-700">
                                        <span class="material-symbols-outlined text-[16px] text-slate-400">{{ user.unit.icon || 'business' }}</span>
                                        {{ user.unit.nama_unit }}
                                    </span>
                                    <span v-else class="inline-flex items-center px-2.5 py-1 rounded bg-slate-100 border border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-widest">
                                        Pusat (Semua Unit)
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link :href="route('portal.cms.users.edit', user.id)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </Link>
                                        <button 
                                            v-if="user.id !== $page.props.auth.user.id"
                                            @click="confirmDelete(user)" 
                                            class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition" 
                                            title="Hapus"
                                        >
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                        <span v-else class="p-2 text-slate-300 cursor-not-allowed" title="Ini adalah Anda">
                                            <span class="material-symbols-outlined text-[20px]">person</span>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    Belum ada data pengurus.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Placeholder -->
                <div v-if="users.links && users.data.length > 0" class="p-4 border-t border-slate-200 flex items-center justify-between">
                    <div class="text-xs text-slate-500">
                        Menampilkan {{ users.from }} hingga {{ users.to }} dari {{ users.total }} akun
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="userToDelete" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="p-6">
                    <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-2xl">warning</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-2">Hapus Akun Pengurus?</h3>
                    <p class="text-sm text-slate-600">
                        Anda yakin ingin menghapus akun <strong>{{ userToDelete.nama }}</strong> ({{ userToDelete.email }})? Akun ini tidak akan bisa login lagi ke sistem BUMDes.
                    </p>
                </div>
                <div class="px-6 py-4 bg-slate-50 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-slate-100">
                    <button @click="userToDelete = null" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200 rounded-lg transition w-full sm:w-auto text-center">Batal</button>
                    <button @click="deleteUser" :disabled="form.processing" class="px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition flex items-center justify-center gap-2 w-full sm:w-auto">
                        <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        Ya, Hapus Akun
                    </button>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
