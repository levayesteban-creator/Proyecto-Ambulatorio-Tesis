<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Patient extends Model
{
    use HasFactory;

    /**
     * Atributos asignables de forma masiva (Mass Assignment)
     * Sincronizados exactamente con las reglas de StorePatientRequest.
     */
    protected $fillable = [
        'id_number',
        'full_name',
        'birth_date',
        'gender',
        'nationality',
        'nationality_country',
        'phone_number',
        'marital_status_id',
        'ethnicity_id',
        'religion_id',
        'religion_detail',
        'occupation_id',
        'occupation_detail',
        'instruction_level_id',
        'blood_type',
        'rh_factor',

        // Sincronizado: Lugar de nacimiento consolidado
        'birth_place',

        'addr_state',
        'addr_municipality',
        'addr_parish',
        'addr_locality',
        'addr_sector',
        'addr_street',
        'addr_house_number',

        // Sincronizado: Columna real en base de datos
        'addr_zip_code',

        'addr_reference',
        'residence_time',
    ];

    /**
     * Conversión de tipos nativos (Casts)
     */
    protected $casts = [
        'birth_date' => 'date',
    ];

    /**
     * Atributos dinámicos que se adjuntan automáticamente a la serialización JSON / Inertia.
     */
    protected $appends = [
    'age',
    'current_address', // Dirección consolidada que pre-carga el formulario de consulta
    'phone',           // Alias de phone_number para el formulario de consulta
];

    /**
     * --------------------------------------------------------------------------
     * ATRIBUTOS DINÁMICOS (ACCESSORS)
     * --------------------------------------------------------------------------
     */

    /**
     * Determina la edad exacta del paciente de forma dinámica en base a su fecha de nacimiento.
     * Evita almacenar enteros estáticos que quedan obsoletos cronológicamente.
     */
    protected function age(): Attribute
    {
        return Attribute::get(fn () =>
            $this->birth_date ? Carbon::parse($this->birth_date)->age : null
        );
    }

    /**
     * Dirección consolidada del paciente para pre-cargar el formulario de consulta.
     *
     * El Vue lee patient.current_address en Consultations/Create.vue.
     * Construye una cadena legible combinando los campos de dirección estructurada
     * de la tabla patients (addr_sector, addr_street, addr_house_number, addr_locality).
     * Si ningún campo tiene valor, devuelve cadena vacía (nunca null).
     */
    protected function currentAddress(): Attribute
    {
        return Attribute::get(function () {
            $parts = array_filter([
                $this->addr_sector,
                $this->addr_street,
                $this->addr_house_number,
                $this->addr_locality,
                $this->addr_parish,
                $this->addr_municipality,
            ]);

            return implode(', ', $parts);
        });
    }

    /**
     * Alias de phone_number para estandarizar el acceso desde el frontend.
     * El Vue lee patient.phone en Consultations/Create.vue.
     * Devuelve cadena vacía (nunca null) para evitar que el formulario quede undefined.
     */
    protected function phone(): Attribute
    {
        return Attribute::get(fn () => $this->phone_number ?? '');
    }

    /**
     * --------------------------------------------------------------------------
     * RELACIONES PARA INTEGRIDAD REFERENCIAL (Tablas Maestras / Catálogos)
     * --------------------------------------------------------------------------
     */

    public function maritalStatus(): BelongsTo
    {
        return $this->belongsTo(MaritalStatus::class);
    }

    public function ethnicity(): BelongsTo
    {
        return $this->belongsTo(Ethnicity::class);
    }

    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class);
    }

    public function occupation(): BelongsTo
    {
        return $this->belongsTo(Occupation::class);
    }

    public function instructionLevel(): BelongsTo
    {
        return $this->belongsTo(InstructionLevel::class);
    }

    /**
     * --------------------------------------------------------------------------
     * RELACIONES CLÍNICAS Y ANTECEDENTES
     * --------------------------------------------------------------------------
     */

    /**
     * Relación uno a uno con el historial clínico y antecedentes del paciente.
     */
    public function patientBackground(): HasOne
    {
        return $this->hasOne(PatientBackground::class, 'patient_id');
    }

    /**
     * Antecedentes específicos de la línea familiar consanguínea.
     */
    public function familyBackground(): HasOne
    {
        return $this->hasOne(FamilyBackground::class, 'patient_id');
    }

    /**
     * Obtiene los hábitos psicobiológicos asociados al paciente.
     * Corregido: Ajustado a camelCase estándar de Laravel manteniendo clave foránea explícita.
     */
    public function psychobiologicalHabit(): HasOne
    {
        return $this->hasOne(PsychobiologicalHabit::class, 'patient_id');
    }

    /**
     * Historial de consultas médicas asociadas al paciente.
     */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }
}
