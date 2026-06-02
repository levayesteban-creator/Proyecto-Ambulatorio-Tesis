<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * EXAMEN FÍSICO — consultation_physical_exams
     *
     * Estructura: una fila por consulta, cada sección del examen físico
     * almacenada como columna JSON independiente.
     *
     * Secciones:
     *   General  → facies, marcha, aptitud, biotipo, piel, ganglios
     *   Especial → 9 secciones de cabeza a extremidades
     *   Neuro    → Glasgow, conciencia, motor, pares craneales (sección propia)
     */
    public function up(): void
    {
        Schema::create('consultation_physical_exams', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consultation_id')
                ->unique()
                ->constrained('consultations')
                ->cascadeOnDelete()
                ->comment('Relación 1:1 con la consulta médica principal');

            // ── SECCIÓN GENERAL ───────────────────────────────────────────

            $table->json('general_data')
                ->nullable()
                ->comment('{ facies, marcha, aptitud, biotipo }');

            $table->json('skin')
                ->nullable()
                ->comment('{ fitzpatrick, coloration, temperature, hydration, turgor, elasticity, nail_color, nail_appearance, capillary_refill, lesions }');

            $table->json('lymph_nodes')
                ->nullable()
                ->comment('{ location, size, painful, consistency, mobility, arrangement }');

            // ── SECCIÓN ESPECIAL ──────────────────────────────────────────

            $table->json('head')
                ->nullable()
                ->comment('{ type[Mesocéfalo/Bradicéfalo/Macrocéfalo], superficial_pain, superficial_detail, deep_pain, deep_detail, swelling, scalp, hair_distribution, hair_color, hair_quantity, hair_type, acromotrichia, alopecia }');

            $table->json('eyes')
                ->nullable()
                ->comment('{ implantation, symmetry, cornea, sclera, iris, pupils, conjunctiva, brows_lashes, eyelids, fundus }');

            $table->json('nose')
                ->nullable()
                ->comment('{ septum[central/desviado], pyramid, passages, mucosa, turbinates, secretions, sinuses }');

            $table->json('mouth_pharynx')
                ->nullable()
                ->comment('{ lips, gums, dental_arch[completa/incompleta], dental_detail, tongue, pharynx, tonsils, uvula, pillars }');

            $table->json('ears')
                ->nullable()
                ->comment('{ auricles, canal, tympanic_membrane }');

            $table->json('neck')
                ->nullable()
                ->comment('{ length[largo/corto], form, symmetry, mobility, jugular_pulse, trachea, thyroid }');

            $table->json('thorax')
                ->nullable()
                ->comment('{ symmetry, body_type[normolínea/brevilínea/longilínea], configuration, venous_circulation, expansibility, breathing_type, intercostal_retraction, nasal_flaring, palpation_pain, vocal_vibrations, vesicular_murmur, laryngotracheal_murmur, added_sounds, sonority }');

            $table->json('cardiovascular')
                ->nullable()
                ->comment('{ apex_visible, apex_palpable, dullness, heart_sounds, murmurs }');

            $table->json('breasts')
                ->nullable()
                ->comment('{ size, symmetry, masses, secretions, nipple }');

            $table->json('abdomen')
                ->nullable()
                ->comment('{ form, venous_circulation, breathing_protrusions, valsalva_protrusions, depressible, bowel_sounds, vascular_sounds, tympanism, hepatic_dullness, palpation_pain, masses, hepatometry_parasternal, hepatometry_midclavicular, hepatometry_axillary, kidney_punch }');

            $table->json('genital')
                ->nullable()
                ->comment('Masculino: { testes, scrotum, lesions } | Femenino: { vulva, hymen, vagina, prolapse, cervix }');

            $table->json('rectal_exam')
                ->nullable()
                ->comment('{ anus, rectal_touch, prostate, masses }');

            // ── NEUROLÓGICO (sección propia por complejidad) ──────────────

            $table->json('neurological')
                ->nullable()
                ->comment('{ glasgow_eye, glasgow_verbal, glasgow_motor, consciousness_level, consciousness_state, language, thought, memory, calculation, upper_strength, lower_strength, babinski, reflex_bicipital, reflex_styloradial, reflex_patellar, reflex_achilles, involuntary_movements, cn_i, cn_ii, cn_iii_iv_vi, cn_v, cn_vii, cn_viii, cn_ix_x, cn_xi, cn_xii }');

            // ── EXTREMIDADES ──────────────────────────────────────────────

            $table->json('extremities')
                ->nullable()
                ->comment('{ symmetry, varicose_veins, edema, peripheral_pulse, mobility, muscle_tone, paresthesias, pain, deformity }');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_physical_exams');
    }
};
