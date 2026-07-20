<?php

namespace App\Http\Requests;

class UpdateConsultationRequest extends StoreConsultationRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['edit_justification'] = ['required', 'string', 'min:10'];
        return $rules;
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'edit_justification.required' => 'Debe indicar la justificación del cambio (mín. 10 caracteres).',
            'edit_justification.min' => 'La justificación debe tener al menos 10 caracteres.',
        ]);
    }
}
