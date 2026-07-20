<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Patient;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para generación de reportes médicos en formato PDF
 */
class ReportController extends Controller
{
    /**
     * Verifica si hay datos para un reporte EPI antes de exportar
     *
     * @param Request $request - tipo, y fecha/semana/periodo
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkExportData(Request $request)
    {
        $tipo = $request->input('tipo');
        $hasData = false;
        $message = '';

        try {
            switch ($tipo) {
                case 'epi10':
                    $request->validate(['fecha' => 'required|date']);
                    $hasData = Consultation::whereDate('consultation_date', $request->fecha)->exists();
                    $message = $hasData ? '' : 'No hay consultas registradas para la fecha ' . $request->fecha;
                    break;

                case 'epi12':
                case 'epi13':
                    $request->validate(['semana' => 'required|regex:/^\d{4}-W?\d{2}$/']);
                    $semana = str_replace('-W', '-', $request->semana);
                    $anio = (int)substr($semana, 0, 4);
                    $numSem = (int)ltrim(substr($semana, 5, 2), '0');
                    $fechaInicio = \Carbon\Carbon::now()->setISODate($anio, $numSem)->startOfWeek();
                    $fechaFin = $fechaInicio->copy()->endOfWeek();
                    $hasData = Consultation::whereBetween('consultation_date', [
                        $fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')
                    ])->exists();
                    $message = $hasData ? '' : 'No hay consultas registradas para la semana ' . $request->semana;
                    break;

                case 'epi15':
                    $request->validate(['periodo' => 'required|date_format:Y-m']);
                    $fechaInicio = $request->periodo . '-01';
                    $fechaFin = date('Y-m-t', strtotime($fechaInicio));
                    $hasData = Consultation::whereBetween('consultation_date', [$fechaInicio, $fechaFin])->exists();
                    $message = $hasData ? '' : 'No hay consultas registradas para el período ' . $request->periodo;
                    break;

                default:
                    return response()->json(['hasData' => false, 'message' => 'Tipo de reporte no válido'], 400);
            }

            return response()->json(['hasData' => $hasData, 'message' => $message]);

        } catch (\Exception $e) {
            return response()->json(['hasData' => false, 'message' => 'Error al verificar datos'], 500);
        }
    }

    /**
     * Exporta el Formulario EPI-10 (Registro Diario de Consulta) en PDF
     * 
     * @param Request $request - Debe contener la fecha en formato Y-m-d
     * @return \Illuminate\Http\Response
     */
    public function exportEpi10(Request $request)
    {
        // Validar la fecha
        $request->validate([
            'fecha' => 'required|date'
        ]);

        // Consultar todas las consultas del día especificado con Eager Loading
        // Esto evita el problema N+1 optimizando las consultas a la base de datos
        $consultations = Consultation::with([
            'patient:id,full_name,id_number,gender,birth_date,phone_number,addr_state,addr_municipality,addr_parish,addr_sector,addr_street,addr_house_number,ethnicity_id,instruction_level_id,occupation_id',
            'sisDiagnoses.sisDiagnosis:id,code,name',
            'sisDiagnoses.medicalConduct:id,name',
            'doctor:id,name',
            'functionalExam',
            'physicalExam',
            'referrals'
        ])
        ->whereDate('consultation_date', $request->fecha)
        ->orderBy('consultation_date', 'asc')
        ->get();

        // Si no hay consultas en esa fecha
        if ($consultations->isEmpty()) {
            Log::warning('EPI-10: Sin consultas para la fecha', ['fecha' => $request->fecha]);
            return redirect('/dashboard')->with('error', 'No hay consultas registradas para la fecha: ' . $request->fecha);
        }

        // Cargar la vista PDF con los datos (formato oficial SIS-02/EPI-10)
        $pdf = Pdf::loadView('reports.epi10-oficial', [
            'consultations' => $consultations,
            'fecha' => $request->fecha,
            'establecimiento' => 'Consultorio Popular El Chaparro',
            'lugarAtencion' => 'Consultorio',
            'asic' => '',
            'medico' => $consultations->first()->doctor?->name ?? '',
        ])
        ->setPaper([0, 0, 936, 612], 'landscape') // 13in x 8.5in landscape
        ->setOption('defaultFont', 'Arial')
        ->setOption('isRemoteEnabled', true);

        // Generar nombre del archivo con formato: EPI-10_YYYY-MM-DD.pdf
        $fileName = 'EPI-10_' . $request->fecha . '.pdf';

        // Descargar el PDF directamente
        return $pdf->download($fileName);
    }

