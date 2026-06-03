/**
 * Convierte el estado del formulario Vue ↔ payload de StorePatientRequest / UpdatePatientRequest.
 */

export const DISABILITY_TYPE_MAP = {
  Mentales: 1,
  Visuales: 2,
  Auditivas: 3,
  Dolor: 4,
  'Voz y habla': 5,
  'Cardiovascular y respiratorio': 6,
  'Hematológica e inmunológica': 7,
  'Genitourinaria y reproductora': 8,
  'Digestivo, metabólico y endocrino': 9,
  'Neuro músculo-esqueléticos': 10,
  'Piel y otras estructuras': 11,
}

export const DISABILITY_LABELS = Object.fromEntries(
  Object.entries(DISABILITY_TYPE_MAP).map(([label, code]) => [code, label])
)

const DISABILITY_CODE_TO_LABEL = { ...DISABILITY_LABELS }

function toInt(value) {
  if (value === '' || value === null || value === undefined) return null
  const n = parseInt(String(value), 10)
  return Number.isNaN(n) ? null : n
}

function toBool(value) {
  if (typeof value === 'boolean') return value
  if (value === 'true' || value === 1 || value === '1') return true
  if (value === 'false' || value === 0 || value === '0') return false
  return Boolean(value)
}

function parseSince(text) {
  if (!text) return { value: null, unit: null }
  const m = String(text).trim().match(/^(\d+)\s*(d[ií]as|meses|a[nñ]os)?/i)
  if (!m) return { value: null, unit: null }
  let unit = (m[2] || 'años').toLowerCase()
  if (unit.startsWith('d')) unit = 'días'
  if (unit.startsWith('a')) unit = 'años'
  return { value: parseInt(m[1], 10), unit }
}

function formatSince(value, unit) {
  if (value == null || !unit) return ''
  return `${value} ${unit}`
}

function mapHospitalization(value) {
  if (typeof value === 'boolean') return value
  const v = String(value || '').toLowerCase()
  if (!v) return false
  if (v.includes('sin')) return false
  if (v.includes('con')) return true
  return false
}

function hospitalizationLabel(value) {
  if (value === true) return 'Con hospitalización'
  if (value === false) return 'Sin hospitalización'
  return ''
}

function mapFamilyMember(member) {
  const raw = String(member?.status || '').trim()
  if (!raw || raw.toLowerCase() === 'desconoce') {
    return { unknown: true, status: null, age: null, pathology: null }
  }
  return {
    unknown: false,
    status: raw.toLowerCase().includes('fallec') ? 'fallecido' : 'vivo',
    age: toInt(member.age),
    pathology: member.pathology || null,
  }
}

function familyMemberToForm(member, feminine = false) {
  if (!member || member.unknown || member.status == null || member.status === '') {
    return { unknown: false, status: '', age: '', pathology: '' }
  }
  const dead = member.status === 'fallecido'
  let status = ''
  if (dead) status = feminine ? 'Fallecida' : 'Fallecido'
  else status = feminine ? 'Viva' : 'Vivo'

  return {
    unknown: false,
    status,
    age: member.age ?? '',
    pathology: member.pathology ?? '',
  }
}

function mapSiblingsOrChildren(block) {
  const female = toInt(block?.female_count) ?? 0
  const male = toInt(block?.male_count) ?? 0
  const quantity = female + male
  const statusRaw = String(block?.status || '').trim()
  const unknown =
    Boolean(block?.not_apply) ||
    (quantity === 0 && !statusRaw && !block?.pathology)

  return {
    unknown,
    quantity: unknown ? null : quantity,
    female_count: female,
    male_count: male,
    status: statusRaw || null,
    pathology: block?.pathology || null,
  }
}

function siblingsOrChildrenToForm(block) {
  if (!block || block.unknown) {
    return {
      not_apply: false,
      female_count: '',
      male_count: '',
      status: '',
      pathology: '',
    }
  }
  return {
    not_apply: false,
    female_count: block.female_count ?? '',
    male_count: block.male_count ?? '',
    status: block.status ?? '',
    pathology: block.pathology ?? '',
  }
}

