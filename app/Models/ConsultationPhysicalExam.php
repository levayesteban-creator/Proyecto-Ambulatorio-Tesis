<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Examen físico estructurado (1:1 con Consultation).
 *
 * Cada sección anatómica es una columna JSON; el frontend envía objetos
 * anidados bajo physical_exam.* y el ORM los persiste vía $casts.
 */
class ConsultationPhysicalExam extends Model
{
    use HasFactory;

    protected $table = 'consultation_physical_exams';

    protected $fillable = [
        'consultation_id',
        'general_data',
        'skin',
        'lymph_nodes',
        'head',
        'eyes',
        'nose',
        'mouth_pharynx',
        'ears',
        'neck',
        'thorax',
        'cardiovascular',
        'breasts',
        'abdomen',
        'genital',
        'rectal_exam',
        'neurological',
        'extremities',
    ];

    protected $casts = [
        'general_data'   => 'array',
        'skin'           => 'array',
        'lymph_nodes'    => 'array',
        'head'           => 'array',
        'eyes'           => 'array',
        'nose'           => 'array',
        'mouth_pharynx'  => 'array',
        'ears'           => 'array',
        'neck'           => 'array',
        'thorax'         => 'array',
        'cardiovascular' => 'array',
        'breasts'        => 'array',
        'abdomen'        => 'array',
        'genital'        => 'array',
        'rectal_exam'    => 'array',
        'neurological'   => 'array',
        'extremities'    => 'array',
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }
}
