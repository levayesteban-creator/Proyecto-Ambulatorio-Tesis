<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PhysicalExamTab from '@/Components/PhysicalExamTab.vue'
import DatePicker from '@/Components/DatePicker.vue'
import { MPPS_SPECIALTIES, specialtyNameByCode } from '@/utils/mppsSpecialties'

const props = defineProps({
    patient:         Object,
    age_at_moment:   Number,
    consultation:    Object,
    mode:            String,
    institution:     String,
    userName:        String,
    diagnosesCatalog:Array,
    medicalConducts: Array,
})

// ── APARTADOS CLÍNICOS (estructura MPPS) ─────────────────
const sections = [
    { label: 'Anamnesis', short: 'Anamnesis' },
    { label: 'Examen funcional por aparatos y sistemas', short: 'Examen funcional' },
    { label: 'Examen físico y signos vitales', short: 'Examen físico' },
    { label: 'Consulta y diagnóstico', short: 'Consulta / diagnóstico' },
]

const currentStep = ref(0)

const scrollToFormTop = () => {
    requestAnimationFrame(() => {
        document.querySelector('.tabs-wrapper')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    })
}

const goToStep = (index) => {
    if (index < 0 || index >= sections.length) return
    currentStep.value = index
    scrollToFormTop()
}

const nextStep = () => goToStep(currentStep.value + 1)
const prevStep = () => goToStep(currentStep.value - 1)

const isFirstStep = computed(() => currentStep.value === 0)
const isLastStep  = computed(() => currentStep.value === sections.length - 1)
const currentSection = computed(() => sections[currentStep.value])

// ── FUNCTIONAL EXAM SYSTEMS ──────────────────────────────
const functionalSystems = [
    { key: 'general',         icon: '🩺', label: 'General',             hint: 'Mareos, Fiebre, Escalofríos, ↑↓ Peso, Sudoración' },
    { key: 'skin',            icon: '🩹', label: 'Piel y Faneras',       hint: 'Lesiones, ubicación, diámetro, palidez, ictericia, cianosis' },
    { key: 'head_face',       icon: '🧠', label: 'Cabeza y Cara',        hint: 'Deformidades, tumoraciones, alopecia, movimientos anormales' },
    { key: 'neck_throat',     icon: '🗣️',  label: 'Cuello y Garganta',   hint: 'Dolor, movilidad, bocio, adenopatías, tumoraciones' },
    { key: 'eyes',            icon: '👁️',  label: 'Ojos',                hint: 'Agudeza visual, epifora, fotofobia, eritema, diplopía' },
    { key: 'mouth',           icon: '👄', label: 'Boca',                 hint: 'Gusto, lesiones, halitosis, pigmentaciones, prótesis, caries' },
    { key: 'breasts',         icon: '🫁', label: 'Mamas',               hint: 'Dolor, tumor, secreciones, ginecomastia, galactorrea' },
    { key: 'ears',            icon: '👂', label: 'Oídos',               hint: 'Audición, otalgia, secreción, acúfenos' },
    { key: 'nose',            icon: '👃', label: 'Nariz',               hint: 'Obstrucción, deformidad, rinorrea, epistaxis, hiposmia' },
    { key: 'respiratory',     icon: '💨', label: 'Respiratorio',        hint: 'Dolor torácico, estridor, disfonía, tos, expectoración' },
    { key: 'cardiovascular',  icon: '❤️', label: 'Cardiovascular',      hint: 'Dolor precordial, disnea, síncope, edema, palpitaciones' },
    { key: 'gastrointestinal',icon: '🦠', label: 'Gastrointestinal',    hint: 'N° evacuaciones, color, olor, forma, consistencia, pirosis' },
    { key: 'genitourinary',   icon: '💧', label: 'Genitourinario',      hint: 'N° micciones, patrón, color, olor, hematuria, disuria' },
    { key: 'menstrual_cycle', icon: '🌸', label: 'Ciclo Menstrual',     hint: 'Periodicidad, duración, cuantía, FUR, amenorrea, dismenorrea' },
    { key: 'nervous_mental',  icon: '🧬', label: 'Nervioso y Mental',   hint: 'Síncopes, vértigo, paresia, parestesia, convulsiones' },
    { key: 'osteomuscular',   icon: '🦿', label: 'Osteomuscular',       hint: 'Mialgias, artralgias, artritis, claudicación, deformidad' },
]

const commonSpecialties = MPPS_SPECIALTIES