function mapImmuneChildhoodStatus(immunological) {
  const s = String(immunological?.childhood_status || '').toLowerCase()
  if (s.startsWith('niega')) return 'niega'
  if (s.startsWith('comp')) return 'completa'
  if (s.startsWith('inc')) return 'incompleta'
  return 'niega'
}

function immuneToForm(bg) {
  const status = bg?.immune_childhood_status || 'niega'
  return {
    childhood_status:
      status === 'completa'
        ? 'Completa'
        : status === 'incompleta'
          ? 'Incompleta'
          : 'Niega',
    missing_vaccines: bg?.immune_missing_vaccines ?? '',
    adult_vaccines: bg?.immune_adult_vaccines ?? '',
    adult_age: bg?.immune_adult_age ?? '',
    complications: bg?.immune_complications ?? '',
  }
}

function mapNutritionType(nutrition) {
  const t = String(nutrition?.type || '').toLowerCase()
  if (t.includes('predominio')) return 'predominio'
  return 'balanceada'
}

function nutritionToForm(nutrition) {
  if (!nutrition) {
    return {
      type: 'Balanceada',
      predominance_description: '',
      meals_count: '',
      appetite: 'Conservado',
    }
  }
  return {
    type: nutrition.type === 'predominio' ? 'A predominio de' : 'Balanceada',
    predominance_description: nutrition.predominance_description ?? '',
    meals_count: nutrition.meals_count ?? '',
    appetite: nutrition.appetite ?? 'Conservado',
  }
}

function mapSleepType(sleep) {
  if (sleep?.interrupted) return 'interrumpido'
  const t = String(sleep?.type || '').toLowerCase()
  if (t.includes('diurn')) return 'diurno'
  if (t.includes('insomnio') && t.includes('inicial')) return 'insomnio_inicial'
  if (t.includes('insomnio') && t.includes('terminal')) return 'insomnio_terminal'
  if (t.includes('interrump')) return 'interrumpido'
  return 'nocturno'
}

function sleepToForm(sleep) {
  if (!sleep) {
    return {
      type: 'Nocturno',
      hours: '',
      frequency_per_day: '',
      interrupted: false,
      medication: '',
      siesta_duration_min: '',
      siesta_frequency_per_day: '',
    }
  }
  const typeMap = {
    nocturno: 'Nocturno',
    diurno: 'Diurno',
    insomnio_inicial: 'Nocturno',
    insomnio_terminal: 'Nocturno',
    interrumpido: 'Nocturno',
  }
  return {
    type: typeMap[sleep.type] || 'Nocturno',
    hours: sleep.hours ?? '',
    frequency_per_day: sleep.frequency_per_day ?? '',
    interrupted: Boolean(sleep.interrupted) || sleep.type === 'interrumpido',
    medication: sleep.medication ?? '',
    siesta_duration_min: sleep.siesta_duration_min ?? '',
    siesta_frequency_per_day: sleep.siesta_frequency_per_day ?? '',
  }
}

function disabilityTypesToForm(bg) {
  const codes = bg?.disability_types?.length
    ? bg.disability_types
    : bg?.disability_type
      ? [bg.disability_type]
      : []
  return codes.map((c) => DISABILITY_CODE_TO_LABEL[c]).filter(Boolean)
}

function mapDisabilityTypes(types) {
  return (types || [])
    .map((label) => DISABILITY_TYPE_MAP[label])
    .filter((n) => n != null)
}

