<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    email: {
        type: String,
    },
});

const form = useForm({
    code: '',
});

const submit = () => {
    form.post(route('login.2fa'), {
        onFinish: () => form.reset('code'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Verificación en Dos Pasos - El Chaparro de Guanta" />

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Verificación en Dos Pasos</h1>
            <p class="text-sm text-gray-500 mt-2">
                Hemos enviado un código de verificación a <strong>{{ email }}</strong>
            </p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="code" value="Código de Verificación" />

                <TextInput
                    id="code"
                    type="text"
                    class="mt-1 block w-full text-center text-2xl tracking-widest"
                    v-model="form.code"
                    required
                    autofocus
                    autocomplete="off"
                    placeholder="000000"
                    maxlength="6"
                />

                <InputError class="mt-2" :message="form.errors.code" />
            </div>

            <div class="flex items-center justify-end mt-6">
                <PrimaryButton class="bg-gray-700 hover:bg-gray-800 focus:bg-gray-900" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Verificar Código
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
