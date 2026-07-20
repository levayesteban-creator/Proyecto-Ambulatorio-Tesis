<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Permitir registro a todos los usuarios autenticados
    }

    public function rules(): array
    {
        return [
            // ==========================================================================
            // PARTE 1: FICHA PATRONÍMICA (DATOS PERSONALES)
            // ==========================================================================

            'full_name'            => ['required', 'string', 'max:255'],
            'id_number'            => ['required', 'string', 'max:20', Rule::unique('patients', 'id_number')->ignore($this->patient)],
            'nationality'          => ['required', 'string', 'max:1', Rule::in(['V', 'E'])],
            'nationality_country'  => ['nullable', 'string', 'max:100'],
            'occupation_detail'    => ['nullable', 'string', 'max:255'],
            'religion_detail'      => ['nullable', 'string', 'max:255'],
            'gender'               => ['required', Rule::in(['F', 'M', 'O'])],
            'birth_date'           => ['required', 'date', 'before:today', 'after:1900-01-01'],

            'birth_place'          => ['required', 'string', 'max:255'],

            'marital_status_id'    => ['required', 'exists:marital_statuses,id'],
            'ethnicity_id'         => ['required', 'exists:ethnicities,id'],
            'instruction_level_id' => ['required', 'exists:instruction_levels,id'],
            'occupation_id'        => ['required', 'exists:occupations,id'],
            'religion_id'          => ['required', 'exists:religions,id'],

            'knows_blood_type'     => ['required', 'boolean'],
            'blood_type'           => ['required_if:knows_blood_type,true', 'nullable', Rule::in(['A', 'B', 'AB', 'O', 'Desconoce'])],
            'rh_factor'            => ['required_if:knows_blood_type,true', 'nullable', Rule::in(['+', '-'])],

            'phone_number'         => ['nullable', 'string', 'max:20', 'regex:/^[\d\s\+\-\(\)]+$/'],
            'addr_state'           => ['required', 'string', 'max:100'],
            'addr_municipality'    => ['required', 'string', 'max:100'],
            'addr_parish'          => ['required', 'string', 'max:100'],
            'addr_locality'        => ['nullable', 'string', 'max:255'],
            'addr_sector'          => ['required', 'string', 'max:255'],
            'addr_street'          => ['nullable', 'string', 'max:255'],
            'addr_house_number'    => ['nullable', 'string', 'max:100'],
            'addr_zip_code'        => ['nullable', 'string', 'max:20', 'regex:/^\d{4,10}$/'],
            'addr_reference'       => ['nullable', 'string', 'max:255'],
            'residence_time'       => ['nullable', 'string', 'max:100'],

            // ==========================================================================
            // PARTE 2: HISTORIAL CLÍNICO (background)
            // ==========================================================================
            'background' => ['required', 'array'],

            'background.allergies_deny'        => ['required', 'boolean'],
            'background.allergies_description' => ['required_if:background.allergies_deny,false', 'nullable', 'string', 'max:255'],

            'background.pathological_deny'        => ['required', 'boolean'],
            'background.pathological_disease'     => ['required_if:background.pathological_deny,false', 'nullable', 'string', 'max:255'],
            'background.pathological_onset_value' => ['required_if:background.pathological_deny,false', 'nullable', 'integer', 'min:0'],
            'background.pathological_onset_unit'  => ['required_if:background.pathological_deny,false', 'nullable', Rule::in(['días', 'meses', 'años'])],
            'background.pathological_controlled'  => ['required_if:background.pathological_deny,false', 'nullable', 'boolean'],
            'background.pathological_treatment'   => ['required_if:background.pathological_deny,false', 'nullable', 'string', 'max:255'],

            'background.infectious_deny'            => ['required', 'boolean'],
            'background.infectious_disease'         => ['required_if:background.infectious_deny,false', 'nullable', 'string', 'max:255'],
            'background.infectious_age'             => ['required_if:background.infectious_deny,false', 'nullable', 'integer', 'min:0'],
            'background.infectious_treatment'       => ['required_if:background.infectious_deny,false', 'nullable', 'string', 'max:255'],
            'background.infectious_hospitalization' => ['required_if:background.infectious_deny,false', 'nullable', 'boolean'],
            'background.infectious_complications'   => ['nullable', 'string', 'max:255'],

            'background.immune_deny_vaccination' => ['required', 'boolean'],
            'background.immune_childhood_status' => ['required', Rule::in(['completa', 'incompleta', 'niega'])],
            'background.immune_missing_vaccines' => ['required_if:background.immune_childhood_status,incompleta', 'nullable', 'string', 'max:255'],
            'background.immune_adult_vaccines'   => ['nullable', 'string', 'max:255'],
            'background.immune_adult_age'        => ['nullable', 'integer', 'min:0'],
            'background.immune_complications'    => ['nullable', 'string', 'max:255'],

            'background.transfusion_deny'       => ['required', 'boolean'],
            'background.transfusion_age'        => ['required_if:background.transfusion_deny,false', 'nullable', 'integer', 'min:0'],
            'background.transfusion_type'       => ['required_if:background.transfusion_deny,false', 'nullable', 'string', 'max:50'],
            'background.transfusion_bags_count' => ['required_if:background.transfusion_deny,false', 'nullable', 'integer', 'min:1'],
            'background.transfusion_reason'     => ['required_if:background.transfusion_deny,false', 'nullable', 'string', 'max:255'],

            'background.obgyn_apply'              => ['required', 'boolean'],
            'background.obgyn_gestas'             => ['required_if:background.obgyn_apply,true', 'nullable', 'string', 'max:10', 'regex:/^[0-9]+$/'],
            'background.obgyn_partos'             => ['required_if:background.obgyn_apply,true', 'nullable', 'string', 'max:10', 'regex:/^[0-9]+$/'],
            'background.obgyn_cesareas'           => ['required_if:background.obgyn_apply,true', 'nullable', 'string', 'max:10', 'regex:/^[0-9]+$/'],
            'background.obgyn_abortos'            => ['required_if:background.obgyn_apply,true', 'nullable', 'string', 'max:10', 'regex:/^[0-9]+$/'],
            'background.obgyn_menarche'           => ['required_if:background.obgyn_apply,true', 'nullable', 'date', 'before:today', 'after:1900-01-01'],
            'background.obgyn_menopause'          => ['nullable', 'date', 'after:1900-01-01', 'before_or_equal:today'],
            'background.obgyn_cycle_periodicity'  => ['required_if:background.obgyn_apply,true', 'nullable', 'string', 'max:50'],
            'background.obgyn_cycle_duration'     => ['required_if:background.obgyn_apply,true', 'nullable', 'string', 'max:50'],
            'background.obgyn_cycle_pads_per_day' => ['required_if:background.obgyn_apply,true', 'nullable', 'integer', 'min:0', 'max:50'],
            'background.obgyn_fur'                => ['required_if:background.obgyn_apply,true', 'nullable', 'date', 'before_or_equal:today'],

            'background.surgical_deny'          => ['required', 'boolean'],
            'background.surgical_intervention'  => ['required_if:background.surgical_deny,false', 'nullable', 'string', 'max:255'],
            'background.surgical_age'           => ['required_if:background.surgical_deny,false', 'nullable', 'integer', 'min:0'],
            'background.surgical_complications' => ['nullable', 'string', 'max:255'],

            'background.traumatic_deny'          => ['required', 'boolean'],
            'background.traumatic_fracture'      => ['required_if:background.traumatic_deny,false', 'nullable', 'string', 'max:255'],
            'background.traumatic_age'           => ['required_if:background.traumatic_deny,false', 'nullable', 'integer', 'min:0'],
            'background.traumatic_treatment'      => ['required_if:background.traumatic_deny,false', 'nullable', 'string', 'max:255'],
            'background.traumatic_complications' => ['nullable', 'string', 'max:255'],

            'background.std_deny'            => ['required', 'boolean'],
            'background.std_disease'         => ['required_if:background.std_deny,false', 'nullable', 'string', 'max:255'],
            'background.std_age'             => ['required_if:background.std_deny,false', 'nullable', 'integer', 'min:0'],
            'background.std_treatment'       => ['required_if:background.std_deny,false', 'nullable', 'string', 'max:255'],
            'background.std_hospitalization' => ['required_if:background.std_deny,false', 'nullable', 'boolean'],
            'background.std_complications'   => ['nullable', 'string', 'max:255'],

            'background.epidemiological_deny' => ['required', 'boolean'],
            'background.epidem_destination'    => ['required_if:background.epidemiological_deny,false', 'nullable', 'string', 'max:255'],
            'background.epidem_start_date'     => ['required_if:background.epidemiological_deny,false', 'nullable', 'date'],
            'background.epidem_end_date'       => ['required_if:background.epidemiological_deny,false', 'nullable', 'date', 'after_or_equal:background.epidem_start_date'],
            'background.epidem_biome'          => ['required_if:background.epidemiological_deny,false', 'nullable', 'string', 'max:255'],

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

            'habits.alcohol.deny'           => ['required', 'boolean'],
            'habits.alcohol.start_age'      => ['required_if:habits.alcohol.deny,false', 'nullable', 'integer', 'min:0', 'max:120'],
            'habits.alcohol.end_age'        => ['nullable', 'string', 'max:50'],
            'habits.alcohol.type'           => ['required_if:habits.alcohol.deny,false', 'nullable', 'string', 'max:100'],
            'habits.alcohol.quantity_ml'    => ['required_if:habits.alcohol.deny,false', 'nullable', 'integer', 'min:0', 'max:500'],
            'habits.alcohol.frequency_days' => ['nullable', 'string', 'max:50'],
            'habits.alcohol.gets_drunk'     => ['required_if:habits.alcohol.deny,false', 'nullable', 'boolean'],

            'habits.tobacco.deny'               => ['required', 'boolean'],
            'habits.tobacco.start_age'          => ['required_if:habits.tobacco.deny,false', 'nullable', 'integer', 'min:0', 'max:120'],
            'habits.tobacco.end_age'            => ['nullable', 'string', 'max:50'],
            'habits.tobacco.cigarettes_per_day' => ['required_if:habits.tobacco.deny,false', 'nullable', 'integer', 'min:0', 'max:100'],
            'habits.tobacco.boxes_per_year'     => ['required_if:habits.tobacco.deny,false', 'nullable', 'integer', 'min:0', 'max:365'],

            'habits.coffee.deny'        => ['required', 'boolean'],
            'habits.coffee.start_age'   => ['nullable', 'integer', 'min:0'],
            'habits.coffee.end_age'     => ['nullable', 'string', 'max:50'],
            'habits.coffee.quantity_ml' => ['required_if:habits.coffee.deny,false', 'nullable', 'integer', 'min:0'],
            'habits.coffee.type'        => ['required_if:habits.coffee.deny,false', 'nullable', 'string', 'max:255'],

            'habits.drugs.deny'              => ['required', 'boolean'],
            'habits.drugs.start_age'         => ['nullable', 'integer', 'min:0'],
            'habits.drugs.end_age'           => ['nullable', 'string', 'max:50'],
            'habits.drugs.route'             => ['required_if:habits.drugs.deny,false', 'nullable', 'string', 'max:100'],
            'habits.drugs.frequency_per_day' => ['required_if:habits.drugs.deny,false', 'nullable', 'string', 'max:100'],

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

            'habits.sexual_habits'                      => ['nullable', 'array'],
            'habits.sexual_habits.active'                 => ['required', 'boolean'],
            'habits.sexual_habits.sexarche_age'           => ['required_if:habits.sexual_habits.active,true', 'nullable', 'integer', 'min:0'],
            'habits.sexual_habits.partners_count'         => ['nullable', 'integer', 'min:0'],
            'habits.sexual_habits.orientation'            => ['nullable', 'string', 'max:100'],
            'habits.sexual_habits.frequency_per_week'     => ['nullable', 'integer', 'min:0'],
            'habits.sexual_habits.contraceptive_method'   => ['nullable', 'string', 'max:100'],

            'habits.gastrointestinal'                   => ['nullable', 'array'],
            'habits.genitourinary'                      => ['nullable', 'array'],

            'habits.housing'                            => ['required', 'array'],
            'habits.housing.floor_material'               => ['required', 'string', 'max:100'],
            'habits.housing.roof_material'                => ['required', 'string', 'max:100'],
            'habits.housing.walls_material'               => ['required', 'string', 'max:100'],
            'habits.housing.rooms_count'                  => ['required', 'integer', 'min:1'],
            'habits.housing.habitants_count'              => ['required', 'integer', 'min:1'],
            'habits.housing.animals'                      => ['nullable', 'array'],

            'habits.housing.services'                   => ['required', 'array'],
            'habits.housing.services.water'               => ['required', 'boolean'],
            'habits.housing.services.electricity'         => ['required', 'boolean'],
            'habits.housing.services.gas'                 => ['required', 'boolean'],
            'habits.housing.services.waste_collection'    => ['required', 'boolean'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        if ($key !== null) {
            return $validated;
        }

        $this->validateClinicalConsistency();

        if ($this->has('habits')) {
            $validated['habits'] = array_replace_recursive(
                $this->input('habits', []),
                $validated['habits'] ?? []
            );
        }

        return $validated;
    }

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

            'background.allergies_description.required_if'         => 'La descripción de la alergia es obligatoria si no la niega.',
            'background.pathological_disease.required_if'          => 'Debe especificar el nombre de la enfermedad patológica.',
            'background.pathological_onset_value.required_if'      => 'El tiempo de evolución de la enfermedad es obligatorio.',
            'background.pathological_onset_unit.required_if'       => 'La unidad de tiempo de evolución es obligatoria.',
            'background.pathological_controlled.required_if'       => 'Indique si la enfermedad está controlada.',
            'background.pathological_treatment.required_if'        => 'El tratamiento recibido es obligatorio.',

            'background.infectious_disease.required_if'            => 'El nombre de la enfermedad infecciosa es obligatorio.',
            'background.infectious_age.required_if'                => 'La edad al padecer la enfermedad infecciosa es obligatoria.',
            'background.infectious_treatment.required_if'          => 'El tratamiento recibido es obligatorio.',
            'background.infectious_hospitalization.required_if'    => 'Indique si requirió hospitalización.',

            'background.immune_missing_vaccines.required_if'       => 'Las vacunas faltantes son obligatorias.',

            'background.transfusion_age.required_if'               => 'La edad de la transfusión es obligatoria.',
            'background.transfusion_type.required_if'              => 'El tipo de transfusión es obligatorio.',
            'background.transfusion_bags_count.required_if'        => 'El número de bolsas transfundidas es obligatorio.',
            'background.transfusion_reason.required_if'            => 'El motivo de la transfusión es obligatorio.',

            'background.obgyn_gestas.required_if'                  => 'El número de gestas es obligatorio para perfiles gineco-obstétricos activos.',
            'background.obgyn_partos.required_if'                  => 'El número de partos es obligatorio.',
            'background.obgyn_cesareas.required_if'                => 'El número de cesáreas es obligatorio.',
            'background.obgyn_abortos.required_if'                 => 'El número de abortos es obligatorio.',
            'background.obgyn_menarche.required_if'                => 'La fecha de menarca es obligatoria.',
            'background.obgyn_cycle_periodicity.required_if'       => 'La periodicidad del ciclo menstrual es obligatoria.',
            'background.obgyn_cycle_duration.required_if'          => 'La duración del ciclo menstrual es obligatoria.',
            'background.obgyn_cycle_pads_per_day.required_if'      => 'La cantidad de toallas por día es obligatoria.',
            'background.obgyn_fur.required_if'                     => 'La fecha de última regla es obligatoria.',

            'background.surgical_intervention.required_if'         => 'La intervención quirúrgica es obligatoria.',
            'background.surgical_age.required_if'                  => 'La edad de la cirugía es obligatoria.',

            'background.traumatic_fracture.required_if'            => 'La fractura o traumatismo es obligatorio.',
            'background.traumatic_age.required_if'                 => 'La edad del traumatismo es obligatoria.',
            'background.traumatic_treatment.required_if'           => 'El tratamiento del traumatismo es obligatorio.',

            'background.std_disease.required_if'                   => 'El nombre de la ETS es obligatorio.',
            'background.std_age.required_if'                       => 'La edad al padecer la ETS es obligatoria.',
            'background.std_treatment.required_if'                 => 'El tratamiento de la ETS es obligatorio.',
            'background.std_hospitalization.required_if'           => 'Indique si requirió hospitalización por la ETS.',

            'background.epidem_destination.required_if'            => 'El destino del viaje es obligatorio.',
            'background.epidem_start_date.required_if'             => 'La fecha de ida del viaje es obligatoria.',
            'background.epidem_end_date.required_if'               => 'La fecha de retorno del viaje es obligatoria.',
            'background.epidem_end_date.after_or_equal'            => 'La fecha de retorno no puede ser anterior a la de ida.',
            'background.epidem_biome.required_if'                  => 'El bioma del lugar visitado es obligatorio.',

            'background.disability_specific_name.required_if'                => 'El nombre de la discapacidad es obligatorio.',
            'background.disability_onset_value.required_if'                  => 'El tiempo de evolución de la discapacidad es obligatorio.',
            'background.disability_onset_unit.required_if'                   => 'La unidad de tiempo de evolución de la discapacidad es obligatoria.',
            'background.disability_pharmacological_treatment.required_if'    => 'El tratamiento farmacológico es obligatorio.',

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
            'habits.housing.floor_material.required'     => 'El material del piso es obligatorio.',
            'habits.housing.services.water.required'        => 'Indique si dispone de agua potable.',
            'habits.housing.services.electricity.required'  => 'Indique si dispone de electricidad.',
            'habits.housing.services.gas.required'          => 'Indique si dispone de gas.',
            'habits.housing.services.waste_collection.required' => 'Indique si dispone de recolección de basura.',

            'habits.nutrition.type.required'                => 'El tipo de alimentación es obligatorio.',

            'habits.sexual_habits.active.required'          => 'Indique si mantiene vida sexual activa.',

            'background.obgyn_menarche.before'              => 'La menarca no puede ser posterior al día de hoy.',
            'background.obgyn_menarche.after'               => 'La menarca debe ser posterior a 1900.',
            'background.obgyn_menopause.after_or_equal'     => 'La menopausia no puede ser posterior al día de hoy.',
            'background.obgyn_fur.before_or_equal'          => 'La fecha de última regla no puede ser posterior al día de hoy.',
        ];
    }

    public function attributes(): array
    {
        return [
            // Datos demográficos
            'full_name'          => 'nombre completo',
            'id_number'          => 'cédula de identidad',
            'nationality'        => 'nacionalidad',
            'gender'             => 'género',
            'birth_date'         => 'fecha de nacimiento',
            'birth_place'        => 'lugar de nacimiento',
            'marital_status_id'  => 'estado civil',
            'ethnicity_id'       => 'etnia',
            'instruction_level_id' => 'nivel de instrucción',
            'occupation_id'      => 'profesión u oficio',
            'religion_id'        => 'religión',
            'knows_blood_type'   => 'conoce su tipo de sangre',
            'blood_type'         => 'tipo de sangre',
            'rh_factor'          => 'factor Rh',
            'addr_state'         => 'estado',
            'addr_municipality'  => 'municipio',
            'addr_parish'        => 'parroquia',
            'addr_sector'        => 'sector',

            // Antecedentes personales
            'background.allergies_deny'              => 'negación de alergias',
            'background.pathological_deny'           => 'negación de enfermedades patológicas',
            'background.infectious_deny'             => 'negación de enfermedades infecciosas',
            'background.immune_deny_vaccination'     => 'esquema de vacunación',
            'background.immune_childhood_status'     => 'estado de inmunizaciones en la infancia',
            'background.transfusion_deny'            => 'negación de transfusiones',
            'background.obgyn_apply'                 => 'aplica antecedentes gineco-obstétricos',
            'background.surgical_deny'               => 'negación de cirugías',
            'background.traumatic_deny'              => 'negación de traumatismos',
            'background.std_deny'                    => 'negación de ETS',
            'background.epidemiological_deny'        => 'negación de antecedentes epidemiológicos',
            'background.disability_deny'             => 'negación de discapacidad',
            'background.allergies_description'       => 'descripción de alergias',
            'background.pathological_disease'        => 'enfermedad patológica',
            'background.obgyn_gestas'                => 'número de gestas',
            'background.obgyn_partos'               => 'número de partos',
            'background.obgyn_cesareas'             => 'número de cesáreas',
            'background.obgyn_abortos'              => 'número de abortos',
            'background.obgyn_menarche'             => 'fecha de menarca',
            'background.obgyn_menopause'            => 'fecha de menopausia',
            'background.obgyn_cycle_periodicity'    => 'periodicidad del ciclo menstrual',
            'background.obgyn_cycle_duration'       => 'duración del ciclo menstrual',
            'background.obgyn_cycle_pads_per_day'   => 'cantidad de toallas por día',
            'background.obgyn_fur'                  => 'fecha de última regla',
            'background.pathological_onset_value'   => 'tiempo de evolución de la enfermedad',
            'background.pathological_onset_unit'    => 'unidad de tiempo de evolución',
            'background.pathological_controlled'    => 'enfermedad controlada',
            'background.pathological_treatment'     => 'tratamiento recibido',
            'background.infectious_disease'         => 'enfermedad infecciosa padecida',
            'background.infectious_age'             => 'edad al padecerla',
            'background.infectious_treatment'       => 'tratamiento recibido',
            'background.infectious_hospitalization' => 'requirió hospitalización',
            'background.immune_missing_vaccines'    => 'vacunas faltantes',
            'background.transfusion_age'            => 'edad de la transfusión',
            'background.transfusion_type'           => 'tipo de transfusión',
            'background.transfusion_bags_count'     => 'número de bolsas transfundidas',
            'background.transfusion_reason'         => 'motivo de la transfusión',
            'background.surgical_intervention'      => 'intervención quirúrgica',
            'background.surgical_age'               => 'edad de la cirugía',
            'background.traumatic_fracture'         => 'fractura o traumatismo',
            'background.traumatic_age'              => 'edad del traumatismo',
            'background.traumatic_treatment'        => 'tratamiento recibido',
            'background.std_disease'                => 'ETS padecida',
            'background.std_age'                    => 'edad al padecerla',
            'background.std_treatment'              => 'tratamiento recibido',
            'background.std_hospitalization'        => 'requirió hospitalización',
            'background.epidem_destination'         => 'destino del viaje',
            'background.epidem_start_date'          => 'fecha de ida',
            'background.epidem_end_date'            => 'fecha de retorno',
            'background.epidem_biome'               => 'bioma del lugar visitado',
            'background.disability_specific_name'   => 'nombre de la discapacidad',
            'background.disability_onset_value'     => 'tiempo de evolución',
            'background.disability_onset_unit'      => 'unidad de tiempo',
            'background.disability_pharmacological_treatment' => 'tratamiento farmacológico',

            // Antecedentes familiares
            'family_background.mother.status'             => 'estado de la madre',
            'family_background.mother.age'                => 'edad de la madre',
            'family_background.mother.pathology'          => 'patología de la madre',
            'family_background.mother.unknown'            => 'madre sin datos',
            'family_background.father.status'             => 'estado del padre',
            'family_background.father.age'                => 'edad del padre',
            'family_background.father.pathology'          => 'patología del padre',
            'family_background.father.unknown'            => 'padre sin datos',
            'family_background.grandmother_maternal.status' => 'estado de la abuela materna',
            'family_background.grandmother_maternal.age'    => 'edad de la abuela materna',
            'family_background.grandmother_maternal.pathology' => 'patología de la abuela materna',
            'family_background.grandmother_maternal.unknown' => 'abuela materna sin datos',
            'family_background.grandfather_maternal.status' => 'estado del abuelo materno',
            'family_background.grandfather_maternal.age'    => 'edad del abuelo materno',
            'family_background.grandfather_maternal.pathology'  => 'patología del abuelo materno',
            'family_background.grandfather_maternal.unknown' => 'abuelo materno sin datos',
            'family_background.grandmother_paternal.status' => 'estado de la abuela paterna',
            'family_background.grandmother_paternal.age'    => 'edad de la abuela paterna',
            'family_background.grandmother_paternal.pathology' => 'patología de la abuela paterna',
            'family_background.grandmother_paternal.unknown' => 'abuela paterna sin datos',
            'family_background.grandfather_paternal.status' => 'estado del abuelo paterno',
            'family_background.grandfather_paternal.age'    => 'edad del abuelo paterno',
            'family_background.grandfather_paternal.pathology' => 'patología del abuelo paterno',
            'family_background.grandfather_paternal.unknown' => 'abuelo paterno sin datos',
            'family_background.siblings.unknown'      => 'hermanos sin datos',
            'family_background.siblings.quantity'    => 'cantidad de hermanos',
            'family_background.siblings.female_count' => 'cantidad de hermanas',
            'family_background.siblings.male_count'  => 'cantidad de hermanos',
            'family_background.siblings.pathology'   => 'patología de los hermanos',
            'family_background.children.unknown'     => 'hijos sin datos',
            'family_background.children.quantity'    => 'cantidad de hijos',
            'family_background.children.female_count' => 'cantidad de hijas',
            'family_background.children.male_count'  => 'cantidad de hijos',
            'family_background.children.pathology'   => 'patología de los hijos',

            // Hábitos
            'habits.nutrition.type'                     => 'tipo de alimentación',
            'habits.nutrition.meals_count'              => 'número de comidas al día',
            'habits.nutrition.predominance_description' => 'descripción del predominio alimenticio',
            'habits.alcohol.deny'                       => 'niega consumo de alcohol',
            'habits.alcohol.start_age'                  => 'edad de inicio de alcohol',
            'habits.alcohol.end_age'                    => 'edad de abandono',
            'habits.alcohol.type'                       => 'tipo de bebida alcohólica',
            'habits.alcohol.quantity_ml'                => 'cantidad de alcohol en ml',
            'habits.alcohol.frequency_days'             => 'frecuencia de consumo',
            'habits.alcohol.gets_drunk'                 => 'se embriaga',
            'habits.tobacco.deny'                       => 'niega consumo de tabaco',
            'habits.tobacco.start_age'                  => 'edad de inicio de tabaco',
            'habits.tobacco.end_age'                    => 'edad de abandono',
            'habits.tobacco.cigarettes_per_day'         => 'cigarrillos por día',
            'habits.tobacco.boxes_per_year'             => 'cajetillas por año',
            'habits.coffee.deny'                        => 'niega consumo de café',
            'habits.coffee.start_age'                   => 'edad de inicio de café',
            'habits.coffee.end_age'                     => 'edad de abandono',
            'habits.coffee.quantity_ml'                 => 'cantidad de café en ml',
            'habits.coffee.type'                        => 'tipo de café',
            'habits.drugs.deny'                         => 'niega consumo de drogas',
            'habits.drugs.start_age'                    => 'edad de inicio',
            'habits.drugs.end_age'                      => 'edad de abandono',
            'habits.drugs.route'                        => 'vía de administración',
            'habits.drugs.frequency_per_day'            => 'frecuencia de uso',
            'habits.sleep.type'                         => 'patrón de sueño',
            'habits.sleep.hours'                        => 'horas de sueño',
            'habits.sleep.frequency_per_day'            => 'frecuencia de sueño diaria',
            'habits.physical_activity.minutes_per_day'  => 'minutos de actividad física al día',
            'habits.physical_activity.times_per_week'   => 'veces de actividad física por semana',
            'habits.sexual_habits.active'               => 'vida sexual activa',
            'habits.sexual_habits.sexarche_age'         => 'edad de sexarquía',
            'habits.sexual_habits.partners_count'       => 'número de parejas sexuales',
            'habits.sexual_habits.orientation'          => 'orientación sexual',
            'habits.sexual_habits.frequency_per_week'   => 'frecuencia semanal',
            'habits.sexual_habits.contraceptive_method' => 'método anticonceptivo',
            'habits.housing.floor_material'             => 'material del piso',
            'habits.housing.roof_material'              => 'material del techo',
            'habits.housing.walls_material'             => 'material de las paredes',
            'habits.housing.rooms_count'                => 'número de habitaciones',
            'habits.housing.habitants_count'            => 'número de habitantes',
        ];
    }

    private function validateClinicalConsistency(): void
    {
        $data = $this->all();

        if (isset($data['background']['obgyn_fur'])) {
            $birthDate = \Carbon\Carbon::parse($data['birth_date']);
            $dueDate = \Carbon\Carbon::parse($data['background']['obgyn_fur']);
            $gestationalWeeks = $birthDate->diffInWeeks($dueDate);

            if ($gestationalWeeks < 0 || $gestationalWeeks > 42) {
                $this->throwClinicalValidationError('La fecha probable de parto debe indicar entre 0 y 42 semanas de gestación.');
            }
        }

        if (isset($data['background']['obgyn_menarche']) && isset($data['background']['obgyn_menopause'])) {
            $menarche = \Carbon\Carbon::parse($data['background']['obgyn_menarche']);
            $menopause = \Carbon\Carbon::parse($data['background']['obgyn_menopause']);

            if ($menopause->lt($menarche)) {
                $this->throwClinicalValidationError('La menopausia debe ser posterior a la menarca.');
            }

            $ageAtMenopause = $menopause->diffInYears(\Carbon\Carbon::parse($data['birth_date']));
            if ($ageAtMenopause < 45) {
                $this->throwClinicalValidationError('La menopausia no puede ser antes de los 45 años.');
            }
        }

        if (isset($data['background']['obgyn_menarche'])) {
            $birthDate = \Carbon\Carbon::parse($data['birth_date']);
            $menarcheDate = \Carbon\Carbon::parse($data['background']['obgyn_menarche']);
            $ageAtMenarche = $menarcheDate->diffInYears($birthDate);

            if ($ageAtMenarche < 9 || $ageAtMenarche > 16) {
                $this->throwClinicalValidationError('La menarca debe ser entre los 9 y 16 años de edad.');
            }
        }

        $birthDate = \Carbon\Carbon::parse($data['birth_date']);
        $currentAge = $birthDate->age;

        if (isset($data['habits']['alcohol']['start_age']) && $data['habits']['alcohol']['start_age'] > $currentAge) {
            $this->throwClinicalValidationError('La edad de inicio del consumo de alcohol no puede ser mayor que la edad actual.');
        }

        if (isset($data['habits']['tobacco']['start_age']) && $data['habits']['tobacco']['start_age'] > $currentAge) {
            $this->throwClinicalValidationError('La edad de inicio del consumo de tabaco no puede ser mayor que la edad actual.');
        }

        if (isset($data['habits']['physical_activity']['minutes_per_day']) && $data['habits']['physical_activity']['minutes_per_day'] > 300) {
            $this->throwClinicalValidationError('El tiempo de actividad física por día no debe exceder 5 horas (300 minutos).');
        }

        if (isset($data['habits']['physical_activity']['times_per_week']) && $data['habits']['physical_activity']['times_per_week'] > 14) {
            $this->throwClinicalValidationError('La frecuencia de actividad física por semana no debe exceder 14 veces.');
        }

        if (isset($data['habits']['nutrition']['meals_count'])) {
            $mealsCount = $data['habits']['nutrition']['meals_count'];
            if ($mealsCount < 1 || $mealsCount > 8) {
                $this->throwClinicalValidationError('El número de comidas por día debe ser entre 1 y 8.');
            }
        }
    }

    private function throwClinicalValidationError(string $message): void
    {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'clinical_validation_error' => $message,
        ]);
    }
}
