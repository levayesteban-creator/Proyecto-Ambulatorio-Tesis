<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { DISABILITY_LABELS, overcrowdingIndex } from '@/utils/patientFormPayload';

const props = defineProps({
    patient: { type: Object, required: true },
    consultations: { type: Array, default: () => [] },
});

const bg = computed(() => props.patient.patient_background);
const fb = computed(() => props.patient.family_background);
const habits = computed(() => props.patient.psychobiological_habit);

const formatDate = (value) => {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('es-VE', { year: 'numeric', month: 'long', day: 'numeric' });
};

const genderLabel = (g) => ({ M: 'Masculino', F: 'Femenino', O: 'Otro' }[g] || g);

const bloodLabel = computed(() => {
    const p = props.patient;
    if (!p.blood_type || p.blood_type === 'Desconoce') return 'Desconoce';
    return `${p.blood_type}${p.rh_factor || ''}`;
});

const occupationLabel = computed(() => {
    const base = props.patient.occupation?.name || '—';
    if (props.patient.occupation_detail) {
        return `${base}: ${props.patient.occupation_detail}`;
    }
    return base;
});

const religionLabel = computed(() => {
    const base = props.patient.religion?.name || '—';
    if (props.patient.religion_detail) {
        return `${base}: ${props.patient.religion_detail}`;
    }
    return base;
});

const fullAddress = computed(() => {
    const p = props.patient;
    return [
        p.addr_sector,
        p.addr_street,
        p.addr_house_number ? `Nº ${p.addr_house_number}` : null,
        p.addr_locality,
        p.addr_parish,
        p.addr_municipality,
        p.addr_state,
    ].filter(Boolean).join(', ') || '—';
});

const hacinamiento = computed(() => {
    const h = habits.value?.housing;
    if (!h?.rooms_count || !h?.habitants_count) return null;
    const result = overcrowdingIndex(h.rooms_count, h.habitants_count);
    return result.index != null
        ? { idx: result.display, overloaded: result.overloaded }
        : null;
});

const disabilityLabels = computed(() => {
    if (!bg.value || bg.value.disability_deny) return [];
    const codes = bg.value.disability_types?.length
        ? bg.value.disability_types
        : bg.value.disability_type
            ? [bg.value.disability_type]
            : [];
    return codes.map((c) => DISABILITY_LABELS[c] || c);
});

const animalsLabel = computed(() => {
    const a = habits.value?.housing?.animals;
    if (!a?.quantity) return null;
    const parts = [];
    if (a.intradomiciliary) parts.push('intradomiciliario');
    if (a.extradomiciliary) parts.push('extradomiciliario');
    const loc = parts.length ? ` (${parts.join(', ')})` : '';
    return `${a.quantity} animal(es)${loc}`;
});

const familyMemberLabel = (member) => {
    if (!member || member.unknown) return 'Desconoce';
    const status = member.status === 'fallecido' ? 'Fallecido(a)' : 'Vivo(a)';
    const age = member.age != null ? `, ${member.age} años` : '';
    const path = member.pathology ? ` — ${member.pathology}` : '';
    return `${status}${age}${path}`;
};

const siblingsChildrenLabel = (block) => {
    if (!block || block.unknown) return 'Desconoce';
    let text = `Cantidad ${block.quantity} (♀ ${block.female_count} / ♂ ${block.male_count})`;
    if (block.status) text += `. Estado: ${block.status}`;
    if (block.pathology) text += `. ${block.pathology}`;
    return text;
};

const denyOr = (deny, text) => (deny ? 'Niega' : (text || '—'));
</script>

