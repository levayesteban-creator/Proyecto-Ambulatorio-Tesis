<script setup>
import { computed } from 'vue'

const props = defineProps({
    physicalExam: { type: Object, default: null },
})

const SECTIONS = [
    { key: 'general_data',   label: 'General — Facies, Marcha, Aptitud, Biotipo' },
    { key: 'skin',           label: 'Piel y Faneras' },
    { key: 'lymph_nodes',    label: 'Ganglios Linfáticos' },
    { key: 'head',           label: 'Cabeza' },
    { key: 'eyes',           label: 'Ojos' },
    { key: 'nose',           label: 'Nariz y Senos Paranasales' },
    { key: 'mouth_pharynx',  label: 'Boca y Faringe' },
    { key: 'ears',           label: 'Oídos' },
    { key: 'neck',           label: 'Cuello' },
    { key: 'thorax',         label: 'Tórax' },
    { key: 'cardiovascular', label: 'Cardiovascular' },
    { key: 'breasts',        label: 'Mamas' },
    { key: 'abdomen',        label: 'Abdomen' },
    { key: 'genital',        label: 'Examen Genital' },
    { key: 'rectal_exam',    label: 'Examen Rectal' },
    { key: 'neurological',   label: 'Neurológico' },
    { key: 'extremities',    label: 'Extremidades' },
]

const FIELD_LABELS = {
    facies: 'Facies', marcha: 'Marcha', aptitud: 'Aptitud', biotipo: 'Biotipo',
    fitzpatrick: 'Fototipo Fitzpatrick', coloration: 'Coloración', temperature: 'Temperatura',
    hydration: 'Hidratación', turgor: 'Turgor', elasticity: 'Elasticidad',
    nail_color: 'Color uñas', nail_appearance: 'Aspecto uñas', capillary_refill: 'Llenado capilar',
    lesions: 'Lesiones', location: 'Localización', size: 'Tamaño', painful: 'Dolor',
    consistency: 'Consistencia', mobility: 'Movilidad', arrangement: 'Disposición',
    type: 'Tipo', superficial_pain: 'Palpación superficial', superficial_detail: 'Detalle superficial',
    deep_pain: 'Palpación profunda', deep_detail: 'Detalle profundo', swelling: 'Tumefacción',
    scalp: 'Cuero cabelludo', hair_distribution: 'Distribución vello', hair_color: 'Color cabello',
    hair_quantity: 'Cantidad cabello', hair_type: 'Tipo cabello', acromotrichia: 'Acromotriquia',
    alopecia: 'Alopecia', implantation: 'Implantación', symmetry: 'Simetría', cornea: 'Córnea',
    sclera: 'Esclerótica', iris: 'Iris', pupils: 'Pupilas', conjunctiva: 'Conjuntiva',
    brows_lashes: 'Cejas y pestañas', eyelids: 'Párpados', fundus: 'Fondo de ojo',
    septum: 'Tabique', pyramid: 'Pirámide nasal', passages: 'Fosas nasales', mucosa: 'Mucosa',
    turbinates: 'Cornetes', secretions: 'Secreciones', sinuses: 'Senos paranasales',
    lips: 'Labios', gums: 'Encía', dental_arch: 'Arcada dentaria', dental_detail: 'Detalle dental',
    tongue: 'Lengua', pharynx: 'Faringe', tonsils: 'Amígdalas', uvula: 'Úvula', pillars: 'Pilares',
    auricles: 'Pabellones', canal: 'Conducto auditivo', tympanic_membrane: 'Membrana timpánica',
    length: 'Largo', form: 'Forma', jugular_pulse: 'Pulso yugular', trachea: 'Tráquea',
    thyroid: 'Tiroides', body_type: 'Tipo corporal', configuration: 'Configuración',
    venous_circulation: 'Circulación venosa', expansibility: 'Expansibilidad',
    breathing_type: 'Tipo respiración', intercostal_retraction: 'Tiraje intercostal',
    nasal_flaring: 'Aleteo nasal', palpation_pain: 'Dolor palpación',
    vocal_vibrations: 'Vibraciones vocales', vesicular_murmur: 'Murmullo vesicular',
    laryngotracheal_murmur: 'Murmullo laringotraqueal', added_sounds: 'Ruidos agregados',
    sonority: 'Sonoridad', apex_visible: 'Ápex visible', apex_palpable: 'Ápex palpable',
    dullness: 'Matidez cardiaca', heart_sounds: 'Ruidos cardiacos', murmurs: 'Soplos',
    masses: 'Masas', nipple: 'Pezón', depressible: 'Depresible',
    bowel_sounds: 'Ruidos hidroaéreos', vascular_sounds: 'Ruidos vasculares',
    tympanism: 'Timpanismo', hepatic_dullness: 'Mátidez hepática',
    hepatometry_parasternal: 'Hepatometría paraesternal',
    hepatometry_midclavicular: 'Hepatometría medioclavicular',
    hepatometry_axillary: 'Hepatometría axilar anterior', kidney_punch: 'Puñopercusión',
    gender_type: 'Tipo genital', testes: 'Testículos', scrotum: 'Escroto',
    vulva: 'Vulva', hymen: 'Himen', vagina: 'Vagina', prolapse: 'Prolapsos', cervix: 'Cérvix',
    anus: 'Ano', rectal_touch: 'Tacto rectal', prostate: 'Próstata',
    glasgow_eye: 'Glasgow — Apertura ocular', glasgow_verbal: 'Glasgow — Verbal',
    glasgow_motor: 'Glasgow — Motor', consciousness_level: 'Nivel conciencia',
    consciousness_state: 'Estado conciencia', language: 'Lenguaje', thought: 'Pensamiento',
    memory: 'Memoria', calculation: 'Cálculo', upper_strength: 'Fuerza EESS',
    lower_strength: 'Fuerza EEII', babinski: 'Babinski', reflex_bicipital: 'Reflejo bicipital',
    reflex_styloradial: 'Reflejo estilorradial', reflex_patellar: 'Reflejo patelar',
    reflex_achilles: 'Reflejo aquíleo', involuntary_movements: 'Movimientos involuntarios',
    cn_i: 'PC I — Olfato', cn_ii: 'PC II — Agudeza visual', cn_iii_iv_vi: 'PC III, IV, VI',
    cn_v: 'PC V', cn_vii: 'PC VII', cn_viii: 'PC VIII', cn_ix_x: 'PC IX, X',
    cn_xi: 'PC XI', cn_xii: 'PC XII', varicose_veins: 'Venas varicosas', edema: 'Edema',
    peripheral_pulse: 'Pulsos periféricos', mobility: 'Movilidad', muscle_tone: 'Tono muscular',
    paresthesias: 'Parestesias', pain: 'Dolor', deformity: 'Deformidad',
    breathing_protrusions: 'Protrusiones al respirar', valsalva_protrusions: 'Protrusiones Valsalva',
}