function mapBackground(bg) {
  const pathSince = parseSince(bg.pathological?.since)
  const disabilitySince = parseSince(bg.disability?.since)
  const disabilityTypes = mapDisabilityTypes(bg.disability?.types)
  const immuneStatus = mapImmuneChildhoodStatus(bg.immunological || {})

  return {
    allergies_deny: toBool(bg.allergic?.deny),
    allergies_description: bg.allergic?.description || null,

    pathological_deny: toBool(bg.pathological?.deny),
    pathological_disease: bg.pathological?.disease || null,
    pathological_onset_value: pathSince.value,
    pathological_onset_unit: pathSince.unit,
    pathological_controlled: bg.pathological?.deny
      ? false
      : String(bg.pathological?.state || '').trim().toLowerCase() === 'controlado',
    pathological_treatment: bg.pathological?.treatment || null,

    infectious_deny: toBool(bg.infectious?.deny),
    infectious_disease: bg.infectious?.infection || null,
    infectious_age: toInt(bg.infectious?.age),
    infectious_treatment: bg.infectious?.treatment || null,
    infectious_hospitalization: mapHospitalization(bg.infectious?.hospitalization),
    infectious_complications: bg.infectious?.complications || null,

    immune_deny_vaccination: immuneStatus === 'niega',
    immune_childhood_status: immuneStatus,
    immune_missing_vaccines: bg.immunological?.missing_vaccines || null,
    immune_adult_vaccines: bg.immunological?.adult_vaccines || null,
    immune_adult_age: toInt(bg.immunological?.adult_age),
    immune_complications: bg.immunological?.complications || null,

    transfusion_deny: toBool(bg.transfusion?.deny),
    transfusion_age: toInt(bg.transfusion?.age),
    transfusion_type: bg.transfusion?.blood_type_used || null,
    transfusion_bags_count: toInt(bg.transfusion?.bags_count),
    transfusion_reason: bg.transfusion?.reason || null,

    obgyn_apply: !toBool(bg.gynecological?.not_apply),
    obgyn_gestas: bg.gynecological?.gestas || null,
    obgyn_partos: bg.gynecological?.deliveries || null,
    obgyn_cesareas: bg.gynecological?.cesareans || null,
    obgyn_abortos: bg.gynecological?.abortions || null,
    obgyn_menarche: bg.gynecological?.menarche || null,
    obgyn_menopause: bg.gynecological?.menopause || null,
    obgyn_cycle_periodicity: bg.gynecological?.cycle_period || null,
    obgyn_cycle_duration: bg.gynecological?.cycle_duration || null,
    obgyn_cycle_pads_per_day: toInt(bg.gynecological?.pads_per_day),
    obgyn_fur: bg.gynecological?.last_period || null,

    surgical_deny: toBool(bg.surgical?.deny),
    surgical_intervention: bg.surgical?.surgery || null,
    surgical_age: toInt(bg.surgical?.age),
    surgical_complications: bg.surgical?.complications || null,

    traumatic_deny: toBool(bg.traumatic?.deny),
    traumatic_fracture: bg.traumatic?.injury || null,
    traumatic_age: toInt(bg.traumatic?.age),
    traumatic_treatment: bg.traumatic?.treatment || null,
    traumatic_complications: bg.traumatic?.complications || null,

    std_deny: toBool(bg.ets?.deny),
    std_disease: bg.ets?.disease || null,
    std_age: toInt(bg.ets?.age),
    std_treatment: bg.ets?.treatment || null,
    std_hospitalization: mapHospitalization(bg.ets?.hospitalization),
    std_complications: bg.ets?.complications || null,

    epidemiological_deny: toBool(bg.epidemiological?.deny),
    epidem_destination: bg.epidemiological?.destination || null,
    epidem_start_date: bg.epidemiological?.departure_date || null,
    epidem_end_date: bg.epidemiological?.return_date || null,
    epidem_biome: bg.epidemiological?.biome || null,

    disability_deny: toBool(bg.disability?.deny),
    disability_types: disabilityTypes.length ? disabilityTypes : null,
    disability_type: disabilityTypes[0] ?? null,
    disability_specific_name: bg.disability?.description || null,
    disability_onset_value: disabilitySince.value,
    disability_onset_unit: disabilitySince.unit,
    disability_pharmacological_treatment: bg.disability?.treatment || null,
  }
}

