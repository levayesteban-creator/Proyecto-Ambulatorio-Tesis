<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultationReferral extends Model
{
    protected $table = 'consultation_referrals';

    protected $fillable = [
        'consultation_id',
        'specialty_code',
        'type',
    ];

    // Diccionario estático para renderizar los nombres de las especialidades sin sobrecargar la BD con otra tabla maestra
    public static array $specialties = [
        1 => 'Odontología', 2 => 'Oftalmología', 3 => 'Traumatología y Ortopedia', 4 => 'ORL',
        5 => 'Pediatría', 6 => 'Medicina Interna', 7 => 'Dermatología', 8 => 'Cirugía',
        9 => 'Nutrición', 10 => 'Neumonología', 11 => 'Ginecología', 12 => 'Patología de Cuello',
        13 => 'Patología de Mama', 14 => 'Obstetricia', 15 => 'Cardiología', 16 => 'Nefrología',
        17 => 'Salud Mental', 18 => 'Endocrinología', 19 => 'Neurología', 20 => 'Atención Psiquiátrica',
        21 => 'Hospitalización psiquiátrica', 22 => 'Comunidad terapéutica', 23 => 'Programas Sociales',
        24 => 'Educación Especial ME', 25 => 'Rehabilitación', 26 => 'Médico de Familia',
        27 => 'Reumatología', 28 => 'Oncología', 29 => 'Urología', 30 => 'Gastroenterología',
        31 => 'Psicología', 32 => 'Infectología', 33 => 'Cirugía Cardiovascular', 34 => 'Hematología',
        35 => 'Neuroradiología', 36 => 'Radiodiagnóstico', 37 => 'Toxicología', 38 => 'Alergólogo',
        39 => 'Optometrista'
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }
}
