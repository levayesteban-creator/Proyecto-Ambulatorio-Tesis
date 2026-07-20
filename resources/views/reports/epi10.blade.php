<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Formulario EPI-10 - Registro Diario de Consulta</title>
    <style>
        @page {
            size: landscape;
            margin: 8mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            line-height: 1.1;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* Cabecera oficial del formulario */
        .header-section {
            border: 2px solid #000;
            margin-bottom: 5px;
        }

        .header-top {
            text-align: center;
            padding: 8px;
            background: #fff;
            border-bottom: 1px solid #000;
        }

        .header-minister {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.3;
        }

        .header-ministry {
            font-size: 10pt;
            font-weight: bold;
        }

        .header-form-number {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            padding: 5px;
            border: 1px solid #000;
            margin: 5px 10px;
            display: inline-block;
            min-width: 80px;
        }

        .header-title {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            padding: 5px 10px;
            background: #333;
            color: #fff;
        }

        .header-info-section {
            border: 1px solid #000;
            margin: 5px;
            padding: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .info-label {
            font-weight: bold;
            font-size: 8pt;
        }

        .info-value {
            font-size: 8pt;
        }

        /* Tabla principal del formulario */
        .epi10-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin: 5px 0;
            font-size: 7pt;
        }

        .epi10-table thead tr {
            background-color: #e0e0e0;
        }

        .epi10-table th {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: center;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .epi10-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: center;
            vertical-align: middle;
            font-size: 7pt;
        }

        .epi10-table td.no-border {
            border: none;
        }

        .epi10-table td.text-left {
            text-align: left;
            padding-left: 6px;
        }

        .epi10-table td.text-center {
            text-align: center;
        }

        .epi10-table td.bold {
            font-weight: bold;
        }

        .epi10-table td.small-text {
            font-size: 6pt;
        }

        .epi10-table input {
            border: none;
            border-bottom: 1px solid #000;
            font-size: 8pt;
            text-align: center;
            width: 100%;
        }

        .epi10-table textarea {
            border: 1px solid #000;
            font-size: 7pt;
            width: 100%;
            min-height: 30px;
        }

        .epi10-table select {
            border: 1px solid #000;
            font-size: 7pt;
            padding: 2px;
        }

        .epi10-table .section-header {
            background-color: #004080;
            color: white;
            font-weight: bold;
            padding: 4px 6px;
            font-size: 8pt;
            text-transform: uppercase;
            text-align: center;
        }

        /* Pie de página */
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 8pt;
        }

        .footer-logos {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .footer-logo {
            font-size: 7pt;
        }
    </style>
</head>
<body>
    <!-- Cabecera del formulario EPI-10 -->
    <div class="header-section">
        <div class="header-top">
            <div class="header-minister">
                República Bolivariana de Venezuela
            </div>
            <div class="header-ministry">
                Ministerio del Poder Popular para la Salud
            </div>
        </div>
        
        <div style="text-align: center; padding: 5px;">
            <span class="header-form-number">EPI-10</span>
        </div>
        
        <div class="header-title">
            REGISTRO DIARIO DE ATENCIÓN INTEGRAL DEL ESTABLECIMIENTO DE SALUD
        </div>
        
        <div class="header-info-section">
            <div class="info-row">
                <div>
                    <span class="info-label">Fecha de Impresión:</span>
                    <span class="info-value">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</span>
                </div>
                <div>
                    <span class="info-label">Hora de Impresión:</span>
                    <span class="info-value">{{ \Carbon\Carbon::now()->format('H:i') }}</span>
                </div>
            </div>
            <div class="info-row">
                <div>
                    <span class="info-label">Fecha del Reporte:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</span>
                </div>
                <div>
                    <span class="info-label">Total Consultas:</span>
                    <span class="info-value">{{ $consultations->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Información del establecimiento -->
    <div style="border: 2px solid #000; padding: 10px; margin: 5px 0; font-size: 8pt;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="border: 1px solid #000; padding: 5px;" class="info-label">Nombre del Establecimiento:</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold;">Consultorio Popular El Chaparro</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px;" class="info-label">Dirección:</td>
                <td style="border: 1px solid #000; padding: 5px;">El Chaparro, Guanta, Estado Anzoátegui</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px;" class="info-label">Código RSA:</td>
                <td style="border: 1px solid #000; padding: 5px;">_____________________</td>
            </tr>
        </table>
    </div>

    <!-- Tabla principal de consultas -->
    <table class="epi10-table">
        <thead>
            <tr>
                <th style="width: 3%;">N°</th>
                <th style="width: 10%;">Cédula</th>
                <th style="width: 15%;">Nombre y Apellido</th>
                <th style="width: 4%;">Edad</th>
                <th style="width: 4%;">Sexo</th>
                <th style="width: 7%;">Fecha</th>
                <th style="width: 5%;">Hora</th>
                <th style="width: 6%;">Tipo</th>
                <th style="width: 10%;">Diagnóstico (CIE-10)</th>
                <th style="width: 8%;">Conducta</th>
                <th style="width: 10%;">Signos Vitales</th>
                <th style="width: 12%;">Examen Físico</th>
                <th style="width: 10%;">Tratamiento</th>
                <th style="width: 6%;">Evolución</th>
                <th style="width:6%;">Referencia</th>
                <th style="width: 8%;">Médico</th>
                <th style="width: 5%;">Firma</th>
            </tr>
        </thead>
        <tbody>
            @php $contador = 1; @endphp
            @foreach ($consultations as $consultation)
                <tr>
                    <!-- N° -->
                    <td>{{ $contador++ }}</td>

                    <!-- Cédula -->
                    <td class="text-left">
                        {{ $consultation->patient->nationality ?? 'V' }}-{{ $consultation->patient->id_number }}
                    </td>

                    <!-- Nombre y Apellido -->
                    <td class="text-left small-text">
                        {{ $consultation->patient->full_name }}
                    </td>

                    <!-- Edad -->
                    <td>
                        @if ($consultation->patient->birth_date)
                            {{ \Carbon\Carbon::parse($consultation->patient->birth_date)->age }}
                        @else
                            —
                        @endif
                    </td>

                    <!-- Sexo -->
                    <td>
                        @if ($consultation->patient->gender === 'M')
                            M
                        @elseif ($consultation->patient->gender === 'F')
                            F
                        @else
                            O
                        @endif
                    </td>

                    <!-- Fecha -->
                    <td>{{ \Carbon\Carbon::parse($consultation->created_at)->format('d/m/Y') }}</td>

                    <!-- Hora -->
                    <td>{{ \Carbon\Carbon::parse($consultation->created_at)->format('H:i') }}</td>

                    <!-- Tipo -->
                    <td>
                        @if ($consultation->is_healthy ?? false)
                            C
                        @else
                            M
                        @endif
                    </td>

                    <!-- Diagnóstico CIE-10 -->
                    <td class="text-left small-text">
                        @if ($consultation->sisDiagnoses && $consultation->sisDiagnoses->isNotEmpty())
                            {{ $consultation->sisDiagnoses->pluck('sisDiagnosis.code')->join(', ') }}
                        @else
                            —
                        @endif
                    </td>

                    <!-- Conducta -->
                    <td class="text-left small-text">
                        @if ($consultation->sisDiagnoses && $consultation->sisDiagnoses->isNotEmpty())
                            {{ $consultation->sisDiagnoses->pluck('medicalConduct.name')->join(', ') }}
                        @else
                            —
                        @endif
                    </td>

                    <!-- Signos Vitales -->
                    <td class="text-left small-text">
                        @if ($consultation->physicalExam)
                            PA: {{ $consultation->physicalExam->blood_pressure_systolic ?? '—' }}/
                            {{ $consultation->physicalExam->blood_pressure_diastolic ?? '—' }}
                            FC: {{ $consultation->physicalExam->heart_rate ?? '—' }}
                            FR: {{ $consultation->physicalExam->respiratory_rate ?? '—' }}
                            T: {{ $consultation->physicalExam->temperature ?? '—' }}°C
                        @else
                            —
                        @endif
                    </td>

                    <!-- Examen Físico -->
                    <td class="text-left small-text">
                        @if ($consultation->physicalExam)
                            {{ $consultation->physicalExam->general_exam ?? '—' }}
                        @else
                            —
                        @endif
                    </td>

                    <!-- Tratamiento -->
                    <td class="text-left small-text">
                        {{ $consultation->treatment ?? '—' }}
                    </td>

                    <!-- Evolución -->
                    <td class="text-left small-text">
                        @if ($consultation->evolution)
                            {{ $consultation->evolution }}
                        @else
                            —
                        @endif
                    </td>

                    <!-- Referencia -->
                    <td class="text-left small-text">
                        @if ($consultation->referral)
                            {{ $consultation->referral }}
                        @else
                            —
                        @endif
                    </td>

                    <!-- Médico -->
                    <td class="small-text">
                        {{ $consultation->doctor->name ?? '—' }}
                    </td>

                    <!-- Firma -->
                    <td style="height: 50px;">&nbsp;</td>
                </tr>
            @endforeach
            
            @if ($consultations->isEmpty())
                <tr>
                    <td colspan="17" style="text-align: center; padding: 20px; font-style: italic;">
                        No hay consultas registradas para esta fecha
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Pie de página -->
    <div class="footer">
        <div class="footer-logos">
            <div class="footer-logo">
                Formulario EPI-10 - Ministerio del Poder Popular para la Salud
            </div>
            <div class="footer-logo">
                Consultorio Popular El Chaparro - El Chaparro, Estado Anzoátegui
            </div>
        </div>
    </div>
</body>
</html>
