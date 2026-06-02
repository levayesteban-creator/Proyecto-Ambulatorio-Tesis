<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consultation_functional_exams', function (Blueprint $table) {
            $table->id();

            // Relación con la consulta principal
            $table->foreignId('consultation_id')
                  ->constrained()
                  ->onDelete('cascade')
                  ->comment('Enlace con la consulta médica principal');

            // 1. GENERAL (Mareos | Fiebre | Escalofríos | ↑o ↓Peso | Sudoración)
            $table->boolean('general_deny')->default(true);
            $table->text('general_description')->nullable();

            // 2. PIEL (Tipo de lesión, ubicación, diámetro, cantidad, forma, secreciones, etc.)
            $table->boolean('skin_deny')->default(true);
            $table->text('skin_description')->nullable();

            // 3. CABEZA Y CARA (Deformidades | tumoraciones | alopecia | movimientos anormales)
            $table->boolean('head_face_deny')->default(true);
            $table->text('head_face_description')->nullable();

            // 4. CUELLO Y GARGANTA (Dolor | movilidad | latidos | bocio | adenopatías | tumoraciones)
            $table->boolean('neck_throat_deny')->default(true);
            $table->text('neck_throat_description')->nullable();

            // 5. OJOS (Agudeza visual | Epifora | fotofobia | eritema | secreción | fosfenos | escotomas | diplopía | exoftalmos)
            $table->boolean('eyes_deny')->default(true);
            $table->text('eyes_description')->nullable();

            // 6. BOCA (Gusto | lesiones | halitosis | pigmentaciones | prótesis | caries | edéntula)
            $table->boolean('mouth_deny')->default(true);
            $table->text('mouth_description')->nullable();

            // 7. MAMAS (dolor | tumor | secreciones | ginecomastia | galactorrea)
            $table->boolean('breasts_deny')->default(true);
            $table->text('breasts_description')->nullable();

            // 8. OÍDOS (audición | otalgia | secreción | acúfenos)
            $table->boolean('ears_deny')->default(true);
            $table->text('ears_description')->nullable();

            // 9. NARIZ (Obstrucción | deformidad | rinorrea | prurito | rinorraquia | epistaxis | hiposmia | anosmia | cacosmia | parosmia)
            $table->boolean('nose_deny')->default(true);
            $table->text('nose_description')->nullable();

            // 10. RESPIRATORIO (dolor torácico | estridor | disfonía | tos | expectoración | hemoptisis | vómica)
            $table->boolean('respiratory_deny')->default(true);
            $table->text('respiratory_description')->nullable();

            // 11. CARDIOVASCULAR (dolor precordial | disnea | síncope | edema | palpitaciones)
            $table->boolean('cardiovascular_deny')->default(true);
            $table->text('cardiovascular_description')->nullable();

            // 12. GASTROINTESTINAL (N° evacuaciones, color, olor, Bristol, moco, sangre, pirosis, etc.)
            // Nota: Este sistema no suele llevar un "Niega" directo ya que siempre hay evacuaciones que describir bajo la escala de Bristol.
            $table->text('gastrointestinal_description')->nullable();

            // 13. GENITOURINARIO (N° micciones, patrón miccional, color, olor, turbidez, disuria, etc.)
            $table->text('genitourinary_description')->nullable();

            // 14. CICLO MENSTRUAL (periodicidad | duración | cuantía | FUR | menopausia | climaterio | amenorrea, etc.)
            $table->boolean('menstrual_cycle_deny')->default(true);
            $table->text('menstrual_cycle_description')->nullable();

            // 15. NERVIOSO Y MENTAL (síncopes | vértigo | anestesia | paresia | parestesia | parálisis | convulsiones | personalidad)
            $table->boolean('nervous_mental_deny')->default(true);
            $table->text('nervous_mental_description')->nullable();

            // 16. OSTEOMUSCULAR (mialgias | artralgias | artritis | claudicación | deformidad | aumento de volumen)
            $table->boolean('osteomuscular_deny')->default(true);
            $table->text('osteomuscular_description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_functional_exams');
    }
};