// ── FORM STATE ───────────────────────────────────────────
const form = useForm({
    // Tab 0: Anamnesis

    reason_for_consultation: '',
    current_illness:         '',
    consultation_type:       'P',
    service_type:            'MG',
    is_healthy:              false,

    // Tab 1: Examen Funcional (16 sistemas)
    functional_exam: {
        general_deny: true,           general_description: '',
        skin_deny: true,              skin_description: '',
        head_face_deny: true,         head_face_description: '',
        neck_throat_deny: true,       neck_throat_description: '',
        eyes_deny: true,              eyes_description: '',
        mouth_deny: true,             mouth_description: '',
        breasts_deny: true,           breasts_description: '',
        ears_deny: true,              ears_description: '',
        nose_deny: true,              nose_description: '',
        respiratory_deny: true,       respiratory_description: '',
        cardiovascular_deny: true,    cardiovascular_description: '',
        gastrointestinal_deny: true,  gastrointestinal_description: '',
        genitourinary_deny: true,     genitourinary_description: '',
        menstrual_cycle_deny: true,   menstrual_cycle_description: '',
        nervous_mental_deny: true,    nervous_mental_description: '',
        osteomuscular_deny: true,     osteomuscular_description: '',
    },

    // Tab 2: Examen Físico / Signos Vitales
    blood_pressure:    '',
    temperature:       null,
    temperature_route: 'Axilar',  // Oral | Axilar | Rectal | Timpánica
    heart_rate:        null,
    respiratory_rate:  null,
    oxygen_saturation: null,      // SpO₂ %
    weight:            null,
    height:            null,
    physical_examination: '',
    complementary_studies: '',
    epicrisis: '',

    // Examen Físico Estructurado (17 secciones JSON)
    physical_exam: {
        general_data:   { facies: '', marcha: '', aptitud: '', biotipo: '', _normal: false },
        skin:           { fitzpatrick: '', coloration: '', temperature: '', hydration: '', turgor: '', elasticity: '', nail_color: '', nail_appearance: '', capillary_refill: null, lesions: '', _normal: false },
        lymph_nodes:    { location: '', size: '', painful: '', consistency: '', mobility: '', arrangement: '', _normal: false },
        head:           { type: '', superficial_pain: '', superficial_detail: '', deep_pain: '', deep_detail: '', swelling: '', scalp: '', hair_distribution: '', hair_color: '', hair_quantity: '', hair_type: '', acromotrichia: '', alopecia: '', _normal: false },
        eyes:           { implantation: '', symmetry: '', cornea: '', sclera: '', iris: '', pupils: '', conjunctiva: '', brows_lashes: '', eyelids: '', fundus: '', _normal: false },
        nose:           { septum: '', pyramid: '', passages: '', mucosa: '', turbinates: '', secretions: '', sinuses: '', _normal: false },
        mouth_pharynx:  { lips: '', gums: '', dental_arch: '', dental_detail: '', tongue: '', pharynx: '', tonsils: '', uvula: '', pillars: '', _normal: false },
        ears:           { auricles: '', canal: '', tympanic_membrane: '', _normal: false },
        neck:           { length: '', form: '', symmetry: '', mobility: '', jugular_pulse: '', trachea: '', thyroid: '', _normal: false },
        thorax:         { symmetry: '', body_type: '', configuration: '', venous_circulation: '', expansibility: '', breathing_type: '', intercostal_retraction: '', nasal_flaring: '', palpation_pain: '', vocal_vibrations: '', vesicular_murmur: '', laryngotracheal_murmur: '', added_sounds: '', sonority: '', _normal: false },
        cardiovascular: { apex_visible: '', apex_palpable: '', dullness: '', heart_sounds: '', murmurs: '', _normal: false },
        breasts:        { size: '', symmetry: '', masses: '', secretions: '', nipple: '', _normal: false },
        abdomen:        { form: '', venous_circulation: '', breathing_protrusions: '', valsalva_protrusions: '', depressible: '', bowel_sounds: '', vascular_sounds: '', tympanism: '', hepatic_dullness: '', palpation_pain: '', masses: '', hepatometry_parasternal: '', hepatometry_midclavicular: '', hepatometry_axillary: '', kidney_punch: '', _normal: false },
        genital:        { gender_type: '', _normal: false },
        rectal_exam:    { anus: '', rectal_touch: '', prostate: '', masses: '', _normal: false },
        neurological:   { glasgow_eye: '', glasgow_verbal: '', glasgow_motor: '', consciousness_level: '', consciousness_state: '', language: '', thought: '', memory: '', calculation: '', upper_strength: '', lower_strength: '', babinski: '', reflex_bicipital: '', reflex_styloradial: '', reflex_patellar: '', reflex_achilles: '', involuntary_movements: '', cn_i: '', cn_ii: '', cn_iii_iv_vi: '', cn_v: '', cn_vii: '', cn_viii: '', cn_ix_x: '', cn_xi: '', cn_xii: '', _normal: false },
        extremities:    { symmetry: '', varicose_veins: '', edema: '', peripheral_pulse: '', mobility: '', muscle_tone: '', paresthesias: '', pain: '', deformity: '', _normal: false },
    },

    // Tab 3: Diagnósticos SIS
    diagnoses: [{
        sis_diagnosis_id: null,
        unlisted_diagnosis: '',
        diagnosis_type: 'Confirmado',
        medical_conduct_id: '',
        sort_order: 1,             // FIX Punto 4: era order_index
    }],

    referrals:      [],
    referral_state: 'N/A', // N/A | Referencia | Contra-referencia (estado global de referencia)
    treatment_plan: '',
    edit_justification: '',
})

const referralApiType = computed(() =>
    form.referral_state === 'Contra-referencia' ? 'counter_referral' : 'referral'
)

const isReferralSelected = (code) =>
    form.referrals.some(
        r => r.specialty_code === code && r.type === referralApiType.value
    )

const toggleReferral = (spec) => {
    const type = referralApiType.value
    const idx = form.referrals.findIndex(
        r => r.specialty_code === spec.code && r.type === type
    )
    if (idx === -1) form.referrals.push({ specialty_code: spec.code, type })
    else form.referrals.splice(idx, 1)
}

watch(() => form.referral_state, (state, prev) => {
    if (state === 'N/A') {
        form.referrals = []
        return
    }
    if (prev && prev !== 'N/A' && state !== prev) {
        const type = state === 'Contra-referencia' ? 'counter_referral' : 'referral'
        form.referrals = form.referrals.map(r => ({ ...r, type }))
    }
})

watch(() => form.is_healthy, (healthy) => {
    if (healthy) {
        if (!form.reason_for_consultation.trim()) {
            form.reason_for_consultation = 'Control de salud / consulta preventiva'
        }
        if (!form.current_illness.trim()) {
            form.current_illness = 'Paciente refiere encontrarse en buen estado general. Sin síntomas activos al momento de la consulta.'
        }
    }
})

// ── COMPUTED ─────────────────────────────────────────────

// Errores por apartado (indicador ! en pestaña)
const hasStep0Error = computed(() => {
    let err = !!(
        form.errors.reason_for_consultation ||
        form.errors.current_illness ||
        form.errors.consultation_type ||
        form.errors.service_type
    )
    return err
})

const hasStep1Error = computed(() =>
    Object.keys(form.errors).some(k => k.startsWith('functional_exam.'))
)

const hasStep2Error = computed(() =>
    Object.keys(form.errors).some(k =>
        k.startsWith('physical_exam.') ||
        ['blood_pressure', 'temperature', 'temperature_route', 'oxygen_saturation',
         'heart_rate', 'respiratory_rate', 'weight', 'height'].includes(k)
    )
)

const hasStep3Error = computed(() => {
    let err = !!(
        form.errors.complementary_studies ||
        form.errors.physical_examination ||
        form.errors.treatment_plan ||
        form.errors.epicrisis
    )

    Object.keys(form.errors).forEach((k) => {
        if (k.includes('diagnoses.') || k.startsWith('referrals.')) err = true
    })

    return err
})

const stepErrors = [hasStep0Error, hasStep1Error, hasStep2Error, hasStep3Error]

// IMC reactivo (talla en metros, según planilla)
const imc = computed(() => {
    if (!form.weight || !form.height || form.height <= 0) return null
    const hm  = form.height
    const val = (form.weight / (hm * hm)).toFixed(1)
    let label = '', color = ''
    if (val < 18.5)      { label = 'Bajo peso';  color = '#D97706' }
    else if (val < 25)   { label = 'Normal';      color = '#059669' }
    else if (val < 30)   { label = 'Sobrepeso';   color = '#EA580C' }
    else                 { label = 'Obesidad';    color = '#DC2626' }
    return { val, label, color }
})

// Cuenta sistemas alterados para el tab badge
const alteredCount = computed(() =>
    functionalSystems.filter(s => !form.functional_exam[`${s.key}_deny`]).length
)

// ── METHODS ──────────────────────────────────────────────

const denyAllFunctional = () => {
    functionalSystems.forEach(s => {
        form.functional_exam[`${s.key}_deny`]        = true
        form.functional_exam[`${s.key}_description`] = ''
    })
}

const setSistema = (key, mode) => {
    form.functional_exam[`${key}_deny`] = (mode === 'niega')
    if (mode === 'niega') form.functional_exam[`${key}_description`] = ''
}