function backgroundToForm(bg) {
  if (!bg) return defaultBackground()
  return {
    allergic: {
      deny: Boolean(bg.allergies_deny),
      description: bg.allergies_description ?? '',
    },
    pathological: {
      deny: Boolean(bg.pathological_deny),
      disease: bg.pathological_disease ?? '',
      since: formatSince(bg.pathological_onset_value, bg.pathological_onset_unit),
      state: bg.pathological_controlled ? 'Controlado' : 'No Controlado',
      treatment: bg.pathological_treatment ?? '',
    },
    infectious: {
      deny: Boolean(bg.infectious_deny),
      infection: bg.infectious_disease ?? '',
      age: bg.infectious_age ?? '',
      treatment: bg.infectious_treatment ?? '',
      hospitalization: hospitalizationLabel(bg.infectious_hospitalization),
      complications: bg.infectious_complications ?? '',
    },
    immunological: immuneToForm(bg),
    transfusion: {
      deny: Boolean(bg.transfusion_deny),
      age: bg.transfusion_age ?? '',
      blood_type_used: bg.transfusion_type ?? '',
      bags_count: bg.transfusion_bags_count ?? '',
      reason: bg.transfusion_reason ?? '',
    },
    gynecological: {
      not_apply: !bg.obgyn_apply,
      gestas: bg.obgyn_gestas ?? '',
      deliveries: bg.obgyn_partos ?? '',
      cesareans: bg.obgyn_cesareas ?? '',
      abortions: bg.obgyn_abortos ?? '',
      menarche: bg.obgyn_menarche ?? '',
      menopause: bg.obgyn_menopause ?? '',
      cycle_period: bg.obgyn_cycle_periodicity ?? '',
      cycle_duration: bg.obgyn_cycle_duration ?? '',
      pads_per_day: bg.obgyn_cycle_pads_per_day ?? '',
      last_period: bg.obgyn_fur ? String(bg.obgyn_fur).slice(0, 10) : '',
    },
    surgical: {
      deny: Boolean(bg.surgical_deny),
      surgery: bg.surgical_intervention ?? '',
      age: bg.surgical_age ?? '',
      complications: bg.surgical_complications ?? '',
    },
    traumatic: {
      deny: Boolean(bg.traumatic_deny),
      injury: bg.traumatic_fracture ?? '',
      age: bg.traumatic_age ?? '',
      treatment: bg.traumatic_treatment ?? '',
      complications: bg.traumatic_complications ?? '',
    },
    ets: {
      deny: Boolean(bg.std_deny),
      disease: bg.std_disease ?? '',
      age: bg.std_age ?? '',
      treatment: bg.std_treatment ?? '',
      hospitalization: hospitalizationLabel(bg.std_hospitalization),
      complications: bg.std_complications ?? '',
    },
    epidemiological: {
      deny: Boolean(bg.epidemiological_deny),
      destination: bg.epidem_destination ?? '',
      departure_date: bg.epidem_start_date ? String(bg.epidem_start_date).slice(0, 10) : '',
      return_date: bg.epidem_end_date ? String(bg.epidem_end_date).slice(0, 10) : '',
      biome: bg.epidem_biome ?? '',
    },
    disability: {
      deny: Boolean(bg.disability_deny),
      types: disabilityTypesToForm(bg),
      description: bg.disability_specific_name ?? '',
      since: formatSince(bg.disability_onset_value, bg.disability_onset_unit),
      treatment: bg.disability_pharmacological_treatment ?? '',
    },
  }
}

function mapFamilyBackground(fb) {
  return {
    mother: mapFamilyMember(fb.mother),
    father: mapFamilyMember(fb.father),
    grandmother_maternal: mapFamilyMember(fb.grandmother_maternal),
    grandfather_maternal: mapFamilyMember(fb.grandfather_maternal),
    grandmother_paternal: mapFamilyMember(fb.grandmother_paternal),
    grandfather_paternal: mapFamilyMember(fb.grandfather_paternal),
    siblings: mapSiblingsOrChildren(fb.siblings),
    children: mapSiblingsOrChildren(fb.children),
  }
}

