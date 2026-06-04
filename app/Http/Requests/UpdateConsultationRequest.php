<?php

namespace App\Http\Requests;

/**
 * UpdateConsultationRequest
 * 
 * Hereda todas las reglas de validación de StoreConsultationRequest.
 * Permite mantener la lógica de validación centralizada mientras proporciona
 * explicititud y flexibilidad para futuras diferencias entre create y update.
 * 
 * EJEMPLO DE USO FUTURO:
 * Si necesitas validaciones diferentes en actualización:
 * 
 *    public function rules(): array
 *    {
 *        $rules = parent::rules();
 *        
 *        // No permitir cambiar tipo de consulta en actualización
 *        $rules['consultation_type'] = ['required', Rule::in(['P', 'S'])];
 *        
 *        return $rules;
 *    }
 */
class UpdateConsultationRequest extends StoreConsultationRequest
{
    // Hereda completamente de StoreConsultationRequest
    // Proporciona explicititud en ConsultationController::update()
}
