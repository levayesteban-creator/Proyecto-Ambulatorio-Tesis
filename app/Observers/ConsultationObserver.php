<?php

namespace App\Observers;

use App\Models\Consultation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ConsultationObserver
{
    /**
     * Handle the Consultation "created" event.
     */
    public function created(Consultation $consultation): void
    {
        $user = Auth::user();
        Log::channel('audit')->info('Consultation created', [
            'consultation_id' => $consultation->id,
            'patient_id' => $consultation->patient_id,
            'patient_name' => $consultation->patient->full_name ?? 'N/A',
            'consultation_type' => $consultation->consultation_type,
            'reason_for_consultation' => $consultation->reason_for_consultation,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'ip_address' => request()->ip(),
            'created_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Handle the Consultation "updated" event.
     */
    public function updated(Consultation $consultation): void
    {
        $user = Auth::user();
        $changes = $consultation->getDirty();
        
        // Filtrar cambios sensibles para logging
        $sensitiveChanges = [];
        foreach ($changes as $field => $newValue) {
            $oldValue = $consultation->getOriginal($field);
            
            // No loggear timestamps que cambian automáticamente
            if (in_array($field, ['updated_at', 'created_at'])) {
                continue;
            }
            
            // Loggear el cambio
            $sensitiveChanges[$field] = [
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }
        
        if (!empty($sensitiveChanges)) {
            Log::channel('audit')->info('Consultation updated', [
                'consultation_id' => $consultation->id,
                'patient_id' => $consultation->patient_id,
                'patient_name' => $consultation->patient->full_name ?? 'N/A',
                'user_id' => $user?->id,
                'user_name' => $user?->name,
                'user_email' => $user?->email,
                'ip_address' => request()->ip(),
                'changes' => $sensitiveChanges,
                'updated_at' => now()->toDateTimeString(),
            ]);
        }
    }

    /**
     * Handle the Consultation "deleted" event.
     */
    public function deleted(Consultation $consultation): void
    {
        $user = Auth::user();
        Log::channel('audit')->warning('Consultation deleted (soft delete)', [
            'consultation_id' => $consultation->id,
            'patient_id' => $consultation->patient_id,
            'patient_name' => $consultation->patient->full_name ?? 'N/A',
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'ip_address' => request()->ip(),
            'deleted_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Handle the Consultation "restored" event.
     */
    public function restored(Consultation $consultation): void
    {
        $user = Auth::user();
        Log::channel('audit')->info('Consultation restored', [
            'consultation_id' => $consultation->id,
            'patient_id' => $consultation->patient_id,
            'patient_name' => $consultation->patient->full_name ?? 'N/A',
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'ip_address' => request()->ip(),
            'restored_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Handle the Consultation "force deleted" event.
     */
    public function forceDeleted(Consultation $consultation): void
    {
        $user = Auth::user();
        Log::channel('audit')->alert('Consultation force deleted (permanent deletion)', [
            'consultation_id' => $consultation->id,
            'patient_id' => $consultation->patient_id,
            'patient_name' => $consultation->patient->full_name ?? 'N/A',
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'ip_address' => request()->ip(),
            'force_deleted_at' => now()->toDateTimeString(),
        ]);
    }
}
