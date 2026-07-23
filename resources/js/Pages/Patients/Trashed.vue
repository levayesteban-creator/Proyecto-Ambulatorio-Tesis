<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    patients: Object, // Paginación de Laravel
    filters: Object,
    auth: Object
});

const search = ref(props.filters.search || '');

// Lógica de búsqueda optimizada (Debounce 300ms)
watch(search, debounce((value) => {
    router.get(route('patients.trashed'), { search: value }, {
        preserveState: true,
        replace: true
    });
}, 300));

// Función para restaurar paciente
const restorePatient = (patientId) => {
    if (confirm('¿Está seguro de restaurar esta historia clínica?')) {
        router.post(route('patients.restore', patientId), {}, {
            preserveState: true,
            onSuccess: () => {
                // El mensaje de éxito viene del backend
            }
        });
    }
};

// Función para eliminar permanentemente (solo admin)
const forceDeletePatient = (patientId) => {
    if (confirm('⚠️ ADVERTENCIA: Esta acción es IRREVERSIBLE. ¿Está seguro de eliminar permanentemente esta historia clínica?')) {
        if (confirm('⚠️ ÚLTIMA CONFIRMACIÓN: Esta acción no puede deshacerse. ¿Continuar?')) {
            router.delete(route('patients.force-delete', patientId), {}, {
                preserveState: true,
                onSuccess: () => {
                    router.get(route('patients.trashed'));
                }
            });
        }
    }
};
</script>

<template>
    <Head title="Papelera de Pacientes" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Papelera de Historias Clínicas</h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 bg-white p-4 shadow rounded-lg border-l-4 border-gray-600">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Papelera de Pacientes</h1>
                    <p class="text-sm text-gray-600">Historias clínicas eliminadas (papelera suave)</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <Link :href="route('patients.index')" class="inline-flex items-center px-4 py-2 bg-blue-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-800 transition">
                        ← Volver a Pacientes
                    </Link>
                </div>
            </div>

            <div class="bg-white p-4 shadow rounded-t-lg border-b border-gray-200">
                <div class="w-full md:w-1/3 relative">
                    <input v-model="search" type="text" placeholder="Buscar en papelera..." class="w-full pl-10 pr-4 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-b-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Paciente</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Identificación</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Género</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha de Eliminación</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones de Recuperación</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="patient in patients.data" :key="patient.id" class="hover:bg-blue-50/50 transition bg-red-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ patient.full_name }}</div>
                                <div class="text-xs text-gray-500 italic">Nacimiento: {{ patient.birth_date }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ patient.nationality }}-{{ patient.id_number }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ patient.gender === 'M' ? 'Masculino' : patient.gender === 'F' ? 'Femenino' : 'Otro' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <span class="text-red-600 font-semibold">{{ new Date(patient.deleted_at).toLocaleString('es-VE') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <button
                                    @click="restorePatient(patient.id)"
                                    class="text-green-600 hover:text-green-900 bg-green-50 px-3 py-1 rounded-md transition"
                                    title="Restaurar paciente"
                                >
                                    ♻️ Restaurar
                                </button>
                                <button
                                    v-if="auth?.user?.role_name === 'Administrador'"
                                    @click="forceDeletePatient(patient.id)"
                                    class="text-red-600 hover:text-red-900 bg-red-50 px-3 py-1 rounded-md transition"
                                    title="Eliminar permanentemente (irreversible)"
                                >
                                    🗑️ Eliminar Permanentemente
                                </button>
                            </td>
                        </tr>
                        <tr v-if="patients.data.length === 0">
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">La papelera está vacía.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex items-center justify-between bg-white px-4 py-3 sm:px-6 shadow rounded-lg" v-if="patients.links.length > 3">
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                    <Link v-for="(link, index) in patients.links" :key="index" :href="link.url || '#'" v-html="link.label"
                          class="relative inline-flex items-center px-4 py-2 text-sm font-medium border"
                          :class="[link.active ? 'z-10 bg-gray-600 text-white' : 'text-gray-700 hover:bg-gray-50', !link.url ? 'opacity-50 cursor-not-allowed' : '']" />
                </nav>
            </div>
        </div>
    </AppLayout>
</template>