    /**
     * Exporta el Formulario EPI-12 (Consolidado Semanal de Enfermedades y Eventos de Notificación Obligatoria Morbilidad) en PDF
     * Formato oficial SIS-04 / EPI-12 con 52 enfermedades y 13 grupos de edad
     * 
     * @param Request $request - Debe contener la semana y año en formato Y-W
     * @return \Illuminate\Http\Response
     */
    public function exportEpi12(Request $request)
    {
        // Validar la semana y año (acepta YYYY-Www o YYYY-ww)
        $request->validate([
            'semana' => 'required|regex:/^\d{4}-W?\d{2}$/'
        ]);

        $semana = str_replace('-W', '-', $request->semana);
        $anio = substr($semana, 0, 4);
        $numeroSemana = ltrim(substr($semana, 5, 2), '0');

        // Calcular fechas de inicio y fin de la semana usando ISO week
        $fechaInicio = \Carbon\Carbon::now()->setISODate((int)$anio, (int)$numeroSemana)->startOfWeek();
        $fechaFin = $fechaInicio->copy()->endOfWeek();

        // Consultar todas las consultas del período semanal con sus diagnósticos
        $consultations = Consultation::with([
            'patient:id,full_name,gender,birth_date',
            'sisDiagnoses.sisDiagnosis:id,code,name'
        ])
        ->whereBetween('consultation_date', [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')])
        ->orderBy('consultation_date', 'asc')
        ->get();

        // Si no hay consultas en esa semana
        if ($consultations->isEmpty()) {
            Log::warning('EPI-12: Sin consultas para la semana', ['semana' => $semana]);
            return redirect('/dashboard')->with('error', 'No hay consultas registradas para la semana: ' . $semana);
        }

        // Lista de 52 enfermedades del formato oficial EPI-12
        $enfermedadesOficiales = [
            'A00' => 'Cólera',
            'A08-A09' => 'Diarreas',
            'A06' => 'Amibiasis',
            'A01.0' => 'Fiebre Tifoidea',
            'ETA_BROTES' => 'ETA N° de Brotes',
            'ETA_ASOCIADOS' => 'Casos Asociados a Brotes de ETA',
            'B15' => 'Hepatitis Aguda Tipo A',
            'A15-A19' => 'Tuberculosis',
            'J10-J11' => 'Influenza / Enfermedad Tipo Influenza',
            'A50' => 'Sífilis Congénita',
            'Z21' => 'Infección Asintomática VIH',
            'B20-B24' => 'Enfermedad VIH/SIDA',
            'A37' => 'Tosferina',
            'B26' => 'Parotiditis',
            'A33' => 'Tétanos Neonatal',
            'A34' => 'Tétanos Obstétrico',
            'A35' => 'Tétanos (otros)',
            'A36' => 'Difteria',
            'B05' => 'Sarampión Sospecha',
            'B06' => 'Rubéola',
            'DENGUE_SIN' => 'Dengue sin Signo de Alarma',
            'DENGUE_CON' => 'Dengue con Signo de Alarma',
            'A91' => 'Dengue Grave',
            'CHIKUNGUNYA' => 'Chikungunya',
            'U08' => 'Zika',
            'A92.2' => 'Encefalitis Equina Venezolana',
            'A95' => 'Fiebre Amarilla',
            'B55.0' => 'Leishmaniasis Visceral',
            'B55.1' => 'Leishmaniasis Cutánea',
            'B55.2' => 'Leishmaniasis Mucocutánea',
            'B55.9' => 'Leishmaniasis no Específica',
            'B57.0-B57.1' => 'Enfermedad de Chagas Aguda',
            'B57.2-B57.5' => 'Enfermedad de Chagas Crónica',
            'A82' => 'Rabia Humana',
            'A96.8' => 'Fiebre Hemorrágica Venezolana',
            'A27' => 'Leptospirosis',
            'A87' => 'Meningitis Viral',
            'G00' => 'Meningitis Bacteriana',
            'A39.0' => 'Enfermedad Meningococcica',
            'A39.9' => 'Enfermedad Meningococcica',
            'B01' => 'Varicela',
            'B16' => 'Hepatitis Aguda Tipo B',
            'B17.1_B18.2' => 'Hepatitis Aguda Tipo C',
            'B17' => 'Hepatitis Otras Agudas',
            'B19' => 'Hepatitis No Específicas',
            'G82.0' => 'Parálisis Flácida < 15 años',
            'J12-J18' => 'Neumonías',
            'T60' => 'Intoxicación por Plaguicidas',
            'A82_RABIA' => 'Mordedura Sospechosa de Rabia',
            'R50' => 'Fiebre',
            'Y40-Y57' => 'Efectos Adversos de Medicamentos',
            'Y58-Y59' => 'Efectos Adversos de Vacunas',
        ];

        // Inicializar estructura de datos para las 52 enfermedades
        $datosEpi12 = [];
        foreach ($enfermedadesOficiales as $claveEnf => $nombre) {
            $datosEpi12[$claveEnf] = [
                'codigo' => $claveEnf,
                'diagnostico' => $nombre,
                'grupos' => [
                    'lt1_H' => 0, 'lt1_M' => 0,
                    '1_4_H' => 0, '1_4_M' => 0,
                    '5_6_H' => 0, '5_6_M' => 0,
                    '7_9_H' => 0, '7_9_M' => 0,
                    '10_11_H' => 0, '10_11_M' => 0,
                    '12_14_H' => 0, '12_14_M' => 0,
                    '15_19_H' => 0, '15_19_M' => 0,
                    '20_24_H' => 0, '20_24_M' => 0,
                    '25_44_H' => 0, '25_44_M' => 0,
                    '45_59_H' => 0, '45_59_M' => 0,
                    '60_64_H' => 0, '60_64_M' => 0,
                    '65plus_H' => 0, '65plus_M' => 0,
                    'ignorado_H' => 0, 'ignorado_M' => 0,
                ],
                'total_hombres' => 0,
                'total_mujeres' => 0,
                'total' => 0,
            ];
        }

        // Clasificar y contar consultas
        foreach ($consultations as $consultation) {
            if ($consultation->sisDiagnoses && $consultation->sisDiagnoses->isNotEmpty()) {
                foreach ($consultation->sisDiagnoses as $consultaDiag) {
                    $diagnosis = $consultaDiag->sisDiagnosis;
                    if (!$diagnosis) continue;

                    $patient = $consultation->patient;
                    if (!$patient) continue;

                    // Clasificar diagnóstico según las 52 enfermedades del EPI-12
                    $claveEnf = $this->clasificarEnfermedadEpi12($diagnosis);
                    if (!$claveEnf || !isset($datosEpi12[$claveEnf])) continue;

                    // Calcular grupo de edad (13 grupos del oficial)
                    $edad = $patient->birth_date ? \Carbon\Carbon::parse($patient->birth_date)->age : null;
                    $grupoEdad = $this->obtenerGrupoEdadEpi12($edad);

                    // Obtener sexo
                    $sexo = $this->normalizarSexo($patient->gender);

                    $claveGrupo = $grupoEdad . '_' . $sexo;
                    if (isset($datosEpi12[$claveEnf]['grupos'][$claveGrupo])) {
                        $datosEpi12[$claveEnf]['grupos'][$claveGrupo]++;
                        $datosEpi12[$claveEnf]['total']++;
                        if ($sexo === 'H') {
                            $datosEpi12[$claveEnf]['total_hombres']++;
                        } else {
                            $datosEpi12[$claveEnf]['total_mujeres']++;
                        }
                    }
                }
            }
        }

        // Cargar la vista PDF con los datos (formato oficial SIS-04/EPI-12)
        $pdf = Pdf::loadView('reports.epi12-oficial', [
            'datosEpi12' => $datosEpi12,
            'semana' => $semana,
            'numeroSemana' => ltrim($numeroSemana, '0'),
            'anio' => $anio,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'totalConsultas' => $consultations->count(),
            'entidad' => 'ANZOÁTEGUI',
            'municipio' => 'GUANTA',
            'establecimiento' => 'CPT III CHAPARRO DE GUANTA',
        ])
        ->setPaper([0, 0, 990, 612], 'landscape') // 13.75in x 8.5in landscape
        ->setOption('defaultFont', 'Arial')
        ->setOption('isRemoteEnabled', true);

        // Generar nombre del archivo con formato: EPI-12_YYYY-Wnn.pdf
        $fileName = 'EPI-12_' . $semana . '.pdf';

        // Descargar el PDF directamente
        return $pdf->download($fileName);
    }

    /**
     * Exporta el Formulario EPI-13 (Registro de Enfermedades de Notificación Obligatoria) en PDF
     * Formato oficial SIS-03 / EPI-13 como line listing individual de casos
     * 
     * @param Request $request - Debe contener la semana y año en formato Y-W
     * @return \Illuminate\Http\Response
     */
    public function exportEpi13(Request $request)
    {
        // Validar la semana y año (acepta YYYY-Www o YYYY-ww)
        $request->validate([
            'semana' => 'required|regex:/^\d{4}-W?\d{2}$/'
        ]);

        $semana = str_replace('-W', '-', $request->semana);
        $anio = substr($semana, 0, 4);
        $numeroSemana = ltrim(substr($semana, 5, 2), '0');

        // Calcular fechas de inicio y fin de la semana usando ISO week
        $fechaInicio = \Carbon\Carbon::now()->setISODate((int)$anio, (int)$numeroSemana)->startOfWeek();
        $fechaFin = $fechaInicio->copy()->endOfWeek();

        // Consultar todas las consultas del período semanal
        $consultations = Consultation::with([
            'patient:id,full_name,id_number,gender,birth_date,addr_state,addr_municipality,addr_parish,nationality',
            'sisDiagnoses.sisDiagnosis:id,code,name'
        ])
        ->whereBetween('consultation_date', [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')])
        ->orderBy('consultation_date', 'asc')
        ->get();

        // Si no hay consultas en esa semana
        if ($consultations->isEmpty()) {
            Log::warning('EPI-13: Sin consultas para la semana', ['semana' => $semana]);
            return redirect('/dashboard')->with('error', 'No hay consultas registradas para la semana: ' . $semana);
        }

        // Generar line listing de casos individuales
        $casos = [];

        foreach ($consultations as $consultation) {
            if ($consultation->sisDiagnoses && $consultation->sisDiagnoses->isNotEmpty()) {
                foreach ($consultation->sisDiagnoses as $consultaDiag) {
                    $diagnosis = $consultaDiag->sisDiagnosis;
                    if (!$diagnosis) continue;

                    $categoria = $this->clasificarEnfermedadNotificable($diagnosis);
                    if (!$categoria) continue;

                    $patient = $consultation->patient;
                    if (!$patient) continue;

                    $edad = $patient->birth_date ? \Carbon\Carbon::parse($patient->birth_date)->age : '';

                    $casos[] = [
                        'fecha' => \Carbon\Carbon::parse($consultation->consultation_date)->format('d/m/Y'),
                        'nombre' => $patient->full_name ?? '',
                        'ci' => ($patient->nationality ?? 'V') . '-' . ($patient->id_number ?? ''),
                        'genero' => strtoupper($patient->gender ?? ''),
                        'fecha_nac' => $patient->birth_date ? \Carbon\Carbon::parse($patient->birth_date)->format('d/m/Y') : '',
                        'edad' => $edad,
                        'direccion' => ($patient->addr_parish ?? '') . ($patient->addr_municipality ? ', ' . $patient->addr_municipality : ''),
                        'entidad' => 'ANZOÁTEGUI',
                        'municipio' => $patient->addr_municipality ?? 'GUANTA',
                        'parroquia' => 'CHORRERÓN',
                        'enfermedad' => $categoria,
                    ];
                }
            }
        }

        $totalCasos = count($casos);

        // Cargar la vista PDF con los datos (formato oficial SIS-03/EPI-13)
        $pdf = Pdf::loadView('reports.epi13-oficial', [
            'casos' => $casos,
            'semana' => $semana,
            'numeroSemana' => $numeroSemana,
            'anio' => $anio,
            'totalCasos' => $totalCasos,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'entidad' => 'ANZOÁTEGUI',
            'municipio' => 'GUANTA',
            'parroquia' => 'CHORRERÓN',
            'localidad' => 'EL CHAPARRO',
            'establecimiento' => 'CONSULTORIO POPULAR',
        ])
        ->setPaper([0, 0, 936, 612], 'landscape') // 13in x 8.5in landscape
        ->setOption('defaultFont', 'Arial')
        ->setOption('isRemoteEnabled', true);

        // Generar nombre del archivo con formato: EPI-13_YYYY-Wnn.pdf
        $fileName = 'EPI-13_' . $semana . '.pdf';

        // Descargar el PDF directamente
        return $pdf->download($fileName);
    }

    /**
     * Clasifica una enfermedad como notificable según MPPS
     * 
     * @param Diagnosis $diagnosis
     * @return string
     */
    private function clasificarEnfermedadNotificable($diagnosis): string
    {
        $codigo = $diagnosis->code ?? '';
        $nombre = strtolower($diagnosis->name ?? '');

        // Malaria (CIE-10 B50-B54)
        if (strpos($codigo, 'B50') === 0 || strpos($codigo, 'B51') === 0 || strpos($codigo, 'B52') === 0 || strpos($codigo, 'B53') === 0 || strpos($codigo, 'B54') === 0 || strpos($nombre, 'paludismo') !== false || strpos($nombre, 'malaria') !== false) {
            return 'MALARIA';
        }

        // Dengue (CIE-10 A90)
        if (strpos($codigo, 'A90') === 0 || strpos($nombre, 'dengue') !== false) {
            return 'DENGUE';
        }

        // Chikungunya (CIE-10 A92)
        if (strpos($codigo, 'A92') === 0 || strpos($nombre, 'chikungunya') !== false) {
            return 'CHIKUNGUNYA';
        }

        // Zika (CIE-10 A928)
        if (strpos($codigo, 'A928') === 0 || strpos($nombre, 'zika') !== false) {
            return 'ZIKA';
        }

        // Sarampión (CIE-10 B05)
        if (strpos($codigo, 'B05') === 0 || strpos($nombre, 'sarampi') !== false) {
            return 'SARAMPIÓN';
        }

        // Rubéola (CIE-10 B06)
        if (strpos($codigo, 'B06') === 0 || strpos($nombre, 'rubéola') !== false || strpos($nombre, 'rubeola') !== false) {
            return 'RUBÉOLA';
        }

        // Fiebre amarilla (CIE-10 A95)
        if (strpos($codigo, 'A95') === 0 || strpos($nombre, 'fiebre amarilla') !== false) {
            return 'FIEBRE AMARILLA';
        }

        // Fiebre tifoidea y paratifoidea (CIE-10 A01)
        if (strpos($codigo, 'A01') === 0 || strpos($nombre, 'tifoidea') !== false) {
            return 'FIEBRE TIFOIDEA Y PARATIFOIDEA';
        }

        // Tuberculosis (CIE-10 A15-A19)
        if (strpos($codigo, 'A15') === 0 || strpos($codigo, 'A16') === 0 || strpos($codigo, 'A17') === 0 || strpos($codigo, 'A18') === 0 || strpos($codigo, 'A19') === 0 || strpos($nombre, 'tuberculosis') !== false || strpos($nombre, 'tb') !== false) {
            return 'TUBERCULOSIS';
        }

        // Lepra (CIE-10 A30)
        if (strpos($codigo, 'A30') === 0 || strpos($nombre, 'lepra') !== false) {
            return 'LEPRA';
        }

        // COVID-19 (CIE-10 U07)
        if (strpos($codigo, 'U07') === 0 || strpos($nombre, 'covid') !== false || strpos($nombre, 'coronavirus') !== false) {
            return 'COVID-19';
        }

        // Infección respiratoria aguda grave IRAG
        if (strpos($nombre, 'irag') !== false || strpos($nombre, 'infección respiratoria aguda grave') !== false) {
            return 'INFECCIÓN RESPIRATORIA AGUDA GRAVE (IRAG)';
        }

        // Neumonía adquirida
        if (strpos($codigo, 'J12') === 0 || strpos($codigo, 'J13') === 0 || strpos($codigo, 'J15') === 0 || strpos($codigo, 'J18') === 0 || strpos($nombre, 'neumonía') !== false) {
            return 'NEUMONÍA ADQUIRIDA';
        }

        // Diarrea aguda severa
        if (strpos($codigo, 'A09') === 0 || strpos($nombre, 'diarrea aguda severa') !== false) {
            return 'DIARREA AGUDA SEVERA';
        }

        // Sífilis congénita (CIE-10 A50)
        if (strpos($codigo, 'A50') === 0 || strpos($nombre, 'sífilis congénita') !== false) {
            return 'SÍFILIS CONGÉNITA';
        }

        // Chagas
        if (strpos($codigo, 'B57') === 0 || strpos($nombre, 'chagas') !== false) {
            return 'CHAGAS';
        }

        // Meningitis meningocócica
        if (strpos($codigo, 'A39') === 0 || strpos($nombre, 'meningitis') !== false && strpos($nombre, 'meningocócica') !== false) {
            return 'MENINGITIS MENINGOCÓCICA';
        }

        // Por defecto, otras enfermedades notificables
        return 'OTRAS ENFERMEDADES NOTIFICABLES';
    }

    /**
     * Determina si es primera vez o control/repetida
     * 
     * @param Consultation $consultation
     * @return string
     */
    private function determinarTipoConsulta($consultation): string
    {
        // Si el campo is_healthy existe y es true, es control
        if (isset($consultation->is_healthy) && $consultation->is_healthy) {
            return 'control';
        }

        // Verificar si es consulta de repetida por otros criterios
        // Por ahora, asumimos que si no es healthy, es primera vez
        // Esto puede ajustarse según la lógica específica del sistema
        return 'primera_vez';
    }

    /**
     * Clasifica un diagnóstico según las 52 enfermedades del EPI-12 oficial
     * 
     * @param mixed $diagnosis - Modelo SisDiagnosis
     * @return string|null - Clave de la enfermedad o null si no clasifica
     */
    private function clasificarEnfermedadEpi12($diagnosis): ?string
    {
        $codigo = strtoupper(trim($diagnosis->code ?? ''));
        $nombre = strtolower(trim($diagnosis->name ?? ''));

        // Cólera (A00)
        if (strpos($codigo, 'A00') === 0) return 'A00';

        // Diarreas (A08-A09)
        if (strpos($codigo, 'A08') === 0 || strpos($codigo, 'A09') === 0 || strpos($nombre, 'diarrea') !== false) return 'A08-A09';

        // Amibiasis (A06)
        if (strpos($codigo, 'A06') === 0 || strpos($nombre, 'amibi') !== false || strpos($nombre, 'amebi') !== false) return 'A06';

        // Fiebre Tifoidea (A01.0)
        if (strpos($codigo, 'A01') === 0 || strpos($nombre, 'tifoidea') !== false) return 'A01.0';

        // Hepatitis Aguda Tipo A (B15)
        if (strpos($codigo, 'B15') === 0) return 'B15';

        // Tuberculosis (A15-A19)
        if (preg_match('/^A1[5-9]/', $codigo) || strpos($nombre, 'tuberculosis') !== false || strpos($nombre, 'tb ') !== false) return 'A15-A19';

        // Influenza (J10-J11)
        if (preg_match('/^J1[01]/', $codigo) || strpos($nombre, 'influenza') !== false || strpos($nombre, 'gripe') !== false) return 'J10-J11';

        // Sífilis Congénita (A50)
        if (strpos($codigo, 'A50') === 0 || strpos($nombre, 'sífilis congénita') !== false) return 'A50';

        // Infección Asintomática VIH (Z21)
        if (strpos($codigo, 'Z21') === 0) return 'Z21';

        // Enfermedad VIH/SIDA (B20-B24)
        if (preg_match('/^B2[0-4]/', $codigo) || strpos($nombre, 'hiv') !== false || strpos($nombre, 'sida') !== false) return 'B20-B24';

        // Tosferina (A37)
        if (strpos($codigo, 'A37') === 0 || strpos($nombre, 'tosferina') !== false || strpos($nombre, 'tos ferina') !== false) return 'A37';

        // Parotiditis (B26)
        if (strpos($codigo, 'B26') === 0 || strpos($nombre, 'parotiditis') !== false || strpos($nombre, 'paperas') !== false) return 'B26';

        // Tétanos Neonatal (A33)
        if (strpos($codigo, 'A33') === 0) return 'A33';

        // Tétanos Obstétrico (A34)
        if (strpos($codigo, 'A34') === 0) return 'A34';

        // Tétanos otros (A35)
        if (strpos($codigo, 'A35') === 0) return 'A35';

        // Difteria (A36)
        if (strpos($codigo, 'A36') === 0 || strpos($nombre, 'difteria') !== false) return 'A36';

        // Sarampión Sospecha (B05)
        if (strpos($codigo, 'B05') === 0 || strpos($nombre, 'sarampión') !== false || strpos($nombre, 'sarampion') !== false) return 'B05';

        // Rubéola (B06)
        if (strpos($codigo, 'B06') === 0 || strpos($nombre, 'rubéola') !== false || strpos($nombre, 'rubeola') !== false) return 'B06';

        // Dengue (A90, A91, A92.0-A92.9 excluding A92.2)
        if (strpos($nombre, 'dengue') !== false) {
            if (strpos($nombre, 'grave') !== false || strpos($codigo, 'A91') === 0) return 'A91';
            if (strpos($nombre, 'alarma') !== false) return 'DENGUE_CON';
            return 'DENGUE_SIN';
        }

        // Chikungunya
        if (strpos($nombre, 'chikungunya') !== false) return 'CHIKUNGUNYA';

        // Zika (U08)
        if (strpos($codigo, 'U08') === 0 || strpos($nombre, 'zika') !== false) return 'U08';

        // Encefalitis Equina Venezolana (A92.2)
        if (strpos($codigo, 'A92.2') === 0 || strpos($nombre, 'encefalitis equina') !== false) return 'A92.2';

        // Fiebre Amarilla (A95)
        if (strpos($codigo, 'A95') === 0 || strpos($nombre, 'fiebre amarilla') !== false) return 'A95';

        // Leishmaniasis Visceral (B55.0)
        if (strpos($codigo, 'B55.0') === 0 || (strpos($nombre, 'leishmaniasis') !== false && strpos($nombre, 'visceral') !== false)) return 'B55.0';

        // Leishmaniasis Cutánea (B55.1)
        if (strpos($codigo, 'B55.1') === 0 || (strpos($nombre, 'leishmaniasis') !== false && strpos($nombre, 'cutánea') !== false)) return 'B55.1';

        // Leishmaniasis Mucocutánea (B55.2)
        if (strpos($codigo, 'B55.2') === 0 || (strpos($nombre, 'leishmaniasis') !== false && strpos($nombre, 'mucocut') !== false)) return 'B55.2';

        // Leishmaniasis no Específica (B55.9)
        if (strpos($codigo, 'B55') === 0 || (strpos($nombre, 'leishmaniasis') !== false && strpos($nombre, 'específ') === false && strpos($nombre, 'visceral') === false && strpos($nombre, 'cutánea') === false && strpos($nombre, 'mucocut') === false)) return 'B55.9';

        // Chagas Aguda (B57.0-B57.1)
        if (preg_match('/^B57\.[01]/', $codigo) || (strpos($nombre, 'chagas') !== false && strpos($nombre, 'aguda') !== false)) return 'B57.0-B57.1';

        // Chagas Crónica (B57.2-B57.5)
        if (preg_match('/^B57\.[2-5]/', $codigo) || (strpos($nombre, 'chagas') !== false && strpos($nombre, 'crónica') !== false)) return 'B57.2-B57.5';

        // Rabia Humana (A82)
        if (strpos($codigo, 'A82') === 0 || strpos($nombre, 'rabia humana') !== false) return 'A82';

        // Fiebre Hemorrágica Venezolana (A96.8)
        if (strpos($codigo, 'A96.8') === 0 || strpos($nombre, 'fiebre hemorrágica venezolana') !== false) return 'A96.8';

        // Leptospirosis (A27)
        if (strpos($codigo, 'A27') === 0 || strpos($nombre, 'leptospirosis') !== false) return 'A27';

        // Meningitis Viral (A87)
        if (strpos($codigo, 'A87') === 0 || (strpos($nombre, 'meningitis') !== false && strpos($nombre, 'viral') !== false)) return 'A87';

        // Meningitis Bacteriana (G00)
        if (strpos($codigo, 'G00') === 0 || (strpos($nombre, 'meningitis') !== false && strpos($nombre, 'bacteriana') !== false)) return 'G00';

        // Enfermedad Meningococcica (A39.0)
        if (strpos($codigo, 'A39.0') === 0) return 'A39.0';

        // Enfermedad Meningococcica (A39.9)
        if (strpos($codigo, 'A39.9') === 0 || strpos($codigo, 'A39') === 0) return 'A39.9';

        // Varicela (B01)
        if (strpos($codigo, 'B01') === 0 || strpos($nombre, 'varicela') !== false) return 'B01';

        // Hepatitis Aguda Tipo B (B16)
        if (strpos($codigo, 'B16') === 0) return 'B16';

        // Hepatitis Aguda Tipo C (B17.1, B18.2)
        if (strpos($codigo, 'B17.1') === 0 || strpos($codigo, 'B18.2') === 0) return 'B17.1_B18.2';

        // Hepatitis Otras Agudas (B17)
        if (strpos($codigo, 'B17') === 0 || (strpos($nombre, 'hepatitis') !== false && strpos($nombre, 'aguda') !== false && strpos($codigo, 'B15') !== 0 && strpos($codigo, 'B16') !== 0 && strpos($codigo, 'B17.1') !== 0 && strpos($codigo, 'B18.2') !== 0)) return 'B17';

        // Hepatitis No Específicas (B19)
        if (strpos($codigo, 'B19') === 0) return 'B19';

        // Parálisis Flácida < 15 años (G82.0)
        if (strpos($codigo, 'G82') === 0 || strpos($nombre, 'parálisis flácida') !== false) return 'G82.0';

        // Neumonías (J12-J18)
        if (preg_match('/^J1[2-8]/', $codigo) || strpos($nombre, 'neumonía') !== false || strpos($nombre, 'neumonia') !== false) return 'J12-J18';

        // Intoxicación por Plaguicidas (T60)
        if (strpos($codigo, 'T60') === 0 || strpos($nombre, 'plaguicida') !== false || strpos($nombre, 'pesticida') !== false || strpos($nombre, 'intoxicación') !== false) return 'T60';

        // Mordedura Sospechosa de Rabia (A82)
        if (strpos($nombre, 'mordedura') !== false && strpos($nombre, 'rabia') !== false) return 'A82_RABIA';

        // Fiebre (R50)
        if (strpos($codigo, 'R50') === 0 || strpos($nombre, 'fiebre') !== false) return 'R50';

        // Efectos Adversos de Medicamentos (Y40-Y57)
        if (preg_match('/^Y4[0-9]/', $codigo) || preg_match('/^Y5[0-7]/', $codigo) || strpos($nombre, 'efecto adverso') !== false || strpos($nombre, 'efectos adversos') !== false) return 'Y40-Y57';

        // Efectos Adversos de Vacunas (Y58-Y59)
        if (preg_match('/^Y5[89]/', $codigo) || strpos($nombre, 'vacuna') !== false) return 'Y58-Y59';

        return null;
    }

    /**
     * Obtiene el grupo de edad según los 13 grupos del EPI-12 oficial
     * 
     * @param int|null $edad
     * @return string
     */
    private function obtenerGrupoEdadEpi12(?int $edad): string
    {
        if ($edad === null || $edad < 0) return 'ignorado';
        if ($edad < 1) return 'lt1';
        if ($edad >= 1 && $edad <= 4) return '1_4';
        if ($edad >= 5 && $edad <= 6) return '5_6';
        if ($edad >= 7 && $edad <= 9) return '7_9';
        if ($edad >= 10 && $edad <= 11) return '10_11';
        if ($edad >= 12 && $edad <= 14) return '12_14';
        if ($edad >= 15 && $edad <= 19) return '15_19';
        if ($edad >= 20 && $edad <= 24) return '20_24';
        if ($edad >= 25 && $edad <= 44) return '25_44';
        if ($edad >= 45 && $edad <= 59) return '45_59';
        if ($edad >= 60 && $edad <= 64) return '60_64';
        return '65plus';
    }

    /**
     * Obtiene el grupo de edad según la edad del paciente (para EPI-12 legacy)
     * 
     * @param int $edad
     * @return string
     */
    private function obtenerGrupoEdad($edad): string
    {
        if ($edad < 1) return '0_1';
        if ($edad >= 1 && $edad <= 4) return '1_4';
        if ($edad >= 5 && $edad <= 14) return '5_14';
        if ($edad >= 15 && $edad <= 49) return '15_49';
        if ($edad >= 50 && $edad <= 64) return '50_64';
        return '65_plus';
    }

    /**
     * Normaliza el sexo del paciente
     * 
     * @param string|null $sexo
     * @return string
     */
    private function normalizarSexo($sexo): string
    {
        if (strtoupper($sexo) === 'M') return 'H'; // Hombre (EPI-12 convention)
        if (strtoupper($sexo) === 'F') return 'M'; // Mujer (EPI-12 convention)
        return 'X'; // Desconocido / no especificado
    }

    /**
     * Exporta el Formulario EPI-15 (Consolidado Mensual de Morbilidad por Enfermedades, Aparatos y Sistemas) en PDF
     * 
     * @param Request $request - Debe contener el mes y año en formato Y-m
     * @return \Illuminate\Http\Response
     */
    public function exportEpi15(Request $request)
    {
        $request->validate([
            'periodo' => 'required|date_format:Y-m'
        ]);

        $periodo = $request->periodo;
        $anio = substr($periodo, 0, 4);
        $fechaInicio = $periodo . '-01';
        $fechaFin = date('Y-m-t', strtotime($fechaInicio));

        // Consultas del mes
        $consultations = Consultation::with([
            'patient:id,full_name,gender,birth_date',
            'sisDiagnoses.sisDiagnosis:id,code,name'
        ])
        ->whereBetween('consultation_date', [$fechaInicio, $fechaFin])
        ->orderBy('consultation_date', 'asc')
        ->get();

        if ($consultations->isEmpty()) {
            Log::warning('EPI-15: Sin consultas para el período', ['periodo' => $periodo]);
            return redirect('/dashboard')->with('error', 'No hay consultas registradas para el período: ' . $periodo);
        }

        // Consultas del año completo (para acumulado)
        $yearConsultations = Consultation::with([
            'patient:id,full_name,gender,birth_date',
            'sisDiagnoses.sisDiagnosis:id,code,name'
        ])
        ->whereBetween('consultation_date', [$anio . '-01-01', $fechaFin])
        ->get();

        // Consolidar datos
        $mesData = $this->consolidarEpi15($consultations);
        $anioData = $this->consolidarEpi15($yearConsultations);

        // Obtener filas del formulario
        $rows = $this->getEpi15Rows();

        // Calcular totales
        $totalP = 0; $totalS = 0; $totalX = 0; $totalAcum = 0;
        foreach (range(1, 271) as $n) {
            $totalP += $mesData[$n]['p'];
            $totalS += $mesData[$n]['s'];
            $totalX += $mesData[$n]['x'];
            $totalAcum += $anioData[$n]['p'] + $anioData[$n]['s'] + $anioData[$n]['x'];
        }

        $pdf = Pdf::loadView('reports.epi15-oficial', [
            'rows' => $rows,
            'mesData' => $mesData,
            'anioData' => $anioData,
            'periodo' => $periodo,
            'totalConsultas' => $consultations->count(),
            'totalP' => $totalP,
            'totalS' => $totalS,
            'totalX' => $totalX,
            'totalAcum' => $totalAcum,
        ])
        ->setPaper('a4', 'landscape')
        ->setOption('defaultFont', 'Arial')
        ->setOption('isRemoteEnabled', true);

        return $pdf->download('EPI-15_' . $periodo . '.pdf');
    }

    /**
     * Devuelve el listado maestro de enfermedades del EPI-15 con su estructura jerárquica
     */
    private function getEpi15Rows(): array
    {
        return [
            // I.- ENFERM. INFECCIOSAS Y PARASITARIAS
            ['t'=>'cap', 'nom'=>'I.- ENFERM. INFECCIOSAS Y PARASITARIAS'],
            ['t'=>'sub', 'nom'=>'Ia.- TRANSMISION HIDRICA Y ALIMENTOS'],
            ['t'=>'enf','n'=>1,'nom'=>'CÓLERA (A00)','cods'=>['A00']],
            ['t'=>'enf','n'=>2,'nom'=>'AMIBIASIS (A06)','cods'=>['A06']],
            ['t'=>'enf-3e','n'=>3,'nom'=>'DIARREAS < 1 AÑO (A08-A09)','cods'=>['A08','A09'],'emax'=>0],
            ['t'=>'enf-3e','n'=>4,'nom'=>'DIARREAS 1-4 AÑOS (A08-A09)','cods'=>['A08','A09'],'emin'=>1,'emax'=>4],
            ['t'=>'enf-3e','n'=>5,'nom'=>'DIARREAS 5 AÑOS Y MAS (A08-A09)','cods'=>['A08','A09'],'emin'=>5],
            ['t'=>'enf','n'=>6,'nom'=>'DIARREA SIN DESHIDRATACIÓN (A08-A09)','cods'=>['A08','A09']],
            ['t'=>'enf','n'=>7,'nom'=>'DIARREA CON DESHIDRATACIÓN LEVE / MODERADA (A08-A09)','cods'=>['A08','A09']],
            ['t'=>'enf','n'=>8,'nom'=>'DIARREA CON DESHIDRATACIÓN GRAVE (A08-A09)','cods'=>['A08','A09']],
            ['t'=>'enf','n'=>9,'nom'=>'GIARDIASIS (A07.1)','cods'=>['A07.1','A07']],
            ['t'=>'enf','n'=>10,'nom'=>'HELMINTIASIS (B65-B68-B70-B83)','cods'=>['B65','B66','B67','B68','B69','B70','B71','B72','B73','B74','B75','B76','B77','B78','B79','B80','B81','B82','B83']],
            ['t'=>'enf','n'=>11,'nom'=>'FIEBRE TIFOIDEA (A01.0)','cods'=>['A01']],
            ['t'=>'enf','n'=>12,'nom'=>'ETA Nº DE BROTES','cods'=>[]],
            ['t'=>'enf','n'=>13,'nom'=>'CASOS ASOC. A BROTES DE ENFERM. TRANSM. POR ALIMENTOS','cods'=>[]],
            ['t'=>'enf','n'=>14,'nom'=>'HEPATITIS AGUDA TIPO A (B15)','cods'=>['B15']],
            ['t'=>'sub','nom'=>'Ib.- TRANSMISION AEREA'],
            ['t'=>'enf','n'=>15,'nom'=>'TUBERCULOSIS (A15-A19)','cods'=>['A15','A16','A17','A18','A19']],
            ['t'=>'enf','n'=>16,'nom'=>'TUBERCULOSIS SERIE P (PULMONAR BK +) (A15.0-A15.3)','cods'=>['A15']],
            ['t'=>'enf','n'=>17,'nom'=>'TUBERCULOSIS SERIE N (PULMONAR BK -) (A16.0)','cods'=>['A16.0','A16']],
            ['t'=>'enf','n'=>18,'nom'=>'TUBERCULOSIS SERIE EP (EXTRAPULMONARES) (A16.3-A16.9-A17-A19)','cods'=>['A16.3','A16.4','A16.5','A16.6','A16.7','A16.8','A16.9','A17','A18','A19']],
            ['t'=>'enf','n'=>19,'nom'=>'INFLUENZA (J09-J11) ENF.TIPO INFLUENZA','cods'=>['J09','J10','J11']],
            ['t'=>'sub','nom'=>'Ic.- TRANSMISION SEXUAL'],
            ['t'=>'enf','n'=>20,'nom'=>'INFECCIÓN GONOCÓCICA (A54)','cods'=>['A54']],
            ['t'=>'enf','n'=>21,'nom'=>'SÍFILIS (A51-A53)','cods'=>['A51','A52','A53']],
            ['t'=>'enf','n'=>22,'nom'=>'SÍFILIS CONGÉNITA (A50)','cods'=>['A50']],
            ['t'=>'enf','n'=>23,'nom'=>'INFECCIÓN ASINTOMÁTICA VIH (Z21)','cods'=>['Z21']],
            ['t'=>'enf','n'=>24,'nom'=>'ENFERMEDAD VIH/SIDA (B20-B24)','cods'=>['B20','B21','B22','B23','B24']],
            ['t'=>'enf','n'=>25,'nom'=>'CONDILOMA ACUMINADO (A63.0)','cods'=>['A63']],
            ['t'=>'enf','n'=>26,'nom'=>'TRICOMONIASIS (A59.9)','cods'=>['A59']],
            ['t'=>'enf','n'=>27,'nom'=>'CANDIDIASIS GENITAL (B37.3)','cods'=>['B37.3','B37']],
            ['t'=>'enf','n'=>28,'nom'=>'ÚLCERA GENITAL (N76.6-N50.8)','cods'=>['N76.6','N50.8']],
            ['t'=>'enf','n'=>29,'nom'=>'FLUJO VAGINAL (N89.8)','cods'=>['N89.8','N89']],
            ['t'=>'enf','n'=>30,'nom'=>'EDEMA DE ESCROTO (N50.8)','cods'=>['N50.8']],
            ['t'=>'enf','n'=>31,'nom'=>'FLUJO URETRAL (N51-A54.0-A59.0)','cods'=>['N51','A54.0','A59.0']],
            ['t'=>'enf','n'=>32,'nom'=>'BUBÓN INGUINAL (I88.8)','cods'=>['I88']],
            ['t'=>'enf','n'=>33,'nom'=>'INFECCIÓN POR VIRUS PAPILOMA HUMANO (B97.7)','cods'=>['B97.7','B97']],
            ['t'=>'sub','nom'=>'Id.- PREVENIBLES POR VACUNAS'],
            ['t'=>'enf','n'=>34,'nom'=>'POLIOMIELITIS (A80)','cods'=>['A80']],
            ['t'=>'enf','n'=>35,'nom'=>'TOSFERINA (A37)','cods'=>['A37']],
            ['t'=>'enf','n'=>36,'nom'=>'PAROTIDITIS INFECCIOSA (B26)','cods'=>['B26']],
            ['t'=>'enf','n'=>37,'nom'=>'TÉTANOS NEONATAL (A33)','cods'=>['A33']],
            ['t'=>'enf','n'=>38,'nom'=>'TÉTANOS OBSTÉTRICO (A34)','cods'=>['A34']],
            ['t'=>'enf','n'=>39,'nom'=>'OTROS TÉTANOS (A35)','cods'=>['A35']],
            ['t'=>'enf','n'=>40,'nom'=>'DIFTERIA (A36)','cods'=>['A36']],
            ['t'=>'enf','n'=>41,'nom'=>'SARAMPIÓN (B05)','cods'=>['B05']],
            ['t'=>'enf','n'=>42,'nom'=>'RUBÉOLA (B06)','cods'=>['B06']],
            ['t'=>'sub','nom'=>'Ie.- TRANSMITIDAS POR VECTORES'],
            ['t'=>'enf','n'=>43,'nom'=>'DENGUE SIN SIGNOS DE ALARMA (A90)','cods'=>['A90']],
            ['t'=>'enf','n'=>44,'nom'=>'DENGUE CON SIGNOS DE ALARMA (A90)','cods'=>['A90']],
            ['t'=>'enf','n'=>45,'nom'=>'DENGUE GRAVE (A91)','cods'=>['A91']],
            ['t'=>'enf','n'=>46,'nom'=>'CHIKUNGUNYA (A92.0)','cods'=>['A92.0','A92']],
            ['t'=>'enf','n'=>47,'nom'=>'ZIKA (U06)','cods'=>['U06']],
            ['t'=>'enf','n'=>48,'nom'=>'ENCEFALITIS EQUINA VENEZOLANA (A92.2)','cods'=>['A92.2']],
            ['t'=>'enf','n'=>49,'nom'=>'FIEBRE AMARILLA (A95)','cods'=>['A95']],
            ['t'=>'enf','n'=>50,'nom'=>'PALUDISMO A FALCIPARUM (B50)','cods'=>['B50']],
            ['t'=>'enf','n'=>51,'nom'=>'PALUDISMO A VIVAX (B51)','cods'=>['B51']],
            ['t'=>'enf','n'=>52,'nom'=>'PALUDISMO A MALARIAE (B52)','cods'=>['B52']],
            ['t'=>'enf','n'=>53,'nom'=>'PALUDISMO MIXTA (B50-B51-B52)','cods'=>['B50','B51','B52']],
            ['t'=>'enf','n'=>54,'nom'=>'LEISHMANIASIS VISCERAL (B55.0)','cods'=>['B55.0']],
            ['t'=>'enf','n'=>55,'nom'=>'LEISHMANIASIS CUTÁNEA (B55.1)','cods'=>['B55.1']],
            ['t'=>'enf','n'=>56,'nom'=>'LEISHMANIASIS MUCOCUTÁNEA (B55.2)','cods'=>['B55.2']],
            ['t'=>'enf','n'=>57,'nom'=>'LEISHMANIASIS NO ESPECÍFICA (B55.9)','cods'=>['B55.9','B55']],
            ['t'=>'enf','n'=>58,'nom'=>'ENFERMEDAD DE CHAGAS AGUDA (B57.0-B57.1)','cods'=>['B57.0','B57.1','B57']],
            ['t'=>'enf','n'=>59,'nom'=>'ENFERMEDAD DE CHAGAS CRÓNICA (B57.2-B57.5)','cods'=>['B57.2','B57.3','B57.4','B57.5']],
            ['t'=>'enf','n'=>60,'nom'=>'ONCOCERCOSIS (B73)','cods'=>['B73']],
            ['t'=>'enf','n'=>61,'nom'=>'FIEBRE DEL OESTE DEL NILO (A92.3)','cods'=>['A92.3']],
            ['t'=>'sub','nom'=>'If.- ENFERMEDADES ZOONOTICAS'],
            ['t'=>'enf','n'=>62,'nom'=>'RABIA HUMANA (A82)','cods'=>['A82']],
            ['t'=>'enf','n'=>63,'nom'=>'FIEBRE HEMORRAGICA VENEZOLANA (A96.8)','cods'=>['A96.8','A96']],
            ['t'=>'enf','n'=>64,'nom'=>'LEPTOSPIROSIS (A27)','cods'=>['A27']],
            ['t'=>'enf','n'=>65,'nom'=>'BRUCELOSIS (A23)','cods'=>['A23']],
            ['t'=>'enf','n'=>66,'nom'=>'CISTICERCOSIS (B69)','cods'=>['B69']],
            ['t'=>'enf','n'=>67,'nom'=>'PESTE (A20)','cods'=>['A20']],
            ['t'=>'enf','n'=>68,'nom'=>'RUMOR DE EPIZOOTIAS','cods'=>[]],
            ['t'=>'sub','nom'=>'Ig.- OTRAS ENF. TRANSMISIBLES'],
            ['t'=>'enf','n'=>69,'nom'=>'MENINGITIS VIRAL (A87)','cods'=>['A87']],
            ['t'=>'enf','n'=>70,'nom'=>'MENINGITIS BACTERIANA (G00)','cods'=>['G00']],
            ['t'=>'enf','n'=>71,'nom'=>'MENINGITIS MENINGOCÓCICA (A39.0)','cods'=>['A39']],
            ['t'=>'enf','n'=>72,'nom'=>'ENFERMEDAD MENINGOCÓCICA (A39.9)','cods'=>['A39.9']],
            ['t'=>'enf','n'=>73,'nom'=>'VARICELA (B01)','cods'=>['B01']],
            ['t'=>'enf','n'=>74,'nom'=>'HEPATITIS AGUDA TIPO B (B16)','cods'=>['B16']],
            ['t'=>'enf','n'=>75,'nom'=>'HEPATITIS AGUDA TIPO C (B17.1)','cods'=>['B17']],
            ['t'=>'enf','n'=>76,'nom'=>'HEPATITIS OTRAS AGUDA (B17)','cods'=>['B17']],
            ['t'=>'enf','n'=>77,'nom'=>'HEPATITIS NO ESPECIFICADA (B19)','cods'=>['B19']],
            ['t'=>'enf','n'=>78,'nom'=>'SÍNDROME VIRAL (B34)','cods'=>['B34']],
            ['t'=>'enf','n'=>79,'nom'=>'PEDICULOSIS (B85) Y ÁCAROS (B87)','cods'=>['B85','B87']],
            ['t'=>'enf','n'=>80,'nom'=>'ESCABIOSIS (B86)','cods'=>['B86']],
            ['t'=>'enf','n'=>81,'nom'=>'MICOSIS SUPERFICIAL (B35-B36)','cods'=>['B35','B36']],
            ['t'=>'enf','n'=>82,'nom'=>'LEPRA (A30)','cods'=>['A30']],
            ['t'=>'enf','n'=>83,'nom'=>'ÉBOLA (A98.4)','cods'=>['A98']],
            ['t'=>'enf','n'=>84,'nom'=>'HANTAVIROSIS SCPH (B33.4)','cods'=>['B33']],
            ['t'=>'enf','n'=>85,'nom'=>'MONONUCLEOSIS INFECCIOSA (B27)','cods'=>['B27']],
            ['t'=>'enf','n'=>86,'nom'=>'VIRUELA (B03)','cods'=>['B03']],
            // II. NEOPLASIAS
            ['t'=>'cap','nom'=>'II. NEOPLASIAS'],
            ['t'=>'enf','n'=>87,'nom'=>'NEOPLASIAS (C00-D48)','cods'=>['C','D0','D1','D2','D3','D4']],
            ['t'=>'enf','n'=>88,'nom'=>'TUMORES MALIGNOS NO ESPECIFICADO (C80.9)','cods'=>['C80']],
            // III. ENF.DE LA SANGRE
            ['t'=>'cap','nom'=>'III. ENF.DE LA SANGRE Y ORG. HEMATOPOYÉTICOS'],
            ['t'=>'enf','n'=>89,'nom'=>'ANEMIAS (D50-D64)','cods'=>['D5','D6']],
            // IV. ENF. ENDOCRINAS
            ['t'=>'cap','nom'=>'IV. ENF. ENDOCRINAS, NUTRIC. Y METABOLICAS'],
            ['t'=>'enf','n'=>90,'nom'=>'TRASTORNOS TIROIDEOS (E00-E07)','cods'=>['E0']],
            ['t'=>'enf-3e','n'=>91,'nom'=>'DIABETES MELLITUS TIPO 1 (E10-E14)','cods'=>['E10'],'emin'=>0,'emax'=>30],
            ['t'=>'enf-3e','n'=>92,'nom'=>'DIABETES MELLITUS TIPO 2 (E10-E14)','cods'=>['E10','E11','E12','E13','E14'],'emin'=>31],
            ['t'=>'enf','n'=>93,'nom'=>'DESNUTRICIÓN LEVE < 15 AÑOS (E44.1)','cods'=>['E44']],
            ['t'=>'enf','n'=>94,'nom'=>'DESNUTRICIÓN MODERADA < 15 AÑOS (E44.0)','cods'=>['E44']],
            ['t'=>'enf','n'=>95,'nom'=>'DESNUTRICIÓN GRAVE < 15 AÑOS (E40-E43-E45)','cods'=>['E40','E41','E42','E43','E45']],
            ['t'=>'enf','n'=>96,'nom'=>'SOBREPESO (E66.9)','cods'=>['E66']],
            ['t'=>'enf','n'=>97,'nom'=>'OBESIDAD MÓRBIDA (E66)','cods'=>['E66']],
            // V. TRAST.MENTALES
            ['t'=>'cap','nom'=>'V. TRAST.MENTALES Y DEL COMPORTAMIENTO'],
            ['t'=>'enf','n'=>98,'nom'=>'TRAST. MENT. Y DEL COMPORTAMIENTO (F00-F99)','cods'=>['F']],
            ['t'=>'enf','n'=>99,'nom'=>'TRAST. MENT. ORGÁNICOS (F00-F09)','cods'=>['F0']],
            ['t'=>'enf','n'=>100,'nom'=>'TRAST. MENT. POR USO SUSTANCIAS PSICOACTIVAS (F10-F19)','cods'=>['F1']],
            ['t'=>'enf','n'=>101,'nom'=>'ESQUIZOFRENIA, ESQUIZOTÍPICOS Y TRAST. DELIRANTES (F20-F29)','cods'=>['F2']],
            ['t'=>'enf','n'=>102,'nom'=>'TRASTORNOS DEL HUMOR (F30-F39)','cods'=>['F3']],
            ['t'=>'enf','n'=>103,'nom'=>'TRASTORNOS NEURÓTICOS DEL ESTRÉS (F40-F48)','cods'=>['F4']],
            ['t'=>'enf','n'=>104,'nom'=>'SINDR. COMPORTAMIENTO ASOC. A FACT. FÍSICOS (F50-F59)','cods'=>['F5']],
            ['t'=>'enf','n'=>105,'nom'=>'TRASTORNOS DE LA PERSONALIDAD (F60-F69)','cods'=>['F6']],
            ['t'=>'enf','n'=>106,'nom'=>'RETARDO MENTAL (F70-F79)','cods'=>['F7']],
            ['t'=>'enf','n'=>107,'nom'=>'TRASTORNOS DEL DESARROLLO PSICOLÓGICO (F80-F89)','cods'=>['F8']],
            ['t'=>'enf','n'=>108,'nom'=>'TRASTORNOS EMOCIONALES Y DEL COMPORTAMIENTO (F90-F98)','cods'=>['F9']],
            ['t'=>'enf','n'=>109,'nom'=>'TRASTORNO MENTAL NO ESPECIFICADO (F99)','cods'=>['F99']],
            // VII. TRAST DEL SUEÑO Y LA VIGILIA
            ['t'=>'cap','nom'=>'VII. TRAST DEL SUEÑO Y LA VIGILIA'],
            ['t'=>'enf','n'=>110,'nom'=>'TRAST DEL SUEÑO Y LA VIGILIA','cods'=>['G47']],
            // VI. ENF. SISTEMA NERVIOSO
            ['t'=>'cap','nom'=>'VI. ENF. DEL SISTEMA NERVIOSO'],
            ['t'=>'enf','n'=>111,'nom'=>'PARÁLISIS FLÁCIDA < 15 AÑOS (G82.0)','cods'=>['G82']],
            ['t'=>'enf','n'=>112,'nom'=>'PARÁLISIS FLÁCIDA 15 AÑOS Y MÁS (G82.0)','cods'=>['G82']],
            ['t'=>'enf','n'=>113,'nom'=>'SÍNDROME DE GUILLAIN BARRÉ (G61.0)','cods'=>['G61']],
            ['t'=>'enf','n'=>114,'nom'=>'EPILEPSIA (G40-G41)','cods'=>['G40','G41']],
            ['t'=>'enf','n'=>115,'nom'=>'MIGRAÑA (G43)','cods'=>['G43']],
            // VII. ENF. DEL OJO
            ['t'=>'cap','nom'=>'VII. ENF.DEL OJO Y SUS ANEXOS'],
            ['t'=>'enf','n'=>116,'nom'=>'CONJUNTIVITIS (H10)','cods'=>['H10']],
            ['t'=>'enf','n'=>117,'nom'=>'TRASTORNOS DE LA ACOMODACIÓN Y REFRACCIÓN (H52)','cods'=>['H52']],
            ['t'=>'enf','n'=>118,'nom'=>'PERSONA CIEGA (PC) (CIF B210)','cods'=>[]],
            ['t'=>'enf','n'=>119,'nom'=>'DEFICIENCIA VISUAL SEVERA (DVS) (CIF B210)','cods'=>[]],
            ['t'=>'enf','n'=>120,'nom'=>'DEFICIENCIA VISUAL (DV) (CIF B210)','cods'=>[]],
            ['t'=>'enf','n'=>121,'nom'=>'OTRAS ENFERMEDADES DEL OJO (H00-H09-H11-H59)','cods'=>['H00','H01','H02','H03','H04','H05','H06','H07','H08','H09','H11','H50','H51','H53','H54','H55','H56','H57','H58','H59']],
            ['t'=>'enf','n'=>122,'nom'=>'RETINOPATIA DIABETICA (H36-E14.3)','cods'=>['H36']],
            ['t'=>'enf','n'=>123,'nom'=>'GLAUCOMA (H40)','cods'=>['H40']],
            ['t'=>'enf','n'=>124,'nom'=>'CATARATA (H25-H28)','cods'=>['H25','H26','H27','H28']],
            ['t'=>'enf','n'=>125,'nom'=>'AMBLIOPÍA (H53.0)','cods'=>['H53']],
            // VIII. ENF. DEL OIDO
            ['t'=>'cap','nom'=>'VIII. ENF. DEL OIDO Y APOFISIS MASTOIDES'],
            ['t'=>'enf','n'=>126,'nom'=>'OTITIS EXTERNA (H60)','cods'=>['H60']],
            ['t'=>'enf','n'=>127,'nom'=>'OTITIS MEDIA AGUDA < 5 AÑOS (H65.0-H65.1-H66.0-H66.9)','cods'=>['H65','H66']],
            ['t'=>'enf','n'=>128,'nom'=>'OTITIS MEDIA AGUDA ≥ 5 AÑOS (H65.0-H65.1-H66.0-H66.9)','cods'=>['H65','H66']],
            ['t'=>'enf','n'=>129,'nom'=>'OTITIS MEDIA CRÓNICA (H65.2-H65.9)','cods'=>['H65','H66']],
            // IX. ENF. SISTEMA CIRCULATORIO
            ['t'=>'cap','nom'=>'IX. ENFERM. DEL SISTEMA CIRCULATORIO'],
            ['t'=>'enf','n'=>130,'nom'=>'FIEBRE REUMAT. Y ENF.CARD.REUM. CRON. (I00-I09)','cods'=>['I0']],
            ['t'=>'enf','n'=>131,'nom'=>'FIEBRE REUMÁTICA SIN COMPLICACIONES CARDÍACAS (I00)','cods'=>['I00']],
            ['t'=>'enf-3e','n'=>132,'nom'=>'HIPERTENSIÓN ARTERIAL < 15 AÑOS (I10)','cods'=>['I10'],'emax'=>14],
            ['t'=>'enf-3e','n'=>133,'nom'=>'HIPERTENSIÓN ARTERIAL 15-44 AÑOS (I10)','cods'=>['I10'],'emin'=>15,'emax'=>44],
            ['t'=>'enf-3e','n'=>134,'nom'=>'HIPERTENSIÓN ARTERIAL 45 AÑOS Y MAS (I10)','cods'=>['I10'],'emin'=>45],
            ['t'=>'enf','n'=>135,'nom'=>'ENFERM. ISQUÉMICAS DEL CORAZÓN (I20-I22-I25)','cods'=>['I20','I22','I25']],
            ['t'=>'enf','n'=>136,'nom'=>'INFARTO AGUDO DEL MIOCARDIO < 45 AÑOS (I21)','cods'=>['I21']],
            ['t'=>'enf','n'=>137,'nom'=>'INFARTO AGUDO DEL MIOCARDIO 45 AÑOS Y MAS (I21)','cods'=>['I21']],
            ['t'=>'enf','n'=>138,'nom'=>'ENFERMEDADES CEREBROVASCULARES (I60-I69)','cods'=>['I6']],
            ['t'=>'enf','n'=>139,'nom'=>'VÁRICES DE MIEMBROS INFERIORES (I83)','cods'=>['I83']],
            ['t'=>'enf','n'=>140,'nom'=>'INSUFICIENCIA CARDÍACA (I50)','cods'=>['I50']],
            ['t'=>'enf','n'=>141,'nom'=>'ANGINA DE PECHO (I20)','cods'=>['I20']],
            ['t'=>'enf','n'=>142,'nom'=>'CARDIOPATÍA ISQUÉMICA (I25.9)','cods'=>['I25']],
            // X. ENF. SISTEMA RESPIRATORIO
            ['t'=>'cap','nom'=>'X. ENFERM. DEL SISTEMA RESPIRATORIO'],
            ['t'=>'enf','n'=>143,'nom'=>'RINOFARINGITIS AGUDA < 5 AÑOS (J00)','cods'=>['J00']],
            ['t'=>'enf','n'=>144,'nom'=>'RINOFARINGITIS AGUDA ≥ 5 AÑOS (J00)','cods'=>['J00']],
            ['t'=>'enf','n'=>145,'nom'=>'FARINGITIS AGUDA < 5 AÑOS (J02)','cods'=>['J02']],
            ['t'=>'enf','n'=>146,'nom'=>'FARINGITIS AGUDA ≥ 5 AÑOS (J02)','cods'=>['J02']],
            ['t'=>'enf','n'=>147,'nom'=>'AMIGDALITIS AGUDA (J03)','cods'=>['J03']],
            ['t'=>'enf-3e','n'=>148,'nom'=>'NEUMONÍA < 1 AÑO (J12-J18)','cods'=>['J12','J13','J14','J15','J16','J17','J18'],'emax'=>0],
            ['t'=>'enf-3e','n'=>149,'nom'=>'NEUMONÍA 1-4 AÑOS (J12-J18)','cods'=>['J12','J13','J14','J15','J16','J17','J18'],'emin'=>1,'emax'=>4],
            ['t'=>'enf-3e','n'=>150,'nom'=>'NEUMONÍAS 5 AÑOS Y MAS (J12-J18)','cods'=>['J12','J13','J14','J15','J16','J17','J18'],'emin'=>5],
            ['t'=>'enf','n'=>151,'nom'=>'NEUMONÍA GRAVE < 2 MESES (J12-J18)','cods'=>['J12','J13','J14','J15','J16','J17','J18']],
            ['t'=>'enf','n'=>152,'nom'=>'BRONQUIOLITIS AGUDA < 2 AÑOS (J21)','cods'=>['J21']],
            ['t'=>'enf','n'=>153,'nom'=>'BRONQUITIS AGUDA (J20)','cods'=>['J20']],
            ['t'=>'enf','n'=>154,'nom'=>'BRONQUITIS CRÓNICA (J41-J42-J44.8)','cods'=>['J41','J42','J44']],
            ['t'=>'enf','n'=>155,'nom'=>'ASMA < 10 AÑOS (J45)','cods'=>['J45']],
            ['t'=>'enf','n'=>156,'nom'=>'ASMA ≥ 10 AÑOS (J45)','cods'=>['J45']],
            ['t'=>'enf','n'=>157,'nom'=>'RINITIS ALÉRGICA (J30.1-J30.4)','cods'=>['J30']],
            ['t'=>'enf','n'=>158,'nom'=>'ENFERMEDAD PULMONAR OBSTRUCTIVA CRONICA (J44.9)','cods'=>['J44']],
            ['t'=>'enf','n'=>159,'nom'=>'SINUSITIS AGUDA (J01)','cods'=>['J01']],
            ['t'=>'enf','n'=>160,'nom'=>'LARINGITIS Y TRAQUEÍTIS AGUDA (J04)','cods'=>['J04']],
            ['t'=>'enf','n'=>161,'nom'=>'LARINGITIS OBSTRUCTIVA Y EPIGLOTITIS (J05)','cods'=>['J05']],
            ['t'=>'enf','n'=>162,'nom'=>'IRA VIAS RESP. SUP. Y SITIOS MULTIPLES NO ESPECIF. (J06)','cods'=>['J06']],
            ['t'=>'enf','n'=>163,'nom'=>'IRA NO ESPECIF.VIAS RESP.INFERIORES (J22)','cods'=>['J22']],
            ['t'=>'enf','n'=>164,'nom'=>'INFECCIÓN RESPIRATORIA AGUDA GRAVE (J22)','cods'=>['J22']],
            ['t'=>'enf','n'=>165,'nom'=>'SÍNDROME RESPIRATORIO AGUDO SEVERO SARS (U04.9)','cods'=>['U04']],
            ['t'=>'enf','n'=>166,'nom'=>'SINTOMÁTICO RESPIRATORIO','cods'=>[]],
            ['t'=>'enf','n'=>167,'nom'=>'EXACERBACIÓN AGUDA DE EPOC (J44.1)','cods'=>['J44']],
            // XI. ENF. SISTEMA DIGESTIVO
            ['t'=>'cap','nom'=>'XI. ENFERMEDADES DEL SISTEMA DIGESTIVO'],
            ['t'=>'enf','n'=>168,'nom'=>'CARIES DENTAL (K02)','cods'=>['K02']],
            ['t'=>'enf','n'=>169,'nom'=>'GINGIVITIS (K05.0-K05.1)','cods'=>['K05']],
            ['t'=>'enf','n'=>170,'nom'=>'ESTOMATITIS (K12.0-K12.1)','cods'=>['K12']],
            ['t'=>'enf','n'=>171,'nom'=>'GASTRITIS (K29)','cods'=>['K29']],
            ['t'=>'enf','n'=>172,'nom'=>'APENDICITIS TODAS FORMAS (K35-K37)','cods'=>['K35','K36','K37']],
            ['t'=>'enf','n'=>173,'nom'=>'HERNIAS CAVIDAD ABDOMINAL (K40-K46)','cods'=>['K4']],
            ['t'=>'enf','n'=>174,'nom'=>'COLELITIASIS (K80)','cods'=>['K80']],
            ['t'=>'enf','n'=>175,'nom'=>'PANCREATITIS AGUDA (K85)','cods'=>['K85']],
            ['t'=>'enf','n'=>176,'nom'=>'OTRAS ENF. ESOF., ESTÓMAGO E INTESTINO (K22-K31-K63)','cods'=>['K22','K23','K24','K25','K26','K27','K28','K29','K30','K31','K63']],
            // XII. ENF. PIEL
            ['t'=>'cap','nom'=>'XII. ENF.DE LA PIEL Y TEJ. SUBCUTANEO'],
            ['t'=>'enf','n'=>177,'nom'=>'ABSCESOS (L02)','cods'=>['L02']],
            ['t'=>'enf','n'=>178,'nom'=>'CELULITIS (L03)','cods'=>['L03']],
            ['t'=>'enf','n'=>179,'nom'=>'DERMATITIS (L20, L30)','cods'=>['L20','L30']],
            ['t'=>'enf','n'=>180,'nom'=>'PIODERMITIS (L08.0)','cods'=>['L08']],
            ['t'=>'enf','n'=>181,'nom'=>'URTICARIA (L50)','cods'=>['L50']],
            // XIII. ENF. OSTEOMUSC.
            ['t'=>'cap','nom'=>'XIII. ENF.SIST.OSTEOMUSC. Y TEJ. CONJUNTIVO'],
            ['t'=>'enf','n'=>182,'nom'=>'ARTRITIS (M00-M14)','cods'=>['M0','M1']],
            ['t'=>'enf','n'=>183,'nom'=>'MIALGIAS (M79.1)','cods'=>['M79']],
            ['t'=>'enf','n'=>184,'nom'=>'NEURALGIAS (M79.2)','cods'=>['M79']],
            ['t'=>'enf','n'=>185,'nom'=>'BURSITIS (M70-M71-M75-M77)','cods'=>['M70','M71','M75','M76','M77']],
            // XIV. ENF. GENITO-URINARIO
            ['t'=>'cap','nom'=>'XIV. ENF.DEL SISTEMA GENITO-URINARIO'],
            ['t'=>'enf','n'=>186,'nom'=>'INFECCIÓN URINARIA (N39.0)','cods'=>['N39']],
            ['t'=>'enf','n'=>187,'nom'=>'URETRITIS NO GONOCÓCICA (N34.1)','cods'=>['N34']],
            ['t'=>'enf','n'=>188,'nom'=>'LEUCORREA NO ESPECIFICADA (N89.8)','cods'=>['N89']],
            ['t'=>'enf','n'=>189,'nom'=>'HEMORR. GENITAL NO ESPEC. HEMBRAS (N93.9)','cods'=>['N93']],
            ['t'=>'enf','n'=>190,'nom'=>'CÓLICO NEFRÍTICO (N23)','cods'=>['N23']],
            ['t'=>'enf','n'=>191,'nom'=>'DISMENORREA NO ESPECIFICADA (N94.6)','cods'=>['N94']],
            ['t'=>'enf','n'=>192,'nom'=>'SALPINGITIS Y OOFORITIS (N70)','cods'=>['N70']],
            ['t'=>'enf','n'=>193,'nom'=>'ENFERM. INFLAMATORIAS DEL ÚTERO (N71-N72)','cods'=>['N71','N72']],
            ['t'=>'enf','n'=>194,'nom'=>'ENFERM. RENAL (N00-N08-N10-N13.3-N14-N20.0-N25-N29.8)','cods'=>['N0','N1','N2']],
            ['t'=>'enf','n'=>195,'nom'=>'INSUFICIENCIA RENAL AGUDA (N17.9)','cods'=>['N17']],
            ['t'=>'enf','n'=>196,'nom'=>'CÁLCULO DEL RIÑÓN Y DEL URETER (N20.0)','cods'=>['N20']],
            ['t'=>'enf','n'=>197,'nom'=>'CISTITIS (N30.9)','cods'=>['N30']],
            // XV. EMBARAZO, PARTO Y PUERPERIO
            ['t'=>'cap','nom'=>'XV. EMBARAZO, PARTO Y PUERPERIO (**)'],
            ['t'=>'enf','n'=>198,'nom'=>'HEMORRAGIA 1º TRIMESTRE (O20-O44)','cods'=>['O20','O21','O22','O23','O24','O25','O26','O27','O28','O29','O30','O31','O32','O33','O34','O35','O36','O37','O38','O39','O40','O41','O42','O43','O44']],
            ['t'=>'enf','n'=>199,'nom'=>'HEMORRAGIA 2º TRIMESTRE (O20-O44)','cods'=>['O20','O21','O22','O23','O24','O25','O26','O27','O28','O29','O30','O31','O32','O33','O34','O35','O36','O37','O38','O39','O40','O41','O42','O43','O44']],
            ['t'=>'enf','n'=>200,'nom'=>'HEMORRAGIA 3º TRIM. EMBARAZO (O20-O44-O46)','cods'=>['O20','O21','O22','O23','O24','O25','O26','O27','O28','O29','O30','O31','O32','O33','O34','O35','O36','O37','O38','O39','O40','O41','O42','O43','O44','O46']],
            ['t'=>'enf','n'=>201,'nom'=>'HIPERTENSIÓN PREVIA (O10)','cods'=>['O10','O11']],
            ['t'=>'enf','n'=>202,'nom'=>'PRE-ECLAMPSIA (O13-O14)','cods'=>['O13','O14']],
            ['t'=>'enf','n'=>203,'nom'=>'ECLAMPSIA (O15)','cods'=>['O15']],
            ['t'=>'enf','n'=>204,'nom'=>'INTENTO FALLIDO DE ABORTO (O07)','cods'=>['O07']],
            ['t'=>'enf','n'=>205,'nom'=>'ABORTO (O00-O06-O08)','cods'=>['O00','O01','O02','O03','O04','O05','O06','O08']],
            ['t'=>'enf','n'=>206,'nom'=>'RUPTURA PREMATURA MEMBRANA (O42.9)','cods'=>['O42']],
            ['t'=>'enf','n'=>207,'nom'=>'TRAST. MAMARIOS DEL PUERPERIO (O91-O92)','cods'=>['O91','O92']],
            ['t'=>'enf','n'=>208,'nom'=>'DM PREGESTACINAL I (O24.1)','cods'=>['O24']],
            ['t'=>'enf','n'=>209,'nom'=>'DM PREGESTACINAL II (O24.2)','cods'=>['O24']],
            ['t'=>'enf','n'=>210,'nom'=>'DIABETES GESTACIONAL (O24.4)','cods'=>['O24']],
            ['t'=>'enf','n'=>211,'nom'=>'INFECCIONES PUERPERALES (O85-O86)','cods'=>['O85','O86']],
            ['t'=>'enf','n'=>212,'nom'=>'HEMORRAGIAS PUERPERALES (O72.1-O72.2)','cods'=>['O72']],
            ['t'=>'enf','n'=>213,'nom'=>'ENDOMETRITIS (N71)','cods'=>['N71']],
            ['t'=>'enf','n'=>214,'nom'=>'DEHISCENCIA DE EPISIORRAFIA (O90.1)','cods'=>['O90']],
            ['t'=>'enf','n'=>215,'nom'=>'ABSCESO DE PARED ABDOMINAL (O75.4)','cods'=>['O75']],
            ['t'=>'enf','n'=>216,'nom'=>'DEPRESIÓN POSTPARTO (O99.3)','cods'=>['O99']],
            ['t'=>'enf','n'=>217,'nom'=>'MASTITIS (O91)','cods'=>['O91']],
            ['t'=>'enf','n'=>218,'nom'=>'GRIETAS Ó SIGNOS DE INFECCIÓN EN LOS PEZONES (O92.1)','cods'=>['O92']],
            ['t'=>'enf','n'=>219,'nom'=>'OTRAS COMPLICACIONES DEL EMB.PART. Y PUERPERIO (O99.8)','cods'=>['O99']],
            // XVI. PERIODO PERINATAL
            ['t'=>'cap','nom'=>'XVI. AFECCIONES EN EL PERIODO PERINATAL'],
            ['t'=>'enf','n'=>220,'nom'=>'SÍNDROME DE RUBÉOLA CONGÉNITA (P35.0)','cods'=>['P35']],
            // XVIII. SINT. Y HALLAZGOS
            ['t'=>'cap','nom'=>'XVIII. SINT.SIG. Y HALLAZGOS ANORMALES'],
            ['t'=>'enf','n'=>221,'nom'=>'CEFALEA (R51)','cods'=>['R51']],
            ['t'=>'enf','n'=>222,'nom'=>'FIEBRE (R50)','cods'=>['R50']],
            ['t'=>'enf','n'=>223,'nom'=>'CONVULSIONES (R56)','cods'=>['R56']],
            ['t'=>'enf','n'=>224,'nom'=>'ABDOMEN AGUDO (R10.0)','cods'=>['R10']],
            ['t'=>'enf','n'=>225,'nom'=>'DOLOR ABDOMINAL (R10.4)','cods'=>['R10']],
            ['t'=>'enf','n'=>226,'nom'=>'PROTEINURIA (R80)','cods'=>['R80']],
            // XIX. TRAUMATISMOS
            ['t'=>'cap','nom'=>'XIX. TRAUMATISMOS Y ENVENENAMIENTOS'],
            ['t'=>'enf','n'=>227,'nom'=>'QUEMADURAS (T20-T32)','cods'=>['T2','T3']],
            ['t'=>'enf','n'=>228,'nom'=>'ENVENEN. POR DROG. Y OTRAS SUST. (T36-T50)','cods'=>['T36','T37','T38','T39','T40','T41','T42','T43','T44','T45','T46','T47','T48','T49','T50']],
            ['t'=>'enf','n'=>229,'nom'=>'HERIDAS (T14.1)','cods'=>['T14']],
            ['t'=>'enf','n'=>230,'nom'=>'FRACTURAS (T14.2)','cods'=>['T14']],
            ['t'=>'enf','n'=>231,'nom'=>'LUXACIONES Y ESGUINCES (T14.3)','cods'=>['T14']],
            ['t'=>'enf','n'=>232,'nom'=>'INTOXICACIÓN POR PLAGUICIDAS (T60)','cods'=>['T60']],
            ['t'=>'enf','n'=>233,'nom'=>'CUERPO EXTRAÑO EN ORIFICIO NATURAL (T15-T19)','cods'=>['T1']],
            ['t'=>'enf','n'=>234,'nom'=>'TRAUMA OCULAR (S05)','cods'=>['S05']],
            ['t'=>'enf','n'=>235,'nom'=>'OTROS TRAUMATISMOS (T14.8)','cods'=>['T14']],
            // XX. CAUSAS EXTERNAS
            ['t'=>'cap','nom'=>'XX. CAUSAS EXTERNAS DE MORBILIDAD Y MORT.'],
            ['t'=>'enf','n'=>236,'nom'=>'ACCID. TRANSPORT. TERRESTRE (V01-V89)','cods'=>['V']],
            ['t'=>'enf','n'=>237,'nom'=>'PICADURA DE INSEC. Y OTROS ANIM. (X21-X27-X29)','cods'=>['X2']],
            ['t'=>'enf','n'=>238,'nom'=>'EMPOZOÑAMIENTO OFÍDICO (X20-W59)','cods'=>['X20','W59']],
            ['t'=>'enf','n'=>239,'nom'=>'MORDEDURAS SOSPECHOSAS DE RABIA (A82)','cods'=>['A82']],
            ['t'=>'enf','n'=>240,'nom'=>'OTROS ACCIDENTES','cods'=>[]],
            ['t'=>'enf','n'=>241,'nom'=>'ACCIDENTES DEL HOGAR','cods'=>[]],
            ['t'=>'enf','n'=>242,'nom'=>'ACCIDENTES LABORALES','cods'=>[]],
            ['t'=>'enf','n'=>243,'nom'=>'EFECTOS ADVERSOS DE MEDICAMENTOS (Y40-Y57)','cods'=>['Y4','Y5']],
            ['t'=>'enf','n'=>244,'nom'=>'EFECTOS ADVERSOS DE VACUNAS (Y58-Y59)','cods'=>['Y58','Y59']],
            ['t'=>'enf','n'=>245,'nom'=>'ACULIADURA DE ALACRÁN (X22)','cods'=>['X22']],
            // VIOLENCIA FAMILIAR
            ['t'=>'cap','nom'=>'VIOLENCIA FAMILIAR'],
            ['t'=>'enf','n'=>246,'nom'=>'V. FLIAR. FÍSICA NIÑO (0 A 11 AÑOS)','cods'=>[]],
            ['t'=>'enf','n'=>247,'nom'=>'V. FLIAR. FÍSICA NIÑA (0 A 11 AÑOS)','cods'=>[]],
            ['t'=>'enf','n'=>248,'nom'=>'V. FLIAR. FÍSICA ADOLESCENTE HOMBRE (12 A19 AÑOS)','cods'=>[]],
            ['t'=>'enf','n'=>249,'nom'=>'V. FLIAR. FÍSICA ADOLESCENTE MUJER (12 A 19 AÑOS)','cods'=>[]],
            ['t'=>'enf','n'=>250,'nom'=>'V. FLIAR. FÍSICA ADULTO (20 A 59 AÑOS)','cods'=>[]],
            ['t'=>'enf','n'=>251,'nom'=>'V. FLIAR. FÍSICA ADULTA (20 A 59 AÑOS)','cods'=>[]],
            ['t'=>'enf','n'=>252,'nom'=>'V. FLIAR. FÍSICA ADULTO MAYOR (60 AÑOS Y MAS)','cods'=>[]],
            ['t'=>'enf','n'=>253,'nom'=>'V. FLIAR. FÍSICA ADULTA MAYOR (60 AÑOS Y MAS)','cods'=>[]],
            ['t'=>'enf','n'=>254,'nom'=>'V. FLIAR. PSICOLÓGICA NIÑO (0 A 11 AÑOS)','cods'=>[]],
            ['t'=>'enf','n'=>255,'nom'=>'V. FLIAR. PSICOLÓGICA NIÑA (0 A 11 AÑOS)','cods'=>[]],
            ['t'=>'enf','n'=>256,'nom'=>'V. FLIAR. PSICOLÓGICA ADOLESCENTE HOMBRE (12 A 19 AÑOS)','cods'=>[]],
            ['t'=>'enf','n'=>257,'nom'=>'V. FLIAR. PSICOLÓGICA ADOLESCENTE MUJER (12 A 19 AÑOS)','cods'=>[]],
            ['t'=>'enf','n'=>258,'nom'=>'V. FLIAR. PSICOLÓGICA ADULTO (20 A 59 AÑOS)','cods'=>[]],
            ['t'=>'enf','n'=>259,'nom'=>'V. FLIAR. PSICOLÓGICA ADULTA (20 A 59 AÑOS)','cods'=>[]],
            ['t'=>'enf','n'=>260,'nom'=>'V. FLIAR. PSICOLÓGICA ADULTO MAYOR (60 AÑOS Y MAS)','cods'=>[]],
            ['t'=>'enf','n'=>261,'nom'=>'V. FLIAR. PSICOLÓGICA ADULTA MAYOR (60 AÑOS Y MAS)','cods'=>[]],
            ['t'=>'enf','n'=>262,'nom'=>'V. FLIAR. SEXUAL NIÑO (0 A 11 AÑOS)','cods'=>[]],
            ['t'=>'enf','n'=>263,'nom'=>'V. FLIAR. SEXUAL NIÑA (0 A 11 AÑOS)','cods'=>[]],
            ['t'=>'enf','n'=>264,'nom'=>'V. FLIAR. SEXUAL ADOLESCENTE HOMBRE (12 A 19 AÑOS)','cods'=>[]],
            ['t'=>'enf','n'=>265,'nom'=>'V. FLIAR. SEXUAL ADOLESCENTE MUJER (12 A 19 AÑOS)','cods'=>[]],
            ['t'=>'enf','n'=>266,'nom'=>'V. FLIAR. SEXUAL ADULTO (20 A 59 AÑOS)','cods'=>[]],
            ['t'=>'enf','n'=>267,'nom'=>'V. FLIAR. SEXUAL ADULTA (20 A 59 AÑOS)','cods'=>[]],
            ['t'=>'enf','n'=>268,'nom'=>'V. FLIAR. SEXUAL ADULTO MAYOR (60 AÑOS Y MAS)','cods'=>[]],
            ['t'=>'enf','n'=>269,'nom'=>'V. FLIAR. SEXUAL ADULTA MAYOR (60 AÑOS Y MAS)','cods'=>[]],
            // OTRAS CAUSAS
            ['t'=>'cap','nom'=>'OTRAS CAUSAS DE CONSULTA'],
            ['t'=>'enf','n'=>270,'nom'=>'OTRAS CAUSAS DE CONSULTA','cods'=>[]],
            ['t'=>'enf','n'=>271,'nom'=>'PACIENTES HOSPITALIZADOS O REFERIDOS PARA HOSPITALIZACIÓN','cods'=>[]],
            ['t'=>'cap','nom'=>'TOTAL CAUSAS DE CONSULTA'],
        ];
    }

    /**
     * Clasifica un código CIE-10 en una de las 271 enfermedades del EPI-15
     * 
     * @param string $codigo Código CIE-10 del diagnóstico
     * @param int|null $edad Edad del paciente
     * @return int|null Número de enfermedad (1-271) o null si no clasifica
     */
    private function clasificarEnfermedadEpi15(?string $codigo, ?int $edad = null): ?int
    {
        $rows = $this->getEpi15Rows();
        if ($codigo === null || trim($codigo) === '') return null;
        $codigo = strtoupper(trim($codigo));

        $candidatos = [];

        foreach ($rows as $row) {
            if ($row['t'] !== 'enf' && $row['t'] !== 'enf-3e') continue;
            if (empty($row['cods'])) continue;

            foreach ($row['cods'] as $prefix) {
                if (str_starts_with($codigo, $prefix)) {
                    $candidatos[] = $row;
                    break;
                }
            }
        }

        if (empty($candidatos)) return null;

        // Si solo hay un candidato, devolverlo
        if (count($candidatos) === 1) return $candidatos[0]['n'];

        // Si hay múltiples, buscar el que mejor se ajuste por edad
        if ($edad !== null) {
            foreach ($candidatos as $c) {
                $edadOk = true;
                if (isset($c['emin']) && $edad < $c['emin']) $edadOk = false;
                if (isset($c['emax']) && $edad > $c['emax']) $edadOk = false;
                if ($edadOk) return $c['n'];
            }
        }

        // Si no hay match por edad, devolver el primero
        return $candidatos[0]['n'];
    }

    /**
     * Clasifica una colección de consultas en el consolidado EPI-15
     * 
     * @param \Illuminate\Support\Collection $consultations
     * @return array [numero_enfermedad => ['p'=>int, 's'=>int, 'x'=>int]]
     */
    private function consolidarEpi15($consultations): array
    {
        $consolidado = [];
        foreach (range(1, 271) as $n) {
            $consolidado[$n] = ['p' => 0, 's' => 0, 'x' => 0];
        }

        foreach ($consultations as $consultation) {
            $patient = $consultation->patient;
            if (!$patient) continue;

            $edad = $patient->birth_date ? \Carbon\Carbon::parse($patient->birth_date)->age : null;
            $tipo = strtoupper(trim($consultation->consultation_type ?? 'P'));

            if ($consultation->sisDiagnoses && $consultation->sisDiagnoses->isNotEmpty()) {
                foreach ($consultation->sisDiagnoses as $consultaDiag) {
                    $diagnosis = $consultaDiag->sisDiagnosis;
                    if (!$diagnosis) continue;

                    $codigo = $diagnosis->code;
                    $numEnf = $this->clasificarEnfermedadEpi15($codigo, $edad);

                    if ($numEnf !== null && isset($consolidado[$numEnf])) {
                        if ($tipo === 'P') $consolidado[$numEnf]['p']++;
                        elseif ($tipo === 'S') $consolidado[$numEnf]['s']++;
                        else $consolidado[$numEnf]['x']++;
                    }
                }
            }
        }

        return $consolidado;
    }

    /**
     * Historial epidemiológico - búsqueda por CIE-10 y sector.
     * GET /reportes/historical
     */
    public function historical(Request $request): \Inertia\Response
    {
        $query = \App\Models\Consultation::with([
            'patient:id,full_name,id_number,gender,birth_date,addr_sector',
            'doctor:id,name',
            'sisDiagnoses.sisDiagnosis:id,code,name',
        ])->orderBy('consultation_date', 'desc');

        if ($code = $request->input('code')) {
            $query->whereHas('sisDiagnoses.sisDiagnosis', fn($q) => $q->where('code', 'like', "$code%")->orWhere('name', 'like', "%$code%"));
        }

        if ($sector = $request->input('sector')) {
            $query->whereHas('patient', fn($q) => $q->where('addr_sector', 'like', "%$sector%"));
        }

        $consultations = $query->paginate(50)->withQueryString();

        $count = $consultations->total();

        $sectors = \App\Models\Patient::whereNotNull('addr_sector')->distinct()->pluck('addr_sector')->sort()->values();

        return \Inertia\Inertia::render('Reports/Historical', [
            'consultations' => $consultations,
            'filters' => $request->only(['code', 'sector']),
            'count' => $count,
            'sectors' => $sectors,
        ]);
    }

    /**
     * Muestra la interfaz interactiva de la matriz EPI.
     * GET /reportes/epi
     */
    public function epiMatrix(): \Inertia\Response
    {
        return \Inertia\Inertia::render('Reports/EpiMatrix', [
            'currentYear' => (int) date('Y'),
        ]);
    }

    /**
     * Devuelve los datos de la matriz EPI-12 para una semana específica.
     * POST /reportes/epi/data
     */
    public function epiMatrixData(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2050',
            'week' => 'required|integer|min:1|max:53',
        ]);

        $year = $request->integer('year');
        $week = $request->integer('week');

        $fechaInicio = \Carbon\Carbon::now()->setISODate($year, $week)->startOfWeek();
        $fechaFin = $fechaInicio->copy()->endOfWeek();

        $consultations = Consultation::with([
            'patient:id,full_name,gender,birth_date',
            'sisDiagnoses.sisDiagnosis:id,code,name'
        ])
        ->whereBetween('consultation_date', [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')])
        ->orderBy('consultation_date', 'asc')
        ->get();

        // Lista de 52 enfermedades del formato oficial EPI-12
        $enfermedadesOficiales = [
            'A00' => 'Cólera',
            'A08-A09' => 'Diarreas',
            'A06' => 'Amibiasis',
            'A01.0' => 'Fiebre Tifoidea',
            'ETA_BROTES' => 'ETA N° de Brotes',
            'ETA_ASOCIADOS' => 'Casos Asociados a Brotes de ETA',
            'B15' => 'Hepatitis Aguda Tipo A',
            'A15-A19' => 'Tuberculosis',
            'J10-J11' => 'Influenza / Enfermedad Tipo Influenza',
            'A50' => 'Sífilis Congénita',
            'Z21' => 'Infección Asintomática VIH',
            'B20-B24' => 'Enfermedad VIH/SIDA',
            'A37' => 'Tosferina',
            'B26' => 'Parotiditis',
            'A33' => 'Tétanos Neonatal',
            'A34' => 'Tétanos Obstétrico',
            'A35' => 'Tétanos (otros)',
            'A36' => 'Difteria',
            'B05' => 'Sarampión Sospecha',
            'B06' => 'Rubéola',
            'DENGUE_SIN' => 'Dengue sin Signo de Alarma',
            'DENGUE_CON' => 'Dengue con Signo de Alarma',
            'A91' => 'Dengue Grave',
            'CHIKUNGUNYA' => 'Chikungunya',
            'U08' => 'Zika',
            'A92.2' => 'Encefalitis Equina Venezolana',
            'A95' => 'Fiebre Amarilla',
            'B55.0' => 'Leishmaniasis Visceral',
            'B55.1' => 'Leishmaniasis Cutánea',
            'B55.2' => 'Leishmaniasis Mucocutánea',
            'B55.9' => 'Leishmaniasis no Específica',
            'B57.0-B57.1' => 'Enfermedad de Chagas Aguda',
            'B57.2-B57.5' => 'Enfermedad de Chagas Crónica',
            'A82' => 'Rabia Humana',
            'A96.8' => 'Fiebre Hemorrágica Venezolana',
            'A27' => 'Leptospirosis',
            'A87' => 'Meningitis Viral',
            'G00' => 'Meningitis Bacteriana',
            'A39.0' => 'Enfermedad Meningococcica',
            'A39.9' => 'Enfermedad Meningococcica',
            'B01' => 'Varicela',
            'B16' => 'Hepatitis Aguda Tipo B',
            'B17.1_B18.2' => 'Hepatitis Aguda Tipo C',
            'B17' => 'Hepatitis Otras Agudas',
            'B19' => 'Hepatitis No Específicas',
            'G82.0' => 'Parálisis Flácida < 15 años',
            'J12-J18' => 'Neumonías',
            'T60' => 'Intoxicación por Plaguicidas',
            'A82_RABIA' => 'Mordedura Sospechosa de Rabia',
            'R50' => 'Fiebre',
            'Y40-Y57' => 'Efectos Adversos de Medicamentos',
            'Y58-Y59' => 'Efectos Adversos de Vacunas',
        ];

        $ageGroups = [
            'lt1' => '< 1 año',
            '1_4' => '1 a 4 años',
            '5_6' => '5 a 6 años',
            '7_9' => '7 a 9 años',
            '10_11' => '10 a 11 años',
            '12_14' => '12 a 14 años',
            '15_19' => '15 a 19 años',
            '20_24' => '20 a 24 años',
            '25_44' => '25 a 44 años',
            '45_59' => '45 a 59 años',
            '60_64' => '60 a 64 años',
            '65plus' => '65 años y más',
            'ignorado' => 'Edad ignorada',
        ];

        // Inicializar estructura
        $rows = [];
        foreach ($enfermedadesOficiales as $claveEnf => $nombre) {
            $grupos = [];
            foreach ($ageGroups as $gk => $gl) {
                $grupos[$gk] = ['H' => 0, 'M' => 0];
            }
            $rows[$claveEnf] = [
                'codigo' => $claveEnf,
                'diagnostico' => $nombre,
                'grupos' => $grupos,
                'total_h' => 0,
                'total_m' => 0,
                'total' => 0,
            ];
        }

        $totalGeneral = 0;
        $sinDiagnostico = 0;
        $conDiagnostico = 0;

        // Clasificar y contar
        foreach ($consultations as $consultation) {
            $tieneDiag = false;
            if ($consultation->sisDiagnoses && $consultation->sisDiagnoses->isNotEmpty()) {
                foreach ($consultation->sisDiagnoses as $consultaDiag) {
                    $diagnosis = $consultaDiag->sisDiagnosis;
                    if (!$diagnosis) { continue; }

                    $tieneDiag = true;
                    $claveEnf = $this->clasificarEnfermedadEpi12($diagnosis);
                    if (!$claveEnf || !isset($rows[$claveEnf])) continue;

                    $edad = $consultation->patient?->birth_date
                        ? \Carbon\Carbon::parse($consultation->patient->birth_date)->age
                        : null;
                    $grupoEdad = $this->obtenerGrupoEdadEpi12($edad);
                    $sexo = $this->normalizarSexo($consultation->patient?->gender);

                    if (isset($rows[$claveEnf]['grupos'][$grupoEdad][$sexo])) {
                        $rows[$claveEnf]['grupos'][$grupoEdad][$sexo]++;
                        $rows[$claveEnf]['total_h'] += ($sexo === 'H' ? 1 : 0);
                        $rows[$claveEnf]['total_m'] += ($sexo === 'M' ? 1 : 0);
                        $rows[$claveEnf]['total']++;
                        $totalGeneral++;
                    }
                }
            }
            if ($tieneDiag) $conDiagnostico++;
            if (!$tieneDiag || ($consultation->sisDiagnoses && $consultation->sisDiagnoses->isEmpty())) {
                $sinDiagnostico++;
            }
        }

        // Alertas con nombres de pacientes
        $alertas = [];
        if ($consultations->isEmpty()) {
            $alertas[] = ['tipo' => 'sin_datos', 'titulo' => 'No hay consultas registradas para esta semana.', 'items' => []];
        }

        $sinDiag = [];
        $sinCodigo = 0;
        $sinFechaNac = [];
        $sinSexo = [];
        $sinSector = [];
        $totalPacientes = 0;
        foreach ($consultations as $c) {
            if ($c->patient) $totalPacientes++;
            if ($c->patient && !$c->patient->birth_date) $sinFechaNac[$c->patient->id] = $c->patient;
            if ($c->patient && !$c->patient->gender) $sinSexo[$c->patient->id] = $c->patient;
            if ($c->patient && !$c->patient->addr_sector) $sinSector[$c->patient->id] = $c->patient;
            if (!$c->sisDiagnoses || $c->sisDiagnoses->isEmpty()) {
                $p = $c->patient;
                $sinDiag[] = $p ? ($p->id_number ? "{$p->full_name} (C.I: {$p->id_number})" : $p->full_name) : "Consulta #{$c->id}";
            }
            if ($c->sisDiagnoses && $c->sisDiagnoses->isNotEmpty()) {
                foreach ($c->sisDiagnoses as $cd) {
                    if (!$cd->sisDiagnosis) {
                        $sinCodigo++;
                        break;
                    }
                }
            }
        }
        $fmtPac = fn($arr) => array_values(array_map(fn($p) => $p->id_number ? "{$p->full_name} (C.I: {$p->id_number})" : $p->full_name, $arr));

        if (count($sinDiag) > 0) {
            $alertas[] = ['tipo' => 'sin_diag', 'titulo' => 'Consultas sin diagnóstico médico', 'items' => array_values($sinDiag)];
        }
        if ($sinCodigo > 0) {
            $alertas[] = ['tipo' => 'sin_codigo', 'titulo' => "Diagnósticos sin código CIE-10 ($sinCodigo consultas)", 'items' => []];
        }
        if (count($sinFechaNac) > 0) {
            $alertas[] = ['tipo' => 'sin_fecha_nac', 'titulo' => 'Pacientes sin fecha de nacimiento', 'items' => $fmtPac($sinFechaNac)];
        }
        if (count($sinSector) > 0) {
            $alertas[] = ['tipo' => 'sin_sector', 'titulo' => 'Pacientes sin sector de residencia', 'items' => $fmtPac($sinSector)];
        }

        return response()->json([
            'rows' => array_values($rows),
            'ageGroups' => $ageGroups,
            'totalConsultas' => $consultations->count(),
            'totalGeneral' => $totalGeneral,
            'alertas' => $alertas,
            'calidadDatos' => [
                'totalPacientes' => $totalPacientes,
                'sinFechaNac' => count($sinFechaNac),
                'sinSexo' => count($sinSexo),
                'sinSector' => count($sinSector),
            ],
            'semana' => "Semana $week, $year",
            'fechaInicio' => $fechaInicio->format('d/m/Y'),
            'fechaFin' => $fechaFin->format('d/m/Y'),
        ]);
    }

    /**
     * Devuelve los datos de la matriz EPI-15 para un mes específico.
     * POST /reportes/epi/data-15
     */
    public function epiMatrixData15(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2050',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year = $request->integer('year');
        $month = $request->integer('month');
        $periodo = sprintf('%04d-%02d', $year, $month);

        $fechaInicio = "$periodo-01";
        $fechaFin = date('Y-m-t', strtotime($fechaInicio));

        $consultations = Consultation::with([
            'patient:id,full_name,gender,birth_date',
            'sisDiagnoses.sisDiagnosis:id,code,name'
        ])
        ->whereBetween('consultation_date', [$fechaInicio, $fechaFin])
        ->orderBy('consultation_date', 'asc')
        ->get();

        // Year-to-date consultations for accumulated data
        $yearConsultations = Consultation::with([
            'patient:id,full_name,gender,birth_date',
            'sisDiagnoses.sisDiagnosis:id,code,name'
        ])
        ->whereBetween('consultation_date', ["$year-01-01", $fechaFin])
        ->get();

        $rows = $this->getEpi15Rows();
        $mesData = $this->consolidarEpi15($consultations);
        $anioData = $this->consolidarEpi15($yearConsultations);

        // Build response rows maintaining hierarchy
        $responseRows = [];
        $totalP = 0; $totalS = 0; $totalX = 0;
        $totalAcumP = 0; $totalAcumS = 0; $totalAcumX = 0;

        foreach ($rows as $row) {
            $n = $row['n'] ?? null;
            $entry = [
                'tipo' => $row['t'],
                'nombre' => $row['nom'],
            ];
            if ($row['t'] === 'enf' || $row['t'] === 'enf-3e') {
                $p = $mesData[$n]['p'] ?? 0;
                $s = $mesData[$n]['s'] ?? 0;
                $x = $mesData[$n]['x'] ?? 0;
                $acumP = $anioData[$n]['p'] ?? 0;
                $acumS = $anioData[$n]['s'] ?? 0;
                $acumX = $anioData[$n]['x'] ?? 0;
                $entry['n'] = $n;
                $entry['p'] = $p;
                $entry['s'] = $s;
                $entry['x'] = $x;
                $entry['total'] = $p + $s + $x;
                $entry['acumP'] = $acumP;
                $entry['acumS'] = $acumS;
                $entry['acumX'] = $acumX;
                $entry['acumTotal'] = $acumP + $acumS + $acumX;
                $totalP += $p; $totalS += $s; $totalX += $x;
                $totalAcumP += $acumP; $totalAcumS += $acumS; $totalAcumX += $acumX;
            }
            $responseRows[] = $entry;
        }

        // Alertas con nombres de pacientes
        $alertas = [];
        if ($consultations->isEmpty()) {
            $alertas[] = ['tipo' => 'sin_datos', 'titulo' => 'No hay consultas registradas para este mes.', 'items' => []];
        }

        $sinDiag = [];
        $sinFechaNac = [];
        $sinSexo = [];
        $sinSector = [];
        $totalPacientes = 0;
        foreach ($consultations as $c) {
            if ($c->patient) $totalPacientes++;
            if ($c->patient && !$c->patient->birth_date) $sinFechaNac[$c->patient->id] = $c->patient;
            if ($c->patient && !$c->patient->gender) $sinSexo[$c->patient->id] = $c->patient;
            if ($c->patient && !$c->patient->addr_sector) $sinSector[$c->patient->id] = $c->patient;
            if (!$c->sisDiagnoses || $c->sisDiagnoses->isEmpty()) {
                $p = $c->patient;
                $sinDiag[] = $p ? ($p->id_number ? "{$p->full_name} (C.I: {$p->id_number})" : $p->full_name) : "Consulta #{$c->id}";
            }
        }
        $fmtPac = fn($arr) => array_values(array_map(fn($p) => $p->id_number ? "{$p->full_name} (C.I: {$p->id_number})" : $p->full_name, $arr));

        if (count($sinDiag) > 0) {
            $alertas[] = ['tipo' => 'sin_diag', 'titulo' => 'Consultas sin diagnóstico médico', 'items' => array_values($sinDiag)];
        }
        if (count($sinFechaNac) > 0) {
            $alertas[] = ['tipo' => 'sin_fecha_nac', 'titulo' => 'Pacientes sin fecha de nacimiento', 'items' => $fmtPac($sinFechaNac)];
        }
        if (count($sinSexo) > 0) {
            $alertas[] = ['tipo' => 'sin_sexo', 'titulo' => 'Pacientes sin sexo registrado', 'items' => $fmtPac($sinSexo)];
        }
        if (count($sinSector) > 0) {
            $alertas[] = ['tipo' => 'sin_sector', 'titulo' => 'Pacientes sin sector de residencia', 'items' => $fmtPac($sinSector)];
        }

        $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

        return response()->json([
            'type' => 'epi15',
            'rows' => $responseRows,
            'totalConsultas' => $consultations->count(),
            'totalGeneral' => $totalP + $totalS + $totalX,
            'totales' => ['p' => $totalP, 's' => $totalS, 'x' => $totalX, 'total' => $totalP + $totalS + $totalX],
            'totalesAcum' => ['p' => $totalAcumP, 's' => $totalAcumS, 'x' => $totalAcumX, 'total' => $totalAcumP + $totalAcumS + $totalAcumX],
            'alertas' => $alertas,
            'calidadDatos' => [
                'totalPacientes' => $totalPacientes,
                'sinFechaNac' => count($sinFechaNac),
                'sinSexo' => count($sinSexo),
                'sinSector' => count($sinSector),
            ],
            'periodo' => $meses[$month - 1] . " $year",
            'fechaInicio' => date('d/m/Y', strtotime($fechaInicio)),
            'fechaFin' => date('d/m/Y', strtotime($fechaFin)),
        ]);
    }

    /**
     * Marca las consultas de una semana como verificadas.
     * POST /reportes/epi/verify
     */
    public function verifyWeek(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2050',
            'week' => 'required|integer|min:1|max:53',
        ]);

        $year = $request->integer('year');
        $week = $request->integer('week');

        $fechaInicio = \Carbon\Carbon::now()->setISODate($year, $week)->startOfWeek();
        $fechaFin = $fechaInicio->copy()->endOfWeek();

        $count = Consultation::whereBetween('consultation_date', [
            $fechaInicio->format('Y-m-d'),
            $fechaFin->format('Y-m-d'),
        ])->whereNull('verified_at')->update([
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
        ]);

        if ($count === 0) {
            return response()->json([
                'message' => "No hay consultas pendientes por verificar en la semana $week de $year.",
                'count' => 0,
            ]);
        }

        return response()->json([
            'message' => "Semana $week de $year verificada. $count consulta(s) actualizadas.",
            'count' => $count,
        ]);
    }
}
