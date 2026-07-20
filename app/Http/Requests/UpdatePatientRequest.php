<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends StorePatientRequest
{
    public function authorize(): bool
    {
        $patient = $this->route('patient');
        return Gate::allows('update', $patient);
    }

    public function rules(): array
    {
        $rules = parent::rules();

        $patientId = $this->route('patient')?->id ?? $this->route('patient');

        $rules['id_number'] = [
            'required',
            'string',
            'max:20',
            Rule::unique('patients', 'id_number')->ignore($patientId),
        ];

        return $rules;
    }
}
