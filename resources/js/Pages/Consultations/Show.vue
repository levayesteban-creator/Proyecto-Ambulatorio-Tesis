<script setup>
import { computed } from 'vue';
import { Link, Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PhysicalExamShow from '@/Components/PhysicalExamShow.vue';

const props = defineProps({
    consultation: { type: Object, required: true },
});

const currentUser = computed(() => usePage().props.auth?.user);
const canDelete = computed(() => {
    return currentUser.value?.role_id <= 2;
});

const p = props.consultation.patient;
const c = props.consultation;

// Lógica de fechas
const formatDate = (d) => d
    ? new Date(d).toLocaleDateString('es-VE', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })
    : '—';

const consultationDate = computed(() => formatDate(c.consultation_date ?? c.created_at));

// Funciones de eliminación
const deleteConsultation = () => {
    if (confirm('¿Estás seguro de que deseas eliminar esta consulta? Esta acción no se puede deshacer.')) {
        router.delete(route('consultations.destroy', { patient: p?.id || c.patient_id, consultation: c.id }));
    }
};

// Diccionario de especialidades y utilidades
const typeLabel = (t) => ({ P: 'Primera Vez', S: 'Sucesiva', X: 'Asociada' }[t] ?? t);
const typeColor = (t) => ({ P: 'bg-green-100 text-green-700', S: 'bg-blue-100 text-blue-700', X: 'bg-purple-100 text-purple-700' }[t] ?? 'bg-gray-100 text-gray-700');
const serviceTypeLabel = (s) => ({ MG:'Medicina General', EP:'Epidemiología', EM:'Emergencia', PR:'Preventiva / Programas', OT:'Otra' }[s] ?? s);
const diagTypeColor = (t) => ({
    Confirmado: 'bg-green-100 text-green-700',
    Probable: 'bg-yellow-100 text-yellow-700',
    Sospechoso: 'bg-red-100 text-red-700',
    'No Aplica': 'bg-gray-100 text-gray-600',
}[t] ?? 'bg-gray-100 text-gray-700');

const imc = computed(() => {
    if (!c.weight || !c.height || c.height <= 0) return null;
    return (c.weight / (c.height * c.height)).toFixed(1);
});

// Nota: Esta función ya la tenías, la mantenemos
const functionalDeny = (key, exam) => exam[`${key}_deny`];

const specialties = {
    1:'Odontología', 2:'Oftalmología', 3:'Traumatología y Ortopedia', 4:'ORL',
    5:'Pediatría', 6:'Medicina Interna', 7:'Dermatología', 8:'Cirugía',
    9:'Nutrición', 10:'Neumonología', 11:'Ginecología', 12:'Patología de Cuello',
    13:'Patología de Mama', 14:'Obstetricia', 15:'Cardiología', 16:'Nefrología',
    17:'Salud Mental', 18:'Endocrinología', 19:'Neurología', 20:'Atención Psiquiátrica',
    21:'Hospitalización psiquiátrica', 22:'Comunidad terapéutica', 23:'Programas Sociales',
    24:'Educación Especial ME', 25:'Rehabilitación', 26:'Médico de Familia',
    27:'Reumatología', 28:'Oncología', 29:'Urología', 30:'Gastroenterología',
    31:'Psicología', 32:'Infectología', 33:'Cirugía Cardiovascular', 34:'Hematología',
    35:'Neurología', 36:'Radiodiagnóstico', 37:'Toxicología', 38:'Alergólogo', 39:'Optometrista',
};
</script>