function familyBackgroundToForm(fb) {
  if (!fb) return defaultFamilyBackground()
  return {
    grandmother_maternal: familyMemberToForm(fb.grandmother_maternal, true),
    grandfather_maternal: familyMemberToForm(fb.grandfather_maternal, false),
    grandmother_paternal: familyMemberToForm(fb.grandmother_paternal, true),
    grandfather_paternal: familyMemberToForm(fb.grandfather_paternal, false),
    mother: familyMemberToForm(fb.mother, true),
    father: familyMemberToForm(fb.father, false),
    siblings: siblingsOrChildrenToForm(fb.siblings),
    children: siblingsOrChildrenToForm(fb.children),
  }
}

function mapHousingAnimals(animals) {
  const legacy = animals?.type_location
  return {
    quantity: toInt(animals?.quantity),
    intradomiciliary: toBool(animals?.intradomiciliary ?? legacy === 'Intradomiciliario'),
    extradomiciliary: toBool(animals?.extradomiciliary ?? legacy === 'Extradomiciliario'),
  }
}

function animalsToForm(animals) {
  if (!animals) {
    return { quantity: '', intradomiciliary: false, extradomiciliary: false }
  }
  if (animals.intradomiciliary != null || animals.extradomiciliary != null) {
    return {
      quantity: animals.quantity ?? '',
      intradomiciliary: Boolean(animals.intradomiciliary),
      extradomiciliary: Boolean(animals.extradomiciliary),
    }
  }
  const loc = animals.type_location
  return {
    quantity: animals.quantity ?? '',
    intradomiciliary: loc === 'Intradomiciliario',
    extradomiciliary: loc === 'Extradomiciliario',
  }
}

function mapHabits(habits) {
  return {
    alcohol: {
      deny: toBool(habits.alcohol?.deny),
      start_age: toInt(habits.alcohol?.start_age),
      end_age: habits.alcohol?.end_age || null,
      type: habits.alcohol?.type || null,
      quantity_ml: toInt(habits.alcohol?.quantity_ml),
      frequency_days: habits.alcohol?.frequency_days || null,
      gets_drunk: toBool(habits.alcohol?.gets_drunk),
    },
    tobacco: {
      deny: toBool(habits.tobacco?.deny),
      start_age: toInt(habits.tobacco?.start_age),
      end_age: habits.tobacco?.end_age || null,
      cigarettes_per_day: toInt(habits.tobacco?.cigarettes_per_day),
      boxes_per_year: toInt(habits.tobacco?.boxes_per_year),
    },
    coffee: {
      deny: toBool(habits.coffee?.deny),
      start_age: toInt(habits.coffee?.start_age),
      end_age: habits.coffee?.end_age || null,
      quantity_ml: toInt(habits.coffee?.quantity_ml),
      type: habits.coffee?.type || null,
    },
    drugs: {
      deny: toBool(habits.drugs?.deny),
      start_age: toInt(habits.drugs?.start_age),
      end_age: habits.drugs?.end_age || null,
      route: habits.drugs?.route || null,
      frequency_per_day: habits.drugs?.frequency_per_day || null,
    },
    physical_activity: {
      type: habits.physical_activity?.type || null,
      times_per_week: toInt(habits.physical_activity?.times_per_week),
      minutes_per_day: toInt(habits.physical_activity?.minutes_per_day),
    },
    sleep: {
      type: mapSleepType(habits.sleep || {}),
      hours: toInt(habits.sleep?.hours) ?? 0,
      frequency_per_day: toInt(habits.sleep?.frequency_per_day),
      interrupted: toBool(habits.sleep?.interrupted),
      medication: habits.sleep?.medication || null,
      siesta_duration_min: toInt(habits.sleep?.siesta_duration_min),
      siesta_frequency_per_day: toInt(habits.sleep?.siesta_frequency_per_day),
    },
    nutrition: {
      type: mapNutritionType(habits.nutrition || {}),
      predominance_description: habits.nutrition?.predominance_description || null,
      meals_count: toInt(habits.nutrition?.meals_count),
      appetite: habits.nutrition?.appetite || null,
    },
    sexual_habits: {
      active: toBool(habits.sexual_habits?.active),
      sexarche_age: toInt(habits.sexual_habits?.sexarche_age),
      partners_count: toInt(habits.sexual_habits?.partners_count),
      orientation: habits.sexual_habits?.orientation || null,
      frequency_per_week: toInt(habits.sexual_habits?.frequency_per_week),
      contraceptive_method: habits.sexual_habits?.contraceptive_method || null,
    },
    gastrointestinal: { ...habits.gastrointestinal },
    genitourinary: { ...habits.genitourinary },
    housing: {
      floor_material: habits.housing?.floor_material || '',
      roof_material: habits.housing?.roof_material || '',
      walls_material: habits.housing?.walls_material || '',
      rooms_count: toInt(habits.housing?.rooms_count) || 1,
      habitants_count: toInt(habits.housing?.habitants_count) || 1,
      services: {
        water: toBool(habits.housing?.services?.water),
        electricity: toBool(habits.housing?.services?.electricity),
        gas: toBool(habits.housing?.services?.gas),
        waste_collection: toBool(habits.housing?.services?.waste_collection),
      },
      animals: mapHousingAnimals(habits.housing?.animals),
    },
  }
}

