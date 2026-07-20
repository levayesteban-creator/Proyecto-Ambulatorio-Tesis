<?php

namespace App\Observers;

use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PatientObserver
{
    /**
     * Handle the Patient "created" event.
     */
    public function created(Patient $patient): void
    {
        $user = Auth::user();
        Log::channel('audit')->info('Patient created', [
            'patient_id' => $patient->id,
            'patient_name' => $patient->full_name,
            'patient_id_number' => $patient->id_number,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'ip_address' => request()->ip(),
            'created_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Handle the Patient "updated" event.
     */
    public function updated(Patient $patient): void
    {
        $user = Auth::user();
        $changes = $patient->getDirty();
        
        // Filtrar cambios sensibles para logging
        $sensitiveChanges = [];
        foreach ($changes as $field => $newValue) {
            $oldValue = $patient->getOriginal($field);
            
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
            Log::channel('audit')->info('Patient updated', [
                'patient_id' => $patient->id,
                'patient_name' => $patient->full_name,
                'patient_id_number' => $patient->id_number,
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
     * Handle the Patient "deleted" event.
     */
    public function deleted(Patient $patient): void
    {
        $user = Auth::user();
        Log::channel('audit')->warning('Patient deleted (soft delete)', [
            'patient_id' => $patient->id,
            'patient_name' => $patient->full_name,
            'patient_id_number' => $patient->id_number,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'ip_address' => request()->ip(),
            'deleted_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Handle the Patient "restored" event.
     */
    public function restored(Patient $patient): void
    {
        $user = Auth::user();
        Log::channel('audit')->info('Patient restored', [
            'patient_id' => $patient->id,
            'patient_name' => $patient->full_name,
            'patient_id_number' => $patient->id_number,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'ip_address' => request()->ip(),
            'restored_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Handle the Patient "force deleted" event.
     */
    public function forceDeleted(Patient $patient): void
    {
        $user = Auth::user();
        Log::channel('audit')->alert('Patient force deleted (permanent deletion)', [
            'patient_id' => $patient->id,
            'patient_name' => $patient->full_name,
            'patient_id_number' => $patient->id_number,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'ip_address' => request()->ip(),
            'force_deleted_at' => now()->toDateTimeString(),
        ]);
    }
}