<template>
    <Head :title="`Consulta #${c.id} — ${p?.full_name}`" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800">
                        Detalle de Consulta <span class="text-blue-600">#{{ c.id }}</span>
                    </h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ p?.full_name }} · {{ consultationDate }}
                    </p>
                </div>
                <div class="flex gap-3">
                    <Link
                        :href="route('consultations.history', p?.id)"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm
                               font-medium text-gray-700 hover:bg-gray-50 transition"
                    >
                        ← Historial
                    </Link>
                    <Link
                        :href="route('patients.show', p?.id)"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm
                               font-medium text-gray-700 hover:bg-gray-50 transition"
                    >
                        Ficha del Paciente
                    </Link>

                    <Link :href="route('consultations.edit', [p?.id, c?.id])"
                          class="px-4 py-2 bg-yellow-50 border border-yellow-200 rounded-lg text-sm font-medium text-yellow-700 hover:bg-yellow-100 transition">
                        Editar
                    </Link>

                    <button v-if="canDelete" @click="deleteConsultation"
                            class="px-4 py-2 bg-red-50 border border-red-200 rounded-lg text-sm font-medium text-red-600 hover:bg-red-100 transition">
                          Eliminar
                      </button>
                  </div>
              </div>
          </template>

        <div class="max-w-5xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-6 print:py-4">

            <!-- ── BANNER SUPERIOR ──────────────────────────────────────── -->
            <div class="bg-blue-700 text-white rounded-xl px-6 py-4 flex items-center
                        justify-between shadow print:rounded-none">
                <div>
                    <p class="text-xs text-blue-200 uppercase font-semibold tracking-wide">
                        Evento Clínico Registrado
                    </p>
                    <h1 class="text-xl font-bold mt-0.5">{{ p?.full_name }}</h1>
                    <p class="text-sm text-blue-200">
                        {{ p?.nationality }}-{{ p?.id_number }} ·
                        {{ c.age_at_moment }} años al momento · Dr. {{ c.doctor?.name }}
                    </p>
                </div>
                <div class="text-right flex items-center gap-2">
                    <span v-if="c.service_type"
                          class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                        {{ serviceTypeLabel(c.service_type) }}
                    </span>
                    <span :class="typeColor(c.consultation_type)"
                          class="inline-block px-3 py-1.5 rounded-full text-sm font-bold">
                        {{ typeLabel(c.consultation_type) }}
                    </span>
                    <p v-if="c.is_healthy"
                       class="mt-2 text-emerald-300 text-xs font-semibold">
                        ✓ Paciente sano declarado
                    </p>
                </div>
            </div>

            <!-- ── SIGNOS VITALES ───────────────────────────────────────── -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-4">
                    Signos Vitales y Antropometría
                </h3>
                <div class="grid grid-cols-3 md:grid-cols-8 gap-3">
                    <div v-for="(val, label) in {
                        'TA (mmHg)':    c.blood_pressure,
                        'Temp (°C)':    c.temperature ? `${c.temperature}${c.temperature_route ? ' (' + c.temperature_route + ')' : ''}` : null,
                        'FC (lpm)':     c.heart_rate,
                        'FR (rpm)':     c.respiratory_rate,
                        'SpO₂ (%)':     c.oxygen_saturation,
                        'Peso (kg)':    c.weight,
                        'Talla (m)':    c.height,
                        'IMC':          imc,
                    }" :key="label"
                         class="bg-gray-50 rounded-lg border p-3 text-center">
                        <p class="text-xs text-gray-400 font-medium">{{ label }}</p>
                        <p class="text-base font-bold font-mono text-gray-800 mt-1">
                            {{ val ?? '—' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- ── ANAMNESIS ────────────────────────────────────────────── -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-5">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide border-b pb-2">
                    Anamnesis
                </h3>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Motivo de Consulta</p>
                    <p class="text-sm text-gray-800 italic">"{{ c.reason_for_consultation }}"</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Historia de la Enfermedad Actual</p>
                    <p class="text-sm text-gray-800 whitespace-pre-line">{{ c.current_illness }}</p>
                </div>
            </div>

            <!-- ── EXAMEN FUNCIONAL ─────────────────────────────────────── -->
            <div v-if="c.functional_exam" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide border-b pb-2 mb-4">
                    Examen Funcional por Aparatos y Sistemas
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div v-for="(label, key) in {
                        general:         'General', skin: 'Piel y Faneras',
                        head_face:       'Cabeza y Cara', neck_throat: 'Cuello y Garganta',
                        eyes:            'Ojos', mouth: 'Boca',
                        ears:            'Oídos', nose: 'Nariz',
                        breasts:         'Mamas', respiratory: 'Respiratorio',
                        cardiovascular:  'Cardiovascular', gastrointestinal: 'Gastrointestinal',
                        genitourinary:   'Genitourinario', menstrual_cycle: 'Ciclo Menstrual',
                        nervous_mental:  'Nervioso / Mental', osteomuscular: 'Osteomuscular',
                    }" :key="key"
                         class="flex items-start gap-3 p-3 rounded-lg border bg-gray-50 text-xs">
                        <span
                            :class="functionalDeny(key, c.functional_exam)
                                ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700'"
                            class="flex-shrink-0 px-2 py-0.5 rounded font-bold uppercase"
                        >
                            {{ functionalDeny(key, c.functional_exam) ? 'Niega' : 'Positivo' }}
                        </span>
                        <div>
                            <p class="font-bold text-gray-700">{{ label }}</p>
                            <p v-if="!functionalDeny(key, c.functional_exam) && c.functional_exam[key + '_description']"
                               class="text-gray-600 mt-0.5 italic">
                                {{ c.functional_exam[key + '_description'] }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── EXAMEN FÍSICO ─────────────────────────────────────────── -->
            <div v-if="c.physical_exam" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide border-b pb-2 mb-4">
                    Examen Físico por Secciones Anatómicas
                </h3>
                <PhysicalExamShow :physical-exam="c.physical_exam" />
            </div>

            <!-- ── EXPLORACIÓN COMPLEMENTARIA ───────────────────────────── -->
            <div v-if="c.complementary_studies"
                 class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide border-b pb-2 mb-3">
                    Exploración Complementaria
                </h3>
                <p class="text-sm text-gray-800 whitespace-pre-line">{{ c.complementary_studies }}</p>
            </div>

            <!-- ── EPICRISIS ────────────────────────────────────────────── -->
            <div v-if="c.epicrisis"
                 class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide border-b pb-2 mb-3">
                    Epicrisis
                </h3>
                <p class="text-sm text-gray-800 whitespace-pre-line">{{ c.epicrisis }}</p>
            </div>

            <!-- ── PLAN TERAPÉUTICO ─────────────────────────────────────── -->
            <div v-if="c.treatment_plan"
                 class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide border-b pb-2 mb-3">
                    Plan Terapéutico
                </h3>
                <p class="text-sm text-gray-800 whitespace-pre-line">
                    {{ c.treatment_plan }}
                </p>
            </div>

            <!-- ── JUSTIFICACIÓN DE EDICIÓN ──────────────────────────── -->
            <div v-if="c.edit_justification"
                 class="bg-white rounded-xl border border-red-200 shadow-sm p-5">
                <h3 class="text-xs font-bold text-red-500 uppercase tracking-wide border-b pb-2 mb-3">
                    Justificación de Modificación
                </h3>
                <p class="text-sm text-gray-800 whitespace-pre-line">{{ c.edit_justification }}</p>
            </div>

            <!-- ── DIAGNÓSTICOS SIS ─────────────────────────────────────── -->
            <div v-if="c.sis_diagnoses?.length > 0"
                 class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide border-b pb-2 mb-4">
                    Diagnósticos (Catálogo SIS/MPPS)
                </h3>
                <div class="space-y-3">
                    <div v-for="(dx, i) in c.sis_diagnoses" :key="dx.id"
                         class="flex items-start gap-4 p-3 rounded-lg border bg-gray-50">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 text-white
                                     text-xs font-bold flex items-center justify-center">
                            {{ i + 1 }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-mono font-bold text-blue-700 text-sm">
                                    {{ dx.sis_diagnosis?.code ?? '—' }}
                                </span>
                                <span class="text-sm font-medium text-gray-800">
                                    {{ dx.sis_diagnosis?.name ?? dx.unlisted_diagnosis ?? 'No especificado' }}
                                </span>
                                <span :class="diagTypeColor(dx.diagnosis_type)"
                                      class="px-2 py-0.5 rounded-full text-xs font-bold">
                                    {{ dx.diagnosis_type }}
                                </span>
                            </div>
                            <p v-if="dx.medical_conduct" class="text-xs text-gray-500 mt-1">
                                Conducta: {{ dx.medical_conduct?.name ?? '—' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── REFERENCIAS A ESPECIALIDADES ───────────────────────────── -->
            <div v-if="c.referrals?.length > 0"
                 class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide border-b pb-2 mb-4">
                    Referencias / Interconsultas
                </h3>
                <div class="flex flex-wrap gap-3">
                    <div v-for="ref in c.referrals" :key="ref.id"
                         class="flex items-center gap-2 px-4 py-2 rounded-lg border
                                bg-indigo-50 border-indigo-200">
                        <span class="text-indigo-700 font-bold text-sm">
                            {{ specialties[ref.specialty_code] ?? `Especialidad ${ref.specialty_code}` }}
                        </span>
                        <span class="text-xs text-indigo-500 font-medium">
                            {{ ref.type === 'referral' ? '↗ Referencia' : '↙ Contrarreferencia' }}
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
