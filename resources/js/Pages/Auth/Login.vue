<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    captcha_expression: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
    captcha: '',
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar Sesión - El Chaparro de Guanta" />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Consultorio Popular Tipo III</h1>
            <h2 class="text-xl font-semibold text-gray-600 mt-1">"El Chaparro de Guanta"</h2>
            <p class="text-sm text-gray-500 mt-2">Sistema de Gestión de Historias Clínicas</p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Correo Electrónico o Cédula" />

                <TextInput
                    id="email"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="correo@ejemplo.com / 12345678"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Contraseña" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel value="Captcha de seguridad" />
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-lg font-semibold bg-gray-100 px-3 py-2 rounded select-none">{{ captcha_expression }} = ?</span>
                    <TextInput
                        id="captcha"
                        type="text"
                        class="block w-24"
                        v-model="form.captcha"
                        required
                        autocomplete="off"
                        placeholder="?"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.captcha" />
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600">Recordarme</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                >
                    ¿Olvidaste tu contraseña?
                </Link>

                <PrimaryButton class="ms-4 bg-gray-700 hover:bg-gray-800 focus:bg-gray-900" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Iniciar Sesión
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
