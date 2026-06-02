<script setup>
/**
 * PhysicalExamTab.vue
 * Componente dedicado al Examen Físico estructurado.
 * Se importa en Consultations/Create.vue como el contenido del Tab 2.
 *
 * Props: form (useForm de Inertia) — acceso directo para v-model reactivo.
 */
defineProps({
    form: { type: Object, required: true },
})

// ── CATÁLOGOS CLÍNICOS ────────────────────────────────────
const faciesOptions = [
    'Normal','Hipotiroidea (Mixedematosa)','Hipertiroidea (Basedowiana)',
    'Cushingniana','Parkinsoniana','Ictérica','Cianótica','Mitral (Cianosis Mitral)',
    'Leónina','Miasténica','Acromegálica','Febril','Dolorosa / Ansiosa','Lúpica',
    'Paralítica','Sardónica','Esclerodérmica','Disneica','Hipocrática','Caquéctica',
    'Alcohólica','Mongólica','Anémica','Hodgkin','Nefrótica','Aórtica',
    'Mediastinitis','Hepática','Peritoneal','Parálisis Bulbar','Depresiva',
    'Maníaca','Pseudobulbar','Adenoidea','Pagética',
]

const marchaOptions = [
    'Normal','Hemipléjica (Segador)','Parkinsoniana (A Pasos Cortos)',
    'Atáxica Cerebelosa (Inestable)','Equina (Pie Caído)','Miopática (De Pato)',
    'Antálgica (Por Dolor)','Espástica (Paraparesoespástica)','En Tijeras',
    'Vestibular (En Estrella)','Taloneante (Tabética)','Trendelenburg',
    'Coreica (Danzante)','Distónica','Apráxica','Senil (A Pequeños Pasos)',
    'Histérica (De Astasia-Abasia)','De Sapo',
]

const aptitudOptions = [
    'Compuesta (normal)','Decúbito dorsal (supino)','Decúbito ventral (prono)',
    'Decúbito lateral','Decúbito indiferente','Decúbito obligado',
    'En gatillo de fusil','Opistótonos','Emprostótonos','Pleurostótonos',
    'Ortotonos','De ortopnea','Genupectoral (plegaria mahometana)',
    'De cuclillas','Parkinsoniana','Hemipléjica (Soldado de Wernicke-Mann)',
]

const consciousnessLevel = ['Vigil','Somnoliento','Estuporoso','Coma']
const glasgowEye    = ['1 — No abre','2 — Al dolor','3 — A la voz','4 — Espontánea']
const glasgowVerbal = ['1 — Sin respuesta','2 — Sonidos','3 — Palabras','4 — Confuso','5 — Orientado']
const glasgowMotor  = ['1 — Sin respuesta','2 — Extensión','3 — Flexión anormal','4 — Retirada','5 — Localiza dolor','6 — Obedece órdenes']

const motorScale = ['0/V — Sin contracción','1/V — Contracción sin movimiento','2/V — Movimiento sin gravedad',
    '3/V — Vence gravedad','4/V — Vence resistencia parcial','5/V — Fuerza normal']

const reflexScale = ['0/V — Ausente','1/V — Hipoactivo','2/V — Normal','3/V — Hiperactivo','4/V — Clono']

// Secciones generales del examen — para el accordion
const sections = [
    { key: 'general_data',   icon: '🧍', label: 'General — Facies, Marcha, Aptitud, Biotipo' },
    { key: 'skin',           icon: '🩹', label: 'Piel y Faneras' },
    { key: 'lymph_nodes',    icon: '🔵', label: 'Ganglios Linfáticos' },
    { key: 'head',           icon: '🧠', label: 'Cabeza' },
    { key: 'eyes',           icon: '👁️',  label: 'Ojos' },
    { key: 'nose',           icon: '👃', label: 'Nariz y Senos Paranasales' },
    { key: 'mouth_pharynx',  icon: '👄', label: 'Boca y Faringe' },
    { key: 'ears',           icon: '👂', label: 'Oídos' },
    { key: 'neck',           icon: '🦒', label: 'Cuello' },
    { key: 'thorax',         icon: '💨', label: 'Tórax' },
    { key: 'cardiovascular', icon: '❤️', label: 'Cardiovascular' },
    { key: 'breasts',        icon: '🫁', label: 'Mamas' },
    { key: 'abdomen',        icon: '🔬', label: 'Abdomen' },
    { key: 'genital',        icon: '⚕️', label: 'Examen Genital' },
    { key: 'rectal_exam',    icon: '🩺', label: 'Examen Rectal' },
    { key: 'neurological',   icon: '🧬', label: 'Neurológico (Glasgow + Pares Craneales)' },
    { key: 'extremities',    icon: '🦿', label: 'Extremidades' },
]

// Control de secciones expandidas (accordion)
import { ref } from 'vue'
const openSection = ref('general_data')
const toggle = (key) => { openSection.value = openSection.value === key ? null : key }

// Marca una sección como "dentro de límites normales" (DLN)
const setNormal = (form, key) => {
    form.physical_exam[key]._normal = true
}
const setAltered = (form, key) => {
    form.physical_exam[key]._normal = false
}
</script>

