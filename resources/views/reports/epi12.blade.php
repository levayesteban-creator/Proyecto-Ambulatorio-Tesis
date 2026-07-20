<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Formulario EPI-12 - Registro de Morbilidad por Grupos de Edad y Sexo</title>
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
            padding: 5px 10px;
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

        /* Tabla principal del formulario EPI-12 */
        .epi12-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin: 5px 0;
            font-size: 7pt;
        }

        .epi12-table thead {
            background-color: #e0e0e0;
        }

        .epi12-table th {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: center;
            font-size: 6pt;
            font-weight: bold;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .epi12-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: center;
            vertical-align: middle;
            font-size: 7pt;
        }

        .epi12-table td.no-border {
            border: none;
        }

        .epi12-table td.text-left {
            text-align: left;
            padding-left: 6px;
            font-size: 6pt;
        }

        .epi12-table td.bold {
            font-weight: bold;
        }

        .epi12-table .section-header {
            background-color: #004080;
            color: white;
            font-weight: bold;
            padding: 4px 6px;
            font-size: 7pt;
            text-transform: uppercase;
            text-align: center;
        }

        .epi12-table .sexo-header {
            background-color: #666;
            color: white;
            font-weight: bold;
            padding: 4px;
            font-size: 7pt;
        }

        .epi12-table .edad-header {
            background-color: #999;
            color: white;
            font-weight: bold;
            padding: 3px;
            font-size: 6pt;
        }

        /* Columnas específicas */
        .epi12-table .col-diagnostico {
            min-width: 150px;
        }

        .epi12-table .col-codigo {
            min-width: 60px;
        }

        .epi12-table .col-total {
            background-color: #fffacd;
            font-weight: bold;
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
    <!-- Cabecera del formulario EPI-12 -->
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
            <span class="header-form-number">EPI-12</span>
        </div>
        
        <div class="header-title">
            CONSOLIDADO SEMANAL DE ENFERMEDADES Y EVENTOS DE NOTIFICACIÓN OBLIGATORIA MORBILIDAD
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
                    <span class="info-label">Semana del Reporte:</span>
                    <span class="info-value">Semana {{ substr($semana, 5, 2) }} de {{ substr($semana, 0, 4) }}</span>
                </div>
                <div>
                    <span class="info-label">Fecha Inicio:</span>
                    <span class="info-value">{{ $fechaInicio->format('d/m/Y') }}</span>
                </div>
                <div>
                    <span class="info-label">Fecha Fin:</span>
                    <span class="info-value">{{ $fechaFin->format('d/m/Y') }}</span>
                </div>
            </div>
            <div class="info-row">
                <div>
                    <span class="info-label">Total Consultas:</span>
                    <span class="info-value">{{ $totalConsultas }}</span>
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

    <!-- Tabla principal EPI-12 -->
    <table class="epi12-table">
        <thead>
            <tr>
                <th rowspan="3" class="col-codigo">Código</th>
                <th rowspan="3" class="col-diagnostico">Diagnóstico (CIE-10)</th>
                <th colspan="2" class="sexo-header">Menores de 1 año</th>
                <th colspan="2" class="sexo-header">1-4 años</th>
                <th colspan="2" class="sexo-header">5-14 años</th>
                <th colspan="2" class="sexo-header">15-49 años</th>
                <th colspan="2" class="sexo-header">50-64 años</th>
                <th colspan="2" class="sexo-header">65 años y más</th>
                <th rowspan="3" class="col-total">Total</th>
            </tr>
            <tr>
                <th class="edad-header">M</th>
                <th class="edad-header">F</th>
                <th class="edad-header">M</th>
                <th class="edad-header">F</th>
                <th class="edad-header">M</th>
                <th class="edad-header">F</th>
                <th class="edad-header">M</th>
                <th class="edad-header">F</th>
                <th class="edad-header">M</th>
                <th class="edad-header">F</th>
                <th class="edad-header">M</th>
                <th class="edad-header">F</th>
            </tr>
            <tr>
                <th class="edad-header">0-0</th>
                <th class="edad-header">0-0</th>
                <th class="edad-header">1-4</th>
                <th class="edad-header">1-4</th>
                <th class="edad-header">5-14</th>
                <th class="edad-header">5-14</th>
                <th class="edad-header">15-49</th>
                <th class="edad-header">15-49</th>
                <th class="edad-header">50-64</th>
                <th class="edad-header">50-64</th>
                <th class="edad-header">65+</th>
                <th class="edad-header">65+</th>
            </tr>
        </thead>
        <tbody>
            @if (empty($datosEpi12))
                <tr>
                    <td colspan="14" style="text-align: center; padding: 20px; font-style: italic;">
                        No hay datos de morbilidad registrados para este período
                    </td>
                </tr>
            @else
                @foreach ($datosEpi12 as $dato)
                    <tr>
                        <!-- Código CIE-10 -->
                        <td class="text-left bold">{{ $dato['codigo'] }}</td>
                        
                        <!-- Diagnóstico -->
                        <td class="text-left">{{ $dato['diagnostico'] }}</td>
                        
                        <!-- Menores de 1 año -->
                        <td>{{ $dato['grupos']['M_0_1'] > 0 ? $dato['grupos']['M_0_1'] : '' }}</td>
                        <td>{{ $dato['grupos']['F_0_1'] > 0 ? $dato['grupos']['F_0_1'] : '' }}</td>
                        
                        <!-- 1-4 años -->
                        <td>{{ $dato['grupos']['M_1_4'] > 0 ? $dato['grupos']['M_1_4'] : '' }}</td>
                        <td>{{ $dato['grupos']['F_1_4'] > 0 ? $dato['grupos']['F_1_4'] : '' }}</td>
                        
                        <!-- 5-14 años -->
                        <td>{{ $dato['grupos']['M_5_14'] > 0 ? $dato['grupos']['M_5_14'] : '' }}</td>
                        <td>{{ $dato['grupos']['F_5_14'] > 0 ? $dato['grupos']['F_5_14'] : '' }}</td>
                        
                        <!-- 15-49 años -->
                        <td>{{ $dato['grupos']['M_15_49'] > 0 ? $dato['grupos']['M_15_49'] : '' }}</td>
                        <td>{{ $dato['grupos']['F_15_49'] > 0 ? $dato['grupos']['F_15_49'] : '' }}</td>
                        
                        <!-- 50-64 años -->
                        <td>{{ $dato['grupos']['M_50_64'] > 0 ? $dato['grupos']['M_50_64'] : '' }}</td>
                        <td>{{ $dato['grupos']['F_50_64'] > 0 ? $dato['grupos']['F_50_64'] : '' }}</td>
                        
                        <!-- 65 años y más -->
                        <td>{{ $dato['grupos']['M_65_plus'] > 0 ? $dato['grupos']['M_65_plus'] : '' }}</td>
                        <td>{{ $dato['grupos']['F_65_plus'] > 0 ? $dato['grupos']['F_65_plus'] : '' }}</td>
                        
                        <!-- Total por diagnóstico -->
                        <td class="col-total">{{ $dato['total'] > 0 ? $dato['total'] : '' }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <!-- Pie de página -->
    <div class="footer">
        <div class="footer-logos">
            <div class="footer-logo">
                Formulario EPI-12 - Ministerio del Poder Popular para la Salud
            </div>
            <div class="footer-logo">
                Consultorio Popular El Chaparro - El Chaparro, Estado Anzoátegui
            </div>
        </div>
        <div style="margin-top: 10px; font-size: 7pt; font-style: italic;">
            Este reporte estadístico es generado automáticamente según normas del MPPS
        </div>
    </div>
</body>
</html>