const humanize = (key) =>
    FIELD_LABELS[key]
    ?? key.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())

const formatValue = (value) => {
    if (value === null || value === undefined || value === '') return null
    if (typeof value === 'boolean') return value ? 'Sí' : 'No'
    if (typeof value === 'object') return JSON.stringify(value)
    return String(value)
}

const populatedSections = computed(() => {
    if (!props.physicalExam) return []

    return SECTIONS.map(({ key, label }) => {
        const data = props.physicalExam[key]
        if (!data || typeof data !== 'object') return null

        const fields = Object.entries(data)
            .filter(([k, v]) => k !== '_normal' && formatValue(v) !== null)
            .map(([k, v]) => ({ key: k, label: humanize(k), value: formatValue(v) }))

        if (!fields.length && !data._normal) return null

        return {
            key,
            label,
            isNormal: Boolean(data._normal),
            fields,
        }
    }).filter(Boolean)
})

const hasContent = computed(() => populatedSections.value.length > 0)
</script>

<template>
    <div v-if="hasContent" class="space-y-4">
        <div
            v-for="section in populatedSections"
            :key="section.key"
            class="rounded-lg border bg-gray-50 p-4"
        >
            <div class="flex items-center justify-between gap-3 mb-3">
                <h4 class="text-sm font-bold text-gray-800">{{ section.label }}</h4>
                <span
                    v-if="section.isNormal"
                    class="px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700"
                >
                    DLN
                </span>
            </div>

            <p v-if="section.isNormal && !section.fields.length" class="text-xs text-gray-500 italic">
                Dentro de límites normales.
            </p>

            <dl v-else class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2 text-xs">
                <div v-for="field in section.fields" :key="field.key">
                    <dt class="font-semibold text-gray-500 uppercase tracking-wide">{{ field.label }}</dt>
                    <dd class="text-gray-800 mt-0.5 whitespace-pre-line">{{ field.value }}</dd>
                </div>
            </dl>
        </div>
    </div>
</template>
