<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi Keamanan BUMDes" />

        <div class="mb-4 text-sm text-slate-600">
            <strong>Verifikasi Keamanan:</strong> Anda akan mengakses area sensitif (Kelola Akses Admin). <br>
            Silakan masukkan <strong>Password Anda (PIN)</strong> untuk memverifikasi identitas Anda sebelum melanjutkan.
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="password" value="Password / PIN Keamanan" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full border-blue-300 focus:border-blue-500 focus:ring-blue-500"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                    placeholder="Masukkan password admin Anda"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-6 flex justify-end">
                <PrimaryButton
                    class="w-full justify-center bg-blue-600 hover:bg-blue-700"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Konfirmasi Akses
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