function habitsToForm(habits) {
  if (!habits) {
    return {
      alcohol: { deny: true, start_age: '', end_age: '', type: '', quantity_ml: '', frequency_days: '', gets_drunk: false },
      coffee: { deny: true, start_age: '', end_age: '', quantity_ml: '', type: '' },
      tobacco: { deny: true, start_age: '', end_age: '', cigarettes_per_day: '', boxes_per_year: '' },
      drugs: { deny: true, start_age: '', end_age: '', route: '', frequency_per_day: '' },
      physical_activity: { type: '', times_per_week: '', minutes_per_day: '' },
      sleep: sleepToForm(null),
      nutrition: nutritionToForm(null),
      sexual_habits: { active: false, sexarche_age: '', partners_count: '', orientation: '', frequency_per_week: '', contraceptive_method: '' },
      gastrointestinal: { evacuations_count: '', frequency_unit: 'Por día', color: '', odor: '', bristol_scale: '4', shape: '' },
      genitourinary: { urinations_count: '', color: '', odor: '', predominance: 'Matutino' },
      housing: {
        floor_material: '', roof_material: '', walls_material: '',
        rooms_count: '', habitants_count: '',
        services: { water: false, electricity: false, gas: false, waste_collection: false },
        animals: { quantity: '', intradomiciliary: false, extradomiciliary: false },
      },
    }
  }
  const h = habits
  return {
    alcohol: {
      deny: h.alcohol?.deny ?? true,
      start_age: h.alcohol?.start_age ?? '',
      end_age: h.alcohol?.end_age ?? '',
      type: h.alcohol?.type ?? '',
      quantity_ml: h.alcohol?.quantity_ml ?? '',
      frequency_days: h.alcohol?.frequency_days ?? '',
      gets_drunk: h.alcohol?.gets_drunk ?? false,
    },
    coffee: {
      deny: h.coffee?.deny ?? true,
      start_age: h.coffee?.start_age ?? '',
      end_age: h.coffee?.end_age ?? '',
      quantity_ml: h.coffee?.quantity_ml ?? '',
      type: h.coffee?.type ?? '',
    },
    tobacco: {
      deny: h.tobacco?.deny ?? true,
      start_age: h.tobacco?.start_age ?? '',
      end_age: h.tobacco?.end_age ?? '',
      cigarettes_per_day: h.tobacco?.cigarettes_per_day ?? '',
      boxes_per_year: h.tobacco?.boxes_per_year ?? '',
    },
    drugs: {
      deny: h.drugs?.deny ?? true,
      start_age: h.drugs?.start_age ?? '',
      end_age: h.drugs?.end_age ?? '',
      route: h.drugs?.route ?? '',
      frequency_per_day: h.drugs?.frequency_per_day ?? '',
    },
    physical_activity: {
      type: h.physical_activity?.type ?? '',
      times_per_week: h.physical_activity?.times_per_week ?? '',
      minutes_per_day: h.physical_activity?.minutes_per_day ?? '',
    },
    sleep: sleepToForm(h.sleep),
    nutrition: nutritionToForm(h.nutrition),
    sexual_habits: {
      active: h.sexual_habits?.active ?? false,
      sexarche_age: h.sexual_habits?.sexarche_age ?? '',
      partners_count: h.sexual_habits?.partners_count ?? '',
      orientation: h.sexual_habits?.orientation ?? '',
      frequency_per_week: h.sexual_habits?.frequency_per_week ?? '',
      contraceptive_method: h.sexual_habits?.contraceptive_method ?? '',
    },
    gastrointestinal: {
      evacuations_count: h.gastrointestinal?.evacuations_count ?? '',
      frequency_unit: h.gastrointestinal?.frequency_unit ?? 'Por día',
      color: h.gastrointestinal?.color ?? '',
      odor: h.gastrointestinal?.odor ?? '',
      bristol_scale: String(h.gastrointestinal?.bristol_scale ?? '4'),
      shape: h.gastrointestinal?.shape ?? '',
    },
    genitourinary: {
      urinations_count: h.genitourinary?.urinations_count ?? '',
      color: h.genitourinary?.color ?? '',
      odor: h.genitourinary?.odor ?? '',
      predominance: h.genitourinary?.predominance ?? 'Matutino',
    },
    housing: {
      floor_material: h.housing?.floor_material ?? '',
      roof_material: h.housing?.roof_material ?? '',
      walls_material: h.housing?.walls_material ?? '',
      rooms_count: h.housing?.rooms_count ?? '',
      habitants_count: h.housing?.habitants_count ?? '',
      services: {
        water: h.housing?.services?.water ?? false,
        electricity: h.housing?.services?.electricity ?? false,
        gas: h.housing?.services?.gas ?? false,
        waste_collection: h.housing?.services?.waste_collection ?? false,
      },
      animals: animalsToForm(h.housing?.animals),
    },
  }
}

