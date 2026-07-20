<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import DatePicker from '@/Components/DatePicker.vue'
import {
  buildPatientStorePayload,
  patientToFormState,
  defaultBackground,
  defaultFamilyBackground,
  defaultHabits,
  overcrowdingIndex,
  DISABILITY_TYPE_MAP,
} from '@/utils/patientFormPayload'

const props = defineProps({
  maritalStatuses:   { type: Array, default: () => [] },
  ethnicities:       { type: Array, default: () => [] },
  instructionLevels: { type: Array, default: () => [] },
  occupations:       { type: Array, default: () => [] },
  religions:         { type: Array, default: () => [] },
  institution:       { type: String, default: '' },
  userName:          { type: String, default: '' },
  mode:              { type: String, default: 'create' },
  patient:           { type: Object, default: null },
})

const isEdit = computed(() => props.mode === 'edit' && props.patient?.id)

// ────────────────────────────────────────────────────────────
// APARTADOS (pasos del formulario)
// ────────────────────────────────────────────────────────────
const sections = [
  { label: 'Datos Demográficos', short: 'Datos demográficos', group: 'Ficha patronímica' },
  { label: 'Antecedentes Clínicos', short: 'Antecedentes clínicos', group: 'Historia clínica' },
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
const stepProgress = computed(() =>
  Math.round(((currentStep.value + 1) / sections.length) * 100)
)

// ────────────────────────────────────────────────────────────
// FORM — estructura exacta que espera StorePatientRequest
// ────────────────────────────────────────────────────────────
const initialState = props.patient
  ? patientToFormState(props.patient)
  : {
      full_name: '',
      id_number: '',
      nationality: 'V',
      nationality_country: '',
      gender: '',
      birth_date: '',
      birth_place: '',
      marital_status_id: '',
      ethnicity_id: '',
      instruction_level_id: '',
      occupation_id: '',
      occupation_detail: '',
      religion_id: '',
      religion_detail: '',
      knows_blood_type: false,
      blood_type: '',
      rh_factor: '',
      phone_number: '',
      addr_state: '',
      addr_municipality: '',
      addr_parish: '',
      addr_locality: '',
      addr_sector: '',
      addr_street: '',
      addr_house_number: '',
      addr_zip_code: '',
      addr_reference: '',
      residence_time: '',
      background: defaultBackground(),
      family_background: defaultFamilyBackground(),
      habits: defaultHabits(),
      close_history: false,
    }

const form = useForm(initialState)

const fieldLabels = {
  full_name: 'Nombre Completo',
  id_number: 'Cédula de Identidad',
  nationality: 'Nacionalidad',
  nationality_country: 'Nacionalidad (país)',
  gender: 'Sexo',
  birth_date: 'Fecha de Nacimiento',
  birth_place: 'Lugar de Nacimiento',
  marital_status_id: 'Estado Civil',
  ethnicity_id: 'Etnia',
  instruction_level_id: 'Grado de Instrucción',
  occupation_id: 'Profesión u Oficio',
  occupation_detail: 'Detalle de Ocupación',
  religion_id: 'Religión',
  religion_detail: 'Detalle de Religión',
  knows_blood_type: '¿Conoce grupo sanguíneo?',
  blood_type: 'Tipo de Sangre',
  rh_factor: 'Factor Rh',
  phone_number: 'Teléfono',
  addr_state: 'Estado (dirección)',
  addr_municipality: 'Municipio',
  addr_parish: 'Parroquia',
  addr_locality: 'Localidad',
  addr_sector: 'Sector',
  addr_street: 'Calle',
  addr_house_number: 'N° Casa',
  addr_zip_code: 'Código Postal',
  addr_reference: 'Punto de Referencia',
  residence_time: 'Tiempo de Residencia',
  close_history: 'Cerrar Historia Clínica',
  clinical_validation_error: 'Validación clínica',

  // Antecedentes personales (background)
  'background.allergies_deny': '¿Niega alergias?',
  'background.allergies_description': 'Descripción de alergias',
  'background.pathological_deny': '¿Niega enfermedades patológicas?',
  'background.pathological_disease': 'Enfermedad patológica',
  'background.pathological_onset_value': 'Tiempo de evolución',
  'background.pathological_onset_unit': 'Unidad de tiempo',
  'background.pathological_controlled': '¿Enfermedad controlada?',
  'background.pathological_treatment': 'Tratamiento',
  'background.infectious_deny': '¿Niega enfermedades infecciosas?',
  'background.infectious_disease': 'Enfermedad infecciosa',
  'background.infectious_age': 'Edad al padecerla',
  'background.infectious_treatment': 'Tratamiento',
  'background.infectious_hospitalization': '¿Requirió hospitalización?',
  'background.infectious_complications': 'Complicaciones',
  'background.immune_deny_vaccination': 'Esquema de vacunación',
  'background.immune_childhood_status': 'Estado inmunizaciones',
  'background.immune_missing_vaccines': 'Vacunas faltantes',
  'background.immune_adult_vaccines': 'Vacunas de adulto',
  'background.immune_adult_age': 'Edad de vacunación',
  'background.immune_complications': 'Complicaciones',
  'background.transfusion_deny': '¿Niega transfusiones?',
  'background.transfusion_age': 'Edad de transfusión',
  'background.transfusion_type': 'Tipo de transfusión',
  'background.transfusion_bags_count': 'N° bolsas transfundidas',
  'background.transfusion_reason': 'Motivo de transfusión',
  'background.obgyn_apply': '¿Aplica antecedentes gineco-obstétricos?',
  'background.obgyn_gestas': 'Número de gestas',
  'background.obgyn_partos': 'Número de partos',
  'background.obgyn_cesareas': 'Número de cesáreas',
  'background.obgyn_abortos': 'Número de abortos',
  'background.obgyn_menarche': 'Fecha de menarca',
  'background.obgyn_menopause': 'Fecha de menopausia',
  'background.obgyn_cycle_periodicity': 'Periodicidad del ciclo',
  'background.obgyn_cycle_duration': 'Duración del ciclo',
  'background.obgyn_cycle_pads_per_day': 'Toallas por día',
  'background.obgyn_fur': 'Fecha de última regla',
  'background.surgical_deny': '¿Niega cirugías?',
  'background.surgical_intervention': 'Intervención quirúrgica',
  'background.surgical_age': 'Edad de la cirugía',
  'background.surgical_complications': 'Complicaciones quirúrgicas',
  'background.traumatic_deny': '¿Niega traumatismos?',
  'background.traumatic_fracture': 'Fractura o traumatismo',
  'background.traumatic_age': 'Edad del traumatismo',
  'background.traumatic_treatment': 'Tratamiento',
  'background.traumatic_complications': 'Complicaciones',
  'background.std_deny': '¿Niega ETS?',
  'background.std_disease': 'ETS padecida',
  'background.std_age': 'Edad al padecerla',
  'background.std_treatment': 'Tratamiento',
  'background.std_hospitalization': '¿Requirió hospitalización?',
  'background.std_complications': 'Complicaciones',
  'background.epidemiological_deny': '¿Niega antecedentes epidemiológicos?',
  'background.epidem_destination': 'Destino del viaje',
  'background.epidem_start_date': 'Fecha de ida',
  'background.epidem_end_date': 'Fecha de retorno',
  'background.epidem_biome': 'Bioma del lugar',
  'background.disability_deny': '¿Niega discapacidad?',
  'background.disability_types': 'Tipos de discapacidad',
  'background.disability_type': 'Tipo de discapacidad',
  'background.disability_specific_name': 'Nombre de discapacidad',
  'background.disability_onset_value': 'Tiempo de evolución',
  'background.disability_onset_unit': 'Unidad de tiempo',
  'background.disability_pharmacological_treatment': 'Tratamiento farmacológico',

  // Antecedentes familiares
  'family_background.mother.status': 'Estado de la madre',
  'family_background.mother.age': 'Edad de la madre',
  'family_background.mother.pathology': 'Patología de la madre',
  'family_background.mother.unknown': 'Madre sin datos',
  'family_background.father.status': 'Estado del padre',
  'family_background.father.age': 'Edad del padre',
  'family_background.father.pathology': 'Patología del padre',
  'family_background.father.unknown': 'Padre sin datos',
  'family_background.grandmother_maternal.status': 'Estado abuela materna',
  'family_background.grandmother_maternal.age': 'Edad abuela materna',
  'family_background.grandmother_maternal.pathology': 'Patología abuela materna',
  'family_background.grandmother_maternal.unknown': 'Abuela materna sin datos',
  'family_background.grandfather_maternal.status': 'Estado abuelo materno',
  'family_background.grandfather_maternal.age': 'Edad abuelo materno',
  'family_background.grandfather_maternal.pathology': 'Patología abuelo materno',
  'family_background.grandfather_maternal.unknown': 'Abuelo materno sin datos',
  'family_background.grandmother_paternal.status': 'Estado abuela paterna',
  'family_background.grandmother_paternal.age': 'Edad abuela paterna',
  'family_background.grandmother_paternal.pathology': 'Patología abuela paterna',
  'family_background.grandmother_paternal.unknown': 'Abuela paterna sin datos',
  'family_background.grandfather_paternal.status': 'Estado abuelo paterno',
  'family_background.grandfather_paternal.age': 'Edad abuelo paterno',
  'family_background.grandfather_paternal.pathology': 'Patología abuelo paterno',
  'family_background.grandfather_paternal.unknown': 'Abuelo paterno sin datos',
  'family_background.siblings.unknown': 'Hermanos sin datos',
  'family_background.siblings.quantity': 'Cantidad de hermanos',
  'family_background.siblings.female_count': 'Cantidad de hermanas',
  'family_background.siblings.male_count': 'Cantidad de hermanos',
  'family_background.siblings.pathology': 'Patología de hermanos',
  'family_background.children.unknown': 'Hijos sin datos',
  'family_background.children.quantity': 'Cantidad de hijos',
  'family_background.children.female_count': 'Cantidad de hijas',
  'family_background.children.male_count': 'Cantidad de hijos',
  'family_background.children.pathology': 'Patología de hijos',

  // Hábitos - Alcohol
  'habits.alcohol.deny': '¿Consume alcohol?',
  'habits.alcohol.start_age': 'Edad de inicio',
  'habits.alcohol.end_age': 'Edad de abandono',
  'habits.alcohol.type': 'Tipo de bebida',
  'habits.alcohol.quantity_ml': 'Cantidad (ml)',
  'habits.alcohol.frequency_days': 'Frecuencia',
  'habits.alcohol.gets_drunk': '¿Se embriaga?',
  // Tabaco
  'habits.tobacco.deny': '¿Consume tabaco?',
  'habits.tobacco.start_age': 'Edad de inicio',
  'habits.tobacco.end_age': 'Edad de abandono',
  'habits.tobacco.cigarettes_per_day': 'Cigarrillos/día',
  'habits.tobacco.boxes_per_year': 'Cajetillas/año',
  // Café
  'habits.coffee.deny': '¿Consume café?',
  'habits.coffee.start_age': 'Edad de inicio',
  'habits.coffee.end_age': 'Edad de abandono',
  'habits.coffee.quantity_ml': 'Cantidad (ml)',
  'habits.coffee.type': 'Tipo de café',
  // Drogas
  'habits.drugs.deny': '¿Consume drogas?',
  'habits.drugs.start_age': 'Edad de inicio',
  'habits.drugs.end_age': 'Edad de abandono',
  'habits.drugs.route': 'Vía de administración',
  'habits.drugs.frequency_per_day': 'Frecuencia diaria',
  // Actividad física
  'habits.physical_activity.type': 'Tipo de actividad',
  'habits.physical_activity.times_per_week': 'Veces por semana',
  'habits.physical_activity.minutes_per_day': 'Minutos por día',
  // Nutrición
  'habits.nutrition.type': 'Tipo de alimentación',
  'habits.nutrition.predominance_description': 'Descripción del predominio',
  'habits.nutrition.meals_count': 'Comidas al día',
  'habits.nutrition.appetite': 'Apetito',
  // Sueño
  'habits.sleep.type': 'Patrón de sueño',
  'habits.sleep.frequency_per_day': 'Frecuencia diaria',
  'habits.sleep.hours': 'Horas de sueño',
  'habits.sleep.interrupted': '¿Sueño interrumpido?',
  'habits.sleep.medication': 'Medicación',
  'habits.sleep.siesta_duration_min': 'Duración siesta (min)',
  'habits.sleep.siesta_frequency_per_day': 'Siestas por día',
  // Vida sexual
  'habits.sexual_habits.active': '¿Vida sexual activa?',
  'habits.sexual_habits.sexarche_age': 'Edad de sexarquía',
  'habits.sexual_habits.partners_count': 'N° parejas sexuales',
  'habits.sexual_habits.orientation': 'Orientación sexual',
  'habits.sexual_habits.frequency_per_week': 'Frecuencia semanal',
  'habits.sexual_habits.contraceptive_method': 'Método anticonceptivo',
  // Vivienda
  'habits.housing.floor_material': 'Material del piso',
  'habits.housing.roof_material': 'Material del techo',
  'habits.housing.walls_material': 'Material de paredes',
  'habits.housing.rooms_count': 'N° habitaciones',
  'habits.housing.habitants_count': 'N° habitantes',
  'habits.housing.animals': 'Animales',
  'habits.housing.services.water': 'Agua potable',
  'habits.housing.services.electricity': 'Electricidad',
  'habits.housing.services.gas': 'Gas',
  'habits.housing.services.waste_collection': 'Recolección de basura',
}

// ────────────────────────────────────────────────────────────
// CONFIRMACIÓN AL SALIR CON CAMBIOS SIN GUARDAR
// ────────────────────────────────────────────────────────────
const hasUnsavedChanges = computed(() => form.isDirty)

const confirmExit = () => {
  if (hasUnsavedChanges.value && !form.processing) {
    if (confirm('¿Desea salir sin guardar los cambios?')) {
      router.visit(route('patients.index'))
    }
  } else {
    router.visit(route('patients.index'))
  }
}

watch(() => form.gender, (gender) => {
  if (gender === 'M') {
    form.background.gynecological.not_apply = true
  } else if (gender === 'F') {
    form.background.gynecological.not_apply = false
  }
})

// ── Edad calculada desde fecha de nacimiento ─────────────────
const calculatedAge = computed(() => {
  if (!form.birth_date) return null
  const parts = form.birth_date.split('-')
  if (parts.length !== 3) return null
  const bd = new Date(+parts[0], +parts[1] - 1, +parts[2])
  const now = new Date()
  let age = now.getFullYear() - bd.getFullYear()
  if (now.getMonth() < bd.getMonth() || (now.getMonth() === bd.getMonth() && now.getDate() < bd.getDate())) age--
  return age
})

// ── Calculadora de hacinamiento (habitantes ÷ habitaciones ≥ 2) ──
const hacinamiento = computed(() => {
  const result = overcrowdingIndex(
    form.habits.housing.rooms_count,
    form.habits.housing.habitants_count
  )
  if (result.index == null) {
    return { value: '—', idx: 0, overloaded: false, formula: '' }
  }
  return {
    value: result.display,
    idx: result.index,
    formula: result.formula,
    overloaded: result.overloaded,
  }
})

const occupationIsOther = computed(() => {
  const occ = props.occupations.find((o) => o.id === form.occupation_id)
  return occ?.name?.toLowerCase() === 'otro'
})

const religionIsOther = computed(() => {
  const rel = props.religions.find((r) => r.id === form.religion_id)
  return rel?.name?.toLowerCase() === 'otro'
})

// ── Progreso de completitud (campos clave + avance por apartados) ──
const fieldProgress = computed(() => {
  let filled = 0
  const total = 6
  if (form.full_name)         filled++
  if (form.id_number)         filled++
  if (form.birth_date)        filled++
  if (form.gender)            filled++
  if (form.addr_state)        filled++
  if (form.marital_status_id) filled++
  return Math.round((filled / total) * 100)
})

const progress = computed(() => Math.max(fieldProgress.value, stepProgress.value))

// ── Toggle antecedente niega ─────────────────────────────────
const toggleDeny = (section, val) => {
  form.background[section].deny = val
}
const toggleHabit = (habit, val) => {
  form.habits[habit].deny = val
}
const toggleGynecological = (val) => { form.background.gynecological.not_apply = !val }
const toggleSexualActive = (val) => { form.habits.sexual_habits.active = val }

const filterIdNumber = (e) => {
  const val = e.target.value.replace(/[^0-9.]/g, '')
  e.target.value = val
  form.id_number = val
}
const filterDigits = (e) => {
  const val = e.target.value.replace(/\D/g, '')
  e.target.value = val
  form.addr_zip_code = val
}
const disabilityTypes = Object.keys(DISABILITY_TYPE_MAP)
const toggleDisabilityType = (type) => {
  const idx = form.background.disability.types.indexOf(type)
  if (idx === -1) form.background.disability.types.push(type)
  else            form.background.disability.types.splice(idx, 1)
}

// ── Familiares config ────────────────────────────────────────
const familyMembers = [
  { key: 'grandmother_maternal', label: '👵 Abuela Materna',  statusF: true },
  { key: 'grandfather_maternal', label: '👴 Abuelo Materno',  statusF: false },
  { key: 'grandmother_paternal', label: '👵 Abuela Paterna',  statusF: true },
  { key: 'grandfather_paternal', label: '👴 Abuelo Paterno',  statusF: false },
  { key: 'mother',               label: '👩 Madre',           statusF: true },
  { key: 'father',               label: '👨 Padre',           statusF: false },
]

// ── Manejadores de antecedentes familiares ──────────────────
const handleFamilyMemberStatus = (key, value) => {
  if (value === '') {
    // Se seleccionó "Desconoce"
    form.family_background[key].unknown = true
    form.family_background[key].age = ''
    form.family_background[key].pathology = ''
  } else {
    // Se seleccionó un estado específico
    form.family_background[key].unknown = false
  }
}

const handleSiblingsChildrenStatus = (type, value) => {
  if (value === '') {
    form.family_background[type].not_apply = true
    form.family_background[type].female_count = ''
    form.family_background[type].male_count = ''
    form.family_background[type].status = ''
    form.family_background[type].pathology = ''
  } else {
    form.family_background[type].not_apply = false
    if (!form.family_background[type].female_count) {
      form.family_background[type].female_count = 0
    }
    if (!form.family_background[type].male_count) {
      form.family_background[type].male_count = 0
    }
  }
}

let savedScrollPosition = 0

const saveScrollPosition = () => {
  savedScrollPosition = window.scrollY
}

const restoreScrollPosition = () => {
  requestAnimationFrame(() => {
    window.scrollTo({ top: savedScrollPosition, behavior: 'instant' })
  })
}

const submit = () => {
  console.log('=== INICIANDO ENVÍO DE FORMULARIO ===')
  saveScrollPosition()

  const formData = {
      full_name: form.full_name,
      id_number: form.id_number,
      nationality: form.nationality,
      nationality_country: form.nationality_country,
      gender: form.gender,
      birth_date: form.birth_date,
      birth_place: form.birth_place,
      marital_status_id: form.marital_status_id,
      ethnicity_id: form.ethnicity_id,
      instruction_level_id: form.instruction_level_id,
      occupation_id: form.occupation_id,
      occupation_detail: form.occupation_detail,
      religion_id: form.religion_id,
      religion_detail: form.religion_detail,
      knows_blood_type: form.knows_blood_type,
      blood_type: form.blood_type,
      rh_factor: form.rh_factor,
      phone_number: form.phone_number,
      addr_state: form.addr_state,
      addr_municipality: form.addr_municipality,
      addr_parish: form.addr_parish,
      addr_locality: form.addr_locality,
      addr_sector: form.addr_sector,
      addr_street: form.addr_street,
      addr_house_number: form.addr_house_number,
      addr_zip_code: form.addr_zip_code,
      addr_reference: form.addr_reference,
      residence_time: form.residence_time,
      background: form.background,
      family_background: form.family_background,
      habits: form.habits,
      close_history: form.close_history,
    }

  const payload = buildPatientStorePayload(formData)

  const submitMethod = isEdit.value ? 'put' : 'post'
  const submitRoute = isEdit.value
    ? route('patients.update', props.patient.id)
    : route('patients.store')

  form.transform(() => payload)[submitMethod](submitRoute, {
    preserveScroll: true,
    onSuccess: () => {
      console.log('✅ PACIENTE GUARDADO EXITOSAMENTE')
    },
    onError: (errors) => {
      console.error('❌ Error de validación:', errors)
      restoreScrollPosition()
      requestAnimationFrame(() => {
        document.querySelector('.validation-errors')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
      })
    },
    onFinish: () => {
      console.log('🏁 PROCESO FINALIZADO')
    }
  })
}
</script>

<template>
  <Head :title="isEdit ? 'Editar Historia Clínica' : 'Nueva Historia Clínica'" />

  <AppLayout :title="isEdit ? 'Editar Historia Clínica' : 'Nueva Historia Clínica'">

    <!-- ══ PATIENT HEADER CARD ════════════════════════════════ -->
    <div class="patient-header-card">
      <div class="patient-avatar-lg">
        {{ form.full_name ? form.full_name.slice(0,2).toUpperCase() : 'HC' }}
      </div>
      <div style="flex:1">
        <div class="patient-id-chip">{{ isEdit ? 'EDICIÓN' : 'NUEVO REGISTRO' }}</div>
        <div style="font-size:17px;font-weight:700;color:var(--clr-text);margin-bottom:4px">
          {{ form.full_name || 'Nombre del Paciente' }}
        </div>
        <div class="meta-chips">
          <span v-if="calculatedAge !== null" class="meta-chip">
            📅 {{ form.birth_date }} · {{ calculatedAge }} años
          </span>
          <span v-if="form.id_number" class="meta-chip">
            🪪 {{ form.nationality }}-{{ form.id_number }}
          </span>
          <span v-if="form.blood_type && form.knows_blood_type" class="meta-chip">
            💉 {{ form.blood_type }}{{ form.rh_factor }}
          </span>
          <span v-if="form.gender" class="meta-chip">
            {{ form.gender === 'F' ? '♀ Femenino' : form.gender === 'M' ? '♂ Masculino' : '⬡ Otro' }}
          </span>
        </div>
      </div>
      <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px">
        <span class="status-badge badge-draft">
          <span class="badge-dot"></span> En Progreso
        </span>
        <div style="font-size:11px;color:var(--clr-muted)">{{ institution }}</div>
        <div style="font-size:11px;color:var(--clr-muted)">{{ userName }}</div>
      </div>
    </div>

    <!-- ══ ERRORES DE VALIDACIÓN ═══════════════════════════════ -->
    <div v-if="form.hasErrors" class="validation-errors">
      <div class="validation-errors-title">Corrige los siguientes errores antes de guardar:</div>
      <ul class="validation-errors-list">
        <li v-for="(msg, field) in form.errors" :key="field">
          <span class="error-field-label">{{ fieldLabels[field] || field }}:</span>
          {{ msg }}
        </li>
      </ul>
    </div>

    <!-- ══ TABS WRAPPER ════════════════════════════════════════ -->
    <div class="tabs-wrapper">

      <!-- Cabecera de apartados -->
      <div class="tabs-header">
        <button
          v-for="(section, i) in sections"
          :key="i"
          class="tab-btn"
          :class="{ active: currentStep === i }"
          type="button"
          :title="section.label"
          @click="goToStep(i)"
        >
          <span class="tab-step-num">{{ i + 1 }}</span>
          <span class="tab-label-text">{{ section?.short }}</span>
        </button>
      </div>

      <div class="step-banner">
        <div>
          <div class="step-group">{{ currentSection.group }}</div>
          <div class="step-title">{{ currentSection.label }}</div>
        </div>
        <div class="step-counter">Apartado {{ currentStep + 1 }} / {{ sections.length }}</div>
      </div>

      <form @submit.prevent="submit">
      <div class="tab-content">

        <!-- 1. Ficha Patronímica -->
        <div v-show="currentStep === 0" class="form-section">
          <div class="section-title">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
              <rect x="2" y="3" width="20" height="14" rx="2"/>
              <path d="M8 21h8M12 17v4"/>
            </svg>
            Ficha Patronímica
          </div>

          <div class="form-grid grid-3">
            <div class="col-2">
              <label class="field-label">Nombre Completo <span class="req">*</span></label>
              <input v-model="form.full_name" class="field-input" type="text" placeholder="Apellidos y Nombres completos" />
              <p v-if="form.errors.full_name" class="field-error">{{ form.errors.full_name }}</p>
            </div>
            <div>
              <label class="field-label">Cédula de Identidad <span class="req">*</span></label>
              <div style="display:flex;gap:6px">
                <select v-model="form.nationality" class="field-select" style="width:70px;flex-shrink:0">
                  <option value="V">V</option>
                  <option value="E">E</option>
                </select>
                <input :value="form.id_number" class="field-input" type="text" placeholder="00.000.000" style="font-family:'DM Mono',monospace" @input="filterIdNumber"/>
              </div>
              <p v-if="form.errors.id_number" class="field-error">{{ form.errors.id_number }}</p>
            </div>
            <div>
              <label class="field-label">Fecha de Nacimiento <span class="req">*</span></label>
              <DatePicker v-model="form.birth_date" class="field-input" />
              <p v-if="form.errors.birth_date" class="field-error">{{ form.errors.birth_date }}</p>
            </div>
            <div>
              <label class="field-label">Edad</label>
              <input class="field-input" type="text" :value="calculatedAge !== null ? `${calculatedAge} años` : '—'"
                     readonly style="background:#F8FAFC;cursor:default"/>
            </div>
            <div>
              <label class="field-label">Nacionalidad</label>
              <input v-model="form.nationality_country" class="field-input" type="text" placeholder="Ej: Venezolana" />
            </div>
            <div>
              <label class="field-label">Lugar de Nacimiento</label>
              <input v-model="form.birth_place" class="field-input" type="text" placeholder="Ciudad / Estado / País" />
            </div>
            <div>
              <label class="field-label">Sexo <span class="req">*</span></label>
              <div class="radio-group">
                <label class="radio-option" :class="{ selected: form.gender === 'F' }" @click="form.gender = 'F'">
                  <div class="radio-dot" :class="{ sel: form.gender === 'F' }"><div class="radio-inner"></div></div> Femenino
                </label>
                <label class="radio-option" :class="{ selected: form.gender === 'M' }" @click="form.gender = 'M'">
                  <div class="radio-dot" :class="{ sel: form.gender === 'M' }"><div class="radio-inner"></div></div> Masculino
                </label>
              </div>
              <p v-if="form.errors.gender" class="field-error">{{ form.errors.gender }}</p>
            </div>
            <div>
              <label class="field-label">Estado Civil</label>
              <select v-model="form.marital_status_id" class="field-select">
                <option value="">Seleccionar…</option>
                <option v-for="m in maritalStatuses" :key="m.id" :value="m.id">{{ m.name }}</option>
              </select>
            </div>
            <div>
              <label class="field-label">Grado de Instrucción</label>
              <select v-model="form.instruction_level_id" class="field-select">
                <option value="">Seleccionar…</option>
                <option v-for="l in instructionLevels" :key="l.id" :value="l.id">
                  {{ l.code }}. {{ l.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="field-label">Profesión u Oficio</label>
              <select v-model="form.occupation_id" class="field-select">
                <option value="">Seleccionar…</option>
                <option v-for="o in occupations" :key="o.id" :value="o.id">{{ o.name }}</option>
              </select>
              <input v-if="occupationIsOther" v-model="form.occupation_detail" class="field-input" type="text"
                     style="margin-top:6px" placeholder="Especifique profesión u oficio"/>
            </div>
            <div>
              <label class="field-label">Religión</label>
              <select v-model="form.religion_id" class="field-select">
                <option value="">Seleccionar…</option>
                <option v-for="r in religions" :key="r.id" :value="r.id">{{ r.name }}</option>
              </select>
              <input v-if="religionIsOther" v-model="form.religion_detail" class="field-input" type="text"
                     style="margin-top:6px" placeholder="Especifique religión o creencia"/>
            </div>
            <div>
              <label class="field-label">Teléfono de Contacto</label>
              <input v-model="form.phone_number" class="field-input" type="tel" placeholder="0414-000-0000"
                     style="font-family:'DM Mono',monospace"/>
            </div>
          </div>
        </div>
        <nav v-show="false" class="section-nav">
          <span class="section-nav-hint">Fin de «Ficha Patronímica»</span>
          <button v-if="!isLastStep" type="button" class="btn btn-primary-nav" @click="nextStep">
            Siguiente: {{ sections[1]?.short }}
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="9,18 15,12 9,6"/></svg>
          </button>
        </nav>

        <!-- 2. Etnia -->
        <div v-show="currentStep === 0" class="form-section">
          <div class="section-title">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
              <circle cx="12" cy="12" r="10"/>
              <line x1="2" y1="12" x2="22" y2="12"/>
              <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
            </svg>
            Etnia / Pueblo Originario
          </div>
          <div class="checkbox-group">
            <label
              v-for="e in ethnicities" :key="e.id"
              class="checkbox-option"
              :class="{ checked: form.ethnicity_id === e.id }"
              @click="form.ethnicity_id = (form.ethnicity_id === e.id ? '' : e.id)"
            >
              <div class="check-box">
                <svg v-if="form.ethnicity_id === e.id" width="9" height="9" fill="none" stroke="#fff" stroke-width="3" viewBox="0 0 24 24">
                  <polyline points="20,6 9,17 4,12"/>
                </svg>
              </div>
              {{ e.code }}. {{ e.name }}
            </label>
          </div>
          <p v-if="form.errors.ethnicity_id" class="field-error">{{ form.errors.ethnicity_id }}</p>
        </div>
        <nav v-show="false" class="section-nav">
          <button type="button" class="btn btn-ghost-nav" @click="prevStep">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="15,18 9,12 15,6"/></svg>
            Anterior
          </button>
          <button v-if="!isLastStep" type="button" class="btn btn-primary-nav" @click="nextStep">
            Siguiente: {{ sections[2]?.short }}
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="9,18 15,12 9,6"/></svg>
          </button>
        </nav>

        <!-- 3. Grupo Sanguíneo -->
        <div v-show="currentStep === 0" class="form-section">
          <div class="section-title">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
              <path d="M12 2a8 8 0 0 0-8 8c0 5.52 8 12 8 12s8-6.48 8-12a8 8 0 0 0-8-8z"/>
            </svg>
            Grupo Sanguíneo
          </div>
          <div style="margin-bottom:12px">
            <label class="checkbox-option" :class="{ checked: !form.knows_blood_type }" @click="form.knows_blood_type = !form.knows_blood_type" style="width:fit-content">
              <div class="check-box">
                <svg v-if="!form.knows_blood_type" width="9" height="9" fill="none" stroke="#fff" stroke-width="3" viewBox="0 0 24 24"><polyline points="20,6 9,17 4,12"/></svg>
              </div>
              Paciente desconoce su grupo sanguíneo
            </label>
          </div>
          <div v-if="form.knows_blood_type" class="form-grid grid-3">
            <div>
              <label class="field-label">Tipología ABO</label>
              <div class="radio-group" style="margin-top:4px">
                <label v-for="bt in ['A','B','AB','O']" :key="bt"
                       class="radio-option" :class="{ selected: form.blood_type === bt }"
                       @click="form.blood_type = bt">
                  <div class="radio-dot" :class="{ sel: form.blood_type === bt }"><div class="radio-inner"></div></div>
                  {{ bt }}
                </label>
              </div>
            </div>
            <div>
              <label class="field-label">Factor Rh</label>
              <div class="radio-group" style="margin-top:4px">
                <label class="radio-option" :class="{ selected: form.rh_factor === '+' }" @click="form.rh_factor = '+'">
                  <div class="radio-dot" :class="{ sel: form.rh_factor === '+' }"><div class="radio-inner"></div></div> Positivo (+)
                </label>
                <label class="radio-option" :class="{ selected: form.rh_factor === '-' }" @click="form.rh_factor = '-'">
                  <div class="radio-dot" :class="{ sel: form.rh_factor === '-' }"><div class="radio-inner"></div></div> Negativo (−)
                </label>
              </div>
            </div>
          </div>
        </div>
        <nav v-show="false" class="section-nav">
          <button type="button" class="btn btn-ghost-nav" @click="prevStep">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="15,18 9,12 15,6"/></svg>
            Anterior
          </button>
          <button v-if="!isLastStep" type="button" class="btn btn-primary-nav" @click="nextStep">
            Siguiente: {{ sections[3]?.short }}
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="9,18 15,12 9,6"/></svg>
          </button>
        </nav>

        <!-- 4. Dirección Actual -->
        <div v-show="currentStep === 0" class="form-section">
          <div class="section-title">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
            Dirección Actual
          </div>
          <div class="form-grid grid-4">
            <div><label class="field-label">Estado</label>
              <input v-model="form.addr_state" class="field-input" type="text" placeholder="Anzoátegui"/></div>
            <div><label class="field-label">Municipio</label>
              <input v-model="form.addr_municipality" class="field-input" type="text" placeholder="Guanta"/></div>
            <div><label class="field-label">Parroquia</label>
              <input v-model="form.addr_parish" class="field-input" type="text"/></div>
            <div><label class="field-label">Localidad</label>
              <input v-model="form.addr_locality" class="field-input" type="text"/></div>
            <div class="col-2"><label class="field-label">Urbanización / Sector</label>
              <input v-model="form.addr_sector" class="field-input" type="text" placeholder="El Chaparro"/></div>
            <div class="col-2"><label class="field-label">Av. / Calle</label>
              <input v-model="form.addr_street" class="field-input" type="text"/></div>
            <div><label class="field-label">N° Casa / Apto.</label>
              <input v-model="form.addr_house_number" class="field-input" type="text"/></div>
            <div><label class="field-label">Código Postal</label>
              <input :value="form.addr_zip_code" class="field-input" type="text" style="font-family:'DM Mono',monospace" @input="filterDigits"/></div>
            <div><label class="field-label">Tiempo de Residencia</label>
              <input v-model="form.residence_time" class="field-input" type="text" placeholder="Ej: 5 años"/></div>
            <div><label class="field-label">Punto de Referencia</label>
              <input v-model="form.addr_reference" class="field-input" type="text" placeholder="Frente a…"/></div>
          </div>
        </div>
        <nav v-show="false" class="section-nav">
          <button type="button" class="btn btn-ghost-nav" @click="prevStep">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="15,18 9,12 15,6"/></svg>
            Anterior
          </button>
          <button v-if="!isLastStep" type="button" class="btn btn-primary-nav" @click="nextStep">
            Siguiente: {{ sections[4]?.short }}
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="9,18 15,12 9,6"/></svg>
          </button>
        </nav>

        <!-- 5. Condiciones de Vivienda -->
        <div v-show="currentStep === 0" class="form-section">
          <div class="section-title">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              <polyline points="9,22 9,12 15,12 15,22"/>
            </svg>
            Condiciones de la Vivienda
          </div>
          <div class="form-grid grid-4">
            <div>
              <label class="field-label">Material del Piso</label>
              <select v-model="form.habits.housing.floor_material" class="field-select">
                <option value="">Seleccionar…</option>
                <option>Cemento</option><option>Cerámica</option>
                <option>Tierra</option><option>Madera</option><option>Mosaico</option>
              </select>
            </div>
            <div>
              <label class="field-label">Material del Techo</label>
              <select v-model="form.habits.housing.roof_material" class="field-select">
                <option value="">Seleccionar…</option>
                <option>Platabanda</option><option>Zinc</option>
                <option>Teja</option><option>Madera</option><option>Paja</option>
              </select>
            </div>
            <div>
              <label class="field-label">Material de Paredes</label>
              <select v-model="form.habits.housing.walls_material" class="field-select">
                <option value="">Seleccionar…</option>
                <option>Bloque/Ladrillo</option><option>Adobe</option>
                <option>Madera</option><option>Zinc</option>
              </select>
            </div>
            <div>
              <label class="field-label">N° Animales en Hogar</label>
              <input v-model="form.habits.housing.animals.quantity" class="field-input" type="number" min="0" placeholder="0"/>
            </div>
          </div>

          <!-- Servicios básicos -->
          <div style="margin-top:14px">
            <label class="field-label">Servicios Básicos Disponibles</label>
            <div class="checkbox-group" style="margin-top:6px">
              <label v-for="(srv, key) in { water:'💧 Agua', electricity:'⚡ Electricidad', gas:'🔥 Gas', waste_collection:'🗑️ Aseo Urbano' }"
                     :key="key" class="checkbox-option" :class="{ checked: form.habits.housing.services[key] }"
                     @click="form.habits.housing.services[key] = !form.habits.housing.services[key]">
                <div class="check-box">
                  <svg v-if="form.habits.housing.services[key]" width="9" height="9" fill="none" stroke="#fff" stroke-width="3" viewBox="0 0 24 24"><polyline points="20,6 9,17 4,12"/></svg>
                </div>
                {{ srv }}
              </label>
            </div>
          </div>

          <!-- Hacinamiento -->
          <div style="margin-top:16px">
            <div class="form-grid grid-4">
              <div>
                <label class="field-label">N° Habitaciones <span class="req">*</span></label>
                <input v-model="form.habits.housing.rooms_count" class="field-input" type="number" min="1" placeholder="3"/>
              </div>
              <div>
                <label class="field-label">N° Habitantes <span class="req">*</span></label>
                <input v-model="form.habits.housing.habitants_count" class="field-input" type="number" min="1" placeholder="5"/>
              </div>
              <div>
                <label class="field-label">Ubicación de animales</label>
                <div class="checkbox-group" style="margin-top:4px;flex-direction:column;gap:4px">
                  <label class="checkbox-option" :class="{ checked: form.habits.housing.animals.intradomiciliary }"
                         @click="form.habits.housing.animals.intradomiciliary = !form.habits.housing.animals.intradomiciliary">
                    <div class="check-box">
                      <svg v-if="form.habits.housing.animals.intradomiciliary" width="9" height="9" fill="none" stroke="#fff" stroke-width="3" viewBox="0 0 24 24"><polyline points="20,6 9,17 4,12"/></svg>
                    </div>
                    Intradomiciliario
                  </label>
                  <label class="checkbox-option" :class="{ checked: form.habits.housing.animals.extradomiciliary }"
                         @click="form.habits.housing.animals.extradomiciliary = !form.habits.housing.animals.extradomiciliary">
                    <div class="check-box">
                      <svg v-if="form.habits.housing.animals.extradomiciliary" width="9" height="9" fill="none" stroke="#fff" stroke-width="3" viewBox="0 0 24 24"><polyline points="20,6 9,17 4,12"/></svg>
                    </div>
                    Extradomiciliario
                  </label>
                </div>
              </div>

              <!-- Calculadora de hacinamiento -->
              <div class="hacin-display"
                   :style="hacinamiento.overloaded ? 'background:linear-gradient(135deg,#FEF2F2,#FEE2E2);border-color:#FCA5A5' : ''">
                <div class="hacin-value" :class="hacinamiento.overloaded ? 'danger' : 'normal'">
                  {{ hacinamiento.value }}
                </div>
                <div class="hacin-label">Índice de Hacinamiento</div>
                <div class="hacin-status" :class="hacinamiento.overloaded ? 'hacin-warn' : 'hacin-ok'">
                  {{ hacinamiento.overloaded ? '⚠ HACINAMIENTO' : '✓ Sin Hacinamiento' }}
                </div>
                <div style="font-size:10px;color:var(--clr-muted);margin-top:4px">
                  {{ hacinamiento.formula }}
                </div>
              </div>
            </div>

            <div class="inline-alert alert-info" style="margin-top:12px">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
              <span>Índice = <strong>N° Habitantes ÷ N° Habitaciones</strong>. Hacinamiento cuando el resultado es <strong>≥ 2</strong> (marcado en rojo).</span>
            </div>
          </div>
        </div>
        <nav v-show="currentStep === 0" class="section-nav">
          <button type="button" class="btn btn-ghost-nav" @click="prevStep">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="15,18 9,12 15,6"/></svg>
            Anterior
          </button>
          <button v-if="!isLastStep" type="button" class="btn btn-primary-nav" @click="nextStep">
            Siguiente: {{ sections[1]?.short }}
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="9,18 15,12 9,6"/></svg>
          </button>
        </nav>

        <!-- 6. Antecedentes Personales -->
        <div v-show="currentStep === 1" class="form-section">
          <div class="section-title">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            Antecedentes Personales
          </div>

          <!-- Alérgicos -->
          <div class="ante-card" :class="{ focused: !form.background.allergic.deny }">
            <div class="ante-header">
              <div class="ante-title">🤧 Antecedentes Alérgicos</div>
              <div class="btn-group-si-no">
                <button type="button" :class="{ active: form.background.allergic.deny }" @click="toggleDeny('allergic', true)">NO</button>
                <button type="button" :class="{ active: !form.background.allergic.deny }" @click="toggleDeny('allergic', false)">SÍ</button>
              </div>
            </div>
            <Transition name="expand">
              <div v-if="!form.background.allergic.deny" class="ante-fields">
                <div><label class="field-label">Alergias conocidas</label>
                  <input v-model="form.background.allergic.description" class="field-input" type="text"
                         placeholder="Ej: Penicilina, Polen, Látex, Mariscos…"/></div>
              </div>
            </Transition>
          </div>

          <!-- Patológicos -->
          <div class="ante-card" :class="{ focused: !form.background.pathological.deny }">
            <div class="ante-header">
              <div class="ante-title">🏥 Antecedentes Patológicos</div>
              <div class="btn-group-si-no">
                <button type="button" :class="{ active: form.background.pathological.deny }" @click="toggleDeny('pathological', true)">NO</button>
                <button type="button" :class="{ active: !form.background.pathological.deny }" @click="toggleDeny('pathological', false)">SÍ</button>
              </div>
            </div>
            <Transition name="expand">
              <div v-if="!form.background.pathological.deny" class="ante-fields form-grid grid-3">
                <div><label class="field-label">Enfermedad crónica</label>
                  <input v-model="form.background.pathological.disease" class="field-input" type="text" placeholder="Ej: HTA, DM Tipo 2…"/></div>
                <div><label class="field-label">Desde</label>
                  <input v-model="form.background.pathological.since" class="field-input" type="text" placeholder="Ej: 3 años"/></div>
                <div><label class="field-label">Estado</label>
                  <select v-model="form.background.pathological.state" class="field-select">
                    <option>Controlado</option><option>No Controlado</option>
                  </select>
                </div>
                <div class="col-full"><label class="field-label">Tratamiento actual</label>
                  <input v-model="form.background.pathological.treatment" class="field-input" type="text" placeholder="Medicamentos y dosis"/></div>
              </div>
            </Transition>
          </div>

          <!-- Infectocontagiosos -->
          <div class="ante-card" :class="{ focused: !form.background.infectious.deny }">
            <div class="ante-header">
              <div class="ante-title">🦠 Antecedentes Infectocontagiosos</div>
              <div class="btn-group-si-no">
                <button type="button" :class="{ active: form.background.infectious.deny }" @click="toggleDeny('infectious', true)">NO</button>
                <button type="button" :class="{ active: !form.background.infectious.deny }" @click="toggleDeny('infectious', false)">SÍ</button>
              </div>
            </div>
            <Transition name="expand">
              <div v-if="!form.background.infectious.deny" class="ante-fields form-grid grid-2">
                <div><label class="field-label">Infección</label>
                  <input v-model="form.background.infectious.infection" class="field-input" type="text" placeholder="Ej: Tuberculosis, VIH…"/></div>
                <div><label class="field-label">Edad al momento</label>
                  <input v-model="form.background.infectious.age" class="field-input" type="text" placeholder="Años"/></div>
                <div><label class="field-label">Tratamiento</label>
                  <input v-model="form.background.infectious.treatment" class="field-input" type="text" placeholder="Fármacos utilizados"/></div>
                <div><label class="field-label">Hospitalización</label>
                  <div class="radio-group" style="margin-top:4px">
                    <label v-for="h in ['Con hospitalización','Sin hospitalización']" :key="h"
                           class="radio-option" :class="{ selected: form.background.infectious.hospitalization === h }"
                           @click="form.background.infectious.hospitalization = h">
                      <div class="radio-dot" :class="{ sel: form.background.infectious.hospitalization === h }"><div class="radio-inner"></div></div>
                      {{ h === 'Con hospitalización' ? 'Con hosp.' : 'Sin hosp.' }}
                    </label>
                  </div>
                </div>
                <div class="col-full"><label class="field-label">Complicaciones</label>
                  <input v-model="form.background.infectious.complications" class="field-input" type="text" placeholder="Describir complicaciones si las hubo"/></div>
              </div>
            </Transition>
          </div>

          <!-- Inmunológicos -->
          <div class="ante-card" :class="{ focused: form.background.immunological.childhood_status !== 'Niega' }">
            <div class="ante-header">
              <div class="ante-title">💉 Antecedentes Inmunológicos (Vacunación)</div>
            </div>
            <div class="ante-fields">
              <div>
                <label class="field-label">Esquema de vacunación</label>
                <div class="radio-group" style="margin-top:4px;flex-wrap:wrap">
                  <label v-for="s in ['Niega','Completa','Incompleta']" :key="s"
                         class="radio-option" :class="{ selected: form.background.immunological.childhood_status === s }"
                         @click="form.background.immunological.childhood_status = s">
                    <div class="radio-dot" :class="{ sel: form.background.immunological.childhood_status === s }"><div class="radio-inner"></div></div>
                    {{ s === 'Niega' ? 'Niega vacunación' : `Vacunación infantil ${s.toLowerCase()}` }}
                  </label>
                </div>
              </div>
              <div v-if="form.background.immunological.childhood_status === 'Incompleta'" class="form-grid grid-2" style="margin-top:10px">
                <div><label class="field-label">Vacunas ausentes</label>
                  <input v-model="form.background.immunological.missing_vaccines" class="field-input" type="text" placeholder="Ej: Sarampión, Polio…"/></div>
              </div>
              <div v-if="form.background.immunological.childhood_status !== 'Niega'" class="form-grid grid-3" style="margin-top:10px">
                <div><label class="field-label">Vacunas del adulto</label>
                  <input v-model="form.background.immunological.adult_vaccines" class="field-input" type="text" placeholder="Ej: Influenza, COVID-19…"/></div>
                <div><label class="field-label">Edad al aplicarlas</label>
                  <input v-model="form.background.immunological.adult_age" class="field-input" type="number" placeholder="Años"/></div>
                <div><label class="field-label">Complicaciones</label>
                  <input v-model="form.background.immunological.complications" class="field-input" type="text" placeholder="Si las hubo"/></div>
              </div>
            </div>
          </div>

          <!-- Transfusionales -->
          <div class="ante-card" :class="{ focused: !form.background.transfusion.deny }">
            <div class="ante-header">
              <div class="ante-title">🩸 Antecedentes Transfusionales</div>
              <div class="btn-group-si-no">
                <button type="button" :class="{ active: form.background.transfusion.deny }" @click="toggleDeny('transfusion', true)">NO</button>
                <button type="button" :class="{ active: !form.background.transfusion.deny }" @click="toggleDeny('transfusion', false)">SÍ</button>
              </div>
            </div>
            <Transition name="expand">
              <div v-if="!form.background.transfusion.deny" class="ante-fields form-grid grid-3">
                <div><label class="field-label">Edad</label>
                  <input v-model="form.background.transfusion.age" class="field-input" type="text" placeholder="Años"/></div>
                <div><label class="field-label">Tipo de sangre transfundida</label>
                  <input v-model="form.background.transfusion.blood_type_used" class="field-input" type="text" placeholder="Ej: A+"/></div>
                <div><label class="field-label">N° de bolsas</label>
                  <input v-model="form.background.transfusion.bags_count" class="field-input" type="number" placeholder="0"/></div>
                <div class="col-full"><label class="field-label">Motivo</label>
                  <input v-model="form.background.transfusion.reason" class="field-input" type="text" placeholder="Razón de la transfusión"/></div>
              </div>
            </Transition>
          </div>

          <!-- Gineco-Obstétricos -->
          <div class="ante-card" :class="{ focused: !form.background.gynecological.not_apply }">
            <div class="ante-header">
              <div class="ante-title">🤰 Antecedentes Gineco-Obstétricos</div>
              <div class="btn-group-si-no">
                <button type="button" :class="{ active: form.background.gynecological.not_apply }" @click="toggleGynecological(false)">NO</button>
                <button type="button" :class="{ active: !form.background.gynecological.not_apply }" @click="toggleGynecological(true)">SÍ</button>
              </div>
            </div>
            <Transition name="expand">
              <div v-if="!form.background.gynecological.not_apply" class="ante-fields form-grid grid-4">
                <div><label class="field-label">Gestas (G)</label>
                  <input v-model="form.background.gynecological.gestas" class="field-input" type="text" placeholder="III"/></div>
                <div><label class="field-label">Partos (P)</label>
                  <input v-model="form.background.gynecological.deliveries" class="field-input" type="text" placeholder="II"/></div>
                <div><label class="field-label">Cesáreas (C)</label>
                  <input v-model="form.background.gynecological.cesareans" class="field-input" type="text" placeholder="I"/></div>
                <div><label class="field-label">Abortos (A)</label>
                  <input v-model="form.background.gynecological.abortions" class="field-input" type="text" placeholder="0"/></div>
                <div><label class="field-label">Menarquía</label>
                  <input v-model="form.background.gynecological.menarche" class="field-input" type="text" placeholder="Ej: 12 años"/></div>
                <div><label class="field-label">Menopausia</label>
                  <input v-model="form.background.gynecological.menopause" class="field-input" type="text" placeholder="Ej: 50 años / N/A"/></div>
                <div><label class="field-label">Periodicidad ciclo</label>
                  <input v-model="form.background.gynecological.cycle_period" class="field-input" type="text" placeholder="Ej: 28 días"/></div>
                <div><label class="field-label">Duración ciclo</label>
                  <input v-model="form.background.gynecological.cycle_duration" class="field-input" type="text" placeholder="Ej: 5 días"/></div>
                <div><label class="field-label">Toallas/día</label>
                  <input v-model="form.background.gynecological.pads_per_day" class="field-input" type="number" placeholder="3"/></div>
                <div><label class="field-label">Fecha Última Regla</label>
                  <DatePicker v-model="form.background.gynecological.last_period" class="field-input" /></div>
              </div>
            </Transition>
          </div>

          <!-- Quirúrgicos -->
          <div class="ante-card" :class="{ focused: !form.background.surgical.deny }">
            <div class="ante-header">
              <div class="ante-title">🔪 Antecedentes Quirúrgicos</div>
              <div class="btn-group-si-no">
                <button type="button" :class="{ active: form.background.surgical.deny }" @click="toggleDeny('surgical', true)">NO</button>
                <button type="button" :class="{ active: !form.background.surgical.deny }" @click="toggleDeny('surgical', false)">SÍ</button>
              </div>
            </div>
            <Transition name="expand">
              <div v-if="!form.background.surgical.deny" class="ante-fields form-grid grid-2">
                <div><label class="field-label">Intervención quirúrgica</label>
                  <input v-model="form.background.surgical.surgery" class="field-input" type="text" placeholder="Ej: Colecistectomía"/></div>
                <div><label class="field-label">Edad al momento</label>
                  <input v-model="form.background.surgical.age" class="field-input" type="text" placeholder="Años"/></div>
                <div class="col-full"><label class="field-label">Complicaciones</label>
                  <input v-model="form.background.surgical.complications" class="field-input" type="text" placeholder="Describir si las hubo"/></div>
              </div>
            </Transition>
          </div>

          <!-- Traumáticos -->
          <div class="ante-card" :class="{ focused: !form.background.traumatic.deny }">
            <div class="ante-header">
              <div class="ante-title">🦴 Antecedentes Traumáticos</div>
              <div class="btn-group-si-no">
                <button type="button" :class="{ active: form.background.traumatic.deny }" @click="toggleDeny('traumatic', true)">NO</button>
                <button type="button" :class="{ active: !form.background.traumatic.deny }" @click="toggleDeny('traumatic', false)">SÍ</button>
              </div>
            </div>
            <Transition name="expand">
              <div v-if="!form.background.traumatic.deny" class="ante-fields form-grid grid-3">
                <div><label class="field-label">Fractura / Lesión</label>
                  <input v-model="form.background.traumatic.injury" class="field-input" type="text" placeholder="Ej: Fractura de radio"/></div>
                <div><label class="field-label">Edad</label>
                  <input v-model="form.background.traumatic.age" class="field-input" type="text" placeholder="Años"/></div>
                <div><label class="field-label">Tratamiento</label>
                  <input v-model="form.background.traumatic.treatment" class="field-input" type="text" placeholder="Ej: Cirugía, yeso"/></div>
                <div class="col-full"><label class="field-label">Complicaciones</label>
                  <input v-model="form.background.traumatic.complications" class="field-input" type="text" placeholder="Describir"/></div>
              </div>
            </Transition>
          </div>

          <!-- ETS -->
          <div class="ante-card" :class="{ focused: !form.background.ets.deny }">
            <div class="ante-header">
              <div class="ante-title">⚕️ Antecedentes de ETS</div>
              <div class="btn-group-si-no">
                <button type="button" :class="{ active: form.background.ets.deny }" @click="toggleDeny('ets', true)">NO</button>
                <button type="button" :class="{ active: !form.background.ets.deny }" @click="toggleDeny('ets', false)">SÍ</button>
              </div>
            </div>
            <Transition name="expand">
              <div v-if="!form.background.ets.deny" class="ante-fields form-grid grid-2">
                <div><label class="field-label">Enfermedad de transmisión sexual</label>
                  <input v-model="form.background.ets.disease" class="field-input" type="text" placeholder="Ej: Sífilis, VPH…"/></div>
                <div><label class="field-label">Edad</label>
                  <input v-model="form.background.ets.age" class="field-input" type="text" placeholder="Años"/></div>
                <div><label class="field-label">Tratamiento</label>
                  <input v-model="form.background.ets.treatment" class="field-input" type="text" placeholder="Fármacos"/></div>
                <div><label class="field-label">Hospitalización</label>
                  <div class="radio-group" style="margin-top:4px">
                    <label v-for="h in ['Con hospitalización','Sin hospitalización']" :key="h"
                           class="radio-option" :class="{ selected: form.background.ets.hospitalization === h }"
                           @click="form.background.ets.hospitalization = h">
                      <div class="radio-dot" :class="{ sel: form.background.ets.hospitalization === h }"><div class="radio-inner"></div></div>
                      {{ h === 'Con hospitalización' ? 'Con' : 'Sin' }}
                    </label>
                  </div>
                </div>
                <div class="col-full"><label class="field-label">Complicaciones</label>
                  <input v-model="form.background.ets.complications" class="field-input" type="text" placeholder="Describir complicaciones"/></div>
              </div>
            </Transition>
          </div>

          <!-- Epidemiológicos (Viaje) -->
          <div class="ante-card" :class="{ focused: !form.background.epidemiological.deny }">
            <div class="ante-header">
              <div class="ante-title">🌍 Antecedentes Epidemiológicos (Viaje)</div>
              <div class="btn-group-si-no">
                <button type="button" :class="{ active: form.background.epidemiological.deny }" @click="toggleDeny('epidemiological', true)">NO</button>
                <button type="button" :class="{ active: !form.background.epidemiological.deny }" @click="toggleDeny('epidemiological', false)">SÍ</button>
              </div>
            </div>
            <Transition name="expand">
              <div v-if="!form.background.epidemiological.deny" class="ante-fields form-grid grid-3">
                <div><label class="field-label">Destino de viaje</label>
                  <input v-model="form.background.epidemiological.destination" class="field-input" type="text" placeholder="País / Ciudad"/></div>
                <div><label class="field-label">Fecha de salida</label>
                  <DatePicker v-model="form.background.epidemiological.departure_date" class="field-input" /></div>
                <div><label class="field-label">Fecha de regreso</label>
                  <DatePicker v-model="form.background.epidemiological.return_date" class="field-input" /></div>
                <div class="col-full"><label class="field-label">Bioma visitado</label>
                  <input v-model="form.background.epidemiological.biome" class="field-input" type="text" placeholder="Ej: Río, playa, selva, montaña…"/></div>
              </div>
            </Transition>
          </div>

          <!-- Discapacidades -->
          <div class="ante-card" :class="{ focused: !form.background.disability.deny }">
            <div class="ante-header">
              <div class="ante-title">♿ Discapacidades</div>
              <div class="btn-group-si-no">
                <button type="button" :class="{ active: form.background.disability.deny }" @click="toggleDeny('disability', true)">NO</button>
                <button type="button" :class="{ active: !form.background.disability.deny }" @click="toggleDeny('disability', false)">SÍ</button>
              </div>
            </div>
            <Transition name="expand">
              <div v-if="!form.background.disability.deny" class="ante-fields">
                <label class="field-label">Tipo de Discapacidad</label>
                <div class="checkbox-group" style="margin-top:6px">
                  <label v-for="type in disabilityTypes" :key="type"
                         class="checkbox-option"
                         :class="{ checked: form.background.disability.types.includes(type) }"
                         @click="toggleDisabilityType(type)">
                    <div class="check-box">
                      <svg v-if="form.background.disability.types.includes(type)" width="9" height="9" fill="none" stroke="#fff" stroke-width="3" viewBox="0 0 24 24"><polyline points="20,6 9,17 4,12"/></svg>
                    </div>
                    {{ type }}
                  </label>
                </div>
                <div class="form-grid grid-3" style="margin-top:10px">
                  <div><label class="field-label">Descripción</label>
                    <input v-model="form.background.disability.description" class="field-input" type="text" placeholder="Discapacidad específica"/></div>
                  <div><label class="field-label">Desde</label>
                    <input v-model="form.background.disability.since" class="field-input" type="text" placeholder="Ej: 2 años"/></div>
                  <div><label class="field-label">Tratamiento farmacológico</label>
                    <input v-model="form.background.disability.treatment" class="field-input" type="text" placeholder="Medicamentos"/></div>
                </div>
              </div>
            </Transition>
          </div>
        </div>
        <nav v-show="false" class="section-nav">
          <button type="button" class="btn btn-ghost-nav" @click="prevStep">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="15,18 9,12 15,6"/></svg>
            Anterior
          </button>
          <button v-if="!isLastStep" type="button" class="btn btn-primary-nav" @click="nextStep">
            Siguiente: {{ sections[6]?.short }}
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="9,18 15,12 9,6"/></svg>
          </button>
        </nav>

        <!-- 7. Antecedentes Familiares -->
        <div v-show="currentStep === 1" class="form-section">
          <div class="section-title">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Antecedentes Familiares
          </div>
          <div class="family-grid">
            <!-- Progenitores y abuelos -->
            <div v-for="m in familyMembers" :key="m.key" class="family-card">
              <div class="family-label">{{ m.label }}</div>
              <div class="form-grid grid-2">
                <div>
                  <label class="field-label">Estado</label>
                  <select v-model="form.family_background[m.key].status" class="field-select" @change="handleFamilyMemberStatus(m.key, $event.target.value)">
                    <option value="">Desconoce</option>
                    <option>{{ m.statusF ? 'Viva' : 'Vivo' }}</option>
                    <option>{{ m.statusF ? 'Fallecida' : 'Fallecido' }}</option>
                  </select>
                </div>
                <div>
                  <label class="field-label">Edad</label>
                  <input :disabled="form.family_background[m.key].unknown" v-model="form.family_background[m.key].age" class="field-input" type="number" placeholder="—"/>
                </div>
                <div class="col-full">
                  <label class="field-label">Patología / Causa de muerte</label>
                  <input :disabled="form.family_background[m.key].unknown" v-model="form.family_background[m.key].pathology" class="field-input" type="text" placeholder="HTA, DM, Ca…"/>
                </div>
              </div>
            </div>
            <!-- Hermanos -->
            <div class="family-card">
              <div class="family-label">👫 Hermanos</div>
              <div class="form-grid grid-3">
                <div><label class="field-label">Cantidad ♀</label>
                  <input v-model.number="form.family_background.siblings.female_count" class="field-input" type="number" min="0"/></div>
                <div><label class="field-label">Cantidad ♂</label>
                  <input v-model.number="form.family_background.siblings.male_count" class="field-input" type="number" min="0"/></div>
                <div><label class="field-label">Estado</label>
                  <select v-model="form.family_background.siblings.status" class="field-select" @change="handleSiblingsChildrenStatus('siblings', $event.target.value)">
                    <option value="">Desconoce</option><option>Vivos</option><option>Alguno fallecido</option>
                  </select>
                </div>
                <div class="col-full"><label class="field-label">Patología relevante</label>
                  <input :disabled="form.family_background.siblings.not_apply" v-model="form.family_background.siblings.pathology" class="field-input" type="text" placeholder="Antecedentes familiares relevantes"/></div>
              </div>
            </div>
            <!-- Hijos -->
            <div class="family-card">
              <div class="family-label">🧒 Hijos</div>
              <div class="form-grid grid-3">
                <div><label class="field-label">Cantidad ♀</label>
                  <input v-model.number="form.family_background.children.female_count" class="field-input" type="number" min="0"/></div>
                <div><label class="field-label">Cantidad ♂</label>
                  <input v-model.number="form.family_background.children.male_count" class="field-input" type="number" min="0"/></div>
                <div><label class="field-label">Estado</label>
                  <select v-model="form.family_background.children.status" class="field-select" @change="handleSiblingsChildrenStatus('children', $event.target.value)">
                    <option value="">Desconoce</option><option>Vivos</option><option>Alguno fallecido</option>
                  </select>
                </div>
                <div class="col-full"><label class="field-label">Patología relevante</label>
                  <input :disabled="form.family_background.children.not_apply" v-model="form.family_background.children.pathology" class="field-input" type="text" placeholder="Enfermedades hereditarias"/></div>
              </div>
            </div>
          </div>
        </div>
        <nav v-show="false" class="section-nav">
          <button type="button" class="btn btn-ghost-nav" @click="prevStep">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="15,18 9,12 15,6"/></svg>
            Anterior
          </button>
          <button v-if="!isLastStep" type="button" class="btn btn-primary-nav" @click="nextStep">
            Siguiente: {{ sections[7]?.short }}
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="9,18 15,12 9,6"/></svg>
          </button>
        </nav>

        <!-- 8. Hábitos Psicobiológicos -->
        <div v-show="currentStep === 1" class="form-section">
          <div class="section-title">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
              <path d="M12 2a10 10 0 1 0 10 10"/><path d="M12 6v6l4 2"/>
            </svg>
            Hábitos Psicobiológicos
          </div>

          <!-- Alcohol -->
          <div class="ante-card">
            <div class="ante-header">
              <div class="ante-title">🍺 Alcohol</div>
              <div class="btn-group-si-no">
                <button type="button" :class="{ active: form.habits.alcohol.deny }" @click="toggleHabit('alcohol', true)">NO</button>
                <button type="button" :class="{ active: !form.habits.alcohol.deny }" @click="toggleHabit('alcohol', false)">SÍ</button>
              </div>
            </div>
            <Transition name="expand">
              <div v-if="!form.habits.alcohol.deny" class="ante-fields form-grid grid-4">
                <div><label class="field-label">Desde los (años)</label>
                  <input v-model="form.habits.alcohol.start_age" class="field-input" type="number" placeholder="18"/></div>
                <div><label class="field-label">Hasta</label>
                  <input v-model="form.habits.alcohol.end_age" class="field-input" type="text" placeholder="Actualmente"/></div>
                <div><label class="field-label">Tipo de alcohol</label>
                  <input v-model="form.habits.alcohol.type" class="field-input" type="text" placeholder="Ej: Cerveza, whisky"/></div>
                <div><label class="field-label">Cantidad (ml/vez)</label>
                  <input v-model="form.habits.alcohol.quantity_ml" class="field-input" type="number" placeholder="350"/></div>
                <div><label class="field-label">Frecuencia</label>
                  <input v-model="form.habits.alcohol.frequency_days" class="field-input" type="text" placeholder="Cada X días"/></div>
                <div>
                  <label class="field-label">¿Se emborracha?</label>
                  <div class="radio-group" style="margin-top:4px">
                    <label class="radio-option" :class="{ selected: form.habits.alcohol.gets_drunk === true }" @click="form.habits.alcohol.gets_drunk = true">
                      <div class="radio-dot" :class="{ sel: form.habits.alcohol.gets_drunk === true }"><div class="radio-inner"></div></div> Sí
                    </label>
                    <label class="radio-option" :class="{ selected: form.habits.alcohol.gets_drunk === false }" @click="form.habits.alcohol.gets_drunk = false">
                      <div class="radio-dot" :class="{ sel: form.habits.alcohol.gets_drunk === false }"><div class="radio-inner"></div></div> No
                    </label>
                  </div>
                </div>
              </div>
            </Transition>
          </div>

          <!-- Café -->
          <div class="ante-card">
            <div class="ante-header">
              <div class="ante-title">☕ Café</div>
              <div class="btn-group-si-no">
                <button type="button" :class="{ active: form.habits.coffee.deny }" @click="toggleHabit('coffee', true)">NO</button>
                <button type="button" :class="{ active: !form.habits.coffee.deny }" @click="toggleHabit('coffee', false)">SÍ</button>
              </div>
            </div>
            <Transition name="expand">
              <div v-if="!form.habits.coffee.deny" class="ante-fields form-grid grid-4">
                <div><label class="field-label">Desde los (años)</label>
                  <input v-model="form.habits.coffee.start_age" class="field-input" type="number"/></div>
                <div><label class="field-label">Hasta</label>
                  <input v-model="form.habits.coffee.end_age" class="field-input" type="text" placeholder="Actualmente"/></div>
                <div><label class="field-label">Cantidad aprox. (ml/día)</label>
                  <input v-model="form.habits.coffee.quantity_ml" class="field-input" type="number"/></div>
                <div><label class="field-label">Tipo de café</label>
                  <input v-model="form.habits.coffee.type" class="field-input" type="text" placeholder="Espresso, colado…"/></div>
              </div>
            </Transition>
          </div>

          <!-- Tabaco -->
          <div class="ante-card">
            <div class="ante-header">
              <div class="ante-title">🚬 Tabaco</div>
              <div class="btn-group-si-no">
                <button type="button" :class="{ active: form.habits.tobacco.deny }" @click="toggleHabit('tobacco', true)">NO</button>
                <button type="button" :class="{ active: !form.habits.tobacco.deny }" @click="toggleHabit('tobacco', false)">SÍ</button>
              </div>
            </div>
            <Transition name="expand">
              <div v-if="!form.habits.tobacco.deny" class="ante-fields form-grid grid-4">
                <div><label class="field-label">Desde los (años)</label>
                  <input v-model="form.habits.tobacco.start_age" class="field-input" type="number"/></div>
                <div><label class="field-label">Hasta</label>
                  <input v-model="form.habits.tobacco.end_age" class="field-input" type="text" placeholder="Actualmente"/></div>
                <div><label class="field-label">Cigarros/día</label>
                  <input v-model="form.habits.tobacco.cigarettes_per_day" class="field-input" type="number"/></div>
                <div><label class="field-label">Cajas/año</label>
                  <input v-model="form.habits.tobacco.boxes_per_year" class="field-input" type="number"/></div>
              </div>
            </Transition>
          </div>

          <!-- Drogas Ilícitas -->
          <div class="ante-card">
            <div class="ante-header">
              <div class="ante-title">💊 Drogas Ilícitas</div>
              <div class="btn-group-si-no">
                <button type="button" :class="{ active: form.habits.drugs.deny }" @click="toggleHabit('drugs', true)">NO</button>
                <button type="button" :class="{ active: !form.habits.drugs.deny }" @click="toggleHabit('drugs', false)">SÍ</button>
              </div>
            </div>
            <Transition name="expand">
              <div v-if="!form.habits.drugs.deny" class="ante-fields form-grid grid-4">
                <div><label class="field-label">Desde los (años)</label>
                  <input v-model="form.habits.drugs.start_age" class="field-input" type="number"/></div>
                <div><label class="field-label">Hasta</label>
                  <input v-model="form.habits.drugs.end_age" class="field-input" type="text" placeholder="Actualmente"/></div>
                <div><label class="field-label">Vía de administración</label>
                  <input v-model="form.habits.drugs.route" class="field-input" type="text" placeholder="Oral, inhalada, IV…"/></div>
                <div><label class="field-label">Veces al día</label>
                  <input v-model="form.habits.drugs.frequency_per_day" class="field-input" type="number"/></div>
              </div>
            </Transition>
          </div>

          <!-- Actividad Física -->
          <div class="ante-card">
            <div class="ante-header"><div class="ante-title">🏃 Actividad Física</div></div>
            <div class="ante-fields form-grid grid-3" style="padding-top:4px">
              <div><label class="field-label">Tipo de ejercicio</label>
                <input v-model="form.habits.physical_activity.type" class="field-input" type="text" placeholder="Caminata, natación…"/></div>
              <div><label class="field-label">Veces por semana</label>
                <input v-model="form.habits.physical_activity.times_per_week" class="field-input" type="number"/></div>
              <div><label class="field-label">Minutos por sesión</label>
                <input v-model="form.habits.physical_activity.minutes_per_day" class="field-input" type="number"/></div>
            </div>
          </div>

          <!-- Sueño -->
          <div class="ante-card">
            <div class="ante-header"><div class="ante-title">😴 Sueño</div></div>
            <div class="ante-fields form-grid grid-4" style="padding-top:4px">
              <div>
                <label class="field-label">Patrón</label>
                <div class="radio-group" style="margin-top:4px">
                  <label v-for="t in ['Nocturno','Diurno']" :key="t"
                         class="radio-option" :class="{ selected: form.habits.sleep.type === t }"
                         @click="form.habits.sleep.type = t">
                    <div class="radio-dot" :class="{ sel: form.habits.sleep.type === t }"><div class="radio-inner"></div></div> {{ t }}
                  </label>
                </div>
              </div>
              <div><label class="field-label">Horas de sueño</label>
                <input v-model="form.habits.sleep.hours" class="field-input" type="number"/></div>
              <div><label class="field-label">Veces al día</label>
                <input v-model="form.habits.sleep.frequency_per_day" class="field-input" type="number" placeholder="1"/></div>
              <div>
                <label class="field-label">Tipo</label>
                <div class="radio-group" style="margin-top:4px">
                  <label class="radio-option" :class="{ selected: !form.habits.sleep.interrupted }" @click="form.habits.sleep.interrupted = false">
                    <div class="radio-dot" :class="{ sel: !form.habits.sleep.interrupted }"><div class="radio-inner"></div></div> Continuo
                  </label>
                  <label class="radio-option" :class="{ selected: form.habits.sleep.interrupted }" @click="form.habits.sleep.interrupted = true">
                    <div class="radio-dot" :class="{ sel: form.habits.sleep.interrupted }"><div class="radio-inner"></div></div> Interrumpido
                  </label>
                </div>
              </div>
              <div><label class="field-label">Medicamentos para dormir</label>
                <input v-model="form.habits.sleep.medication" class="field-input" type="text" placeholder="Ninguno / Especificar"/></div>
              <div><label class="field-label">Siestas (min)</label>
                <input v-model="form.habits.sleep.siesta_duration_min" class="field-input" type="number" placeholder="0"/></div>
              <div><label class="field-label">Siestas por día</label>
                <input v-model="form.habits.sleep.siesta_frequency_per_day" class="field-input" type="number" placeholder="0"/></div>
            </div>
          </div>

          <!-- Alimentación -->
          <div class="ante-card">
            <div class="ante-header"><div class="ante-title">🍽️ Alimentación</div></div>
            <div class="ante-fields form-grid grid-3" style="padding-top:4px">
              <div>
                <label class="field-label">Tipo de dieta</label>
                <div class="radio-group" style="margin-top:4px">
                  <label v-for="t in ['Balanceada','A predominio de']" :key="t"
                         class="radio-option" :class="{ selected: form.habits.nutrition.type === t }"
                         @click="form.habits.nutrition.type = t">
                    <div class="radio-dot" :class="{ sel: form.habits.nutrition.type === t }"><div class="radio-inner"></div></div> {{ t }}
                  </label>
                </div>
              </div>
              <div><label class="field-label">Predominio (si aplica)</label>
                <input v-model="form.habits.nutrition.predominance_description" class="field-input" type="text" placeholder="Carbohidratos, grasas…"/></div>
              <div><label class="field-label">N° ingestas al día</label>
                <input v-model="form.habits.nutrition.meals_count" class="field-input" type="number"/></div>
              <div><label class="field-label">Apetito</label>
                <select v-model="form.habits.nutrition.appetite" class="field-select">
                  <option>Conservado</option><option>Disminuido</option><option>Aumentado</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Hábitos Sexuales -->
          <div class="ante-card">
            <div class="ante-header">
              <div class="ante-title">❤️ Hábitos Sexuales</div>
              <div class="btn-group-si-no">
                <button type="button" :class="{ active: !form.habits.sexual_habits.active }" @click="toggleSexualActive(false)">NO</button>
                <button type="button" :class="{ active: form.habits.sexual_habits.active }" @click="toggleSexualActive(true)">SÍ</button>
              </div>
            </div>
            <Transition name="expand">
              <div v-if="form.habits.sexual_habits.active" class="ante-fields form-grid grid-3">
                <div><label class="field-label">Sexarquía (años)</label>
                  <input v-model="form.habits.sexual_habits.sexarche_age" class="field-input" type="number"/></div>
                <div><label class="field-label">N° parejas sexuales</label>
                  <input v-model="form.habits.sexual_habits.partners_count" class="field-input" type="number"/></div>
                <div><label class="field-label">Orientación sexual</label>
                  <input v-model="form.habits.sexual_habits.orientation" class="field-input" type="text" placeholder="Heterosexual…"/></div>
                <div><label class="field-label">Frecuencia (x semana)</label>
                  <input v-model="form.habits.sexual_habits.frequency_per_week" class="field-input" type="number"/></div>
                <div><label class="field-label">Método anticonceptivo</label>
                  <input v-model="form.habits.sexual_habits.contraceptive_method" class="field-input" type="text" placeholder="Píldoras, preservativo…"/></div>
              </div>
            </Transition>
          </div>

          <!-- Gastrointestinal -->
          <div class="ante-card">
            <div class="ante-header"><div class="ante-title">🚽 Hábito Gastrointestinal</div></div>
            <div class="ante-fields form-grid grid-4" style="padding-top:4px">
              <div><label class="field-label">N° evacuaciones</label>
                <input v-model="form.habits.gastrointestinal.evacuations_count" class="field-input" type="number"/></div>
              <div><label class="field-label">Período</label>
                <select v-model="form.habits.gastrointestinal.frequency_unit" class="field-select">
                  <option>Por día</option><option>Por semana</option><option>Por mes</option>
                </select>
              </div>
              <div><label class="field-label">Color</label>
                <input v-model="form.habits.gastrointestinal.color" class="field-input" type="text" placeholder="Marrón…"/></div>
              <div><label class="field-label">Consistencia (Bristol)</label>
                <select v-model="form.habits.gastrointestinal.bristol_scale" class="field-select">
                  <option value="1">1 — Muy dura</option><option value="2">2 — Dura</option>
                  <option value="3">3 — Firme</option><option value="4">4 — Normal</option>
                  <option value="5">5 — Blanda</option><option value="6">6 — Semilíquida</option>
                  <option value="7">7 — Líquida</option>
                </select>
              </div>
              <div><label class="field-label">Olor</label>
                <input v-model="form.habits.gastrointestinal.odor" class="field-input" type="text" placeholder="Característico"/></div>
              <div><label class="field-label">Forma</label>
                <input v-model="form.habits.gastrointestinal.shape" class="field-input" type="text" placeholder="Cilíndrica"/></div>
            </div>
          </div>

          <!-- Genitourinario -->
          <div class="ante-card">
            <div class="ante-header"><div class="ante-title">💧 Hábito Genitourinario</div></div>
            <div class="ante-fields form-grid grid-4" style="padding-top:4px">
              <div><label class="field-label">N° micciones/día</label>
                <input v-model="form.habits.genitourinary.urinations_count" class="field-input" type="number"/></div>
              <div><label class="field-label">Color orina</label>
                <input v-model="form.habits.genitourinary.color" class="field-input" type="text" placeholder="Amarillo pálido"/></div>
              <div><label class="field-label">Olor</label>
                <input v-model="form.habits.genitourinary.odor" class="field-input" type="text" placeholder="Característico"/></div>
              <div><label class="field-label">Predominio</label>
                <select v-model="form.habits.genitourinary.predominance" class="field-select">
                  <option>Matutino</option><option>Vespertino</option><option>Nocturno</option>
                </select>
              </div>
            </div>
          </div>

        </div>
        <nav v-show="currentStep === 1" class="section-nav">
          <button type="button" class="btn btn-ghost-nav" @click="prevStep">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px"><polyline points="15,18 9,12 15,6"/></svg>
            Anterior
          </button>
          <span class="section-nav-hint">Último apartado — guarde la historia clínica abajo</span>
        </nav>

      </div><!-- /tab-content -->

      <!-- Botón de envío dentro del formulario -->
      <div style="display:flex;justify-content:flex-end;padding-top:16px;align-items:center;gap:16px">
        <!-- Checkbox para cerrar historia clínica -->
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
          <input type="checkbox" v-model="form.close_history" style="width:18px;height:18px;cursor:pointer"/>
          <span style="font-size:14px;color:#374151">
            🔒 Cerrar historia clínica al registrar
          </span>
        </label>
        <button type="button" class="btn btn-ghost" @click="confirmExit" :disabled="form.processing">
          ❌ Cancelar
        </button>
        <button type="submit" class="btn btn-success" :disabled="form.processing">
          <svg v-if="form.processing" style="width:15px;height:15px;animation:spin 1s linear infinite" fill="none" viewBox="0 0 24 24">
            <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <svg v-else fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px">
            <path d="M9 11l3 3L22 4"/>
            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
          </svg>
          {{ form.processing ? 'Guardando…' : (isEdit ? 'Actualizar Historia Clínica' : 'Registrar Historia Clínica') }}
        </button>
      </div>
      </form>

    </div><!-- /tabs-wrapper -->

    <!-- ══ BOTTOM BAR ════════════════════════════════════════ -->
    <div class="bottom-bar">
      <div class="progress-info">
        <div class="progress-bar-wrap">
          <div class="progress-bar-fill" :style="`width:${progress}%`"></div>
        </div>
        <span class="progress-label">
          Apartado {{ currentStep + 1 }}/{{ sections.length }} · {{ progress }}%
        </span>
      </div>
      <div style="display:flex;gap:8px;align-items:center">
        <button type="button" class="btn btn-ghost" :disabled="isFirstStep" @click="prevStep">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px">
            <polyline points="15,18 9,12 15,6"/>
          </svg>
          Anterior
        </button>
        <button v-if="!isLastStep" type="button" class="btn btn-outline" @click="nextStep">
          Siguiente
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:15px;height:15px">
            <polyline points="9,18 15,12 9,6"/>
          </svg>
        </button>
      </div>
    </div>

  </AppLayout>
</template>

<style scoped>
/* ── CSS VARIABLES ───────────────────────────────────────── */
:root {
  --clr-primary:  #2563EB;
  --clr-pl:       #EFF6FF;
  --clr-success:  #10B981;
  --clr-danger:   #EF4444;
  --clr-surface:  #FFFFFF;
  --clr-bg:       #F1F5F9;
  --clr-border:   #E2E8F0;
  --clr-text:     #0F172A;
  --clr-muted:    #64748B;
}

/* ── PATIENT HEADER CARD ─────────────────────────────────── */
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
.meta-chip  {
  font-size: 11px; color: #64748B;
  background: #F1F5F9; padding: 3px 8px; border-radius: 6px;
}
.status-badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
}
.badge-draft { background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0; }
.badge-dot   { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

/* ── TABS ────────────────────────────────────────────────── */
.tabs-wrapper {
  background: #fff; border-radius: 14px;
  border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  overflow: hidden; margin-bottom: 80px;
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
.tab-btn:hover   { color: #0F172A; background: rgba(37,99,235,0.04); }
.tab-btn.active  { color: #2563EB; border-bottom-color: #2563EB; }
.tab-step-num {
  width: 20px; height: 20px; border-radius: 50%;
  background: #E2E8F0; color: #64748B;
  font-size: 10px; font-weight: 700;
  display: inline-flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.tab-btn.active .tab-step-num { background: #2563EB; color: #fff; }
.tab-label-text { font-size: 12px; }
.tab-content { padding: 28px; min-height: 320px; }

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
  flex-wrap: wrap;
  margin: 8px 0 32px;
  padding: 16px 20px;
  background: #F8FAFC;
  border: 1px dashed #CBD5E1;
  border-radius: 10px;
}
.section-nav-hint {
  font-size: 12px; color: #64748B; font-weight: 500;
  margin-left: auto;
}
.btn-primary-nav,
.btn-ghost-nav {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 10px 16px; border-radius: 8px;
  font-size: 13px; font-weight: 600; font-family: inherit;
  cursor: pointer; transition: all 0.18s;
}
.btn-primary-nav {
  background: #2563EB; color: #fff; border: none;
  margin-left: auto;
}
.btn-primary-nav:hover { background: #1D4ED8; }
.btn-ghost-nav {
  background: #fff; color: #475569;
  border: 1px solid #E2E8F0;
}
.btn-ghost-nav:hover { background: #F1F5F9; color: #0F172A; }

/* ── FORM SECTIONS ───────────────────────────────────────── */
.form-section { margin-bottom: 28px; }
.section-title {
  font-size: 11px; font-weight: 700; letter-spacing: 0.1em;
  text-transform: uppercase; color: #64748B;
  padding-bottom: 10px; margin-bottom: 16px;
  border-bottom: 1px solid #E2E8F0;
  display: flex; align-items: center; gap: 8px;
}
.section-icon { width: 14px; height: 14px; }

/* ── GRID ────────────────────────────────────────────────── */
.form-grid { display: grid; gap: 14px; }
.grid-2    { grid-template-columns: repeat(2, 1fr); }
.grid-3    { grid-template-columns: repeat(3, 1fr); }
.grid-4    { grid-template-columns: repeat(4, 1fr); }
.col-2     { grid-column: span 2; }
.col-3     { grid-column: span 3; }
.col-full  { grid-column: 1 / -1; }
@media (max-width: 900px) {
  .grid-2,.grid-3,.grid-4 { grid-template-columns: 1fr 1fr; }
  .col-full,.col-3         { grid-column: 1 / -1; }
}
@media (max-width: 600px) {
  .grid-2,.grid-3,.grid-4 { grid-template-columns: 1fr; }
  .col-2 { grid-column: 1; }
}

/* ── FORM FIELDS ─────────────────────────────────────────── */
.field-label {
  display: block; font-size: 11.5px; font-weight: 600;
  color: #374151; margin-bottom: 5px;
}
.req { color: #EF4444; margin-left: 2px; }
.field-input, .field-select, .field-textarea {
  width: 100%; padding: 8px 12px;
  border: 1.5px solid #E2E8F0; border-radius: 8px;
  font-size: 13px; font-family: inherit; color: #0F172A;
  background: #fff; outline: none;
  transition: border-color 0.18s, box-shadow 0.18s; appearance: none;
}
.field-input:focus, .field-select:focus, .field-textarea:focus {
  border-color: #2563EB;
  box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
.field-input::placeholder { color: #CBD5E1; }
.field-select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px;
}
.field-error { font-size: 11px; color: #EF4444; margin-top: 3px; }

/* ── RADIO ───────────────────────────────────────────────── */
.radio-group  { display: flex; flex-wrap: wrap; gap: 8px; }
.radio-option {
  display: flex; align-items: center; gap: 6px;
  padding: 6px 12px; border-radius: 8px;
  border: 1.5px solid #E2E8F0; cursor: pointer;
  font-size: 12.5px; transition: all 0.15s; user-select: none;
}
.radio-option:hover   { border-color: #93C5FD; background: #EFF6FF; }
.radio-option.selected{ border-color: #2563EB; background: #EFF6FF; color: #2563EB; font-weight: 600; }
.radio-dot {
  width: 14px; height: 14px; border-radius: 50%;
  border: 2px solid #CBD5E1; display: flex; align-items: center;
  justify-content: center; flex-shrink: 0; transition: all 0.15s;
}
.radio-dot.sel { border-color: #2563EB; background: #2563EB; }
.radio-inner { width: 5px; height: 5px; border-radius: 50%; background: #fff; }

/* ── CHECKBOX ────────────────────────────────────────────── */
.checkbox-group  { display: flex; flex-wrap: wrap; gap: 8px; }
.checkbox-option {
  display: flex; align-items: center; gap: 6px;
  padding: 5px 10px; border-radius: 7px;
  border: 1.5px solid #E2E8F0; cursor: pointer;
  font-size: 12px; transition: all 0.15s; user-select: none;
}
.checkbox-option:hover  { border-color: #93C5FD; background: #EFF6FF; }
.checkbox-option.checked{ border-color: #2563EB; background: #EFF6FF; color: #1D4ED8; font-weight: 500; }
.check-box {
  width: 14px; height: 14px; border-radius: 4px;
  border: 2px solid #CBD5E1; display: flex; align-items: center;
  justify-content: center; flex-shrink: 0; transition: all 0.15s;
}
.checkbox-option.checked .check-box { border-color: #2563EB; background: #2563EB; }

/* ── ANTE CARD ───────────────────────────────────────────── */
.ante-card {
  border: 1.5px solid #E2E8F0; border-radius: 10px;
  padding: 16px; background: #FAFAFA; margin-bottom: 12px;
  transition: border-color 0.18s;
}
.ante-card.focused { border-color: #93C5FD; background: #fff; }
.ante-header {
  display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;
}
.ante-title { font-size: 12.5px; font-weight: 600; color: #0F172A; display: flex; align-items: center; gap: 6px; }
.btn-group-si-no {
  display: inline-flex; border-radius: 6px; overflow: hidden;
  border: 1px solid #D1D5DB; background: #fff;
}
.btn-group-si-no button {
  padding: 5px 14px; font-size: 12px; font-weight: 600;
  border: none; cursor: pointer; transition: all 0.15s;
  background: #fff; color: #94A3B8; line-height: 1.4;
}
.btn-group-si-no button.active {
  background: #10B981; color: #fff;
  box-shadow: inset 0 1px 2px rgba(0,0,0,0.08);
}
.btn-group-si-no button:first-child { border-right: 1px solid #E2E8F0; }
.btn-group-si-no button:first-child.active { background: #EF4444; }
.ante-fields { display: grid; gap: 10px; }

/* ── EXPAND TRANSITION ───────────────────────────────────── */
.expand-enter-active, .expand-leave-active {
  transition: all 0.28s cubic-bezier(.4,0,.2,1);
  overflow: hidden;
}
.expand-enter-from, .expand-leave-to { max-height: 0; opacity: 0; }
.expand-enter-to, .expand-leave-from { max-height: 800px; opacity: 1; }

/* ── HACINAMIENTO ────────────────────────────────────────── */
.hacin-display {
  background: linear-gradient(135deg, #EFF6FF, #E0F2FE);
  border: 1.5px solid #BFDBFE; border-radius: 10px;
  padding: 16px; text-align: center; transition: all 0.3s;
}
.hacin-value {
  font-size: 36px; font-weight: 800; font-family: 'DM Mono', monospace;
  line-height: 1; margin-bottom: 4px; transition: color 0.3s;
}
.hacin-value.normal { color: #2563EB; }
.hacin-value.danger { color: #DC2626; }
.hacin-label  { font-size: 12px; color: #64748B; font-weight: 500; }
.hacin-status { font-size: 11px; font-weight: 700; margin-top: 8px; padding: 3px 10px; border-radius: 20px; display: inline-block; }
.hacin-ok     { background: #D1FAE5; color: #065F46; }
.hacin-warn   { background: #FEE2E2; color: #991B1B; }

/* ── INLINE ALERT ────────────────────────────────────────── */
.inline-alert {
  padding: 10px 14px; border-radius: 8px; font-size: 12px; font-weight: 500;
  display: flex; align-items: center; gap: 8px;
}
.alert-info { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }

/* ── DIVIDER ─────────────────────────────────────────────── */
.divider { border: none; border-top: 1px solid #E2E8F0; margin: 20px 0; }

/* ── FAMILY GRID ─────────────────────────────────────────── */
.family-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.family-card {
  border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 14px;
  background: #FAFAFA; transition: all 0.18s;
}
.family-card:focus-within { border-color: #93C5FD; background: #fff; }
.family-label {
  font-size: 11px; font-weight: 700; text-transform: uppercase;
  letter-spacing: 0.05em; color: #64748B; margin-bottom: 10px;
}

/* ── BOTTOM BAR ──────────────────────────────────────────── */
.bottom-bar {
  position: fixed; bottom: 0; right: 0; z-index: 40;
  left: var(--sidebar-w, 240px);
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(12px);
  border-top: 1px solid #E2E8F0;
  padding: 12px 24px;
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
}
.progress-info { display: flex; align-items: center; gap: 12px; }
.progress-bar-wrap {
  width: 120px; height: 5px; background: #E2E8F0;
  border-radius: 10px; overflow: hidden;
}
.progress-bar-fill {
  height: 100%; background: linear-gradient(90deg, #2563EB, #0EA5E9);
  border-radius: 10px; transition: width 0.5s;
}
.progress-label { font-size: 12px; color: #64748B; font-weight: 500; }

/* ── BUTTONS ─────────────────────────────────────────────── */
.btn {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 9px 16px; border-radius: 8px; border: none;
  font-size: 13px; font-weight: 600; cursor: pointer;
  font-family: inherit; transition: all 0.18s; white-space: nowrap;
}
.btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none !important; }
.btn-ghost {
  background: transparent; border: none; color: #64748B;
}
.btn-ghost:hover { color: #0F172A; background: #F1F5F9; }
.btn-outline {
  background: #fff; border: 1.5px solid #E2E8F0; color: #0F172A;
}
.btn-outline:hover { border-color: #94A3B8; background: #F8FAFC; }
.btn-success { background: #10B981; color: #fff; }
.btn-success:hover:not(:disabled) {
  background: #059669; transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(16,185,129,0.3);
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>
