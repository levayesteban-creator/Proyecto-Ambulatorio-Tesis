<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ================================================================
            // 1. FICHA CORE DE LA CONSULTA
            // ================================================================
            'patient_id'              => ['nullable', 'exists:patients,id'],
            'consultation_type'       => ['required', Rule::in(['P', 'S', 'X'])],
            'service_type'            => ['nullable', Rule::in(array_keys(\App\Models\Consultation::SERVICE_TYPES))],
            'is_healthy'              => ['required', 'boolean'],
            'reason_for_consultation' => [
                Rule::requiredIf(fn () => ! $this->boolean('is_healthy')),
                'nullable',
                'string',
                'max:5000',
            ],
            'current_illness'         => [
                Rule::requiredIf(fn () => ! $this->boolean('is_healthy')),
                'nullable',
                'string',
                'max:10000',
            ],
            'treatment_plan'          => ['nullable', 'string', 'max:5000'],
            'physical_examination'    => ['nullable', 'string', 'max:10000'],
            'complementary_studies'   => ['nullable', 'string', 'max:10000'],
            'epicrisis'               => ['nullable', 'string', 'max:10000'],
            'attended_at'             => ['nullable', 'date'],
            // ================================================================
            // 2. SIGNOS VITALES (todos nullable — puede ser consulta preventiva)
            // ================================================================
            'blood_pressure'     => ['nullable', 'string', 'max:10', 'regex:/^\d{1,3}\/\d{1,3}$/'],
            'temperature'        => ['nullable', 'numeric', 'between:30,45'],
            'temperature_route'  => ['nullable', Rule::in(['Oral', 'Axilar', 'Rectal', 'Timpánica'])],
            'heart_rate'         => ['nullable', 'integer', 'between:20,300'],
            'respiratory_rate'   => ['nullable', 'integer', 'between:5,60'],
            'oxygen_saturation'  => ['nullable', 'numeric', 'between:50,100'],
            'weight'             => ['nullable', 'numeric', 'between:0.5,500'],
            'height'             => ['nullable', 'numeric', 'between:0.3,2.5'],

            // ================================================================
            // 3. EXAMEN FUNCIONAL POR APARATOS Y SISTEMAS (16 sistemas)
            // ================================================================
            'functional_exam'                               => ['required', 'array'],
            'functional_exam.general_deny'                  => ['required', 'boolean'],
            'functional_exam.general_description'           => ['required_if:functional_exam.general_deny,false', 'nullable', 'string', 'max:2000'],
            'functional_exam.skin_deny'                     => ['required', 'boolean'],
            'functional_exam.skin_description'              => ['required_if:functional_exam.skin_deny,false', 'nullable', 'string', 'max:2000'],
            'functional_exam.head_face_deny'                => ['required', 'boolean'],
            'functional_exam.head_face_description'         => ['required_if:functional_exam.head_face_deny,false', 'nullable', 'string', 'max:2000'],
            'functional_exam.neck_throat_deny'              => ['required', 'boolean'],
            'functional_exam.neck_throat_description'       => ['required_if:functional_exam.neck_throat_deny,false', 'nullable', 'string', 'max:2000'],
            'functional_exam.eyes_deny'                     => ['required', 'boolean'],
            'functional_exam.eyes_description'              => ['required_if:functional_exam.eyes_deny,false', 'nullable', 'string', 'max:2000'],
            'functional_exam.mouth_deny'                    => ['required', 'boolean'],
            'functional_exam.mouth_description'             => ['required_if:functional_exam.mouth_deny,false', 'nullable', 'string', 'max:2000'],
            'functional_exam.breasts_deny'                  => ['required', 'boolean'],
            'functional_exam.breasts_description'           => ['required_if:functional_exam.breasts_deny,false', 'nullable', 'string', 'max:2000'],
            'functional_exam.ears_deny'                     => ['required', 'boolean'],
            'functional_exam.ears_description'              => ['required_if:functional_exam.ears_deny,false', 'nullable', 'string', 'max:2000'],
            'functional_exam.nose_deny'                     => ['required', 'boolean'],
            'functional_exam.nose_description'              => ['required_if:functional_exam.nose_deny,false', 'nullable', 'string', 'max:2000'],
            'functional_exam.respiratory_deny'              => ['required', 'boolean'],
            'functional_exam.respiratory_description'       => ['required_if:functional_exam.respiratory_deny,false', 'nullable', 'string', 'max:2000'],
            'functional_exam.cardiovascular_deny'           => ['required', 'boolean'],
            'functional_exam.cardiovascular_description'    => ['required_if:functional_exam.cardiovascular_deny,false', 'nullable', 'string', 'max:2000'],
            'functional_exam.gastrointestinal_deny'         => ['required', 'boolean'],
            'functional_exam.gastrointestinal_description'  => ['nullable', 'string', 'max:2000'],
            'functional_exam.genitourinary_deny'            => ['required', 'boolean'],
            'functional_exam.genitourinary_description'     => ['nullable', 'string', 'max:2000'],
            'functional_exam.menstrual_cycle_deny'          => ['required', 'boolean'],
            'functional_exam.menstrual_cycle_description'   => ['required_if:functional_exam.menstrual_cycle_deny,false', 'nullable', 'string', 'max:2000'],
            'functional_exam.nervous_mental_deny'           => ['required', 'boolean'],
            'functional_exam.nervous_mental_description'    => ['required_if:functional_exam.nervous_mental_deny,false', 'nullable', 'string', 'max:2000'],
            'functional_exam.osteomuscular_deny'            => ['required', 'boolean'],
            'functional_exam.osteomuscular_description'     => ['required_if:functional_exam.osteomuscular_deny,false', 'nullable', 'string', 'max:2000'],

            // ================================================================
            // 4. EXAMEN FÍSICO ESTRUCTURADO (17 secciones JSON — todas opcionales)
            // Solo validamos que cada sección, si se envía, sea array.
            // La estructura interna es flexible (JSON libre por sección).
            // ================================================================
            'physical_exam'                  => ['nullable', 'array'],
            'physical_exam.general_data'     => ['nullable', 'array'],
            'physical_exam.skin'             => ['nullable', 'array'],
            'physical_exam.lymph_nodes'      => ['nullable', 'array'],
            'physical_exam.head'             => ['nullable', 'array'],
            'physical_exam.eyes'             => ['nullable', 'array'],
            'physical_exam.nose'             => ['nullable', 'array'],
            'physical_exam.mouth_pharynx'    => ['nullable', 'array'],
            'physical_exam.ears'             => ['nullable', 'array'],
            'physical_exam.neck'             => ['nullable', 'array'],
            'physical_exam.thorax'           => ['nullable', 'array'],
            'physical_exam.cardiovascular'   => ['nullable', 'array'],
            'physical_exam.breasts'          => ['nullable', 'array'],
            'physical_exam.abdomen'          => ['nullable', 'array'],
            'physical_exam.genital'          => ['nullable', 'array'],
            'physical_exam.rectal_exam'      => ['nullable', 'array'],
            'physical_exam.neurological'     => ['nullable', 'array'],
            'physical_exam.extremities'      => ['nullable', 'array'],

            // ================================================================
            // 5. REFERENCIAS A ESPECIALIDADES
            // ================================================================
            'referrals'                  => ['nullable', 'array'],
            'referrals.*.specialty_code' => ['required', 'integer', 'min:1', 'max:39'],
            'referrals.*.type'           => ['required', Rule::in(['referral', 'counter_referral'])],

            // ================================================================
            // 6. DIAGNÓSTICOS SIS
            // ================================================================
            'diagnoses'                       => ['required', 'array', 'min:1'],
            'diagnoses.*.sis_diagnosis_id'    => ['nullable', 'exists:sis_diagnoses,id'],
            'diagnoses.*.unlisted_diagnosis'  => ['nullable', 'string', 'max:500'],
            'diagnoses.*.diagnosis_type'      => ['required', Rule::in(['Sospechoso', 'Probable', 'Confirmado', 'No Aplica'])],
            'diagnoses.*.sort_order'          => ['required', 'integer', 'min:1'],
            'diagnoses.*.medical_conduct_id'  => ['nullable', 'exists:medical_conducts,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'consultation_type.required'             => 'El tipo de consulta es obligatorio.',
            'is_healthy.required'                    => 'Indique si el paciente está sano.',
            'reason_for_consultation.required'       => 'El motivo de la consulta es obligatorio.',
            'current_illness.required'               => 'La enfermedad actual es obligatoria.',
            'blood_pressure.regex'                   => 'La T.A. debe tener formato ###/### (ej: 120/80).',
            'temperature.between'                    => 'La temperatura debe estar entre 30°C y 45°C.',
            'oxygen_saturation.between'              => 'La saturación de O₂ debe estar entre 50% y 100%.',
            'heart_rate.between'                     => 'La FC debe estar entre 20 y 300 lpm.',
            'respiratory_rate.between'               => 'La FR debe estar entre 5 y 60 rpm.',
            'weight.between'                         => 'El peso debe estar entre 0.5 y 500 kg.',
            'height.between'                         => 'La talla debe estar entre 0.3 y 2.5 m.',
            'functional_exam.required'               => 'El examen funcional por aparatos y sistemas es obligatorio.',
            'functional_exam.general_deny.required'              => 'Indique si el examen general está negado.',
            'functional_exam.general_description.required_if'    => 'La descripción del examen general es obligatoria.',
            'functional_exam.skin_deny.required'                 => 'Indique si el examen de piel está negado.',
            'functional_exam.skin_description.required_if'       => 'La descripción del examen de piel es obligatoria.',
            'functional_exam.head_face_deny.required'            => 'Indique si el examen de cabeza está negado.',
            'functional_exam.head_face_description.required_if'  => 'La descripción del examen de cabeza es obligatoria.',
            'functional_exam.neck_throat_deny.required'          => 'Indique si el examen de cuello está negado.',
            'functional_exam.neck_throat_description.required_if' => 'La descripción del examen de cuello es obligatoria.',
            'functional_exam.eyes_deny.required'                 => 'Indique si el examen de ojos está negado.',
            'functional_exam.eyes_description.required_if'       => 'La descripción del examen de ojos es obligatoria.',
            'functional_exam.mouth_deny.required'                => 'Indique si el examen de boca está negado.',
            'functional_exam.mouth_description.required_if'      => 'La descripción del examen de boca es obligatoria.',
            'functional_exam.breasts_deny.required'              => 'Indique si el examen de mamas está negado.',
            'functional_exam.breasts_description.required_if'    => 'La descripción del examen de mamas es obligatoria.',
            'functional_exam.ears_deny.required'                 => 'Indique si el examen de oídos está negado.',
            'functional_exam.ears_description.required_if'       => 'La descripción del examen de oídos es obligatoria.',
            'functional_exam.nose_deny.required'                 => 'Indique si el examen de nariz está negado.',
            'functional_exam.nose_description.required_if'       => 'La descripción del examen de nariz es obligatoria.',
            'functional_exam.respiratory_deny.required'          => 'Indique si el examen respiratorio está negado.',
            'functional_exam.respiratory_description.required_if' => 'La descripción del examen respiratorio es obligatoria.',
            'functional_exam.cardiovascular_deny.required'       => 'Indique si el examen cardiovascular está negado.',
            'functional_exam.cardiovascular_description.required_if' => 'La descripción del examen cardiovascular es obligatoria.',
            'functional_exam.menstrual_cycle_deny.required'      => 'Indique si el ciclo menstrual está negado.',
            'functional_exam.menstrual_cycle_description.required_if' => 'La descripción del ciclo menstrual es obligatoria.',
            'functional_exam.nervous_mental_deny.required'       => 'Indique si el examen neuro-mental está negado.',
            'functional_exam.nervous_mental_description.required_if' => 'La descripción del examen neuro-mental es obligatoria.',
            'functional_exam.osteomuscular_deny.required'        => 'Indique si el examen osteomuscular está negado.',
            'functional_exam.osteomuscular_description.required_if' => 'La descripción del examen osteomuscular es obligatoria.',
            'diagnoses.required'                     => 'Debe ingresar al menos un diagnóstico.',
            'diagnoses.min'                          => 'Debe ingresar al menos un diagnóstico.',
            'diagnoses.*.sis_diagnosis_id.exists'    => 'El diagnóstico seleccionado no es válido.',
            'diagnoses.*.diagnosis_type.required'    => 'Especifique el tipo de diagnóstico.',
            'diagnoses.*.sort_order.required'        => 'El orden del diagnóstico es obligatorio.',
            'diagnoses.*.sort_order.min'             => 'El orden del diagnóstico debe ser al menos 1.',
            'referrals.*.specialty_code.required'    => 'El código de especialidad es obligatorio.',
            'referrals.*.specialty_code.min'         => 'Código de especialidad entre 1 y 39 (catálogo MPPS).',
            'referrals.*.specialty_code.max'         => 'Código de especialidad entre 1 y 39 (catálogo MPPS).',
            'referrals.*.type.required'              => 'Indique si es Referencia o Contrarreferencia.',
        ];
    }

    public function attributes(): array
    {
        return [
            'patient_id'                           => 'paciente',
            'consultation_type'                    => 'tipo de consulta',
            'service_type'                         => 'tipo de servicio',
            'is_healthy'                           => 'paciente sano',
            'reason_for_consultation'              => 'motivo de consulta',
            'current_illness'                      => 'enfermedad actual',
            'treatment_plan'                       => 'plan de tratamiento',
            'physical_examination'                 => 'examen físico',
            'complementary_studies'                => 'estudios complementarios',
            'epicrisis'                            => 'epicrisis',
            'attended_at'                          => 'fecha de atención',

            'blood_pressure'                       => 'tensión arterial',
            'temperature'                          => 'temperatura',
            'temperature_route'                    => 'vía de toma de temperatura',
            'heart_rate'                           => 'frecuencia cardíaca',
            'respiratory_rate'                     => 'frecuencia respiratoria',
            'oxygen_saturation'                    => 'saturación de oxígeno',
            'weight'                               => 'peso',
            'height'                               => 'talla',

            'functional_exam.general_deny'                  => 'examen general negado',
            'functional_exam.general_description'           => 'descripción del examen general',
            'functional_exam.skin_deny'                      => 'examen de piel negado',
            'functional_exam.skin_description'               => 'descripción del examen de piel',
            'functional_exam.head_face_deny'                 => 'examen de cabeza negado',
            'functional_exam.head_face_description'          => 'descripción del examen de cabeza',
            'functional_exam.neck_throat_deny'               => 'examen de cuello negado',
            'functional_exam.neck_throat_description'        => 'descripción del examen de cuello',
            'functional_exam.eyes_deny'                       => 'examen de ojos negado',
            'functional_exam.eyes_description'               => 'descripción del examen de ojos',
            'functional_exam.mouth_deny'                      => 'examen de boca negado',
            'functional_exam.mouth_description'               => 'descripción del examen de boca',
            'functional_exam.breasts_deny'                    => 'examen de mamas negado',
            'functional_exam.breasts_description'             => 'descripción del examen de mamas',
            'functional_exam.ears_deny'                       => 'examen de oídos negado',
            'functional_exam.ears_description'                => 'descripción del examen de oídos',
            'functional_exam.nose_deny'                       => 'examen de nariz negado',
            'functional_exam.nose_description'                => 'descripción del examen de nariz',
            'functional_exam.respiratory_deny'                => 'examen respiratorio negado',
            'functional_exam.respiratory_description'         => 'descripción del examen respiratorio',
            'functional_exam.cardiovascular_deny'             => 'examen cardiovascular negado',
            'functional_exam.cardiovascular_description'      => 'descripción del examen cardiovascular',
            'functional_exam.gastrointestinal_deny'           => 'examen gastrointestinal negado',
            'functional_exam.gastrointestinal_description'    => 'descripción del examen gastrointestinal',
            'functional_exam.genitourinary_deny'              => 'examen genitourinario negado',
            'functional_exam.genitourinary_description'       => 'descripción del examen genitourinario',
            'functional_exam.menstrual_cycle_deny'            => 'ciclo menstrual negado',
            'functional_exam.menstrual_cycle_description'     => 'descripción del ciclo menstrual',
            'functional_exam.nervous_mental_deny'             => 'examen neuro-mental negado',
            'functional_exam.nervous_mental_description'      => 'descripción del examen neuro-mental',
            'functional_exam.osteomuscular_deny'              => 'examen osteomuscular negado',
            'functional_exam.osteomuscular_description'       => 'descripción del examen osteomuscular',

            'diagnoses'                              => 'diagnósticos',
            'referrals'                              => 'referencias',
        ];
    }
}