export function defaultBackground() {
  return {
    allergic: { deny: true, description: '' },
    pathological: { deny: true, disease: '', since: '', state: 'Controlado', treatment: '' },
    infectious: { deny: true, infection: '', age: '', treatment: '', hospitalization: '', complications: '' },
    immunological: {
      childhood_status: 'Niega',
      missing_vaccines: '',
      adult_vaccines: '',
      adult_age: '',
      complications: '',
    },
    transfusion: { deny: true, age: '', blood_type_used: '', bags_count: '', reason: '' },
    gynecological: {
      not_apply: false,
      gestas: '',
      deliveries: '',
      cesareans: '',
      abortions: '',
      menarche: '',
      menopause: '',
      cycle_period: '',
      cycle_duration: '',
      pads_per_day: '',
      last_period: '',
    },
    surgical: { deny: true, surgery: '', age: '', complications: '' },
    traumatic: { deny: true, injury: '', age: '', treatment: '', complications: '' },
    ets: { deny: true, disease: '', age: '', treatment: '', hospitalization: '', complications: '' },
    epidemiological: { deny: true, destination: '', departure_date: '', return_date: '', biome: '' },
    disability: { deny: true, types: [], description: '', since: '', treatment: '' },
  }
}

export function defaultFamilyBackground() {
  return {
    grandmother_maternal: { unknown: false, status: '', age: '', pathology: '' },
    grandfather_maternal: { unknown: false, status: '', age: '', pathology: '' },
    grandmother_paternal: { unknown: false, status: '', age: '', pathology: '' },
    grandfather_paternal: { unknown: false, status: '', age: '', pathology: '' },
    mother: { unknown: false, status: '', age: '', pathology: '' },
    father: { unknown: false, status: '', age: '', pathology: '' },
    siblings: { not_apply: false, female_count: '', male_count: '', status: '', pathology: '' },
    children: { not_apply: false, female_count: '', male_count: '', status: '', pathology: '' },
  }
}

