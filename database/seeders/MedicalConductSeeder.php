<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicalConduct;

class MedicalConductSeeder extends Seeder
{
    public function run(): void
    {
        $conducts = [
            ['code' => '01', 'name' => 'Orientacion sobre horarios de comidas'],
            ['code' => '02', 'name' => 'Ambiente libre de humo de tabaco'],
            ['code' => '03', 'name' => 'Atencion prenatal adecuada'],
            ['code' => '04', 'name' => 'Hábitos saludables para el el cuidado del recien nacido'],
            ['code' => '05', 'name' => 'Orientación sobre evaluación ondontológica'],
            ['code' => '06', 'name' => 'Consejo de evaluación genética'],
            ['code' => '07', 'name' => 'Baja ingesta de sal'],
            ['code' => '08', 'name' => 'Baja ingesta de azúcares refinados'],
            ['code' => '09', 'name' => 'Lactancia materna exclusiva'],
            ['code' => '10', 'name' => 'Lactancia materna complementaria'],
            ['code' => '11', 'name' => 'Práctica de ejercicio y deportes'],
            ['code' => '12', 'name' => 'Educación sobre riesgos de accidentes'],
            ['code' => '13', 'name' => 'Educación en salud sexual y reproductiva'],
            ['code' => '14', 'name' => 'Cepillado dental'],
            ['code' => '15', 'name' => 'Hábitos de higine visual'],
            ['code' => '16', 'name' => 'Mantener el peso corporal'],
            ['code' => '17', 'name' => 'Evitar el consumo de alcohol'],
            ['code' => '18', 'name' => 'Manejo adecuado del estrés'],
            ['code' => '19', 'name' => 'Abandonar el consumo tabáquico'],
            ['code' => '20', 'name' => 'Auto examen de mama'],
            ['code' => '21', 'name' => 'Intensificar los cuidados prenatales'],
            ['code' => '22', 'name' => 'Exámenes complementarios de laboratorio'],
            ['code' => '23', 'name' => 'Exámenes complementarios radiologicos'],
            ['code' => '24', 'name' => 'Uso de preservativos'],
            ['code' => '25', 'name' => 'Toma de citología '],
            ['code' => '26', 'name' => 'Orientación necesidad de inmunizaciones'],
            ['code' => '27', 'name' => 'Uso de mosquiteros'],
            ['code' => '28', 'name' => 'Eliminación de criaderos'],
            ['code' => '29', 'name' => 'Reposo absoluto'],
            ['code' => '30', 'name' => 'Disminuir peso corporal'],
            ['code' => '31', 'name' => 'Medición periódica de la tensión arterial'],
            ['code' => '32', 'name' => 'Aislamiento debido a la infección'],
            ['code' => '33', 'name' => 'Cuidados de enfermería'],
            ['code' => '34', 'name' => 'Extracción de objeto extraño'],
            ['code' => '35', 'name' => 'Inmovilización por soporte y vendas'],
            ['code' => '36', 'name' => 'Inmovilización por férulas o yesos'],
            ['code' => '37', 'name' => 'Vacunación antirrábica'],
            ['code' => '38', 'name' => 'Seguimiento médico'],
            ['code' => '39', 'name' => 'Oclusión ocular'],
            ['code' => '40', 'name' => 'Seguimiento por personal de enfermería'],
            ['code' => '41', 'name' => 'Signos de alarma durante el embarazo'],
            ['code' => '42', 'name' => 'Nebulizaciones'],
            ['code' => '43', 'name' => 'Curas'],
            ['code' => '44', 'name' => 'Paciente en observación 8 - 12 horas'],
            ['code' => '45', 'name' => 'Examen de la presión sanguínea'],
            ['code' => '46', 'name' => 'Examen ginecológico (general) (de rutina)'],
            ['code' => '47', 'name' => 'Pruebas de sensibilización y diagnóstico cutáneo'],
            ['code' => '48', 'name' => 'Inserción de dispositivo anticonceptivo (intrauterino)'],
            ['code' => '49', 'name' => 'Suplemento nutricional'],
            ['code' => '50', 'name' => 'Caterizar vía venosa periférica'],
            ['code' => '51', 'name' => 'Caterizar vejiga'],
            ['code' => '52', 'name' => 'Visita'],
            ['code' => '53', 'name' => 'Entrevistas'],
            ['code' => '54', 'name' => 'Reuniones de grupos'],
            ['code' => '55', 'name' => 'Plan rehidratación A'],
            ['code' => '56', 'name' => 'Plan rehidratación B'],
            ['code' => '57', 'name' => 'Plan rehidratación C'],
            ['code' => '58', 'name' => 'Instrucción de la dieta (Z71.3)'],
            ['code' => '59', 'name' => 'Examen VDRL'],
            ['code' => '60', 'name' => 'Examen HIV'],
            ['code' => '61', 'name' => 'Ultrasonido (Ecosonograma)'],
            ['code' => '62', 'name' => 'Reposo 24,48,72 horas'],
            ['code' => '63', 'name' => 'Atención para la anticoncepción (Z30) planificación familiar'],
            ['code' => '64', 'name' => 'Control general de salud de rutina a integrantes de grupo escolares (Z10.4) '],
            ['code' => '65', 'name' => 'Resonancia magnética'],
            ['code' => '66', 'name' => 'Crioterapia'],
            ['code' => '67', 'name' => 'Evitar alergenos'],
        ];

        foreach ($conducts as $conduct) {
            MedicalConduct::firstOrCreate(['code' => $conduct['code']], $conduct);
        }
    }
}
