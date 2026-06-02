<script setup>
import { Link, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    consultation: { type: Object, required: true },
});

const p = props.consultation.patient;
const c = props.consultation;

const formatDate = (d) => d
    ? new Date(d).toLocaleDateString('es-VE', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })
    : '—';

const typeLabel = (t) => ({ P: 'Primera Vez', S: 'Sucesiva', X: 'Asociada' }[t] ?? t);
const typeColor = (t) => ({ P: 'bg-green-100 text-green-700', S: 'bg-blue-100 text-blue-700', X: 'bg-purple-100 text-purple-700' }[t] ?? 'bg-gray-100 text-gray-700');
const diagTypeColor = (t) => ({ Confirmado: 'bg-green-100 text-green-700', Probable: 'bg-yellow-100 text-yellow-700', Sospechoso: 'bg-red-100 text-red-700' }[t] ?? 'bg-gray-100 text-gray-700');

// Nombre de la especialidad a partir del código (diccionario MPPS)
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
                        {{ p?.full_name }} · {{ formatDate(c.created_at) }}
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
                <div class="text-right">
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
                <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
                    <div v-for="(val, label) in {
                        'TA (mmHg)':    c.blood_pressure,
                        'Temp (°C)':    c.temperature,
                        'FC (lpm)':     c.heart_rate,
                        'FR (rpm)':     c.respiratory_rate,
                        'Peso (kg)':    c.weight,
                        'Talla (m)':    c.height,
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
                            :class="c.functional_exam[key + '_deny']
                                ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700'"
                            class="flex-shrink-0 px-2 py-0.5 rounded font-bold uppercase"
                        >
                            {{ c.functional_exam[key + '_deny'] ? 'Niega' : 'Positivo' }}
                        </span>
                        <div>
                            <p class="font-bold text-gray-700">{{ label }}</p>
                            <p v-if="!c.functional_exam[key + '_deny'] && c.functional_exam[key + '_description']"
                               class="text-gray-600 mt-0.5 italic">
                                {{ c.functional_exam[key + '_description'] }}
                            </p>
                        </div>
                    </div>
                </div>
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
            <div v-if="c.therapeutic_plan || c.treatment_plan"
                 class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide border-b pb-2 mb-3">
                    Plan Terapéutico
                </h3>
                <p class="text-sm text-gray-800 whitespace-pre-line">
                    {{ c.therapeutic_plan ?? c.treatment_plan }}
                </p>
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
                                Conducta: {{ dx.medical_conduct.name }}
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