<template>
    <Head :title="'Ficha - ' + patient.full_name" />

    <AppLayout :title="patient.full_name">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Ficha Clínica: {{ patient.full_name }}
            </h2>
        </template>

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white p-6 shadow rounded-lg border-l-4 border-blue-600 flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-blue-600 bg-blue-50 px-2 py-1 rounded">
                        Historia Patronímica
                    </span>
                    <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ patient.full_name }}</h1>
                    <p class="text-sm text-gray-500">
                        {{ patient.nationality }}-{{ patient.id_number }} · {{ genderLabel(patient.gender) }}
                        <span v-if="patient.age != null"> · {{ patient.age }} años</span>
                    </p>
                </div>
                <div class="mt-4 md:mt-0 flex flex-wrap gap-2">
                    <Link :href="route('patients.index')" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Listado
                    </Link>
                    <Link :href="route('patients.edit', patient.id)" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-md text-sm font-medium shadow-sm">
                        Editar Ficha
                    </Link>
                    <Link :href="route('consultations.create', patient.id)" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium shadow-sm">
                        + Nueva Consulta
                    </Link>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide border-b pb-2 mb-4">Ficha Patronímica</h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-500">Fecha de nacimiento</dt><dd class="font-medium">{{ formatDate(patient.birth_date) }}</dd></div>
                    <div><dt class="text-gray-500">Lugar de nacimiento</dt><dd class="font-medium">{{ patient.birth_place || '—' }}</dd></div>
                    <div><dt class="text-gray-500">Nacionalidad</dt><dd class="font-medium">{{ patient.nationality_country || '—' }}</dd></div>
                    <div><dt class="text-gray-500">Estado civil</dt><dd class="font-medium">{{ patient.marital_status?.name || '—' }}</dd></div>
                    <div><dt class="text-gray-500">Etnia</dt><dd class="font-medium">{{ patient.ethnicity ? `${patient.ethnicity.code}. ${patient.ethnicity.name}` : '—' }}</dd></div>
                    <div><dt class="text-gray-500">Grado de instrucción</dt><dd class="font-medium">{{ patient.instruction_level ? `${patient.instruction_level.code}. ${patient.instruction_level.name}` : '—' }}</dd></div>
                    <div><dt class="text-gray-500">Profesión / oficio</dt><dd class="font-medium">{{ occupationLabel }}</dd></div>
                    <div><dt class="text-gray-500">Religión</dt><dd class="font-medium">{{ religionLabel }}</dd></div>
                    <div><dt class="text-gray-500">Grupo sanguíneo</dt><dd class="font-medium text-red-700">{{ bloodLabel }}</dd></div>
                    <div><dt class="text-gray-500">Teléfono</dt><dd class="font-medium">{{ patient.phone_number || '—' }}</dd></div>
                    <div class="md:col-span-2 lg:col-span-3"><dt class="text-gray-500">Dirección</dt><dd class="font-medium">{{ fullAddress }}</dd></div>
                    <div v-if="patient.addr_zip_code"><dt class="text-gray-500">Código postal</dt><dd class="font-medium">{{ patient.addr_zip_code }}</dd></div>
                    <div v-if="patient.addr_reference"><dt class="text-gray-500">Punto de referencia</dt><dd class="font-medium">{{ patient.addr_reference }}</dd></div>
                    <div v-if="patient.residence_time"><dt class="text-gray-500">Tiempo de residencia</dt><dd class="font-medium">{{ patient.residence_time }}</dd></div>
                </dl>
            </div>

            <div v-if="bg" class="bg-white shadow rounded-lg p-6">
                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide border-b pb-2 mb-4">Antecedentes Personales</h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500 font-medium">Alérgicos</dt>
                        <dd>{{ denyOr(bg.allergies_deny, bg.allergies_description) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Patológicos</dt>
                        <dd>
                            <template v-if="bg.pathological_deny">Niega</template>
                            <template v-else>
                                {{ bg.pathological_disease }}
                                <span v-if="bg.pathological_onset_value"> (desde {{ bg.pathological_onset_value }} {{ bg.pathological_onset_unit }})</span>
                                <span v-if="bg.pathological_controlled != null"> — {{ bg.pathological_controlled ? 'Controlado' : 'No controlado' }}</span>
                                <span v-if="bg.pathological_treatment">. Tratamiento: {{ bg.pathological_treatment }}</span>
                            </template>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Infectocontagiosos</dt>
                        <dd>
                            <template v-if="bg.infectious_deny">Niega</template>
                            <template v-else>
                                {{ bg.infectious_disease }} (edad {{ bg.infectious_age ?? '—' }}).
                                {{ bg.infectious_treatment }}.
                                {{ bg.infectious_hospitalization ? 'Con' : 'Sin' }} hospitalización.
                                <span v-if="bg.infectious_complications"> Complicaciones: {{ bg.infectious_complications }}</span>
                            </template>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Inmunológicos</dt>
                        <dd>
                            <span v-if="bg.immune_childhood_status === 'niega' || bg.immune_deny_vaccination">Niega vacunación</span>
                            <span v-else>Vacunación infantil {{ bg.immune_childhood_status }}</span>
                            <span v-if="bg.immune_missing_vaccines">. Ausente: {{ bg.immune_missing_vaccines }}</span>
                            <span v-if="bg.immune_adult_vaccines">. Adulto: {{ bg.immune_adult_vaccines }}</span>
                            <span v-if="bg.immune_adult_age"> (edad {{ bg.immune_adult_age }})</span>
                            <span v-if="bg.immune_complications">. Complicaciones: {{ bg.immune_complications }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Transfusionales</dt>
                        <dd>
                            <template v-if="bg.transfusion_deny">Niega</template>
                            <template v-else>
                                Edad {{ bg.transfusion_age }}, tipo {{ bg.transfusion_type }}, {{ bg.transfusion_bags_count }} bolsa(s). {{ bg.transfusion_reason }}
                            </template>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Gineco-obstétricos</dt>
                        <dd>
                            <template v-if="!bg.obgyn_apply">No aplica</template>
                            <template v-else>
                                G{{ bg.obgyn_gestas }} P{{ bg.obgyn_partos }} C{{ bg.obgyn_cesareas }} A{{ bg.obgyn_abortos }}.
                                Menarquía: {{ bg.obgyn_menarche || '—' }}.
                                Menopausia: {{ bg.obgyn_menopause || '—' }}.
                                Ciclo: {{ bg.obgyn_cycle_periodicity || '—' }}, {{ bg.obgyn_cycle_duration || '—' }},
                                {{ bg.obgyn_cycle_pads_per_day ?? '—' }} toallas/día.
                                FUR: {{ formatDate(bg.obgyn_fur) }}.
                            </template>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Quirúrgicos</dt>
                        <dd>{{ denyOr(bg.surgical_deny, `${bg.surgical_intervention} (edad ${bg.surgical_age})`) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Traumáticos</dt>
                        <dd>
                            <template v-if="bg.traumatic_deny">Niega</template>
                            <template v-else>
                                {{ bg.traumatic_fracture }} (edad {{ bg.traumatic_age }}). {{ bg.traumatic_treatment }}.
                                <span v-if="bg.traumatic_complications">Complicaciones: {{ bg.traumatic_complications }}</span>
                            </template>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">ETS</dt>
                        <dd>
                            <template v-if="bg.std_deny">Niega</template>
                            <template v-else>
                                {{ bg.std_disease }} (edad {{ bg.std_age }}). {{ bg.std_treatment }}.
                                {{ bg.std_hospitalization ? 'Con' : 'Sin' }} hospitalización.
                                <span v-if="bg.std_complications"> Complicaciones: {{ bg.std_complications }}</span>
                            </template>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Epidemiológicos</dt>
                        <dd>
                            <template v-if="bg.epidemiological_deny">Niega</template>
                            <template v-else>
                                {{ bg.epidem_destination }} ({{ formatDate(bg.epidem_start_date) }} – {{ formatDate(bg.epidem_end_date) }}). Bioma: {{ bg.epidem_biome }}
                            </template>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Discapacidades</dt>
                        <dd>
                            <template v-if="bg.disability_deny">Niega</template>
                            <template v-else>
                                {{ disabilityLabels.join(', ') || '—' }}
                                <span v-if="bg.disability_specific_name"> — {{ bg.disability_specific_name }}</span>
                                <span v-if="bg.disability_onset_value"> (desde {{ bg.disability_onset_value }} {{ bg.disability_onset_unit }})</span>
                                <span v-if="bg.disability_pharmacological_treatment">. Tratamiento: {{ bg.disability_pharmacological_treatment }}</span>
                            </template>
                        </dd>
                    </div>
                </dl>
            </div>

            <div v-if="fb" class="bg-white shadow rounded-lg p-6">
                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide border-b pb-2 mb-4">Antecedentes Familiares</h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-gray-500">Abuela materna</dt><dd>{{ familyMemberLabel(fb.grandmother_maternal) }}</dd></div>
                    <div><dt class="text-gray-500">Abuelo materno</dt><dd>{{ familyMemberLabel(fb.grandfather_maternal) }}</dd></div>
                    <div><dt class="text-gray-500">Abuela paterna</dt><dd>{{ familyMemberLabel(fb.grandmother_paternal) }}</dd></div>
                    <div><dt class="text-gray-500">Abuelo paterno</dt><dd>{{ familyMemberLabel(fb.grandfather_paternal) }}</dd></div>
                    <div><dt class="text-gray-500">Madre</dt><dd>{{ familyMemberLabel(fb.mother) }}</dd></div>
                    <div><dt class="text-gray-500">Padre</dt><dd>{{ familyMemberLabel(fb.father) }}</dd></div>
                    <div><dt class="text-gray-500">Hermanos</dt><dd>{{ siblingsChildrenLabel(fb.siblings) }}</dd></div>
                    <div><dt class="text-gray-500">Hijos</dt><dd>{{ siblingsChildrenLabel(fb.children) }}</dd></div>
                </dl>
            </div>

            <div v-if="habits" class="bg-white shadow rounded-lg p-6">
                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide border-b pb-2 mb-4">Hábitos Psicobiológicos y Vivienda</h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div v-if="habits.alcohol">
                        <dt class="text-gray-500 font-medium">Alcohol</dt>
                        <dd>
                            <template v-if="habits.alcohol.deny">Niega</template>
                            <template v-else>
                                Desde {{ habits.alcohol.start_age }} años{{ habits.alcohol.end_age ? ` hasta ${habits.alcohol.end_age}` : '' }}.
                                {{ habits.alcohol.type }}, {{ habits.alcohol.quantity_ml }} ml cada {{ habits.alcohol.frequency_days }} día(s).
                                {{ habits.alcohol.gets_drunk ? 'Se emborracha.' : 'No se emborracha.' }}
                            </template>
                        </dd>
                    </div>
                    <div v-if="habits.tobacco">
                        <dt class="text-gray-500 font-medium">Tabaco</dt>
                        <dd>
                            <template v-if="habits.tobacco.deny">Niega</template>
                            <template v-else>
                                {{ habits.tobacco.cigarettes_per_day }} cig/día, {{ habits.tobacco.boxes_per_year }} cajas/año
                            </template>
                        </dd>
                    </div>
                    <div v-if="habits.coffee">
                        <dt class="text-gray-500 font-medium">Café</dt>
                        <dd>
                            <template v-if="habits.coffee.deny">Niega</template>
                            <template v-else>
                                {{ habits.coffee.quantity_ml }} ml/día — {{ habits.coffee.type }}
                            </template>
                        </dd>
                    </div>
                    <div v-if="habits.drugs">
                        <dt class="text-gray-500 font-medium">Drogas ilícitas</dt>
                        <dd>{{ habits.drugs.deny ? 'Niega' : `${habits.drugs.route}, ${habits.drugs.frequency_per_day} vez/veces al día` }}</dd>
                    </div>
                    <div v-if="habits.physical_activity">
                        <dt class="text-gray-500 font-medium">Actividad física</dt>
                        <dd>{{ habits.physical_activity.type || '—' }} — {{ habits.physical_activity.times_per_week }}×/sem, {{ habits.physical_activity.minutes_per_day }} min</dd>
                    </div>
                    <div v-if="habits.sleep">
                        <dt class="text-gray-500 font-medium">Sueño</dt>
                        <dd>
                            {{ habits.sleep.hours }} h, {{ habits.sleep.type }}
                            <span v-if="habits.sleep.frequency_per_day"> ({{ habits.sleep.frequency_per_day }}×/día)</span>.
                            <span v-if="habits.sleep.interrupted"> Interrumpido.</span>
                            <span v-if="habits.sleep.medication"> Medicación: {{ habits.sleep.medication }}.</span>
                            Siestas: {{ habits.sleep.siesta_duration_min ?? 0 }} min, {{ habits.sleep.siesta_frequency_per_day ?? 0 }}×/día.
                        </dd>
                    </div>
                    <div v-if="habits.nutrition">
                        <dt class="text-gray-500 font-medium">Alimentación</dt>
                        <dd>
                            {{ habits.nutrition.type === 'predominio' ? 'A predominio de' : 'Dieta balanceada' }}
                            <span v-if="habits.nutrition.predominance_description"> {{ habits.nutrition.predominance_description }}</span>
                            — {{ habits.nutrition.meals_count }} ingestas, apetito {{ habits.nutrition.appetite }}
                        </dd>
                    </div>
                    <div v-if="habits.sexual_habits">
                        <dt class="text-gray-500 font-medium">Hábitos sexuales</dt>
                        <dd>
                            <template v-if="!habits.sexual_habits.active">Inactivo</template>
                            <template v-else>
                                Activo. Sexarquía {{ habits.sexual_habits.sexarche_age }} años.
                                {{ habits.sexual_habits.partners_count }} pareja(s).
                                {{ habits.sexual_habits.orientation }}.
                                {{ habits.sexual_habits.frequency_per_week }}×/sem.
                                Anticonceptivo: {{ habits.sexual_habits.contraceptive_method || '—' }}.
                            </template>
                        </dd>
                    </div>
                    <div v-if="habits.gastrointestinal">
                        <dt class="text-gray-500 font-medium">Gastrointestinal</dt>
                        <dd>
                            {{ habits.gastrointestinal.evacuations_count }} evac. {{ habits.gastrointestinal.frequency_unit }}.
                            Color: {{ habits.gastrointestinal.color || '—' }}.
                            Olor: {{ habits.gastrointestinal.odor || '—' }}.
                            Bristol {{ habits.gastrointestinal.bristol_scale }}.
                            Forma: {{ habits.gastrointestinal.shape || '—' }}.
                        </dd>
                    </div>
                    <div v-if="habits.genitourinary">
                        <dt class="text-gray-500 font-medium">Genitourinario</dt>
                        <dd>
                            {{ habits.genitourinary.urinations_count }} micciones/día.
                            Color: {{ habits.genitourinary.color || '—' }}.
                            Olor: {{ habits.genitourinary.odor || '—' }}.
                            {{ habits.genitourinary.predominance }}.
                        </dd>
                    </div>
                    <div v-if="habits.housing" class="md:col-span-2">
                        <dt class="text-gray-500 font-medium">Vivienda</dt>
                        <dd>
                            Piso: {{ habits.housing.floor_material }}, Techo: {{ habits.housing.roof_material }}, Paredes: {{ habits.housing.walls_material }}.
                            {{ habits.housing.rooms_count }} hab. / {{ habits.housing.habitants_count }} habitan.
                            Servicios:
                            <span v-if="habits.housing.services?.water"> Agua</span>
                            <span v-if="habits.housing.services?.electricity"> Electricidad</span>
                            <span v-if="habits.housing.services?.gas"> Gas</span>
                            <span v-if="habits.housing.services?.waste_collection"> Aseo</span>.
                            <span v-if="animalsLabel"> {{ animalsLabel }}.</span>
                            <span v-if="hacinamiento" :class="hacinamiento.overloaded ? 'text-red-600 font-semibold' : 'text-green-700'">
                                Índice hacinamiento: {{ hacinamiento.idx }}
                                {{ hacinamiento.overloaded ? ' (≥ 2 — hacinamiento)' : '' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide border-b pb-2 mb-4">Historial de Consultas</h2>
                <div v-if="consultations?.length > 0" class="space-y-4">
                    <div v-for="consulta in consultations" :key="consulta.id" class="border rounded-lg p-4 hover:bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-bold uppercase px-2 py-1 bg-blue-100 text-blue-700 rounded">
                                    {{ new Date(consulta.created_at).toLocaleDateString('es-VE') }}
                                </span>
                                <h4 class="mt-2 font-bold text-gray-700">{{ consulta.reason_for_consultation }}</h4>
                            </div>
                            <p class="text-xs text-gray-500">Dr. {{ consulta.doctor?.name }}</p>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-6 text-sm text-gray-500 italic bg-gray-50 rounded border border-dashed">
                    Sin consultas registradas.
                </div>
            </div>
        </div>
    </AppLayout>
</template>
