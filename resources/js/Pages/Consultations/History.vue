<script setup>
import { Link, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    patient:       { type: Object, required: true },
    consultations: { type: Object, required: true }, // objeto paginado de Laravel
});

// Formatea fecha legible en español
const formatDate = (dateString) => {
    if (!dateString) return '—';
    return new Date(dateString).toLocaleDateString('es-VE', {
        year: 'numeric', month: 'long', day: 'numeric',
    });
};

// Etiqueta del tipo de consulta
const typeLabel = (type) => {
    const map = { P: 'Primera Vez', S: 'Sucesiva', X: 'Asociada' };
    return map[type] ?? type;
};

const typeColor = (type) => {
    const map = {
        P: 'bg-green-100 text-green-700',
        S: 'bg-blue-100 text-blue-700',
        X: 'bg-purple-100 text-purple-700',
    };
    return map[type] ?? 'bg-gray-100 text-gray-700';
};

// Etiqueta del tipo de diagnóstico
const diagTypeColor = (type) => {
    const map = {
        Confirmado:  'bg-green-100 text-green-700',
        Probable:    'bg-yellow-100 text-yellow-700',
        Sospechoso:  'bg-red-100 text-red-700',
    };
    return map[type] ?? 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <Head :title="`Historial — ${patient.full_name}`" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Historial de Consultas
                    </h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ patient.full_name }} ·
                        {{ patient.nationality }}-{{ patient.id_number }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('patients.show', patient.id)"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium
                               text-gray-700 hover:bg-gray-50 transition shadow-sm"
                    >
                        ← Ficha del Paciente
                    </Link>
                    <Link
                        :href="route('consultations.create', patient.id)"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg
                               text-sm font-medium transition shadow-sm"
                    >
                        + Nueva Consulta
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

            <!-- Sin consultas -->
            <div
                v-if="consultations.data.length === 0"
                class="text-center py-16 bg-white rounded-xl border border-dashed border-gray-300"
            >
                <p class="text-4xl mb-3">📋</p>
                <p class="text-gray-500 font-medium">El paciente no registra consultas previas.</p>
                <Link
                    :href="route('consultations.create', patient.id)"
                    class="inline-block mt-4 px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition"
                >
                    Registrar Primera Consulta
                </Link>
            </div>

            <!-- Línea de tiempo de consultas -->
            <div v-else class="relative">

                <!-- Línea vertical decorativa -->
                <div class="absolute left-5 top-0 bottom-0 w-0.5 bg-blue-100"></div>

                <div class="space-y-6">
                    <div
                        v-for="consulta in consultations.data"
                        :key="consulta.id"
                        class="relative flex gap-6"
                    >
                        <!-- Círculo en la línea de tiempo -->
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-600 flex items-center
                                    justify-center text-white text-xs font-bold shadow-md z-10">
                            {{ new Date(consulta.created_at).getDate() }}
                        </div>

                        <!-- Tarjeta de la consulta -->
                        <div class="flex-1 bg-white rounded-xl border border-gray-200 shadow-sm
                                    overflow-hidden hover:shadow-md transition-shadow">

                            <!-- Encabezado de la tarjeta -->
                            <div class="flex items-center justify-between px-5 py-3
                                        bg-gray-50 border-b border-gray-200">
                                <div class="flex items-center gap-3">
                                    <span :class="typeColor(consulta.consultation_type)"
                                          class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                                        {{ typeLabel(consulta.consultation_type) }}
                                    </span>
                                    <span v-if="consulta.is_healthy"
                                          class="px-2.5 py-1 rounded-full text-xs font-bold
                                                 bg-emerald-100 text-emerald-700 uppercase">
                                        ✓ Paciente Sano
                                    </span>
                                </div>
                                <div class="flex items-center gap-4 text-xs text-gray-500">
                                    <span>{{ formatDate(consulta.created_at) }}</span>
                                    <span class="font-medium text-gray-700">
                                        Dr. {{ consulta.doctor?.name ?? 'No registrado' }}
                                    </span>
                                    <Link
                                        :href="route('consultations.show', consulta.id)"
                                        class="text-blue-600 font-bold hover:underline"
                                    >
                                        Ver detalle →
                                    </Link>
                                </div>
                            </div>

                            <!-- Cuerpo de la tarjeta -->
                            <div class="px-5 py-4 space-y-4">

                                <!-- Motivo y signos vitales en fila -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="md:col-span-2">
                                        <p class="text-xs font-bold text-gray-500 uppercase mb-1">
                                            Motivo de Consulta
                                        </p>
                                        <p class="text-sm text-gray-800">
                                            "{{ consulta.reason_for_consultation }}"
                                        </p>
                                    </div>

                                    <!-- Signos vitales rápidos -->
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div v-if="consulta.blood_pressure"
                                             class="bg-gray-50 rounded-lg p-2 border text-center">
                                            <p class="text-gray-400 font-medium">TA</p>
                                            <p class="font-bold text-gray-700 font-mono">
                                                {{ consulta.blood_pressure }}
                                            </p>
                                        </div>
                                        <div v-if="consulta.temperature"
                                             class="bg-gray-50 rounded-lg p-2 border text-center">
                                            <p class="text-gray-400 font-medium">Temp</p>
                                            <p class="font-bold text-gray-700 font-mono">
                                                {{ consulta.temperature }}°C
                                            </p>
                                        </div>
                                        <div v-if="consulta.weight"
                                             class="bg-gray-50 rounded-lg p-2 border text-center">
                                            <p class="text-gray-400 font-medium">Peso</p>
                                            <p class="font-bold text-gray-700 font-mono">
                                                {{ consulta.weight }} kg
                                            </p>
                                        </div>
                                        <div v-if="consulta.heart_rate"
                                             class="bg-gray-50 rounded-lg p-2 border text-center">
                                            <p class="text-gray-400 font-medium">FC</p>
                                            <p class="font-bold text-gray-700 font-mono">
                                                {{ consulta.heart_rate }} lpm
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Diagnósticos SIS -->
                                <div v-if="consulta.sis_diagnoses?.length > 0">
                                    <p class="text-xs font-bold text-gray-500 uppercase mb-2">
                                        Diagnósticos
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <div
                                            v-for="dx in consulta.sis_diagnoses"
                                            :key="dx.id"
                                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                                                   border border-gray-200 bg-gray-50 text-xs"
                                        >
                                            <span class="font-bold font-mono text-blue-700">
                                                {{ dx.sis_diagnosis?.code ?? '—' }}
                                            </span>
                                            <span class="text-gray-700">
                                                {{ dx.sis_diagnosis?.name ?? dx.unlisted_diagnosis ?? 'No especificado' }}
                                            </span>
                                            <span :class="diagTypeColor(dx.diagnosis_type)"
                                                  class="px-1.5 py-0.5 rounded text-xs font-medium">
                                                {{ dx.diagnosis_type }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Referencias -->
                                <div v-if="consulta.referrals?.length > 0">
                                    <p class="text-xs font-bold text-gray-500 uppercase mb-2">
                                        Referencias
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            v-for="ref in consulta.referrals"
                                            :key="ref.id"
                                            class="px-2.5 py-1 bg-indigo-50 text-indigo-700
                                                   border border-indigo-200 rounded-full text-xs font-medium"
                                        >
                                            Código {{ ref.specialty_code }}
                                            <span class="opacity-60">
                                                ({{ ref.type === 'referral' ? 'Ref.' : 'Contraref.' }})
                                            </span>
                                        </span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paginación -->
                <div v-if="consultations.links.length > 3"
                     class="mt-8 flex justify-center items-center gap-1">
                    <template v-for="link in consultations.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            :class="[
                                'px-3 py-1.5 rounded-lg text-sm transition-colors',
                                link.active
                                    ? 'bg-blue-600 text-white font-semibold'
                                    : 'text-gray-600 hover:bg-gray-100 border border-gray-200'
                            ]"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="px-3 py-1.5 text-sm text-gray-400"
                            v-html="link.label"
                        />
                    </template>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
