<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultationFunctionalExam extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'consultation_functional_exams';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'consultation_id',
        'general_deny',
        'general_description',
        'skin_deny',
        'skin_description',
        'head_face_deny',
        'head_face_description',
        'neck_throat_deny',
        'neck_throat_description',
        'eyes_deny',
        'eyes_description',
        'mouth_deny',
        'mouth_description',
        'breasts_deny',
        'breasts_description',
        'ears_deny',
        'ears_description',
        'nose_deny',
        'nose_description',
        'respiratory_deny',
        'respiratory_description',
        'cardiovascular_deny',
        'cardiovascular_description',
        'gastrointestinal_deny',
        'gastrointestinal_description',
        'genitourinary_deny',
        'genitourinary_description',
        'menstrual_cycle_deny',
        'menstrual_cycle_description',
        'nervous_mental_deny',
        'nervous_mental_description',
        'osteomuscular_deny',
        'osteomuscular_description',
    ];

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'general_deny'            => 'boolean',
        'skin_deny'               => 'boolean',
        'head_face_deny'          => 'boolean',
        'neck_throat_deny'        => 'boolean',
        'eyes_deny'               => 'boolean',
        'mouth_deny'              => 'boolean',
        'breasts_deny'            => 'boolean',
        'ears_deny'               => 'boolean',
        'nose_deny'               => 'boolean',
        'respiratory_deny'        => 'boolean',
        'cardiovascular_deny'     => 'boolean',
        'gastrointestinal_deny'   => 'boolean',
        'genitourinary_deny'     => 'boolean',
        'menstrual_cycle_deny'    => 'boolean',
        'nervous_mental_deny'     => 'boolean',
        'osteomuscular_deny'      => 'boolean',
    ];

    /**
     * Obtiene la consulta médica a la que pertenece este examen funcional.
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }
}
