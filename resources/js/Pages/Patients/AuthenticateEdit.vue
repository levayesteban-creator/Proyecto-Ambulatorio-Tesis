<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    patient: Object
})

const form = useForm({
    email: '',
    password: ''
})

const authenticate = () => {
    router.post(route('login'), {
        email: form.email,
        password: form.password,
        patient_id: props.patient.id,
        action: 'edit_closed_patient'
    }, {
        onSuccess: () => {
            router.get(route('patients.edit', { patient: props.patient.id, authenticate: 'true' }))
        },
        onError: () => {
            form.errors.email = 'Credenciales inválidas'
        }
    })
}
</script>

<template>
    <Head title="Autenticación Requerida - Editar Historia Clínica Cerrada" />

    <AppLayout>
        <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full space-y-8">
                <div class="bg-white p-8 rounded-lg shadow-lg">
                    <div class="text-center mb-6">
                        <svg class="mx-auto h-12 w-12 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <h2 class="mt-4 text-2xl font-bold text-gray-900">Autenticación Requerida</h2>
                        <p class="mt-2 text-sm text-gray-600">
                            La historia clínica de <strong>{{ patient.full_name }}</strong> está cerrada.
                        </p>
                        <p class="text-sm text-gray-600">
                            Solo administradores y médicos coordinadores pueden editar historias clínicas cerradas.
                        </p>
                    </div>

                    <form @submit.prevent="authenticate" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                placeholder="admin@ejemplo.com"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Contraseña
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                placeholder="••••••••"
                            />
                            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                        </div>

                        <div
                            v-if="form.errors.error"
                            class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md"
                        >
                            {{ form.errors.error }}
                        </div>

                        <div>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ form.processing ? 'Verificando...' : 'Autenticar y Editar' }}
                            </button>
                        </div>

                        <div class="text-center">
                            <button
                                type="button"
                                @click="router.get(route('patients.show', patient.id))"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                Cancelar y volver
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