const addDiagnosis = () => {
    form.diagnoses.push({
        sis_diagnosis_id: null,
        unlisted_diagnosis: '',
        diagnosis_type: 'Confirmado',
        medical_conduct_id: '',
        sort_order: form.diagnoses.length + 1,
    })
}

const removeDiagnosis = (index) => {
    form.diagnoses.splice(index, 1)
    form.diagnoses.forEach((d, i) => { d.sort_order = i + 1 })
}

const buildConsultationPayload = (data) => {
    const referrals =
        data.referral_state === 'N/A'
            ? []
            : data.referrals.map((r) => ({
                specialty_code: r.specialty_code,
                type: data.referral_state === 'Contra-referencia' ? 'counter_referral' : 'referral',
            }))

    const diagnoses = data.diagnoses.map((d) => ({
        ...d,
        medical_conduct_id: d.medical_conduct_id || null,
        sis_diagnosis_id: d.sis_diagnosis_id || null,
    }))

    return {
        ...data,
        referrals,
        diagnoses,
    }
}

const submit = () => {
    const isEdit = props.mode === 'edit' && props.consultation?.id
    const method = isEdit ? 'put' : 'post'
    const url = isEdit
        ? route('consultations.update', { patient: props.patient.id, consultation: props.consultation.id })
        : route('consultations.store', props.patient.id)

    form
        .transform(buildConsultationPayload)
        [method](url, {
            preserveScroll: true,
            onError: () => {
                const idx = stepErrors.findIndex((e) => e.value)
                if (idx !== -1) currentStep.value = idx
            },
        })
}
</script>

