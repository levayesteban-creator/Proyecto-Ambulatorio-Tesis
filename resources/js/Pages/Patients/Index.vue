<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import debounce from 'lodash/debounce';
import DatePicker from '@/Components/DatePicker.vue';

const props = defineProps({
    patients: Object,
    filters: Object,
    auth: Object
});

const search = ref(props.filters.search || '');
const gender = ref(props.filters.gender || '');
const status = ref(props.filters.status || '');
const ageMin = ref(props.filters.age_min || '');
const ageMax = ref(props.filters.age_max || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const showFilters = ref(false);

const applyFilters = debounce(() => {
    router.get(route('patients.index'), {
        search: search.value || null,
        gender: gender.value || null,
        status: status.value || null,
        age_min: ageMin.value || null,
        age_max: ageMax.value || null,
        date_from: dateFrom.value || null,
        date_to: dateTo.value || null,
    }, { preserveState: true, replace: true });
}, 300);

const clearFilters = () => {
    search.value = ''; gender.value = ''; status.value = '';
    ageMin.value = ''; ageMax.value = '';
    dateFrom.value = ''; dateTo.value = '';
    applyFilters();
};

const exportUrl = (format) => {
    const params = new URLSearchParams();
    if (gender.value) params.set('gender', gender.value);
    if (status.value) params.set('status', status.value);
    if (ageMin.value) params.set('age_min', ageMin.value);
    if (ageMax.value) params.set('age_max', ageMax.value);
    if (dateFrom.value) params.set('date_from', dateFrom.value);
    if (dateTo.value) params.set('date_to', dateTo.value);
    if (search.value) params.set('search', search.value);
    const qs = params.toString();
    return route(`export.patients.${format}`) + (qs ? '?' + qs : '');
};

const deletePatient = (patientId) => {
    if (confirm('¿Está seguro de mover esta historia clínica a la papelera? Los datos no se perderán.')) {
        router.delete(route('patients.destroy', patientId), { preserveState: true });
    }
};
</script>

<template>
    <Head title="Pacientes Registrados" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Historias Patronímicas</h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 bg-white p-4 shadow rounded-lg border-l-4 border-blue-900">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Control de Pacientes</h1>
                    <p class="text-sm text-gray-600">Planillas EPI-12 / Historial Clínico</p>
                </div>
                <div class="mt-4 md:mt-0 flex flex-wrap gap-2">
                    <Link :href="route('patients.create')" class="inline-flex items-center px-4 py-2 bg-blue-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-800 transition">
                        + Registrar Paciente
                    </Link>
                    <Link v-if="auth?.user?.role_name === 'Administrador' || auth?.user?.role_name === 'Médico Coordinador'"
                        :href="route('patients.trashed')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                        🗑️ Papelera
                    </Link>
                </div>
            </div>

            <!-- Filtros y Búsqueda -->
            <div class="bg-white p-4 shadow rounded-lg mb-4">
                <div class="flex flex-col md:flex-row md:items-center gap-3">
                    <div class="w-full md:w-1/3 relative">
                        <input v-model="search" @input="applyFilters" type="text" placeholder="Buscar por nombre o cédula..." class="w-full pl-10 pr-4 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    <button @click="showFilters = !showFilters" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filtros
                    </button>
                    <div class="flex gap-2 ml-auto">
                        <a :href="exportUrl('pdf')" target="_blank" class="inline-flex items-center px-3 py-2 bg-red-600 text-white rounded-md text-xs font-semibold hover:bg-red-700 transition">
                            📄 PDF
                        </a>
                        <a :href="exportUrl('csv')" class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded-md text-xs font-semibold hover:bg-green-700 transition">
                            📊 Excel
                        </a>
                    </div>
                </div>

                <!-- Panel de filtros avanzados -->
                <div v-if="showFilters" class="mt-4 pt-4 border-t border-gray-200 grid grid-cols-2 md:grid-cols-6 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Género</label>
                        <select v-model="gender" @change="applyFilters" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                        <select v-model="status" @change="applyFilters" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <option value="open">Abierto</option>
                            <option value="closed">Cerrado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Edad mínima</label>
                        <input v-model="ageMin" @change="applyFilters" type="number" min="0" max="120" placeholder="0" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Edad máxima</label>
                        <input v-model="ageMax" @change="applyFilters" type="number" min="0" max="120" placeholder="120" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
                        <DatePicker v-model="dateFrom" placeholder="dd/mm/aaaa" class="w-full" @update:model-value="applyFilters" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
                        <DatePicker v-model="dateTo" placeholder="dd/mm/aaaa" class="w-full" @update:model-value="applyFilters" />
                    </div>
                    <div class="col-span-2 md:col-span-6">
                        <button @click="clearFilters" class="text-xs text-blue-600 hover:text-blue-800 underline">Limpiar todos los filtros</button>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Paciente</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Identificación</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Género</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Socio-Demográficos</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones Médicas</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="patient in patients.data" :key="patient.id" class="hover:bg-blue-50/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ patient.full_name }}</div>
                                <div class="text-xs text-gray-500 italic">Nacimiento: {{ patient.birth_date }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ patient.nationality }}-{{ patient.id_number }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ patient.gender === 'M' ? 'Masculino' : patient.gender === 'F' ? 'Femenino' : 'Otro' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-xs text-gray-900">Ocupación: {{ patient.occupation?.name || '---' }}</div>
                                <div class="text-xs text-gray-500">Teléfono: {{ patient.phone || 'N/T' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <Link :href="route('patients.show', patient.id)" class="text-blue-600 hover:text-blue-900 bg-blue-50 px-3 py-1 rounded-md transition">Ver Ficha</Link>
                                <Link v-if="auth?.can_edit_patient && !patient.closed_at" :href="route('patients.edit', patient.id)" class="text-amber-700 hover:text-amber-900 bg-amber-50 px-3 py-1 rounded-md transition">Editar</Link>
                                <Link :href="route('consultations.create', patient.id)" class="bg-emerald-600 text-white px-3 py-1 rounded-md hover:bg-emerald-700 transition">Iniciar Consulta</Link>
                                <button v-if="auth?.can_edit_patient" @click="deletePatient(patient.id)" class="text-red-600 hover:text-red-900 bg-red-50 px-3 py-1 rounded-md transition" title="Mover a papelera">🗑️</button>
                            </td>
                        </tr>
                        <tr v-if="patients.data.length === 0">
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">No hay pacientes que coincidan con la búsqueda.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-4 flex items-center justify-between bg-white px-4 py-3 sm:px-6 shadow rounded-lg" v-if="patients.links.length > 3">
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                    <Link v-for="(link, index) in patients.links" :key="index" :href="link.url || '#'" v-html="link.label"
                          class="relative inline-flex items-center px-4 py-2 text-sm font-medium border"
                          :class="[link.active ? 'z-10 bg-blue-900 text-white' : 'text-gray-700 hover:bg-gray-50', !link.url ? 'opacity-50 cursor-not-allowed' : '']" />
                </nav>
            </div>
        </div>
    </AppLayout>
</template>
