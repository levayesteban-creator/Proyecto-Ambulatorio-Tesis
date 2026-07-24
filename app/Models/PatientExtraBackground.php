<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientExtraBackground extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'category',
        'disease_name',
        'onset_value',
        'onset_unit',
        'treatment',
        'complications',
        'description',
    ];

    protected $casts = [
        'onset_value' => 'integer',
    ];

    const CATEGORIES = [
        'allergic'        => 'Alérgicos',
        'pathological'    => 'Patológicos',
        'infectious'      => 'Infectocontagiosos',
        'immunological'   => 'Inmunológicos',
        'transfusion'     => 'Transfusionales',
        'obgyn'           => 'Gineco-Obstétricos',
        'surgical'        => 'Quirúrgicos',
        'traumatic'       => 'Traumáticos',
        'std'             => 'ETS',
        'epidemiological' => 'Epidemiológicos',
        'disability'      => 'Discapacidades',
    ];

    const ONSET_UNITS = [
        'años'   => 'Años',
        'meses'  => 'Meses',
        'días'   => 'Días',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function categoryLabel(): string
    {
        return static::CATEGORIES[$this->category] ?? $this->category;
    }

    public function onsetUnitLabel(): string
    {
        return static::ONSET_UNITS[$this->onset_unit] ?? $this->onset_unit;
    }
}
