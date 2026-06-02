<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplicarán a la solicitud.
     */
    public function rules(): array
    {
        return [
            // ==========================================================================
            // PARTE 1: FICHA PATRONÍMICA (DATOS PERSONALES)
            // ==========================================================================

            // Identidad Básica y Filiación
            'full_name'            => ['required', 'string', 'max:255'],
            'id_number'            => ['required', 'string', 'max:20', Rule::unique('patients', 'id_number')->ignore($this->patient)],
            'nationality'          => ['required', 'string', 'max:1', Rule::in(['V', 'E'])],
            'nationality_country'  => ['nullable', 'string', 'max:100'],
            'occupation_detail'    => ['nullable', 'string', 'max:255'],
            'religion_detail'      => ['nullable', 'string', 'max:255'],
            'gender'               => ['required', Rule::in(['F', 'M', 'O'])],
            'birth_date'           => ['required', 'date', 'before:today'],

            // Lugar de Nacimiento
            'birth_place'          => ['required', 'string', 'max:255'],

            // Llaves Foráneas (Catálogos Maestros)
            'marital_status_id'    => ['required', 'exists:marital_statuses,id'],
            'ethnicity_id'         => ['required', 'exists:ethnicities,id'],
            'instruction_level_id' => ['required', 'exists:instruction_levels,id'],
            'occupation_id'        => ['required', 'exists:occupations,id'],
            'religion_id'          => ['required', 'exists:religions,id'],

            // Datos Clínicos Base
            'knows_blood_type'     => ['required', 'boolean'],
            'blood_type'           => ['required_if:knows_blood_type,true', 'nullable', Rule::in(['A', 'B', 'AB', 'O', 'Desconoce'])],
            'rh_factor'            => ['required_if:knows_blood_type,true', 'nullable', Rule::in(['+', '-'])],

            // Contacto y Dirección Estructurada
            'phone_number'         => ['nullable', 'string', 'max:20'],
            'addr_state'           => ['required', 'string', 'max:100'],
            'addr_municipality'    => ['required', 'string', 'max:100'],
            'addr_parish'          => ['required', 'string', 'max:100'],
            'addr_locality'        => ['nullable', 'string', 'max:255'],
            'addr_sector'          => ['required', 'string', 'max:255'],
            'addr_street'          => ['nullable', 'string', 'max:255'],
            'addr_house_number'    => ['nullable', 'string', 'max:100'],
            'addr_zip_code'        => ['nullable', 'string', 'max:20'],
            'addr_reference'       => ['nullable', 'string', 'max:255'],
            'residence_time'       => ['nullable', 'string', 'max:100'],

            // ==========================================================================
            // PARTE 2: HISTORIAL CLÍNICO (background)
            // ==========================================================================
            'background' => ['required', 'array'],

            // 1. Alérgicos
            'background.allergies_deny'        => ['required', 'boolean'],
            'background.allergies_description' => ['required_if:background.allergies_deny,false', 'nullable', 'string', 'max:255'],

            // 2. Patológicos
            'background.pathological_deny'        => ['required', 'boolean'],
            'background.pathological_disease'     => ['required_if:background.pathological_deny,false', 'nullable', 'string', 'max:255'],
            'background.pathological_onset_value' => ['required_if:background.pathological_deny,false', 'nullable', 'integer', 'min:0'],
            'background.pathological_onset_unit'  => ['required_if:background.pathological_deny,false', 'nullable', Rule::in(['días', 'meses', 'años'])],
            'background.pathological_controlled'  => ['required_if:background.pathological_deny,false', 'nullable', 'boolean'],
            'background.pathological_treatment'   => ['required_if:background.pathological_deny,false', 'nullable', 'string', 'max:255'],

            // 3. Infectocontagiosos
            'background.infectious_deny'            => ['required', 'boolean'],
            'background.infectious_disease'         => ['required_if:background.infectious_deny,false', 'nullable', 'string', 'max:255'],
            'background.infectious_age'             => ['required_if:background.infectious_deny,false', 'nullable', 'integer', 'min:0'],
            'background.infectious_treatment'       => ['required_if:background.infectious_deny,false', 'nullable', 'string', 'max:255'],
            'background.infectious_hospitalization' => ['required_if:background.infectious_deny,false', 'nullable', 'boolean'],
            'background.infectious_complications'   => ['nullable', 'string', 'max:255'],

            // 4. Inmunológicos
            'background.immune_deny_vaccination' => ['required', 'boolean'],
            'background.immune_childhood_status' => ['required', Rule::in(['completa', 'incompleta', 'niega'])],
            'background.immune_missing_vaccines' => ['required_if:background.immune_childhood_status,incompleta', 'nullable', 'string', 'max:255'],
            'background.immune_adult_vaccines'   => ['nullable', 'string', 'max:255'],
            'background.immune_adult_age'        => ['nullable', 'integer', 'min:0'],
            'background.immune_complications'    => ['nullable', 'string', 'max:255'],

            // 5. Transfusionales
            'background.transfusion_deny'       => ['required', 'boolean'],
            'background.transfusion_age'        => ['required_if:background.transfusion_deny,false', 'nullable', 'integer', 'min:0'],
            'background.transfusion_type'       => ['required_if:background.transfusion_deny,false', 'nullable', 'string', 'max:50'],
            'background.transfusion_bags_count' => ['required_if:background.transfusion_deny,false', 'nullable', 'integer', 'min:1'],
            'background.transfusion_reason'     => ['required_if:background.transfusion_deny,false', 'nullable', 'string', 'max:255'],

            // 6. Gineco-Obstétricos
            'background.obgyn_apply'              => ['required', 'boolean'],
            'background.obgyn_gestas'             => ['required_if:background.obgyn_apply,true', 'nullable', 'string', 'max:10'],
            'background.obgyn_partos'             => ['required_if:background.obgyn_apply,true', 'nullable', 'string', 'max:10'],
            'background.obgyn_cesareas'           => ['required_if:background.obgyn_apply,true', 'nullable', 'string', 'max:10'],
            'background.obgyn_abortos'            => ['required_if:background.obgyn_apply,true', 'nullable', 'string', 'max:10'],
            'background.obgyn_menarche'           => ['required_if:background.obgyn_apply,true', 'nullable', 'string', 'max:50'],
            'background.obgyn_menopause'          => ['nullable', 'string', 'max:50'],
            'background.obgyn_cycle_periodicity'  => ['required_if:background.obgyn_apply,true', 'nullable', 'string', 'max:50'],
            'background.obgyn_cycle_duration'     => ['required_if:background.obgyn_apply,true', 'nullable', 'string', 'max:50'],
            'background.obgyn_cycle_pads_per_day' => ['required_if:background.obgyn_apply,true', 'nullable', 'integer', 'min:0'],
            'background.obgyn_fur'                => ['required_if:background.obgyn_apply,true', 'nullable', 'date', 'before_or_equal:today'],

            // 7. Quirúrgicos
            'background.surgical_deny'          => ['required', 'boolean'],
            'background.surgical_intervention'  => ['required_if:background.surgical_deny,false', 'nullable', 'string', 'max:255'],
            'background.surgical_age'           => ['required_if:background.surgical_deny,false', 'nullable', 'integer', 'min:0'],
            'background.surgical_complications' => ['nullable', 'string', 'max:255'],

            // 8. Traumáticos
            'background.traumatic_deny'          => ['required', 'boolean'],
            'background.traumatic_fracture'      => ['required_if:background.traumatic_deny,false', 'nullable', 'string', 'max:255'],
            'background.traumatic_age'           => ['required_if:background.traumatic_deny,false', 'nullable', 'integer', 'min:0'],
            'background.traumatic_treatment'      => ['required_if:background.traumatic_deny,false', 'nullable', 'string', 'max:255'],
            'background.traumatic_complications' => ['nullable', 'string', 'max:255'],

            // 9. ETS
            'background.std_deny'            => ['required', 'boolean'],
            'background.std_disease'         => ['required_if:background.std_deny,false', 'nullable', 'string', 'max:255'],
            'background.std_age'             => ['required_if:background.std_deny,false', 'nullable', 'integer', 'min:0'],
            'background.std_treatment'       => ['required_if:background.std_deny,false', 'nullable', 'string', 'max:255'],
            'background.std_hospitalization' => ['required_if:background.std_deny,false', 'nullable', 'boolean'],
            'background.std_complications'   => ['nullable', 'string', 'max:255'],

            // 10. Epidemiológicos (Viajes)
            'background.epidemiological_deny' => ['required', 'boolean'],
            'background.epidem_destination'    => ['required_if:background.epidemiological_deny,false', 'nullable', 'string', 'max:255'],
            'background.epidem_start_date'     => ['required_if:background.epidemiological_deny,false', 'nullable', 'date'],
            'background.epidem_end_date'       => ['required_if:background.epidemiological_deny,false', 'nullable', 'date', 'after_or_equal:background.epidem_start_date'],
            'background.epidem_biome'          => ['required_if:background.epidemiological_deny,false', 'nullable', 'string', 'max:255'],

            // 11. Discapacidades
            'background.disability_deny'                      => ['required', 'boolean'],
            'background.disability_types'                     => ['required_if:background.disability_deny,false', 'nullable', 'array', 'min:1'],
            'background.disability_types.*'                   => ['integer', 'between:1,11'],
            'background.disability_type'                      => ['nullable', 'integer', 'between:1,11'],
            'background.disability_specific_name'             => ['required_if:background.disability_deny,false', 'nullable', 'string', 'max:255'],
            'background.disability_onset_value'               => ['required_if:background.disability_deny,false', 'nullable', 'integer', 'min:0'],
            'background.disability_onset_unit'                => ['required_if:background.disability_deny,false', 'nullable', Rule::in(['días', 'meses', 'años'])],
            'background.disability_pharmacological_treatment' => ['required_if:background.disability_deny,false', 'nullable', 'string', 'max:255'],

            // ==========================================================================
            // PARTE 3: ANTECEDENTES FAMILIARES (family_background)
            // ==========================================================================
            'family_background' => ['required', 'array'],

            'family_background.mother.unknown'   => ['required', 'boolean'],
            'family_background.mother.status'    => ['required_unless:family_background.mother.unknown,true', 'nullable', Rule::in(['vivo', 'fallecido'])],
            'family_background.mother.age'       => ['required_unless:family_background.mother.unknown,true', 'nullable', 'integer', 'min:0', 'max:120'],
            'family_background.mother.pathology' => ['required_unless:family_background.mother.unknown,true', 'nullable', 'string', 'max:255'],

            'family_background.father.unknown'   => ['required', 'boolean'],
            'family_background.father.status'    => ['required_unless:family_background.father.unknown,true', 'nullable', Rule::in(['vivo', 'fallecido'])],
            'family_background.father.age'       => ['required_unless:family_background.father.unknown,true', 'nullable', 'integer', 'min:0', 'max:120'],
            'family_background.father.pathology' => ['required_unless:family_background.father.unknown,true', 'nullable', 'string', 'max:255'],

            'family_background.grandmother_maternal.unknown'   => ['required', 'boolean'],
            'family_background.grandmother_maternal.status'    => ['required_unless:family_background.grandmother_maternal.unknown,true', 'nullable', Rule::in(['vivo', 'fallecido'])],
            'family_background.grandmother_maternal.age'       => ['required_unless:family_background.grandmother_maternal.unknown,true', 'nullable', 'integer', 'min:0', 'max:120'],
            'family_background.grandmother_maternal.pathology' => ['required_unless:family_background.grandmother_maternal.unknown,true', 'nullable', 'string', 'max:255'],

            'family_background.grandfather_maternal.unknown'   => ['required', 'boolean'],
            'family_background.grandfather_maternal.status'    => ['required_unless:family_background.grandfather_maternal.unknown,true', 'nullable', Rule::in(['vivo', 'fallecido'])],
            'family_background.grandfather_maternal.age'       => ['required_unless:family_background.grandfather_maternal.unknown,true', 'nullable', 'integer', 'min:0', 'max:120'],
            'family_background.grandfather_maternal.pathology' => ['required_unless:family_background.grandfather_maternal.unknown,true', 'nullable', 'string', 'max:255'],

            'family_background.grandmother_paternal.unknown'   => ['required', 'boolean'],
            'family_background.grandmother_paternal.status'    => ['required_unless:family_background.grandmother_paternal.unknown,true', 'nullable', Rule::in(['vivo', 'fallecido'])],
            'family_background.grandmother_paternal.age'       => ['required_unless:family_background.grandmother_paternal.unknown,true', 'nullable', 'integer', 'min:0', 'max:120'],
            'family_background.grandmother_paternal.pathology' => ['required_unless:family_background.grandmother_paternal.unknown,true', 'nullable', 'string', 'max:255'],

            'family_background.grandfather_paternal.unknown'   => ['required', 'boolean'],
            'family_background.grandfather_paternal.status'    => ['required_unless:family_background.grandfather_paternal.unknown,true', 'nullable', Rule::in(['vivo', 'fallecido'])],
            'family_background.grandfather_paternal.age'       => ['required_unless:family_background.grandfather_paternal.unknown,true', 'nullable', 'integer', 'min:0', 'max:120'],
            'family_background.grandfather_paternal.pathology' => ['required_unless:family_background.grandfather_paternal.unknown,true', 'nullable', 'string', 'max:255'],

            'family_background.siblings.unknown'        => ['required', 'boolean'],
            'family_background.siblings.quantity'     => ['required_unless:family_background.siblings.unknown,true', 'nullable', 'integer', 'min:0'],
            'family_background.siblings.female_count' => ['required_unless:family_background.siblings.unknown,true', 'nullable', 'integer', 'min:0'],
            'family_background.siblings.male_count'   => ['required_unless:family_background.siblings.unknown,true', 'nullable', 'integer', 'min:0'],
            'family_background.siblings.pathology'    => ['nullable', 'string', 'max:255'],

            'family_background.children.unknown'        => ['required', 'boolean'],
            'family_background.children.quantity'     => ['required_unless:family_background.children.unknown,true', 'nullable', 'integer', 'min:0'],
            'family_background.children.female_count' => ['required_unless:family_background.children.unknown,true', 'nullable', 'integer', 'min:0'],
            'family_background.children.male_count'   => ['required_unless:family_background.children.unknown,true', 'nullable', 'integer', 'min:0'],
            'family_background.children.pathology'    => ['nullable', 'string', 'max:255'],

            // ==========================================================================
            // PARTE 4: HÁBITOS PSICOBIOLÓGICOS Y ENTORNO (habits)
            // ==========================================================================
            'habits' => ['required', 'array'],

            // Consumo de Alcohol
            'habits.alcohol.deny'           => ['required', 'boolean'],
            'habits.alcohol.start_age'      => ['required_if:habits.alcohol.deny,false', 'nullable', 'integer', 'min:0'],
            'habits.alcohol.end_age'        => ['nullable', 'string', 'max:50'],
            'habits.alcohol.type'           => ['required_if:habits.alcohol.deny,false', 'nullable', 'string', 'max:100'],
            'habits.alcohol.quantity_ml'    => ['required_if:habits.alcohol.deny,false', 'nullable', 'integer', 'min:0'],
            'habits.alcohol.frequency_days' => ['nullable', 'string', 'max:50'],
            'habits.alcohol.gets_drunk'     => ['required_if:habits.alcohol.deny,false', 'nullable', 'boolean'],

            // Tabaquismo
            'habits.tobacco.deny'               => ['required', 'boolean'],
            'habits.tobacco.start_age'          => ['required_if:habits.tobacco.deny,false', 'nullable', 'integer', 'min:0'],
            'habits.tobacco.end_age'            => ['nullable', 'string', 'max:50'],
            'habits.tobacco.cigarettes_per_day' => ['required_if:habits.tobacco.deny,false', 'nullable', 'integer', 'min:0'],
            'habits.tobacco.boxes_per_year'     => ['required_if:habits.tobacco.deny,false', 'nullable', 'integer', 'min:0'],

            // Consumo de Café
            'habits.coffee.deny'        => ['required', 'boolean'],
            'habits.coffee.start_age'   => ['nullable', 'integer', 'min:0'],
            'habits.coffee.end_age'     => ['nullable', 'string', 'max:50'],
            'habits.coffee.quantity_ml' => ['required_if:habits.coffee.deny,false', 'nullable', 'integer', 'min:0'],
            'habits.coffee.type'        => ['required_if:habits.coffee.deny,false', 'nullable', 'string', 'max:255'],

            // Otras Sustancias / Drogas
            'habits.drugs.deny'              => ['required', 'boolean'],
            'habits.drugs.start_age'         => ['nullable', 'integer', 'min:0'],
            'habits.drugs.end_age'           => ['nullable', 'string', 'max:50'],
            'habits.drugs.route'             => ['required_if:habits.drugs.deny,false', 'nullable', 'string', 'max:100'],
            'habits.drugs.frequency_per_day' => ['required_if:habits.drugs.deny,false', 'nullable', 'string', 'max:100'],

            // Estilo de Vida Basal
            'habits.physical_activity'                  => ['nullable', 'array'],
            'habits.physical_activity.type'             => ['nullable', 'string', 'max:255'],
            'habits.physical_activity.times_per_week'   => ['nullable', 'integer', 'min:0'],
            'habits.physical_activity.minutes_per_day'  => ['nullable', 'integer', 'min:0'],

            'habits.nutrition'                          => ['nullable', 'array'],
            'habits.nutrition.type'                     => ['required', Rule::in(['balanceada', 'predominio'])],
            'habits.nutrition.predominance_description' => ['required_if:habits.nutrition.type,predominio', 'nullable', 'string', 'max:255'],
            'habits.nutrition.meals_count'              => ['nullable', 'integer', 'min:0'],
            'habits.nutrition.appetite'                 => ['nullable', 'string', 'max:50'],

            'habits.sleep'                              => ['nullable', 'array'],
            'habits.sleep.type'                         => ['required', Rule::in(['nocturno', 'diurno', 'insomnio_inicial', 'insomnio_terminal', 'interrumpido'])],
            'habits.sleep.frequency_per_day'            => ['nullable', 'integer', 'min:0', 'max:24'],
            'habits.sleep.hours'                        => ['required', 'integer', 'min:0', 'max:24'],
            'habits.sleep.interrupted'                  => ['nullable', 'boolean'],
            'habits.sleep.medication'                   => ['nullable', 'string', 'max:255'],
            'habits.sleep.siesta_duration_min'          => ['nullable', 'integer', 'min:0'],
            'habits.sleep.siesta_frequency_per_day'     => ['nullable', 'integer', 'min:0'],

            // Esfera Sexual
            'habits.sexual_habits'                      => ['nullable', 'array'],
            'habits.sexual_habits.active'                 => ['required', 'boolean'],
            'habits.sexual_habits.sexarche_age'           => ['required_if:habits.sexual_habits.active,true', 'nullable', 'integer', 'min:0'],
            'habits.sexual_habits.partners_count'         => ['nullable', 'integer', 'min:0'],
            'habits.sexual_habits.orientation'            => ['nullable', 'string', 'max:100'],
            'habits.sexual_habits.frequency_per_week'     => ['nullable', 'integer', 'min:0'],
            'habits.sexual_habits.contraceptive_method'   => ['nullable', 'string', 'max:100'],

            'habits.gastrointestinal'                   => ['nullable', 'array'],
            'habits.genitourinary'                      => ['nullable', 'array'],

            // Características de la Vivienda
            'habits.housing'                            => ['required', 'array'],
            'habits.housing.floor_material'               => ['required', 'string', 'max:100'],
            'habits.housing.roof_material'                => ['required', 'string', 'max:100'],
            'habits.housing.walls_material'               => ['required', 'string', 'max:100'],
            'habits.housing.rooms_count'                  => ['required', 'integer', 'min:1'],
            'habits.housing.habitants_count'              => ['required', 'integer', 'min:1'],
            'habits.housing.animals'                      => ['nullable', 'array'],

            // Servicios Intradomiciliarios
            'habits.housing.services'                   => ['required', 'array'],
            'habits.housing.services.water'               => ['required', 'boolean'],
            'habits.housing.services.electricity'         => ['required', 'boolean'],
            'habits.housing.services.gas'                 => ['required', 'boolean'],
            'habits.housing.services.waste_collection'    => ['required', 'boolean'],
        ];
    }

    /**
     * Conserva la estructura JSON completa de hábitos tras la validación de campos clave.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        if ($key !== null) {
            return $validated;
        }

        if ($this->has('habits')) {
            $validated['habits'] = array_replace_recursive(
                $this->input('habits', []),
                $validated['habits'] ?? []
            );
        }

        return $validated;
    }

    /**
     * Obtiene los mensajes de error personalizados para las reglas definidas.
     */
    public function messages(): array
    {
        return [
            'full_name.required'         => 'El nombre completo es obligatorio.',
            'id_number.required'         => 'El número de cédula es obligatorio.',
            'id_number.unique'           => 'La cédula ingresada ya se encuentra registrada en el sistema.',
            'nationality.required'       => 'La nacionalidad es obligatoria.',
            'gender.required'            => 'El género es obligatorio.',
            'birth_date.required'        => 'La fecha de nacimiento es obligatoria.',
            'birth_date.before'          => 'La fecha de nacimiento no puede ser igual o posterior al día de hoy.',
            'birth_place.required'       => 'El lugar de nacimiento es obligatorio.',
            'marital_status_id.required' => 'El estado civil es obligatorio.',
            'ethnicity_id.required'      => 'La etnia o autoidentificación es obligatoria.',
            'instruction_level_id.required' => 'El nivel de instrucción es obligatorio.',
            'occupation_id.required'     => 'La ocupación actual es obligatoria.',
            'religion_id.required'       => 'La religión o creencia es obligatoria.',
            'knows_blood_type.required'  => 'Debe especificar si el paciente conoce su grupo sanguíneo.',
            'blood_type.required_if'     => 'El tipo de sangre es obligatorio si conoce su grupo sanguíneo.',
            'rh_factor.required_if'      => 'El factor Rh es obligatorio si conoce su grupo sanguíneo.',
            'addr_state.required'        => 'El estado de residencia es obligatorio.',
            'addr_municipality.required' => 'El municipio es obligatorio.',
            'addr_parish.required'       => 'La parroquia es obligatoria.',
            'addr_sector.required'       => 'El sector o comunidad es obligatorio.',

            // Mensajes Anidados - Historial Clínico
            'background.allergies_description.required_if' => 'La descripción de la alergia es obligatoria si no la niega.',
            'background.pathological_disease.required_if'  => 'Debe especificar el nombre de la enfermedad patológica.',
            'background.obgyn_gestas.required_if'          => 'El número de gestas es obligatorio para perfiles gineco-obstétricos activos.',
            'background.epidem_end_date.after_or_equal'    => 'La fecha de retorno del viaje no puede ser anterior a la de ida.',

            // Mensajes Anidados - Hábitos y Entorno
            'habits.alcohol.start_age.required_if'       => 'La edad de inicio es obligatoria si consume alcohol.',
            'habits.alcohol.type.required_if'            => 'El tipo de bebida es obligatorio si consume alcohol.',
            'habits.alcohol.quantity_ml.required_if'     => 'La cantidad estimada en ml es requerida.',
            'habits.tobacco.start_age.required_if'       => 'La edad de inicio es obligatoria si consume tabaco.',
            'habits.tobacco.cigarettes_per_day.required_if' => 'La cantidad de cigarrillos diarios es requerida.',
            'habits.coffee.quantity_ml.required_if'      => 'El número de tazas de café al día es obligatorio.',
            'habits.coffee.type.required_if'             => 'El tipo de preparación del café es obligatorio.',
            'habits.drugs.route.required_if'             => 'La vía de administración es requerida si refiere consumo.',
            'habits.drugs.frequency_per_day.required_if' => 'La frecuencia de uso es requerida si refiere consumo.',
            'habits.sleep.type.required'                 => 'El patrón de sueño es obligatorio.',
            'habits.sleep.hours.required'                => 'Las horas de sueño diarias son obligatorias.',
            'habits.sexual_habits.sexarche_age.required_if' => 'La edad de la sexarquía es obligatoria si mantiene vida sexual activa.',
            'habits.housing.roof_material.required'      => 'El material del techo es obligatorio.',
            'habits.housing.walls_material.required'     => 'El material de las paredes es obligatorio.',
            'habits.housing.rooms_count.required'        => 'El número de habitaciones es obligatorio.',
            'habits.housing.habitants_count.required'    => 'El número de habitantes es requerido para calcular el índice de hacinamiento.',
        ];
    }
}