<template>
    <Head :title="`Consulta — ${patient.full_name}`" />

    <AppLayout :title="`Consulta · ${patient.full_name}`">

        <!-- ══ PATIENT HEADER CARD ════════════════════════ -->
        <div class="patient-header-card">
            <div class="patient-avatar-lg">
                {{ patient.full_name?.slice(0,2).toUpperCase() ?? 'PA' }}
            </div>
            <div style="flex:1">
                <div class="patient-id-chip">NUEVA CONSULTA</div>
                <div style="font-size:17px;font-weight:700;color:#0F172A;margin-bottom:4px">
                    {{ patient.full_name }}
                </div>
                <div class="meta-chips">
                    <span class="meta-chip">🪪 {{ patient.nationality }}-{{ patient.id_number }}</span>
                    <span class="meta-chip">📅 {{ age_at_moment }} años al momento</span>
                    <span v-if="patient.occupation?.name" class="meta-chip">
                        💼 {{ patient.occupation.name }}
                    </span>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px">
                <span class="status-badge badge-pending"><span class="badge-dot"></span> En Progreso</span>
                <div style="font-size:11px;color:#64748B">{{ institution }}</div>
                <Link :href="route('patients.show', patient.id)" class="btn btn-outline btn-sm">
                    ← Ficha del paciente
                </Link>
            </div>
        </div>

        <!-- ══ TABS WRAPPER ══════════════════════════════ -->
        <div class="tabs-wrapper">

            <div class="tabs-header">
                <button
                    v-for="(section, i) in sections"
                    :key="i"
                    type="button"
                    class="tab-btn"
                    :class="{ active: currentStep === i }"
                    :title="section.label"
                    @click="goToStep(i)"
                >
                    <span class="tab-step-num">{{ i + 1 }}</span>
                    <span class="tab-label-text">{{ section?.short }}</span>
                    <span v-if="stepErrors[i].value" class="tab-num tab-num-error">!</span>
                </button>
            </div>

            <div class="step-banner">
                <div>
                    <div class="step-group">Consulta médica · Parte 2</div>
                    <div class="step-title">{{ currentSection.label }}</div>
                </div>
                <div class="step-counter">Apartado {{ currentStep + 1 }} / {{ sections.length }}</div>
            </div>

            <form @submit.prevent="submit">

                <div v-if="Object.keys(form.errors).length" class="alert alert-error" style="margin-bottom:16px">
                    <strong>Errores en el formulario:</strong>
                    <ul style="margin:4px 0 0 16px;padding:0">
                        <li v-for="(msg, field) in form.errors" :key="field">{{ msg }}</li>
                    </ul>
                </div>

                <div class="tab-content">

                <!-- ══════ CONSULTA: ANAMNESIS ══════════════ -->
                <div v-show="currentStep === 0">

                    <!-- Control paciente sano -->
                    <div class="inline-alert alert-info" style="margin-bottom:20px;cursor:pointer"
                         @click="form.is_healthy = !form.is_healthy"
                         :style="form.is_healthy ? 'background:#D1FAE5;border-color:#6EE7B7;color:#065F46' : ''">
                        <div class="toggle-switch-lg" :class="{ on: form.is_healthy }">
                            <div class="toggle-knob-lg"></div>
                        </div>
                        <div>
                            <strong>¿Control de Paciente Sano?</strong>
                            <span style="font-weight:400;margin-left:8px">
                                Active si la consulta es preventiva, tamizaje o rutina sin patología activa.
                            </span>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-title">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14,2 14,8 20,8"/>
                            </svg>
                            Datos de la Consulta
                        </div>
                        <div class="form-grid grid-3">
                            <!-- Tipo de consulta -->
                            <div>
                                <label class="field-label">Tipo de Consulta <span class="req">*</span></label>
                                <div class="radio-group" style="margin-top:4px">
                                    <label v-for="t in [{v:'P',l:'P — Primera Vez'},{v:'S',l:'S — Sucesiva'},{v:'X',l:'X — Asociado'}]"
                                           :key="t.v"
                                           class="radio-option" :class="{ selected: form.consultation_type === t.v }"
                                           @click="form.consultation_type = t.v">
                                        <div class="radio-dot" :class="{ sel: form.consultation_type === t.v }">
                                            <div class="radio-inner"></div>
                                        </div>
                                        {{ t.l }}
                                    </label>
                                </div>
                                <p v-if="form.errors.consultation_type" class="field-error">{{ form.errors.consultation_type }}</p>
                            </div>

                            <!-- Servicio / Especialidad -->
                            <div>
                                <label class="field-label">Servicio de Atención <span class="req">*</span></label>
                                <div class="radio-group" style="margin-top:4px">
                                    <label v-for="s in [
                                        {v:'MG',l:'Medicina General'},
                                        {v:'EP',l:'Epidemiología'},
                                        {v:'EM',l:'Emergencia'},
                                        {v:'PR',l:'Preventiva'},
                                        {v:'OT',l:'Otra'},
                                    ]" :key="s.v"
                                           class="radio-option radio-option-sm" :class="{ selected: form.service_type === s.v }"
                                           @click="form.service_type = s.v">
                                        <div class="radio-dot" :class="{ sel: form.service_type === s.v }">
                                            <div class="radio-inner"></div>
                                        </div>
                                        {{ s.l }}
                                    </label>
                                </div>
                                <p v-if="form.errors.service_type" class="field-error">{{ form.errors.service_type }}</p>
                            </div>

                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-title">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                            Anamnesis
                        </div>
                        <div class="form-grid" style="gap:16px">
                            <div>
                                <label class="field-label">Motivo de Consulta <span class="req">*</span></label>
                                <textarea v-model="form.reason_for_consultation" class="field-textarea" rows="3"
                                          placeholder="Expresado en palabras del paciente. Ej: Cefalea intensa de 3 días de evolución…"/>
                                <p v-if="form.errors.reason_for_consultation" class="field-error">{{ form.errors.reason_for_consultation }}</p>
                            </div>
                            <div>
                                <label class="field-label">Enfermedad Actual / Evolución Cronológica <span class="req">*</span></label>
                                <textarea v-model="form.current_illness" class="field-textarea" rows="6"
                                          placeholder="Descripción detallada del cuadro clínico: tiempo de evolución, síntomas asociados, factores desencadenantes…"/>
                                <p v-if="form.errors.current_illness" class="field-error">{{ form.errors.current_illness }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <nav v-show="currentStep === 0" class="section-nav">
                    <span class="section-nav-hint">Fin de «La anamnesis»</span>
                    <button v-if="!isLastStep" type="button" class="btn-primary-nav" @click="nextStep">
                        Siguiente: {{ sections[1]?.short }}
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="9,18 15,12 9,6"/></svg>
                    </button>
                </nav>

                <!-- ══════ EXAMEN FUNCIONAL Y FÍSICO ════════ -->
                <div v-show="currentStep === 1">

                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                        <div class="inline-alert alert-info" style="flex:1;margin-right:16px">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <span>
                                Marque <strong>Altera</strong> en cada sistema para revelar el campo de descripción.
                                Use <strong>Niega</strong> para sistemas sin hallazgos.
                                <span v-if="alteredCount > 0" style="margin-left:8px;font-weight:700;color:#DC2626">
                                    ⚠ {{ alteredCount }} sistema(s) alterado(s)
                                </span>
                            </span>
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" @click="denyAllFunctional">
                            ✓ Negar Todos
                        </button>
                    </div>

                    <div class="sistemas-grid">
                        <div
                            v-for="s in functionalSystems"
                            :key="s.key"
                            class="sistema-item"
                            :class="{
                                'sistema-niega':  form.functional_exam[`${s.key}_deny`],
                                'sistema-altera': !form.functional_exam[`${s.key}_deny`],
                            }"
                        >
                            <div class="sistema-header">
                                <div class="sistema-name">
                                    <div class="sistema-icon">{{ s.icon }}</div>
                                    {{ s.label }}
                                    <span class="info-tip" :title="s.hint">?</span>
                                </div>
                                <div class="sistema-controls">
                                    <button
                                        type="button"
                                        class="ctrl-btn ctrl-niega"
                                        :class="{ sel: form.functional_exam[`${s.key}_deny`] }"
                                        @click="setSistema(s.key, 'niega')"
                                    >✓ Niega</button>
                                    <button
                                        type="button"
                                        class="ctrl-btn ctrl-altera"
                                        :class="{ sel: !form.functional_exam[`${s.key}_deny`] }"
                                        @click="setSistema(s.key, 'altera')"
                                    >⚠ Altera</button>
                                </div>
                            </div>
                            <Transition name="expand">
                                <div v-if="!form.functional_exam[`${s.key}_deny`]" class="sistema-detail">
                                    <textarea
                                        v-model="form.functional_exam[`${s.key}_description`]"
                                        class="field-textarea"
                                        :placeholder="`Describir hallazgos: ${s.hint}…`"
                                        rows="2"
                                        style="margin-top:10px"
                                        @click.stop
                                    />
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
                <nav v-show="currentStep === 1" class="section-nav">
                    <button type="button" class="btn-ghost-nav" @click="prevStep">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="15,18 9,12 15,6"/></svg>
                        Anterior
                    </button>
                    <button type="button" class="btn-primary-nav" @click="nextStep">
                        Siguiente: {{ sections[2]?.short }}
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="9,18 15,12 9,6"/></svg>
                    </button>
                </nav>

                <!-- ══════ EXAMEN FÍSICO Y SIGNOS VITALES ════════ -->
                <div v-show="currentStep === 2">

                    <!-- SIGNOS VITALES ─────────────────────────────────── -->
                    <div class="form-section">
                        <div class="section-title">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                            </svg>
                            Signos Vitales y Triaje
                        </div>

                        <div class="vitals-grid">

                            <!-- T.A. -->
                            <div class="vital-card">
                                <div class="vital-label">T.A. (mmHg)</div>
                                <input v-model="form.blood_pressure" class="vital-input" type="text"
                                       placeholder="120/80" style="font-family:'DM Mono',monospace"/>
                                <p v-if="form.errors.blood_pressure" class="field-error">{{ form.errors.blood_pressure }}</p>
                            </div>

                            <!-- Temperatura + Vía -->
                            <div class="vital-card" style="grid-column: span 2">
                                <div class="vital-label">Temperatura</div>
                                <div style="display:flex;gap:8px;align-items:flex-end">
                                    <div style="flex:1">
                                        <input v-model.number="form.temperature" class="vital-input" type="number"
                                               step="0.1" placeholder="36.5"/>
                                        <span style="font-size:10px;color:#94A3B8;margin-top:2px;display:block">°C</span>
                                    </div>
                                    <div style="flex:1.4">
                                        <select v-model="form.temperature_route"
                                                style="width:100%;border:1.5px solid #E2E8F0;border-radius:8px;padding:6px 10px;font-size:11.5px;font-family:inherit;color:#374151;background:#fff;outline:none;margin-bottom:2px">
                                            <option value="Axilar">Axilar</option>
                                            <option value="Oral">Oral</option>
                                            <option value="Rectal">Rectal</option>
                                            <option value="Timpánica">Timpánica</option>
                                        </select>
                                        <span style="font-size:10px;color:#94A3B8;display:block">Vía</span>
                                    </div>
                                </div>
                                <p v-if="form.errors.temperature" class="field-error">{{ form.errors.temperature }}</p>
                            </div>

                            <!-- FC -->
                            <div class="vital-card">
                                <div class="vital-label">FC (lpm)</div>
                                <input v-model.number="form.heart_rate" class="vital-input" type="number" placeholder="72"/>
                                <p v-if="form.errors.heart_rate" class="field-error">{{ form.errors.heart_rate }}</p>
                            </div>

                            <!-- FR -->
                            <div class="vital-card">
                                <div class="vital-label">FR (rpm)</div>
                                <input v-model.number="form.respiratory_rate" class="vital-input" type="number" placeholder="16"/>
                                <p v-if="form.errors.respiratory_rate" class="field-error">{{ form.errors.respiratory_rate }}</p>
                            </div>

                            <!-- SpO₂ -->
                            <div class="vital-card">
                                <div class="vital-label">SpO₂ (%)</div>
                                <input v-model.number="form.oxygen_saturation" class="vital-input" type="number"
                                       step="0.1" min="50" max="100" placeholder="98"/>
                                <p v-if="form.errors.oxygen_saturation" class="field-error">{{ form.errors.oxygen_saturation }}</p>
                            </div>

                            <!-- Peso -->
                            <div class="vital-card">
                                <div class="vital-label">Peso (kg)</div>
                                <input v-model.number="form.weight" class="vital-input" type="number" step="0.1" placeholder="70.0"/>
                                <p v-if="form.errors.weight" class="field-error">{{ form.errors.weight }}</p>
                            </div>

                            <!-- Talla (metros) -->
                            <div class="vital-card">
                                <div class="vital-label">Talla (m)</div>
                                <input v-model.number="form.height" class="vital-input" type="number"
                                       step="0.01" min="0.3" max="2.5" placeholder="1.70"/>
                                <p v-if="form.errors.height" class="field-error">{{ form.errors.height }}</p>
                            </div>

                            <!-- IMC reactivo -->
                            <div v-if="imc" class="imc-card">
                                <div class="imc-value" :style="`color:${imc.color}`">{{ imc.val }}</div>
                                <div class="imc-label">IMC</div>
                                <div class="imc-status" :style="`color:${imc.color}`">{{ imc.label }}</div>
                            </div>

                        </div>
                    </div>

                    <!-- EXAMEN FÍSICO ESTRUCTURADO ──────────────────────── -->
                    <div class="form-section">
                        <div class="section-title">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                            Examen Físico por Secciones Anatómicas
                        </div>

                        <PhysicalExamTab :form="form" />
                    </div>
                </div>
                <nav v-show="currentStep === 2" class="section-nav">
                    <button type="button" class="btn-ghost-nav" @click="prevStep">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="15,18 9,12 15,6"/></svg>
                        Anterior
                    </button>
                    <button type="button" class="btn-primary-nav" @click="nextStep">
                        Siguiente: {{ sections[3]?.short }}
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="9,18 15,12 9,6"/></svg>
                    </button>
                </nav>

                <!-- ══════ CONSULTA: EXPLORACIÓN COMPLEMENTARIA ═════ -->
                <div v-show="currentStep === 3" class="form-section">
                    <div class="section-title">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
                            <path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2v-4M9 21H5a2 2 0 0 1-2-2v-4"/>
                        </svg>
                        Exploración Complementaria
                    </div>
                    <div class="inline-alert alert-info" style="margin-bottom:14px">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span>Paraclínicos, laboratorio, imagenología, electrocardiograma u otros estudios solicitados o revisados en esta consulta.</span>
                    </div>
                    <label class="field-label">Resultados y estudios complementarios</label>
                    <textarea
                        v-model="form.complementary_studies"
                        class="field-textarea"
                        rows="8"
                        placeholder="Ej: Hemograma completo Hb 12.5… · Radiografía de tórax PA… · Glicemia 95 mg/dL…"
                    />
                    <p v-if="form.errors.complementary_studies" class="field-error">{{ form.errors.complementary_studies }}</p>
                </div>
                <nav v-show="false" class="section-nav">
                    <button type="button" class="btn-ghost-nav" @click="prevStep">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="15,18 9,12 15,6"/></svg>
                        Anterior
                    </button>
                    <button type="button" class="btn-primary-nav" @click="nextStep">
                        Siguiente
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="9,18 15,12 9,6"/></svg>
                    </button>
                </nav>

                <!-- ══════ CONSULTA: DIAGNÓSTICO PRESUNTIVO ═════ -->
                <div v-show="currentStep === 3">
                    <div class="form-section">
                        <div class="section-title" style="justify-content:space-between">
                            <div style="display:flex;align-items:center;gap:8px">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
                                    <path d="M9 11l3 3L22 4"/>
                                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                                </svg>
                                Diagnóstico Presuntivo (Catálogo SIS/MPPS)
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" @click="addDiagnosis">
                                + Añadir Diagnóstico
                            </button>
                        </div>

                        <div v-for="(diag, i) in form.diagnoses" :key="i" class="ante-card" style="margin-bottom:12px">
                            <div class="ante-header">
                                <div class="ante-title">
                                    <span class="priority-badge">Prioridad #{{ i + 1 }}</span>
                                </div>
                                <button v-if="form.diagnoses.length > 1" type="button"
                                        class="btn btn-danger-sm" @click="removeDiagnosis(i)">
                                    ✕ Remover
                                </button>
                            </div>

                            <div class="form-grid grid-2">
                                <div>
                                    <label class="field-label">Catálogo SIS / CIE-10</label>
                                    <select v-model="diag.sis_diagnosis_id" class="field-select">
                                        <option :value="null">No listado / Texto libre…</option>
                                        <option v-for="c in diagnosesCatalog" :key="c.id" :value="c.id">
                                            [{{ c.code }}] {{ c.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="field-label">
                                        Tipo de Diagnóstico <span class="req">*</span>
                                    </label>
                                    <div class="radio-group" style="margin-top:4px">
                                        <label v-for="t in ['Sospechoso','Probable','Confirmado','No Aplica']"
                                               :key="t"
                                               class="radio-option" :class="{ selected: diag.diagnosis_type === t }"
                                               @click="diag.diagnosis_type = t">
                                            <div class="radio-dot" :class="{ sel: diag.diagnosis_type === t }">
                                                <div class="radio-inner"></div>
                                            </div>
                                            {{ t }}
                                        </label>
                                    </div>
                                </div>
                                <div :class="{ 'col-full': diag.sis_diagnosis_id !== null }">
                                    <label class="field-label"
                                           :class="{ 'label-required': diag.sis_diagnosis_id === null }">
                                        Diagnóstico no listado
                                        <span v-if="diag.sis_diagnosis_id === null" class="req">*</span>
                                    </label>
                                    <input v-model="diag.unlisted_diagnosis" class="field-input" type="text"
                                           :required="diag.sis_diagnosis_id === null"
                                           placeholder="Escribir diagnóstico alternativo…"/>
                                </div>
                                <div v-if="diag.sis_diagnosis_id !== null">
                                    <label class="field-label">Conducta Médica <span class="req">*</span></label>
                                    <select v-model="diag.medical_conduct_id" class="field-select" required>
                                        <option value="" disabled>Seleccione…</option>
                                        <option v-for="c in medicalConducts" :key="c.id" :value="c.id">
                                            {{ c.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══════ CONSULTA: TRATAMIENTO ═════ -->
                <div v-show="currentStep === 3" class="form-section">
                        <div class="section-title">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
                                <path d="M12 2a8 8 0 0 0-8 8c0 5.52 8 12 8 12s8-6.48 8-12a8 8 0 0 0-8-8z"/>
                            </svg>
                            Tratamiento
                        </div>
                        <textarea v-model="form.treatment_plan" class="field-textarea" rows="8"
                                  placeholder="Medicamentos, dosis, vía, frecuencia, duración, indicaciones al paciente, medidas generales…"/>
                        <p v-if="form.errors.treatment_plan" class="field-error">{{ form.errors.treatment_plan }}</p>
                </div>

                <!-- ══════ CONSULTA: EPICRISIS ═════ -->
                <div v-show="currentStep === 3">
                    <div class="form-section">
                        <div class="section-title">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14,2 14,8 20,8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                            </svg>
                            Epicrisis
                        </div>
                        <label class="field-label">Resumen y cierre de la consulta</label>
                        <textarea
                            v-model="form.epicrisis"
                            class="field-textarea"
                            rows="6"
                            placeholder="Síntesis del cuadro, evolución, conclusiones y recomendaciones de seguimiento…"
                        />
                        <p v-if="form.errors.epicrisis" class="field-error">{{ form.errors.epicrisis }}</p>
                    </div>

                    <!-- ══════ EDICIÓN: JUSTIFICACIÓN ═════ -->
                    <div v-if="mode === 'edit'" class="form-section" style="border-left-color:#DC2626">
                        <div class="section-title">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon" style="color:#DC2626">
                                <path d="M12 2a8 8 0 0 0-8 8c0 5.52 8 12 8 12s8-6.48 8-12a8 8 0 0 0-8-8z"/>
                            </svg>
                            Justificación de la Modificación <span class="req">*</span>
                        </div>
                        <label class="field-label">Explique brevemente la razón del cambio (auditoría interna)</label>
                        <textarea
                            v-model="form.edit_justification"
                            class="field-textarea"
                            rows="4"
                            placeholder="Ej: Corrección de error de tipeo en diagnóstico / Actualización del tratamiento por nuevos resultados…"
                        />
                        <p v-if="form.errors.edit_justification" class="field-error">{{ form.errors.edit_justification }}</p>
                    </div>

                    <!-- Referencias / Contrarreferencias (cierre) -->
                    <div class="form-section">
                        <div class="section-title">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
                                <polyline points="17,1 21,5 17,9"/>
                                <path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                                <polyline points="7,23 3,19 7,15"/>
                                <path d="M21 13v2a4 4 0 0 1-4 4H3"/>
                            </svg>
                            Referencia / Contra-referencia
                            <span v-if="form.referrals.length > 0"
                                  style="margin-left:8px;background:#DBEAFE;color:#1D4ED8;border-radius:10px;padding:1px 8px;font-size:10px;font-weight:700">
                                {{ form.referrals.length }} seleccionada(s)
                            </span>
                        </div>

                        <!-- Estado global de referencia -->
                        <div style="margin-bottom:14px">
                            <label class="field-label">Estado de la referencia</label>
                            <div class="radio-group" style="margin-top:6px">
                                <label v-for="s in ['N/A','Referencia','Contra-referencia']" :key="s"
                                       class="radio-option" :class="{ selected: form.referral_state === s }"
                                       @click="form.referral_state = s; if (s === 'N/A') form.referrals = []">
                                    <div class="radio-dot" :class="{ sel: form.referral_state === s }">
                                        <div class="radio-inner"></div>
                                    </div>
                                    {{ s }}
                                </label>
                            </div>
                        </div>

                        <!-- Chips de especialidades (solo si Referencia o Contra-referencia) -->
                        <Transition name="expand">
                            <div v-if="form.referral_state !== 'N/A'">
                                <label class="field-label" style="margin-bottom:8px;display:block">
                                    Especialidades a
                                    {{ form.referral_state === 'Referencia' ? 'referir' : 'contra-referir' }}
                                </label>
                                <div class="ref-grid">
                                    <div
                                        v-for="spec in commonSpecialties"
                                        :key="spec.code"
                                        class="ref-chip"
                                        :class="{ selected: isReferralSelected(spec.code) }"
                                        @click="toggleReferral(spec)"
                                    >
                                        <span v-if="isReferralSelected(spec.code)" style="margin-right:4px">✓</span>
                                        {{ spec.name }}
                                    </div>
                                </div>

                                <!-- Resumen -->
                                <div v-if="form.referrals.length > 0"
                                     class="inline-alert alert-info" style="margin-top:12px">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <polyline points="17,1 21,5 17,9"/>
                                        <path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                                    </svg>
                                    <span>
                                        {{ form.referral_state === 'Referencia' ? 'Referido' : 'Contra-referido' }} a:
                                        <strong v-for="(ref, i) in form.referrals" :key="i">
                                            {{ specialtyNameByCode(ref.specialty_code) }}
                                            <span v-if="i < form.referrals.length - 1"> · </span>
                                        </strong>
                                    </span>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
                <nav v-show="currentStep === 3" class="section-nav">
                    <button type="button" class="btn-ghost-nav" @click="prevStep">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="15,18 9,12 15,6"/></svg>
                        Anterior
                    </button>
                    <span class="section-nav-hint">Último apartado — registre la consulta abajo</span>
                </nav>

                </div><!-- /tab-content -->

                <!-- ══ BOTTOM BAR ════════════════════════ -->
                <div class="bottom-bar-inner">
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="display:flex;gap:4px">
                            <div v-for="(_, i) in sections" :key="i"
                                 style="width:6px;height:6px;border-radius:50%;transition:background 0.2s"
                                 :style="currentStep === i
                                     ? 'background:#2563EB'
                                     : stepErrors[i].value ? 'background:#EF4444' : 'background:#CBD5E1'"
                            />
                        </div>
                        <span style="font-size:12px;color:#64748B">
                            Apartado {{ currentStep + 1 }} / {{ sections.length }}
                        </span>
                    </div>

                    <div style="display:flex;gap:8px;align-items:center">
                        <button v-if="!isFirstStep" type="button" class="btn btn-outline" @click="prevStep">
                            ← Anterior
                        </button>

                        <!-- Guardar Borrador -->
                        <button type="button" class="btn btn-ghost" title="Próximamente">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                <polyline points="17,21 17,13 7,13 7,21"/>
                                <polyline points="7,3 7,8 15,8"/>
                            </svg>
                            Borrador
                        </button>

                        <!-- Exportar PDF (solo en último tab) -->
                        <button v-if="isLastStep" type="button" class="btn btn-outline" title="Próximamente">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:14px;height:14px">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14,2 14,8 20,8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                            </svg>
                            Exportar PDF
                        </button>

                        <button v-if="!isLastStep" type="button" class="btn btn-primary" @click="nextStep">
                            Siguiente →
                        </button>
                        <button v-if="isLastStep" type="submit"
                                class="btn btn-success" :disabled="form.processing">
                            <svg v-if="form.processing" style="width:15px;height:15px;animation:spin 1s linear infinite"
                                 fill="none" viewBox="0 0 24 24">
                                <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <svg v-else fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                 style="width:15px;height:15px">
                                <path d="M9 11l3 3L22 4"/>
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                            </svg>
                            {{ form.processing ? 'Guardando consulta…' : 'Finalizar y Registrar' }}
                        </button>
                    </div>
                </div>

            </form>
        </div>

    </AppLayout>
</template>

<style scoped>
/* ── PATIENT HEADER ──────────────────────────────────────── */
.patient-header-card {
    background: #fff; border-radius: 14px;
    border: 1px solid #E2E8F0; padding: 18px 24px;
    display: flex; align-items: center; gap: 20px;
    margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.patient-avatar-lg {
    width: 52px; height: 52px; border-radius: 12px;
    background: linear-gradient(135deg, #DBEAFE, #EDE9FE);
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; font-weight: 700; color: #2563EB; flex-shrink: 0;
}
.patient-id-chip {
    font-size: 10px; font-weight: 600; letter-spacing: 0.05em;
    color: #2563EB; background: #EFF6FF;
    padding: 3px 8px; border-radius: 6px; border: 1px solid #BFDBFE;
    display: inline-block; margin-bottom: 4px;
}
.meta-chips { display: flex; gap: 8px; flex-wrap: wrap; }
.meta-chip  { font-size: 11px; color: #64748B; background: #F1F5F9; padding: 3px 8px; border-radius: 6px; }
.status-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
}
.badge-pending { background: #FEF3C7; color: #92400E; }
.badge-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

/* ── TABS ────────────────────────────────────────────────── */
.tabs-wrapper {
    background: #fff; border-radius: 14px;
    border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    overflow: hidden; margin-bottom: 24px;
}
.tabs-header {
    display: flex; border-bottom: 1px solid #E2E8F0;
    background: #F8FAFC; padding: 0 4px; overflow-x: auto;
}
.tab-btn {
    display: flex; align-items: center; gap: 8px;
    padding: 14px 20px; font-size: 13px; font-weight: 500;
    color: #64748B; cursor: pointer; border: none;
    background: transparent; white-space: nowrap;
    border-bottom: 2px solid transparent; margin-bottom: -1px;
    transition: all 0.18s; font-family: inherit;
}
.tab-btn:hover  { color: #0F172A; background: rgba(37,99,235,0.04); }
.tab-btn.active { color: #2563EB; border-bottom-color: #2563EB; }
.tab-num {
    font-size: 10px; background: #E2E8F0; color: #64748B;
    border-radius: 10px; padding: 1px 6px; font-weight: 600;
}
.tab-btn.active .tab-num { background: #DBEAFE; color: #2563EB; }
.tab-num-error { background: #FEE2E2 !important; color: #DC2626 !important; }
.tab-step-num {
    width: 20px; height: 20px; border-radius: 50%;
    background: #E2E8F0; color: #64748B;
    font-size: 10px; font-weight: 700;
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.tab-btn.active .tab-step-num { background: #2563EB; color: #fff; }
.tab-label-text { font-size: 11px; max-width: 88px; overflow: hidden; text-overflow: ellipsis; }

.step-banner {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 14px 24px; background: linear-gradient(90deg, #EFF6FF, #F8FAFC);
    border-bottom: 1px solid #E2E8F0;
}
.step-group {
    font-size: 10px; font-weight: 600; letter-spacing: 0.08em;
    text-transform: uppercase; color: #2563EB;
}
.step-title { font-size: 15px; font-weight: 700; color: #0F172A; margin-top: 2px; }
.step-counter {
    font-size: 12px; font-weight: 600; color: #64748B;
    background: #fff; border: 1px solid #E2E8F0;
    padding: 6px 12px; border-radius: 8px; white-space: nowrap;
}

.section-nav {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    flex-wrap: wrap; margin: 8px 0 28px; padding: 16px 20px;
    background: #F8FAFC; border: 1px dashed #CBD5E1; border-radius: 10px;
}
.section-nav-hint { font-size: 12px; color: #64748B; font-weight: 500; }
.btn-primary-nav, .btn-ghost-nav {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
    font-family: inherit; cursor: pointer; transition: all 0.18s;
}
.btn-primary-nav { background: #2563EB; color: #fff; border: none; margin-left: auto; }
.btn-primary-nav:hover { background: #1D4ED8; }
.btn-ghost-nav { background: #fff; color: #475569; border: 1px solid #E2E8F0; }
.btn-ghost-nav:hover { background: #F1F5F9; }
.divider { border: none; border-top: 1px solid #E2E8F0; }

.tab-content { padding: 28px; min-height: 280px; }

/* ── FORM BASICS ─────────────────────────────────────────── */
.form-section { margin-bottom: 28px; }
.section-title {
    font-size: 11px; font-weight: 700; letter-spacing: 0.1em;
    text-transform: uppercase; color: #64748B;
    padding-bottom: 10px; margin-bottom: 16px;
    border-bottom: 1px solid #E2E8F0;
    display: flex; align-items: center; gap: 8px;
}
.section-icon { width: 14px; height: 14px; flex-shrink: 0; }

.form-grid  { display: grid; gap: 14px; }
.grid-2     { grid-template-columns: repeat(2, 1fr); }
.col-full   { grid-column: 1 / -1; }
@media (max-width: 700px) { .grid-2 { grid-template-columns: 1fr; } }

.field-label {
    display: block; font-size: 11.5px; font-weight: 600;
    color: #374151; margin-bottom: 5px;
}
.req         { color: #EF4444; margin-left: 2px; }
.field-error { font-size: 11px; color: #EF4444; margin-top: 3px; }

.field-input, .field-select, .field-textarea {
    width: 100%; padding: 8px 12px;
    border: 1.5px solid #E2E8F0; border-radius: 8px;
    font-size: 13px; font-family: inherit; color: #0F172A;
    background: #fff; outline: none;
    transition: border-color 0.18s, box-shadow 0.18s; appearance: none;
}
.field-input:focus, .field-select:focus, .field-textarea:focus {
    border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
.field-input::placeholder, .field-textarea::placeholder { color: #CBD5E1; }
.field-textarea { resize: vertical; min-height: 72px; line-height: 1.5; }
.field-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px;
}

/* ── RADIO ───────────────────────────────────────────────── */
.radio-group   { display: flex; flex-wrap: wrap; gap: 8px; }
.radio-option  {
    display: flex; align-items: center; gap: 6px;
    padding: 6px 12px; border-radius: 8px;
    border: 1.5px solid #E2E8F0; cursor: pointer;
    font-size: 12.5px; transition: all 0.15s; user-select: none;
}
.radio-option:hover   { border-color: #93C5FD; background: #EFF6FF; }
.radio-option.selected{ border-color: #2563EB; background: #EFF6FF; color: #2563EB; font-weight: 600; }
.radio-dot { width: 14px; height: 14px; border-radius: 50%; border: 2px solid #CBD5E1; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.15s; }
.radio-dot.sel { border-color: #2563EB; background: #2563EB; }
.radio-inner { width: 5px; height: 5px; border-radius: 50%; background: #fff; }

/* ── TOGGLE PACIENTE SANO ────────────────────────────────── */
.toggle-switch-lg {
    width: 40px; height: 22px; border-radius: 11px;
    background: #CBD5E1; position: relative; transition: background 0.2s; flex-shrink: 0;
}
.toggle-switch-lg.on { background: #10B981; }
.toggle-knob-lg {
    position: absolute; top: 3px; left: 3px;
    width: 16px; height: 16px; border-radius: 50%; background: #fff;
    transition: transform 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.2);
}
.toggle-switch-lg.on .toggle-knob-lg { transform: translateX(18px); }

/* ── VITALS ──────────────────────────────────────────────── */
.vitals-grid {
    display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px;
}
@media (max-width: 900px) { .vitals-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 600px) { .vitals-grid { grid-template-columns: repeat(2, 1fr); } }

.vital-card {
    background: #F8FAFC; border: 1.5px solid #E2E8F0;
    border-radius: 10px; padding: 12px;
}
.vital-label {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.08em; color: #64748B; margin-bottom: 6px;
}
.vital-input {
    width: 100%; border: none; background: transparent;
    font-size: 20px; font-weight: 700; color: #2563EB;
    outline: none; font-family: 'DM Mono', monospace;
}
.vital-input::placeholder { color: #CBD5E1; font-size: 18px; }
.vital-input:focus { outline: none; }

.imc-card {
    background: linear-gradient(135deg, #EFF6FF, #E0F2FE);
    border: 1.5px solid #BFDBFE; border-radius: 10px; padding: 12px; text-align: center;
}
.imc-value { font-size: 28px; font-weight: 800; font-family: 'DM Mono', monospace; line-height: 1; }
.imc-label { font-size: 10px; color: #64748B; margin: 2px 0; }
.imc-status { font-size: 11px; font-weight: 700; }

/* ── SISTEMAS FUNCIONALES ────────────────────────────────── */
.sistemas-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
}
@media (max-width: 700px) { .sistemas-grid { grid-template-columns: 1fr; } }

.sistema-item {
    border: 1.5px solid #E2E8F0; border-radius: 10px;
    padding: 14px 16px; background: #fff; transition: all 0.18s;
}
.sistema-item:hover { border-color: #BFDBFE; }
.sistema-niega { background: #F0FDF4; border-color: #BBF7D0; }
.sistema-altera { background: #FFF5F5; border-color: #FCA5A5; }

.sistema-header {
    display: flex; align-items: center;
    justify-content: space-between; gap: 12px;
}
.sistema-name {
    font-size: 12.5px; font-weight: 600; display: flex; align-items: center; gap: 8px;
}
.sistema-icon {
    width: 28px; height: 28px; border-radius: 7px;
    background: #EFF6FF; display: flex; align-items: center;
    justify-content: center; font-size: 15px; flex-shrink: 0;
}
.sistema-controls { display: flex; gap: 6px; align-items: center; flex-shrink: 0; }

.ctrl-btn {
    padding: 5px 12px; border-radius: 6px; border: 1.5px solid #E2E8F0;
    font-size: 11.5px; font-weight: 600; cursor: pointer; font-family: inherit;
    transition: all 0.15s; background: #fff; color: #64748B;
}
.ctrl-niega:hover, .ctrl-niega.sel {
    border-color: #6EE7B7; color: #065F46; background: #ECFDF5;
}
.ctrl-altera:hover, .ctrl-altera.sel {
    border-color: #FCA5A5; color: #991B1B; background: #FEF2F2;
}
.info-tip {
    display: inline-flex; width: 14px; height: 14px; border-radius: 50%;
    background: #E2E8F0; color: #64748B; font-size: 9px; font-weight: 700;
    align-items: center; justify-content: center; cursor: help; flex-shrink: 0;
}

/* ── ANTE CARD (diagnósticos) ────────────────────────────── */
.ante-card {
    border: 1.5px solid #E2E8F0; border-radius: 10px;
    padding: 16px; background: #FAFAFA; transition: border-color 0.18s;
}
.ante-header {
    display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;
}
.ante-title { font-size: 12.5px; font-weight: 600; color: #0F172A; }
.priority-badge {
    font-size: 10px; font-weight: 700; background: #DBEAFE; color: #1D4ED8;
    border-radius: 6px; padding: 2px 8px; text-transform: uppercase; letter-spacing: 0.05em;
}

/* ── REFERRALS ───────────────────────────────────────────── */
.ref-grid { display: flex; flex-wrap: wrap; gap: 6px; }
.ref-chip {
    padding: 5px 10px; border-radius: 7px; font-size: 11.5px; font-weight: 500;
    border: 1.5px solid #E2E8F0; cursor: pointer;
    transition: all 0.15s; user-select: none; background: #fff; color: #0F172A;
}
.ref-chip:hover   { border-color: #93C5FD; background: #EFF6FF; color: #2563EB; }
.ref-chip.selected{ border-color: #2563EB; background: #EFF6FF; color: #2563EB; }

/* ── INLINE ALERT ────────────────────────────────────────── */
.inline-alert {
    padding: 10px 14px; border-radius: 8px; font-size: 12px; font-weight: 500;
    display: flex; align-items: center; gap: 8px;
}
.alert-info { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }

/* ── EXPAND TRANSITION ───────────────────────────────────── */
.expand-enter-active, .expand-leave-active {
    transition: all 0.25s cubic-bezier(.4,0,.2,1); overflow: hidden;
}
.expand-enter-from, .expand-leave-to { max-height: 0; opacity: 0; }
.expand-enter-to, .expand-leave-from { max-height: 500px; opacity: 1; }

/* ── BOTTOM BAR INNER ────────────────────────────────────── */
.bottom-bar-inner {
    border-top: 1px solid #E2E8F0; padding: 16px 28px;
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
}

/* ── BUTTONS ─────────────────────────────────────────────── */
.btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 16px; border-radius: 8px; border: none;
    font-size: 13px; font-weight: 600; cursor: pointer;
    font-family: inherit; transition: all 0.18s; white-space: nowrap;
    text-decoration: none;
}
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-sm { padding: 6px 12px; font-size: 12px; }
.btn-primary { background: #2563EB; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #1D4ED8; transform: translateY(-1px); }
.btn-outline { background: #fff; border: 1.5px solid #E2E8F0; color: #0F172A; }
.btn-outline:hover { border-color: #94A3B8; background: #F8FAFC; }
.btn-success { background: #10B981; color: #fff; }
.btn-success:hover:not(:disabled) { background: #059669; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,0.3); }
.btn-danger-sm {
    background: none; border: none; color: #EF4444; font-size: 12px;
    font-weight: 600; cursor: pointer; padding: 2px 6px; font-family: inherit;
    transition: color 0.15s;
}
.btn-danger-sm:hover { color: #DC2626; }

@keyframes spin { to { transform: rotate(360deg); } }
</style>