export function defaultHabits() {
  return habitsToForm(null)
}

export function buildPatientStorePayload(data) {
  return {
    full_name: data.full_name,
    id_number: data.id_number,
    nationality: data.nationality,
    nationality_country: data.nationality_country || null,
    gender: data.gender,
    birth_date: data.birth_date,
    birth_place: data.birth_place,
    marital_status_id: data.marital_status_id,
    ethnicity_id: data.ethnicity_id,
    instruction_level_id: data.instruction_level_id,
    occupation_id: data.occupation_id,
    occupation_detail: data.occupation_detail || null,
    religion_id: data.religion_id,
    religion_detail: data.religion_detail || null,
    knows_blood_type: toBool(data.knows_blood_type),
    blood_type: data.blood_type,
    rh_factor: data.rh_factor,
    phone_number: data.phone_number,
    addr_state: data.addr_state,
    addr_municipality: data.addr_municipality,
    addr_parish: data.addr_parish,
    addr_locality: data.addr_locality,
    addr_sector: data.addr_sector,
    addr_street: data.addr_street,
    addr_house_number: data.addr_house_number,
    addr_zip_code: data.addr_zip_code,
    addr_reference: data.addr_reference,
    residence_time: data.residence_time,
    background: mapBackground(data.background || {}),
    family_background: mapFamilyBackground(data.family_background || {}),
    habits: mapHabits(data.habits || {}),
  }
}

export function patientToFormState(patient) {
  const knowsBlood =
    patient.blood_type && patient.blood_type !== 'Desconoce'

  return {
    full_name: patient.full_name ?? '',
    id_number: patient.id_number ?? '',
    nationality: patient.nationality ?? 'V',
    nationality_country: patient.nationality_country ?? '',
    gender: patient.gender ?? '',
    birth_date: patient.birth_date ? String(patient.birth_date).slice(0, 10) : '',
    birth_place: patient.birth_place ?? '',
    marital_status_id: patient.marital_status_id ?? '',
    ethnicity_id: patient.ethnicity_id ?? '',
    instruction_level_id: patient.instruction_level_id ?? '',
    occupation_id: patient.occupation_id ?? '',
    occupation_detail: patient.occupation_detail ?? '',
    religion_id: patient.religion_id ?? '',
    religion_detail: patient.religion_detail ?? '',
    knows_blood_type: knowsBlood,
    blood_type: knowsBlood ? patient.blood_type : '',
    rh_factor: knowsBlood ? patient.rh_factor ?? '' : '',
    phone_number: patient.phone_number ?? '',
    addr_state: patient.addr_state ?? '',
    addr_municipality: patient.addr_municipality ?? '',
    addr_parish: patient.addr_parish ?? '',
    addr_locality: patient.addr_locality ?? '',
    addr_sector: patient.addr_sector ?? '',
    addr_street: patient.addr_street ?? '',
    addr_house_number: patient.addr_house_number ?? '',
    addr_zip_code: patient.addr_zip_code ?? '',
    addr_reference: patient.addr_reference ?? '',
    residence_time: patient.residence_time ?? '',
    background: backgroundToForm(patient.patient_background),
    family_background: familyBackgroundToForm(patient.family_background),
    habits: habitsToForm(patient.psychobiological_habit),
  }
}

/** Índice de hacinamiento: habitantes ÷ habitaciones (≥ 2 = hacinamiento). */
export function overcrowdingIndex(rooms, habitants) {
  const r = parseFloat(rooms) || 0
  const h = parseFloat(habitants) || 0
  if (r <= 0 || h <= 0) return { index: null, overloaded: false }
  const idx = h / r
  return {
    index: idx,
    display: idx.toFixed(2),
    overloaded: idx >= 2,
    formula: `${h} hab. ÷ ${r} hab.`,
  }
}