<template>
    <!-- ══ AVISO CLÍNICO ══════════════════════════════════ -->
    <div class="inline-alert alert-info" style="margin-bottom:20px">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span>
            Expanda cada sección para registrar los hallazgos.
            Use <strong>DLN</strong> (Dentro de Límites Normales) para marcar secciones sin alteraciones.
            Solo se guarda lo que se registre explícitamente.
        </span>
    </div>

    <!-- ══ ACCORDION DE SECCIONES ════════════════════════ -->
    <div class="exam-accordion">

        <!-- ═══ 1. GENERAL ═══════════════════════════════ -->
        <div class="exam-section" :class="{ open: openSection === 'general_data' }">
            <div class="exam-header" @click="toggle('general_data')">
                <div class="exam-title"><span class="exam-icon">🧍</span> General — Facies, Marcha, Aptitud, Biotipo</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="form.physical_exam.general_data?._normal" class="dln-badge">✓ DLN</span>
                    <span class="exam-chevron">{{ openSection === 'general_data' ? '▲' : '▼' }}</span>
                </div>
            </div>
            <Transition name="expand">
                <div v-if="openSection === 'general_data'" class="exam-body">
                    <div class="dln-bar">
                        <button type="button" class="btn-dln" @click="setNormal(form, 'general_data')">✓ DLN</button>
                        <button type="button" class="btn-altered" @click="setAltered(form, 'general_data')">⚠ Alterado</button>
                    </div>
                    <div class="form-grid grid-2">
                        <div>
                            <label class="field-label">Facies</label>
                            <select v-model="form.physical_exam.general_data.facies" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option v-for="f in faciesOptions" :key="f" :value="f">{{ f }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Marcha</label>
                            <select v-model="form.physical_exam.general_data.marcha" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option v-for="m in marchaOptions" :key="m" :value="m">{{ m }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Aptitud / Posición</label>
                            <select v-model="form.physical_exam.general_data.aptitud" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option v-for="a in aptitudOptions" :key="a" :value="a">{{ a }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Biotipo</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="b in ['Ectomorfo','Endomorfo','Mesomorfo']" :key="b"
                                       class="radio-option" :class="{ selected: form.physical_exam.general_data.biotipo === b }"
                                       @click="form.physical_exam.general_data.biotipo = b">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.general_data.biotipo === b }">
                                        <div class="radio-inner"></div>
                                    </div>{{ b }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ═══ 2. PIEL Y FANERAS ════════════════════════ -->
        <div class="exam-section" :class="{ open: openSection === 'skin' }">
            <div class="exam-header" @click="toggle('skin')">
                <div class="exam-title"><span class="exam-icon">🩹</span> Piel y Faneras</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="form.physical_exam.skin?._normal" class="dln-badge">✓ DLN</span>
                    <span class="exam-chevron">{{ openSection === 'skin' ? '▲' : '▼' }}</span>
                </div>
            </div>
            <Transition name="expand">
                <div v-if="openSection === 'skin'" class="exam-body">
                    <div class="dln-bar">
                        <button type="button" class="btn-dln" @click="setNormal(form, 'skin')">✓ DLN</button>
                        <button type="button" class="btn-altered" @click="setAltered(form, 'skin')">⚠ Alterado</button>
                    </div>
                    <div class="form-grid grid-3">
                        <div>
                            <label class="field-label">Fototipo de Fitzpatrick</label>
                            <select v-model="form.physical_exam.skin.fitzpatrick" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option v-for="n in [1,2,3,4,5,6]" :key="n" :value="n">Tipo {{ n }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Coloración anormal</label>
                            <input v-model="form.physical_exam.skin.coloration" class="field-input" type="text"
                                   placeholder="Ictericia, palidez, cianosis…"/>
                        </div>
                        <div>
                            <label class="field-label">Temperatura cutánea</label>
                            <select v-model="form.physical_exam.skin.temperature" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option>Normotérmica</option>
                                <option>Hipertérmica</option>
                                <option>Hipotérmica</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Hidratación</label>
                            <select v-model="form.physical_exam.skin.hydration" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option>Conservada</option>
                                <option>Deshidratada (+)</option>
                                <option>Deshidratada (++)</option>
                                <option>Deshidratada (+++)</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Turgor</label>
                            <select v-model="form.physical_exam.skin.turgor" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option>Conservado</option>
                                <option>Disminuido</option>
                                <option>Aumentado</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Elasticidad</label>
                            <select v-model="form.physical_exam.skin.elasticity" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option>Conservada</option>
                                <option>Disminuida</option>
                                <option>Aumentada</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Color de uñas</label>
                            <input v-model="form.physical_exam.skin.nail_color" class="field-input" type="text"
                                   placeholder="Rosado, cianótico, leuconiquia…"/>
                        </div>
                        <div>
                            <label class="field-label">Aspecto de uñas</label>
                            <input v-model="form.physical_exam.skin.nail_appearance" class="field-input" type="text"
                                   placeholder="Normal, coiloniquia, acropaquia…"/>
                        </div>
                        <div>
                            <label class="field-label">Llenado capilar (seg.)</label>
                            <input v-model.number="form.physical_exam.skin.capillary_refill" class="field-input"
                                   type="number" step="0.5" placeholder="2"/>
                        </div>
                        <div class="col-full">
                            <label class="field-label">Lesiones cutáneas</label>
                            <textarea v-model="form.physical_exam.skin.lesions" class="field-textarea" rows="2"
                                      placeholder="Tipo de lesión, ubicación, diámetro, cantidad, forma, secreciones…"/>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ═══ 3. GANGLIOS ══════════════════════════════ -->
        <div class="exam-section" :class="{ open: openSection === 'lymph_nodes' }">
            <div class="exam-header" @click="toggle('lymph_nodes')">
                <div class="exam-title"><span class="exam-icon">🔵</span> Ganglios Linfáticos</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="form.physical_exam.lymph_nodes?._normal" class="dln-badge">✓ DLN</span>
                    <span class="exam-chevron">{{ openSection === 'lymph_nodes' ? '▲' : '▼' }}</span>
                </div>
            </div>
            <Transition name="expand">
                <div v-if="openSection === 'lymph_nodes'" class="exam-body">
                    <div class="dln-bar">
                        <button type="button" class="btn-dln" @click="setNormal(form, 'lymph_nodes')">✓ DLN</button>
                        <button type="button" class="btn-altered" @click="setAltered(form, 'lymph_nodes')">⚠ Alterado</button>
                    </div>
                    <div class="form-grid grid-3">
                        <div>
                            <label class="field-label">Localización</label>
                            <input v-model="form.physical_exam.lymph_nodes.location" class="field-input" type="text"
                                   placeholder="Cervical, axilar, inguinal…"/>
                        </div>
                        <div>
                            <label class="field-label">Tamaño (cm)</label>
                            <input v-model="form.physical_exam.lymph_nodes.size" class="field-input" type="text"
                                   placeholder="Ej: 1.5"/>
                        </div>
                        <div>
                            <label class="field-label">Dolor</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="d in ['Doloroso','No doloroso']" :key="d"
                                       class="radio-option" :class="{ selected: form.physical_exam.lymph_nodes.painful === d }"
                                       @click="form.physical_exam.lymph_nodes.painful = d">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.lymph_nodes.painful === d }"><div class="radio-inner"></div></div>{{ d }}
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Consistencia</label>
                            <select v-model="form.physical_exam.lymph_nodes.consistency" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option>Blando</option><option>Elástico</option>
                                <option>Duro</option><option>Pétreo</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Movilidad</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="m in ['Móvil','Adherido a planos profundos']" :key="m"
                                       class="radio-option" :class="{ selected: form.physical_exam.lymph_nodes.mobility === m }"
                                       @click="form.physical_exam.lymph_nodes.mobility = m">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.lymph_nodes.mobility === m }"><div class="radio-inner"></div></div>{{ m }}
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Distribución</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="a in ['Aislados','Conglomerado']" :key="a"
                                       class="radio-option" :class="{ selected: form.physical_exam.lymph_nodes.arrangement === a }"
                                       @click="form.physical_exam.lymph_nodes.arrangement = a">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.lymph_nodes.arrangement === a }"><div class="radio-inner"></div></div>{{ a }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ═══ 4. CABEZA ════════════════════════════════ -->
        <div class="exam-section" :class="{ open: openSection === 'head' }">
            <div class="exam-header" @click="toggle('head')">
                <div class="exam-title"><span class="exam-icon">🧠</span> Cabeza</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="form.physical_exam.head?._normal" class="dln-badge">✓ DLN</span>
                    <span class="exam-chevron">{{ openSection === 'head' ? '▲' : '▼' }}</span>
                </div>
            </div>
            <Transition name="expand">
                <div v-if="openSection === 'head'" class="exam-body">
                    <div class="dln-bar">
                        <button type="button" class="btn-dln" @click="setNormal(form, 'head')">✓ DLN</button>
                        <button type="button" class="btn-altered" @click="setAltered(form, 'head')">⚠ Alterado</button>
                    </div>
                    <div class="form-grid grid-3">
                        <div>
                            <label class="field-label">Tipo de cráneo</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="t in ['Mesocéfalo','Bradicéfalo','Macrocéfalo']" :key="t"
                                       class="radio-option" :class="{ selected: form.physical_exam.head.type === t }"
                                       @click="form.physical_exam.head.type = t">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.head.type === t }"><div class="radio-inner"></div></div>{{ t }}
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Palpación superficial</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="p in ['Indolora','Dolorosa']" :key="p"
                                       class="radio-option" :class="{ selected: form.physical_exam.head.superficial_pain === p }"
                                       @click="form.physical_exam.head.superficial_pain = p">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.head.superficial_pain === p }"><div class="radio-inner"></div></div>{{ p }}
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Detalle dolor superficial</label>
                            <input v-model="form.physical_exam.head.superficial_detail" class="field-input" type="text" placeholder="Localización…"/>
                        </div>
                        <div>
                            <label class="field-label">Palpación profunda</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="p in ['Indolora','Dolorosa']" :key="p"
                                       class="radio-option" :class="{ selected: form.physical_exam.head.deep_pain === p }"
                                       @click="form.physical_exam.head.deep_pain = p">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.head.deep_pain === p }"><div class="radio-inner"></div></div>{{ p }}
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Tumefacción / Masa</label>
                            <input v-model="form.physical_exam.head.swelling" class="field-input" type="text" placeholder="Ausente o describir…"/>
                        </div>
                        <div>
                            <label class="field-label">Cuero cabelludo</label>
                            <input v-model="form.physical_exam.head.scalp" class="field-input" type="text" placeholder="Descamación, lesiones, secreciones…"/>
                        </div>
                        <div>
                            <label class="field-label">Distribución del vello</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="d in ['Ginecoide','Androide']" :key="d"
                                       class="radio-option" :class="{ selected: form.physical_exam.head.hair_distribution === d }"
                                       @click="form.physical_exam.head.hair_distribution = d">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.head.hair_distribution === d }"><div class="radio-inner"></div></div>{{ d }}
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Color del cabello</label>
                            <input v-model="form.physical_exam.head.hair_color" class="field-input" type="text"
                                   placeholder="Negro, castaño, rubio, canoso…"/>
                        </div>
                        <div>
                            <label class="field-label">Cantidad del cabello</label>
                            <select v-model="form.physical_exam.head.hair_quantity" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option>Normal</option>
                                <option>Escaso</option>
                                <option>Abundante</option>
                                <option>Ausente</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Tipo de cabello</label>
                            <select v-model="form.physical_exam.head.hair_type" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option>Liso (Lisotrico / Leiotrico)</option>
                                <option>Crespo (Ulotrico)</option>
                                <option>Ondulado (Quimatotrico)</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Acromotriquia</label>
                            <input v-model="form.physical_exam.head.acromotrichia" class="field-input" type="text"
                                   placeholder="Ausente / presente (canas prematuras, despigmentación…)"/>
                        </div>
                        <div>
                            <label class="field-label">Alopecia</label>
                            <input v-model="form.physical_exam.head.alopecia" class="field-input" type="text"
                                   placeholder="No / Patrón (areata, androgénica, efluvio telógeno…)"/>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ═══ 5. OJOS ══════════════════════════════════ -->
        <div class="exam-section" :class="{ open: openSection === 'eyes' }">
            <div class="exam-header" @click="toggle('eyes')">
                <div class="exam-title"><span class="exam-icon">👁️</span> Ojos</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="form.physical_exam.eyes?._normal" class="dln-badge">✓ DLN</span>
                    <span class="exam-chevron">{{ openSection === 'eyes' ? '▲' : '▼' }}</span>
                </div>
            </div>
            <Transition name="expand">
                <div v-if="openSection === 'eyes'" class="exam-body">
                    <div class="dln-bar">
                        <button type="button" class="btn-dln" @click="setNormal(form, 'eyes')">✓ DLN</button>
                        <button type="button" class="btn-altered" @click="setAltered(form, 'eyes')">⚠ Alterado</button>
                    </div>
                    <div class="form-grid grid-3">
                        <div><label class="field-label">Implantación</label>
                            <input v-model="form.physical_exam.eyes.implantation" class="field-input" type="text" placeholder="Normal, hipertelorismo…"/></div>
                        <div><label class="field-label">Simetría</label>
                            <input v-model="form.physical_exam.eyes.symmetry" class="field-input" type="text" placeholder="Simétricos / causa…"/></div>
                        <div><label class="field-label">Córnea</label>
                            <input v-model="form.physical_exam.eyes.cornea" class="field-input" type="text" placeholder="Forma, transparencia, opacidad…"/></div>
                        <div><label class="field-label">Color de esclerótica</label>
                            <input v-model="form.physical_exam.eyes.sclera" class="field-input" type="text" placeholder="Blanca / ictérica / azulada…"/></div>
                        <div><label class="field-label">Color de iris</label>
                            <input v-model="form.physical_exam.eyes.iris" class="field-input" type="text" placeholder="Pardo, verde, azul…"/></div>
                        <div><label class="field-label">Pupilas</label>
                            <input v-model="form.physical_exam.eyes.pupils" class="field-input" type="text" placeholder="Centrales, isocóricas, tamaño…"/></div>
                        <div><label class="field-label">Conjuntiva bulbar y palpebral</label>
                            <input v-model="form.physical_exam.eyes.conjunctiva" class="field-input" type="text" placeholder="Rosadas / pálidas / ictéricas…"/></div>
                        <div><label class="field-label">Cejas y Pestañas</label>
                            <input v-model="form.physical_exam.eyes.brows_lashes" class="field-input" type="text" placeholder="Implantación, simetría, lesiones…"/></div>
                        <div><label class="field-label">Párpados</label>
                            <input v-model="form.physical_exam.eyes.eyelids" class="field-input" type="text" placeholder="Ptosis, edema, lesiones…"/></div>
                        <div class="col-full"><label class="field-label">Fondo de ojo</label>
                            <input v-model="form.physical_exam.eyes.fundus" class="field-input" type="text" placeholder="Rojo pupilar, mácula, relación A-V…"/></div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ═══ 6. NARIZ ═════════════════════════════════ -->
        <div class="exam-section" :class="{ open: openSection === 'nose' }">
            <div class="exam-header" @click="toggle('nose')">
                <div class="exam-title"><span class="exam-icon">👃</span> Nariz y Senos Paranasales</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="form.physical_exam.nose?._normal" class="dln-badge">✓ DLN</span>
                    <span class="exam-chevron">{{ openSection === 'nose' ? '▲' : '▼' }}</span>
                </div>
            </div>
            <Transition name="expand">
                <div v-if="openSection === 'nose'" class="exam-body">
                    <div class="dln-bar">
                        <button type="button" class="btn-dln" @click="setNormal(form, 'nose')">✓ DLN</button>
                        <button type="button" class="btn-altered" @click="setAltered(form, 'nose')">⚠ Alterado</button>
                    </div>
                    <div class="form-grid grid-3">
                        <div>
                            <label class="field-label">Tabique nasal</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="s in ['Central','Desviado']" :key="s"
                                       class="radio-option" :class="{ selected: form.physical_exam.nose.septum === s }"
                                       @click="form.physical_exam.nose.septum = s">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.nose.septum === s }"><div class="radio-inner"></div></div>{{ s }}
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Pirámide nasal</label>
                            <input v-model="form.physical_exam.nose.pyramid" class="field-input" type="text" placeholder="Normal / alteración…"/>
                        </div>
                        <div>
                            <label class="field-label">Fosas nasales</label>
                            <select v-model="form.physical_exam.nose.passages" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option>Permeables bilateralmente</option>
                                <option>No permeables bilateralmente</option>
                                <option>Permeable solo derecha</option>
                                <option>Permeable solo izquierda</option>
                            </select>
                        </div>
                        <div><label class="field-label">Mucosa nasal</label>
                            <input v-model="form.physical_exam.nose.mucosa" class="field-input" type="text" placeholder="Humedad, color, vibrisas…"/></div>
                        <div><label class="field-label">Cornetes</label>
                            <input v-model="form.physical_exam.nose.turbinates" class="field-input" type="text" placeholder="Lesiones del cornete medio e inferior…"/></div>
                        <div><label class="field-label">Secreciones</label>
                            <input v-model="form.physical_exam.nose.secretions" class="field-input" type="text" placeholder="Tipo, cantidad, color…"/></div>
                        <div class="col-full"><label class="field-label">Senos paranasales</label>
                            <input v-model="form.physical_exam.nose.sinuses" class="field-input" type="text" placeholder="Frontal, maxilar, etmoidal — doloroso/no doloroso…"/></div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ═══ 7. BOCA Y FARINGE ════════════════════════ -->
        <div class="exam-section" :class="{ open: openSection === 'mouth_pharynx' }">
            <div class="exam-header" @click="toggle('mouth_pharynx')">
                <div class="exam-title"><span class="exam-icon">👄</span> Boca y Faringe</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="form.physical_exam.mouth_pharynx?._normal" class="dln-badge">✓ DLN</span>
                    <span class="exam-chevron">{{ openSection === 'mouth_pharynx' ? '▲' : '▼' }}</span>
                </div>
            </div>
            <Transition name="expand">
                <div v-if="openSection === 'mouth_pharynx'" class="exam-body">
                    <div class="dln-bar">
                        <button type="button" class="btn-dln" @click="setNormal(form, 'mouth_pharynx')">✓ DLN</button>
                        <button type="button" class="btn-altered" @click="setAltered(form, 'mouth_pharynx')">⚠ Alterado</button>
                    </div>
                    <div class="form-grid grid-3">
                        <div><label class="field-label">Labios</label>
                            <input v-model="form.physical_exam.mouth_pharynx.lips" class="field-input" type="text" placeholder="Color, simetría, lesiones…"/></div>
                        <div><label class="field-label">Encía y mucosa oral</label>
                            <input v-model="form.physical_exam.mouth_pharynx.gums" class="field-input" type="text" placeholder="Humedad, color, lesiones…"/></div>
                        <div>
                            <label class="field-label">Arcada dentaria</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="a in ['Completa','Incompleta']" :key="a"
                                       class="radio-option" :class="{ selected: form.physical_exam.mouth_pharynx.dental_arch === a }"
                                       @click="form.physical_exam.mouth_pharynx.dental_arch = a">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.mouth_pharynx.dental_arch === a }"><div class="radio-inner"></div></div>{{ a }}
                                </label>
                            </div>
                        </div>
                        <div><label class="field-label">Piezas dentarias ausentes</label>
                            <input v-model="form.physical_exam.mouth_pharynx.dental_detail" class="field-input" type="text" placeholder="Especificar pieza…"/></div>
                        <div><label class="field-label">Lengua</label>
                            <input v-model="form.physical_exam.mouth_pharynx.tongue" class="field-input" type="text" placeholder="Aspecto, color, humedad, alteraciones del trofismo…"/></div>
                        <div><label class="field-label">Faringe</label>
                            <input v-model="form.physical_exam.mouth_pharynx.pharynx" class="field-input" type="text" placeholder="Aspecto, color, humedad, lesiones…"/></div>
                        <div>
                            <label class="field-label">Amígdalas (Escala Brodsky)</label>
                            <select v-model="form.physical_exam.mouth_pharynx.tonsils" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option>0 — No visibles (amigdalectomizadas)</option>
                                <option>1+ — En fosa amigdalina</option>
                                <option>2+ — Asoman al pilar</option>
                                <option>3+ — Más allá del pilar</option>
                                <option>4+ — Contacto en línea media</option>
                            </select>
                        </div>
                        <div><label class="field-label">Úvula</label>
                            <input v-model="form.physical_exam.mouth_pharynx.uvula" class="field-input" type="text" placeholder="Color, humedad, central/desviada…"/></div>
                        <div>
                            <label class="field-label">Pilares</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="p in ['Normales','Alterados']" :key="p"
                                       class="radio-option" :class="{ selected: form.physical_exam.mouth_pharynx.pillars === p }"
                                       @click="form.physical_exam.mouth_pharynx.pillars = p">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.mouth_pharynx.pillars === p }"><div class="radio-inner"></div></div>{{ p }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ═══ 8. OÍDOS ══════════════════════════════════ -->
        <div class="exam-section" :class="{ open: openSection === 'ears' }">
            <div class="exam-header" @click="toggle('ears')">
                <div class="exam-title"><span class="exam-icon">👂</span> Oídos</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="form.physical_exam.ears?._normal" class="dln-badge">✓ DLN</span>
                    <span class="exam-chevron">{{ openSection === 'ears' ? '▲' : '▼' }}</span>
                </div>
            </div>
            <Transition name="expand">
                <div v-if="openSection === 'ears'" class="exam-body">
                    <div class="dln-bar">
                        <button type="button" class="btn-dln" @click="setNormal(form, 'ears')">✓ DLN</button>
                        <button type="button" class="btn-altered" @click="setAltered(form, 'ears')">⚠ Alterado</button>
                    </div>
                    <div class="form-grid grid-3">
                        <div><label class="field-label">Pabellones auriculares</label>
                            <input v-model="form.physical_exam.ears.auricles" class="field-input" type="text" placeholder="Implantación, simetría, dolor, lesiones…"/></div>
                        <div><label class="field-label">Conducto auditivo externo</label>
                            <input v-model="form.physical_exam.ears.canal" class="field-input" type="text" placeholder="Permeabilidad, estado, cerumen…"/></div>
                        <div><label class="field-label">Membrana timpánica</label>
                            <input v-model="form.physical_exam.ears.tympanic_membrane" class="field-input" type="text" placeholder="Color, integridad, triángulo luminoso…"/></div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ═══ 9. CUELLO ════════════════════════════════ -->
        <div class="exam-section" :class="{ open: openSection === 'neck' }">
            <div class="exam-header" @click="toggle('neck')">
                <div class="exam-title"><span class="exam-icon">🦒</span> Cuello</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="form.physical_exam.neck?._normal" class="dln-badge">✓ DLN</span>
                    <span class="exam-chevron">{{ openSection === 'neck' ? '▲' : '▼' }}</span>
                </div>
            </div>
            <Transition name="expand">
                <div v-if="openSection === 'neck'" class="exam-body">
                    <div class="dln-bar">
                        <button type="button" class="btn-dln" @click="setNormal(form, 'neck')">✓ DLN</button>
                        <button type="button" class="btn-altered" @click="setAltered(form, 'neck')">⚠ Alterado</button>
                    </div>
                    <div class="form-grid grid-3">
                        <div>
                            <label class="field-label">Longitud</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="l in ['Largo','Corto']" :key="l"
                                       class="radio-option" :class="{ selected: form.physical_exam.neck.length === l }"
                                       @click="form.physical_exam.neck.length = l">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.neck.length === l }"><div class="radio-inner"></div></div>{{ l }}
                                </label>
                            </div>
                        </div>
                        <div><label class="field-label">Forma</label>
                            <input v-model="form.physical_exam.neck.form" class="field-input" type="text" placeholder="Cilíndrico, engrosado…"/></div>
                        <div><label class="field-label">Simetría</label>
                            <input v-model="form.physical_exam.neck.symmetry" class="field-input" type="text" placeholder="Simétrico / causa de asimetría…"/></div>
                        <div><label class="field-label">Movilidad</label>
                            <input v-model="form.physical_exam.neck.mobility" class="field-input" type="text" placeholder="Activa, pasiva, contra resistencia…"/></div>
                        <div>
                            <label class="field-label">Pulso yugular</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="y in ['Visible','No visible']" :key="y"
                                       class="radio-option" :class="{ selected: form.physical_exam.neck.jugular_pulse === y }"
                                       @click="form.physical_exam.neck.jugular_pulse = y">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.neck.jugular_pulse === y }"><div class="radio-inner"></div></div>{{ y }}
                                </label>
                            </div>
                        </div>
                        <div><label class="field-label">Tráquea</label>
                            <input v-model="form.physical_exam.neck.trachea" class="field-input" type="text" placeholder="Central, móvil…"/></div>
                        <div class="col-full">
                            <label class="field-label">Glándula Tiroides</label>
                            <select v-model="form.physical_exam.neck.thyroid" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option>No palpable ni visible</option>
                                <option>Palpable pero no visible</option>
                                <option>Visible solo a la hiperextensión</option>
                                <option>Visible a simple vista</option>
                            </select>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ═══ 10. TÓRAX ════════════════════════════════ -->
        <div class="exam-section" :class="{ open: openSection === 'thorax' }">
            <div class="exam-header" @click="toggle('thorax')">
                <div class="exam-title"><span class="exam-icon">💨</span> Tórax</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="form.physical_exam.thorax?._normal" class="dln-badge">✓ DLN</span>
                    <span class="exam-chevron">{{ openSection === 'thorax' ? '▲' : '▼' }}</span>
                </div>
            </div>
            <Transition name="expand">
                <div v-if="openSection === 'thorax'" class="exam-body">
                    <div class="dln-bar">
                        <button type="button" class="btn-dln" @click="setNormal(form, 'thorax')">✓ DLN</button>
                        <button type="button" class="btn-altered" @click="setAltered(form, 'thorax')">⚠ Alterado</button>
                    </div>
                    <div class="form-grid grid-3">
                        <div>
                            <label class="field-label">Simetría</label>
                            <input v-model="form.physical_exam.thorax.symmetry" class="field-input" type="text" placeholder="Simétrico / causa…"/>
                        </div>
                        <div>
                            <label class="field-label">Tipo de cuerpo</label>
                            <select v-model="form.physical_exam.thorax.body_type" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option>Normolínea</option><option>Brevilínea</option><option>Longilínea</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Configuración</label>
                            <select v-model="form.physical_exam.thorax.configuration" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option>Esténico</option><option>Hipoesténico</option><option>Hiperesténico</option>
                                <option>En tonel</option><option>Hundido</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Expansibilidad</label>
                            <select v-model="form.physical_exam.thorax.expansibility" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option>Conservada</option><option>Disminuida</option><option>Aumentada</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Tipo de respiración</label>
                            <select v-model="form.physical_exam.thorax.breathing_type" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option>Abdominal</option><option>Costal superior</option>
                                <option>Costo-abdominal</option><option>Diafragmática</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Tiraje intercostal</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="t in ['Presente','Ausente']" :key="t"
                                       class="radio-option" :class="{ selected: form.physical_exam.thorax.intercostal_retraction === t }"
                                       @click="form.physical_exam.thorax.intercostal_retraction = t">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.thorax.intercostal_retraction === t }"><div class="radio-inner"></div></div>{{ t }}
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Vibraciones vocales</label>
                            <input v-model="form.physical_exam.thorax.vocal_vibrations" class="field-input" type="text" placeholder="Conservadas / alteradas, localización…"/>
                        </div>
                        <div>
                            <label class="field-label">Murmullo vesicular</label>
                            <input v-model="form.physical_exam.thorax.vesicular_murmur" class="field-input" type="text" placeholder="Conservado / aumentado / disminuido, localización…"/>
                        </div>
                        <div>
                            <label class="field-label">Ruidos agregados</label>
                            <input v-model="form.physical_exam.thorax.added_sounds" class="field-input" type="text" placeholder="Crepitantes, sibilantes, roncus — fase e inspiración…"/>
                        </div>
                        <div class="col-full">
                            <label class="field-label">Sonoridad pulmonar</label>
                            <input v-model="form.physical_exam.thorax.sonority" class="field-input" type="text"
                                   placeholder="Conservada / reemplazada por (matidez, submatidez, timpanismo)…"/>
                        </div>
                        <div class="col-full">
                            <label class="field-label">Circulación venosa colateral</label>
                            <input v-model="form.physical_exam.thorax.venous_circulation" class="field-input" type="text"
                                   placeholder="Presente / ausente — localización, dirección del flujo…"/>
                        </div>
                        <div>
                            <label class="field-label">Aleteo nasal</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="a in ['Presente','Ausente']" :key="a"
                                       class="radio-option" :class="{ selected: form.physical_exam.thorax.nasal_flaring === a }"
                                       @click="form.physical_exam.thorax.nasal_flaring = a">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.thorax.nasal_flaring === a }">
                                        <div class="radio-inner"></div>
                                    </div>{{ a }}
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Dolor a la palpación</label>
                            <input v-model="form.physical_exam.thorax.palpation_pain" class="field-input" type="text"
                                   placeholder="Superficial / profunda — localización…"/>
                        </div>
                        <div class="col-full">
                            <label class="field-label">Murmullo laringotraqueal</label>
                            <input v-model="form.physical_exam.thorax.laryngotracheal_murmur" class="field-input" type="text"
                                   placeholder="Conservado / alterado (aumentado / disminuido) — localización…"/>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ═══ 11. CARDIOVASCULAR ═══════════════════════ -->
        <div class="exam-section" :class="{ open: openSection === 'cardiovascular' }">
            <div class="exam-header" @click="toggle('cardiovascular')">
                <div class="exam-title"><span class="exam-icon">❤️</span> Cardiovascular</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="form.physical_exam.cardiovascular?._normal" class="dln-badge">✓ DLN</span>
                    <span class="exam-chevron">{{ openSection === 'cardiovascular' ? '▲' : '▼' }}</span>
                </div>
            </div>
            <Transition name="expand">
                <div v-if="openSection === 'cardiovascular'" class="exam-body">
                    <div class="dln-bar">
                        <button type="button" class="btn-dln" @click="setNormal(form, 'cardiovascular')">✓ DLN</button>
                        <button type="button" class="btn-altered" @click="setAltered(form, 'cardiovascular')">⚠ Alterado</button>
                    </div>
                    <div class="form-grid grid-3">
                        <div>
                            <label class="field-label">Ápex visible</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="a in ['Visible','No visible']" :key="a"
                                       class="radio-option" :class="{ selected: form.physical_exam.cardiovascular.apex_visible === a }"
                                       @click="form.physical_exam.cardiovascular.apex_visible = a">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.cardiovascular.apex_visible === a }"><div class="radio-inner"></div></div>{{ a }}
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Ápex palpable</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="a in ['Palpable','No palpable']" :key="a"
                                       class="radio-option" :class="{ selected: form.physical_exam.cardiovascular.apex_palpable === a }"
                                       @click="form.physical_exam.cardiovascular.apex_palpable = a">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.cardiovascular.apex_palpable === a }"><div class="radio-inner"></div></div>{{ a }}
                                </label>
                            </div>
                        </div>
                        <div><label class="field-label">Matidez cardíaca</label>
                            <input v-model="form.physical_exam.cardiovascular.dullness" class="field-input" type="text" placeholder="Conservada / reemplazada por…"/></div>
                        <div class="col-full"><label class="field-label">1er y 2do ruido cardíaco</label>
                            <input v-model="form.physical_exam.cardiovascular.heart_sounds" class="field-input" type="text" placeholder="Presentes, rítmicos, regulares / alteraciones…"/></div>
                        <div class="col-full"><label class="field-label">Soplos</label>
                            <input v-model="form.physical_exam.cardiovascular.murmurs" class="field-input" type="text" placeholder="Ausentes / presentes en (foco, intensidad, irradiación)…"/></div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ═══ 12. MAMAS ════════════════════════════════ -->
        <div class="exam-section" :class="{ open: openSection === 'breasts' }">
            <div class="exam-header" @click="toggle('breasts')">
                <div class="exam-title"><span class="exam-icon">🫁</span> Mamas</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="form.physical_exam.breasts?._normal" class="dln-badge">✓ DLN</span>
                    <span class="exam-chevron">{{ openSection === 'breasts' ? '▲' : '▼' }}</span>
                </div>
            </div>
            <Transition name="expand">
                <div v-if="openSection === 'breasts'" class="exam-body">
                    <div class="dln-bar">
                        <button type="button" class="btn-dln" @click="setNormal(form, 'breasts')">✓ DLN</button>
                        <button type="button" class="btn-altered" @click="setAltered(form, 'breasts')">⚠ Alterado</button>
                    </div>
                    <div class="form-grid grid-3">
                        <div>
                            <label class="field-label">Tamaño</label>
                            <select v-model="form.physical_exam.breasts.size" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option>Pequeñas</option><option>Medianas</option>
                                <option>Grandes</option><option>Hipertróficas</option>
                            </select>
                        </div>
                        <div><label class="field-label">Simetría</label>
                            <input v-model="form.physical_exam.breasts.symmetry" class="field-input" type="text" placeholder="Simétricas / causa de asimetría…"/></div>
                        <div><label class="field-label">Masas</label>
                            <input v-model="form.physical_exam.breasts.masses" class="field-input" type="text" placeholder="Palpables, visibles, localización…"/></div>
                        <div><label class="field-label">Secreciones</label>
                            <input v-model="form.physical_exam.breasts.secretions" class="field-input" type="text" placeholder="Tipo, color, cantidad…"/></div>
                        <div><label class="field-label">Estado del pezón</label>
                            <input v-model="form.physical_exam.breasts.nipple" class="field-input" type="text" placeholder="Forma, simetría, color, integridad…"/></div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ═══ 13. ABDOMEN ══════════════════════════════ -->
        <div class="exam-section" :class="{ open: openSection === 'abdomen' }">
            <div class="exam-header" @click="toggle('abdomen')">
                <div class="exam-title"><span class="exam-icon">🔬</span> Abdomen</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="form.physical_exam.abdomen?._normal" class="dln-badge">✓ DLN</span>
                    <span class="exam-chevron">{{ openSection === 'abdomen' ? '▲' : '▼' }}</span>
                </div>
            </div>
            <Transition name="expand">
                <div v-if="openSection === 'abdomen'" class="exam-body">
                    <div class="dln-bar">
                        <button type="button" class="btn-dln" @click="setNormal(form, 'abdomen')">✓ DLN</button>
                        <button type="button" class="btn-altered" @click="setAltered(form, 'abdomen')">⚠ Alterado</button>
                    </div>
                    <div class="form-grid grid-3">
                        <div><label class="field-label">Forma</label>
                            <input v-model="form.physical_exam.abdomen.form" class="field-input" type="text"
                                   placeholder="Plano, excavado, globoso…"/></div>
                        <div class="col-full">
                            <label class="field-label">Circulación venosa colateral</label>
                            <input v-model="form.physical_exam.abdomen.venous_circulation" class="field-input" type="text"
                                   placeholder="Presente / ausente — localización, dirección del flujo sanguíneo…"/>
                        </div>
                        <div>
                            <label class="field-label">Protrusiones al respirar</label>
                            <input v-model="form.physical_exam.abdomen.breathing_protrusions" class="field-input" type="text"
                                   placeholder="Ausente / describir localización y tipo…"/>
                        </div>
                        <div>
                            <label class="field-label">Protrusiones a la maniobra de Valsalva</label>
                            <input v-model="form.physical_exam.abdomen.valsalva_protrusions" class="field-input" type="text"
                                   placeholder="Ausente / describir localización y tipo…"/>
                        </div>
                        <div>
                            <label class="field-label">Depresible</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="d in ['Depresible','No depresible']" :key="d"
                                       class="radio-option" :class="{ selected: form.physical_exam.abdomen.depressible === d }"
                                       @click="form.physical_exam.abdomen.depressible = d">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.abdomen.depressible === d }"><div class="radio-inner"></div></div>{{ d }}
                                </label>
                            </div>
                        </div>
                        <div><label class="field-label">Ruidos hidroaéreos (x minuto)</label>
                            <input v-model="form.physical_exam.abdomen.bowel_sounds" class="field-input" type="text"
                                   placeholder="Presente, cantidad, localización…"/></div>
                        <div>
                            <label class="field-label">Ruidos vasculares</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="r in ['Presentes','Ausentes']" :key="r"
                                       class="radio-option"
                                       :class="{ selected: form.physical_exam.abdomen.vascular_sounds === r }"
                                       @click="form.physical_exam.abdomen.vascular_sounds = r">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.abdomen.vascular_sounds === r }">
                                        <div class="radio-inner"></div>
                                    </div>{{ r }}
                                </label>
                            </div>
                        </div>
                        <div><label class="field-label">Timpanismo abdominal</label>
                            <input v-model="form.physical_exam.abdomen.tympanism" class="field-input" type="text" placeholder="Conservado / reemplazado por…"/></div>
                        <div><label class="field-label">Matidez hepática</label>
                            <input v-model="form.physical_exam.abdomen.hepatic_dullness" class="field-input" type="text" placeholder="Conservada / reemplazada por…"/></div>
                        <div><label class="field-label">Dolor a la palpación</label>
                            <input v-model="form.physical_exam.abdomen.palpation_pain" class="field-input" type="text" placeholder="Superficial / profunda, localización…"/></div>
                        <div><label class="field-label">Masas</label>
                            <input v-model="form.physical_exam.abdomen.masses" class="field-input" type="text" placeholder="Palpables, visibles, localización…"/></div>

                        <!-- Hepatometría -->
                        <div class="col-full">
                            <div class="subsection-label">Hepatometría (cm)</div>
                            <div class="form-grid grid-3">
                                <div><label class="field-label">Línea paraesternal derecha</label>
                                    <input v-model="form.physical_exam.abdomen.hepatometry_parasternal" class="field-input" type="text" placeholder="___ cm"/></div>
                                <div><label class="field-label">Línea medioclavicular derecha</label>
                                    <input v-model="form.physical_exam.abdomen.hepatometry_midclavicular" class="field-input" type="text" placeholder="___ cm"/></div>
                                <div><label class="field-label">Línea axilar anterior derecha</label>
                                    <input v-model="form.physical_exam.abdomen.hepatometry_axillary" class="field-input" type="text" placeholder="___ cm"/></div>
                            </div>
                        </div>

                        <div class="col-full">
                            <label class="field-label">Dolor a la puñopercusión</label>
                            <input v-model="form.physical_exam.abdomen.kidney_punch" class="field-input" type="text" placeholder="Negativo bilateral / positivo en (derecho/izquierdo)…"/>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ═══ 14. GENITAL ══════════════════════════════ -->
        <div class="exam-section" :class="{ open: openSection === 'genital' }">
            <div class="exam-header" @click="toggle('genital')">
                <div class="exam-title"><span class="exam-icon">⚕️</span> Examen Genital</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="form.physical_exam.genital?._normal" class="dln-badge">✓ DLN</span>
                    <span class="exam-chevron">{{ openSection === 'genital' ? '▲' : '▼' }}</span>
                </div>
            </div>
            <Transition name="expand">
                <div v-if="openSection === 'genital'" class="exam-body">
                    <div class="dln-bar">
                        <button type="button" class="btn-dln" @click="setNormal(form, 'genital')">✓ DLN</button>
                        <button type="button" class="btn-altered" @click="setAltered(form, 'genital')">⚠ Alterado</button>
                    </div>

                    <!-- Tipo de examen -->
                    <div style="margin-bottom:14px">
                        <label class="field-label">Tipo de examen genital</label>
                        <div class="radio-group" style="margin-top:4px">
                            <label v-for="t in ['Masculino','Femenino','No aplica']" :key="t"
                                   class="radio-option" :class="{ selected: form.physical_exam.genital.gender_type === t }"
                                   @click="form.physical_exam.genital.gender_type = t">
                                <div class="radio-dot" :class="{ sel: form.physical_exam.genital.gender_type === t }"><div class="radio-inner"></div></div>{{ t }}
                            </label>
                        </div>
                    </div>

                    <!-- Masculino -->
                    <div v-if="form.physical_exam.genital.gender_type === 'Masculino'" class="form-grid grid-3">
                        <div><label class="field-label">Testículos</label>
                            <input v-model="form.physical_exam.genital.testes" class="field-input" type="text" placeholder="Simetría, forma, masas, aumento de volumen…"/></div>
                        <div><label class="field-label">Escroto</label>
                            <input v-model="form.physical_exam.genital.scrotum" class="field-input" type="text" placeholder="Aumento de volumen, integridad…"/></div>
                        <div><label class="field-label">Lesiones (escroto y pene)</label>
                            <input v-model="form.physical_exam.genital.lesions" class="field-input" type="text" placeholder="Describir…"/></div>
                    </div>

                    <!-- Femenino -->
                    <div v-if="form.physical_exam.genital.gender_type === 'Femenino'" class="form-grid grid-3">
                        <div><label class="field-label">Vulva</label>
                            <input v-model="form.physical_exam.genital.vulva" class="field-input" type="text" placeholder="Color, tumoración, vello…"/></div>
                        <div><label class="field-label">Himen</label>
                            <input v-model="form.physical_exam.genital.hymen" class="field-input" type="text" placeholder="Forma…"/></div>
                        <div><label class="field-label">Vagina</label>
                            <input v-model="form.physical_exam.genital.vagina" class="field-input" type="text" placeholder="Estado de la mucosa, flujo…"/></div>
                        <div><label class="field-label">Prolapsos</label>
                            <input v-model="form.physical_exam.genital.prolapse" class="field-input" type="text" placeholder="Ausentes / describir…"/></div>
                        <div><label class="field-label">Cérvix</label>
                            <input v-model="form.physical_exam.genital.cervix" class="field-input" type="text" placeholder="Color, secreciones, orificio externo…"/></div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ═══ 15. EXAMEN RECTAL ════════════════════════ -->
        <div class="exam-section" :class="{ open: openSection === 'rectal_exam' }">
            <div class="exam-header" @click="toggle('rectal_exam')">
                <div class="exam-title"><span class="exam-icon">🩺</span> Examen Rectal</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="form.physical_exam.rectal_exam?._normal" class="dln-badge">✓ DLN</span>
                    <span class="exam-chevron">{{ openSection === 'rectal_exam' ? '▲' : '▼' }}</span>
                </div>
            </div>
            <Transition name="expand">
                <div v-if="openSection === 'rectal_exam'" class="exam-body">
                    <div class="dln-bar">
                        <button type="button" class="btn-dln" @click="setNormal(form, 'rectal_exam')">✓ DLN</button>
                        <button type="button" class="btn-altered" @click="setAltered(form, 'rectal_exam')">⚠ Alterado</button>
                    </div>
                    <div class="form-grid grid-2">
                        <div><label class="field-label">Estado del ano</label>
                            <input v-model="form.physical_exam.rectal_exam.anus" class="field-input" type="text" placeholder="Fisuras, hemorroides, fístulas…"/></div>
                        <div><label class="field-label">Tacto rectal</label>
                            <input v-model="form.physical_exam.rectal_exam.rectal_touch" class="field-input" type="text" placeholder="Tono del esfínter, estrecheces…"/></div>
                        <div><label class="field-label">Próstata</label>
                            <input v-model="form.physical_exam.rectal_exam.prostate" class="field-input" type="text" placeholder="Tamaño, consistencia, dolorosa…"/></div>
                        <div><label class="field-label">Masas</label>
                            <input v-model="form.physical_exam.rectal_exam.masses" class="field-input" type="text" placeholder="Palpables, localización…"/></div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ═══ 16. NEUROLÓGICO ══════════════════════════ -->
        <div class="exam-section" :class="{ open: openSection === 'neurological' }">
            <div class="exam-header" @click="toggle('neurological')">
                <div class="exam-title"><span class="exam-icon">🧬</span> Neurológico — Glasgow + Pares Craneales</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="form.physical_exam.neurological?._normal" class="dln-badge">✓ DLN</span>
                    <span class="exam-chevron">{{ openSection === 'neurological' ? '▲' : '▼' }}</span>
                </div>
            </div>
            <Transition name="expand">
                <div v-if="openSection === 'neurological'" class="exam-body">
                    <div class="dln-bar">
                        <button type="button" class="btn-dln" @click="setNormal(form, 'neurological')">✓ DLN</button>
                        <button type="button" class="btn-altered" @click="setAltered(form, 'neurological')">⚠ Alterado</button>
                    </div>

                    <!-- Glasgow -->
                    <div class="subsection-label">Escala de Glasgow</div>
                    <div class="form-grid grid-3" style="margin-bottom:16px">
                        <div>
                            <label class="field-label">Apertura Ocular</label>
                            <select v-model="form.physical_exam.neurological.glasgow_eye" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option v-for="g in glasgowEye" :key="g" :value="g">{{ g }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Respuesta Verbal</label>
                            <select v-model="form.physical_exam.neurological.glasgow_verbal" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option v-for="g in glasgowVerbal" :key="g" :value="g">{{ g }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Respuesta Motora</label>
                            <select v-model="form.physical_exam.neurological.glasgow_motor" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option v-for="g in glasgowMotor" :key="g" :value="g">{{ g }}</option>
                            </select>
                        </div>
                        <!-- Total Glasgow calculado reactivo -->
                        <div class="glasgow-total">
                            <div class="glasgow-value">
                                {{ ([1,2,3,4].includes(Number((form.physical_exam.neurological.glasgow_eye||'0').charAt(0))) ? Number((form.physical_exam.neurological.glasgow_eye||'0').charAt(0)) : 0)
                                 + ([1,2,3,4,5].includes(Number((form.physical_exam.neurological.glasgow_verbal||'0').charAt(0))) ? Number((form.physical_exam.neurological.glasgow_verbal||'0').charAt(0)) : 0)
                                 + ([1,2,3,4,5,6].includes(Number((form.physical_exam.neurological.glasgow_motor||'0').charAt(0))) ? Number((form.physical_exam.neurological.glasgow_motor||'0').charAt(0)) : 0)
                                 || '—' }}/15
                            </div>
                            <div class="glasgow-label">Total Glasgow</div>
                        </div>
                    </div>

                    <!-- Funciones Mentales Superiores -->
                    <div class="subsection-label">Funciones Mentales Superiores</div>
                    <div class="form-grid grid-3" style="margin-bottom:16px">
                        <div>
                            <label class="field-label">Nivel de conciencia</label>
                            <select v-model="form.physical_exam.neurological.consciousness_level" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option v-for="c in consciousnessLevel" :key="c" :value="c">{{ c }}</option>
                            </select>
                        </div>
                        <div><label class="field-label">Orientación</label>
                            <input v-model="form.physical_exam.neurological.consciousness_state" class="field-input" type="text" placeholder="Tiempo, espacio, persona…"/></div>
                        <div><label class="field-label">Lenguaje</label>
                            <input v-model="form.physical_exam.neurological.language" class="field-input" type="text" placeholder="Afasia, disfonía, disartria…"/></div>
                        <div><label class="field-label">Pensamiento</label>
                            <input v-model="form.physical_exam.neurological.thought" class="field-input" type="text" placeholder="Bradipsiquia, ideas delictivas, alucinaciones…"/></div>
                        <div><label class="field-label">Memoria</label>
                            <input v-model="form.physical_exam.neurological.memory" class="field-input" type="text" placeholder="Inmediata, mediata, remota…"/></div>
                        <div><label class="field-label">Capacidad de cálculo</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="c in ['Conservada','Disminuida']" :key="c"
                                       class="radio-option" :class="{ selected: form.physical_exam.neurological.calculation === c }"
                                       @click="form.physical_exam.neurological.calculation = c">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.neurological.calculation === c }"><div class="radio-inner"></div></div>{{ c }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Motor -->
                    <div class="subsection-label">Función Motora</div>
                    <div class="form-grid grid-3" style="margin-bottom:16px">
                        <div>
                            <label class="field-label">Fuerza EESS</label>
                            <select v-model="form.physical_exam.neurological.upper_strength" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option v-for="m in motorScale" :key="m" :value="m">{{ m }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Fuerza EEII</label>
                            <select v-model="form.physical_exam.neurological.lower_strength" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option v-for="m in motorScale" :key="m" :value="m">{{ m }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Babinski</label>
                            <div class="radio-group" style="margin-top:4px">
                                <label v-for="b in ['Positivo','Negativo']" :key="b"
                                       class="radio-option" :class="{ selected: form.physical_exam.neurological.babinski === b }"
                                       @click="form.physical_exam.neurological.babinski = b">
                                    <div class="radio-dot" :class="{ sel: form.physical_exam.neurological.babinski === b }"><div class="radio-inner"></div></div>{{ b }}
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">ROT Bicipital</label>
                            <select v-model="form.physical_exam.neurological.reflex_bicipital" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option v-for="r in reflexScale" :key="r" :value="r">{{ r }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">ROT Estilorradial</label>
                            <select v-model="form.physical_exam.neurological.reflex_styloradial" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option v-for="r in reflexScale" :key="r" :value="r">{{ r }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">ROT Patelar</label>
                            <select v-model="form.physical_exam.neurological.reflex_patellar" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option v-for="r in reflexScale" :key="r" :value="r">{{ r }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">ROT Aquíleo</label>
                            <select v-model="form.physical_exam.neurological.reflex_achilles" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option v-for="r in reflexScale" :key="r" :value="r">{{ r }}</option>
                            </select>
                        </div>
                        <div><label class="field-label">Movimientos involuntarios</label>
                            <input v-model="form.physical_exam.neurological.involuntary_movements" class="field-input" type="text" placeholder="Ausentes / temblor, corea, mioclonías…"/></div>
                    </div>

                    <!-- Pares Craneales -->
                    <div class="subsection-label">Pares Craneales (I – XII)</div>
                    <div class="cn-grid">
                        <div v-for="cn in [
                            { key:'cn_i',       num:'I',       name:'Olfatorio',                         ph:'Conservado / anosmia, hiposmia…' },
                            { key:'cn_ii',      num:'II',      name:'Óptico',                            ph:'Agudeza visual, campimetría, visión de colores…' },
                            { key:'cn_iii_iv_vi',num:'III/IV/VI',name:'Oculomotores',                   ph:'Tamaño pupilar, reflejo fotomotor, movimientos oculares, ptosis…' },
                            { key:'cn_v',       num:'V',       name:'Trigémino',                         ph:'Sensibilidad y motricidad facial, reflejo corneal…' },
                            { key:'cn_vii',     num:'VII',     name:'Facial',                            ph:'Simetría facial, movimientos, degustación…' },
                            { key:'cn_viii',    num:'VIII',    name:'Vestibulococlear',                  ph:'Audición, Weber, Rinne OI/OD…' },
                            { key:'cn_ix_x',    num:'IX/X',    name:'Glosofaríngeo / Vago',             ph:'Músculos faríngeos, velo del paladar, úvula, reflejo nauseoso…' },
                            { key:'cn_xi',      num:'XI',      name:'Accesorio',                         ph:'ECM y trapecio tónicos / hipotónico…' },
                            { key:'cn_xii',     num:'XII',     name:'Hipogloso',                         ph:'Movimientos lengua, tono, simetría, fasciculaciones…' },
                        ]" :key="cn.key" class="cn-row">
                            <div class="cn-num">{{ cn.num }}</div>
                            <div style="flex:1">
                                <div class="cn-name">{{ cn.name }}</div>
                                <input v-model="form.physical_exam.neurological[cn.key]" class="field-input" type="text" :placeholder="cn.ph" style="margin-top:4px"/>
                            </div>
                        </div>
                    </div>

                </div>
            </Transition>
        </div>

        <!-- ═══ 17. EXTREMIDADES ═════════════════════════ -->
        <div class="exam-section" :class="{ open: openSection === 'extremities' }">
            <div class="exam-header" @click="toggle('extremities')">
                <div class="exam-title"><span class="exam-icon">🦿</span> Extremidades</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="form.physical_exam.extremities?._normal" class="dln-badge">✓ DLN</span>
                    <span class="exam-chevron">{{ openSection === 'extremities' ? '▲' : '▼' }}</span>
                </div>
            </div>
            <Transition name="expand">
                <div v-if="openSection === 'extremities'" class="exam-body">
                    <div class="dln-bar">
                        <button type="button" class="btn-dln" @click="setNormal(form, 'extremities')">✓ DLN</button>
                        <button type="button" class="btn-altered" @click="setAltered(form, 'extremities')">⚠ Alterado</button>
                    </div>
                    <div class="form-grid grid-3">
                        <div><label class="field-label">Simetría</label>
                            <input v-model="form.physical_exam.extremities.symmetry" class="field-input" type="text" placeholder="Simétricas / causa…"/></div>
                        <div><label class="field-label">Venas varicosas</label>
                            <input v-model="form.physical_exam.extremities.varicose_veins" class="field-input" type="text" placeholder="Presentes/ausentes, localización, tipo, diámetro…"/></div>
                        <div><label class="field-label">Edema</label>
                            <input v-model="form.physical_exam.extremities.edema" class="field-input" type="text" placeholder="Signos de Celso, fóvea, localización…"/></div>
                        <div><label class="field-label">Pulso periférico</label>
                            <input v-model="form.physical_exam.extremities.peripheral_pulse" class="field-input" type="text" placeholder="Carotídeo, radial, pedio — simetría, amplitud…"/></div>
                        <div><label class="field-label">Movilidad</label>
                            <input v-model="form.physical_exam.extremities.mobility" class="field-input" type="text" placeholder="Activa/pasiva, conservada/alterada…"/></div>
                        <div><label class="field-label">Tono muscular</label>
                            <select v-model="form.physical_exam.extremities.muscle_tone" class="field-select">
                                <option value="">Seleccionar…</option>
                                <option>Normal</option>
                                <option>Hipotonía</option>
                                <option>Hipertonía</option>
                            </select>
                        </div>
                        <div><label class="field-label">Parestesias</label>
                            <input v-model="form.physical_exam.extremities.paresthesias" class="field-input" type="text" placeholder="Describir…"/></div>
                        <div><label class="field-label">Dolor</label>
                            <input v-model="form.physical_exam.extremities.pain" class="field-input" type="text" placeholder="Localización, tipo, irradiación…"/></div>
                        <div><label class="field-label">Deformidad</label>
                            <input v-model="form.physical_exam.extremities.deformity" class="field-input" type="text" placeholder="Describir…"/></div>
                    </div>
                </div>
            </Transition>
        </div>

    </div><!-- /exam-accordion -->
</template>

<style scoped>
/* ── ACCORDION ───────────────────────────────────────────── */
.exam-accordion { display: flex; flex-direction: column; gap: 6px; }

.exam-section {
    border: 1.5px solid #E2E8F0; border-radius: 10px;
    background: #fff; overflow: hidden; transition: border-color 0.18s;
}
.exam-section.open { border-color: #93C5FD; }

.exam-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 13px 16px; cursor: pointer; user-select: none;
    transition: background 0.15s;
}
.exam-header:hover { background: #F8FAFC; }
.exam-section.open .exam-header { background: #EFF6FF; }

.exam-title {
    display: flex; align-items: center; gap: 10px;
    font-size: 13px; font-weight: 600; color: #0F172A;
}
.exam-icon {
    width: 30px; height: 30px; border-radius: 7px;
    background: #F1F5F9; display: flex; align-items: center;
    justify-content: center; font-size: 15px; flex-shrink: 0;
}
.exam-chevron { font-size: 10px; color: #94A3B8; }

.exam-body { padding: 16px; border-top: 1px solid #E2E8F0; }

/* ── DLN BAR ─────────────────────────────────────────────── */
.dln-bar {
    display: flex; gap: 8px; margin-bottom: 14px;
    padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;
}
.btn-dln {
    padding: 5px 14px; border-radius: 6px; font-size: 12px; font-weight: 600;
    cursor: pointer; border: 1.5px solid #6EE7B7;
    background: #ECFDF5; color: #065F46; font-family: inherit; transition: all 0.15s;
}
.btn-dln:hover { background: #D1FAE5; }
.btn-altered {
    padding: 5px 14px; border-radius: 6px; font-size: 12px; font-weight: 600;
    cursor: pointer; border: 1.5px solid #FCA5A5;
    background: #FEF2F2; color: #991B1B; font-family: inherit; transition: all 0.15s;
}
.btn-altered:hover { background: #FEE2E2; }
.dln-badge {
    font-size: 10px; font-weight: 700; background: #D1FAE5;
    color: #065F46; padding: 2px 8px; border-radius: 10px;
}

/* ── SUBSECTION ──────────────────────────────────────────── */
.subsection-label {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.1em; color: #64748B;
    padding: 8px 0 6px; margin-bottom: 10px;
    border-bottom: 1px dashed #E2E8F0;
}

/* ── GLASGOW TOTAL ───────────────────────────────────────── */
.glasgow-total {
    background: linear-gradient(135deg, #EFF6FF, #E0F2FE);
    border: 1.5px solid #BFDBFE; border-radius: 10px;
    padding: 12px; text-align: center;
}
.glasgow-value {
    font-size: 28px; font-weight: 800;
    font-family: 'DM Mono', monospace; color: #2563EB; line-height: 1;
}
.glasgow-label { font-size: 10px; color: #64748B; margin-top: 3px; }

/* ── PARES CRANEALES ─────────────────────────────────────── */
.cn-grid { display: flex; flex-direction: column; gap: 8px; }
.cn-row  {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 10px; background: #F8FAFC;
    border: 1px solid #E2E8F0; border-radius: 8px;
}
.cn-num {
    width: 44px; height: 28px; border-radius: 6px; flex-shrink: 0;
    background: #DBEAFE; color: #1D4ED8;
    font-size: 11px; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    font-family: 'DM Mono', monospace;
}
.cn-name { font-size: 11px; font-weight: 700; color: #374151; }

/* ── FORM PRIMITIVES ─────────────────────────────────────── */
.form-grid  { display: grid; gap: 12px; }
.grid-2     { grid-template-columns: repeat(2, 1fr); }
.grid-3     { grid-template-columns: repeat(3, 1fr); }
.col-full   { grid-column: 1 / -1; }
@media (max-width: 800px) {
    .grid-2,.grid-3 { grid-template-columns: 1fr 1fr; }
    .col-full { grid-column: 1 / -1; }
}
@media (max-width: 560px) {
    .grid-2,.grid-3 { grid-template-columns: 1fr; }
}

.field-label {
    display: block; font-size: 11.5px; font-weight: 600;
    color: #374151; margin-bottom: 4px;
}
.field-input, .field-select, .field-textarea {
    width: 100%; padding: 7px 11px;
    border: 1.5px solid #E2E8F0; border-radius: 8px;
    font-size: 12.5px; font-family: inherit; color: #0F172A;
    background: #fff; outline: none;
    transition: border-color 0.18s, box-shadow 0.18s; appearance: none;
}
.field-input:focus,.field-select:focus,.field-textarea:focus {
    border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
.field-input::placeholder,.field-textarea::placeholder { color: #CBD5E1; }
.field-textarea { resize: vertical; min-height: 60px; }
.field-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px;
}

.radio-group  { display: flex; flex-wrap: wrap; gap: 6px; }
.radio-option {
    display: flex; align-items: center; gap: 5px;
    padding: 5px 10px; border-radius: 7px;
    border: 1.5px solid #E2E8F0; cursor: pointer;
    font-size: 12px; transition: all 0.15s; user-select: none;
}
.radio-option:hover   { border-color: #93C5FD; background: #EFF6FF; }
.radio-option.selected{ border-color: #2563EB; background: #EFF6FF; color: #2563EB; font-weight: 600; }
.radio-dot { width: 13px; height: 13px; border-radius: 50%; border: 2px solid #CBD5E1; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.15s; }
.radio-dot.sel { border-color: #2563EB; background: #2563EB; }
.radio-inner { width: 4px; height: 4px; border-radius: 50%; background: #fff; }

.inline-alert {
    padding: 10px 14px; border-radius: 8px; font-size: 12px; font-weight: 500;
    display: flex; align-items: flex-start; gap: 8px;
}
.alert-info { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }

.expand-enter-active, .expand-leave-active {
    transition: all 0.25s cubic-bezier(.4,0,.2,1); overflow: hidden;
}
.expand-enter-from, .expand-leave-to { max-height: 0; opacity: 0; }
.expand-enter-to, .expand-leave-from { max-height: 3000px; opacity: 1; }
</style>
