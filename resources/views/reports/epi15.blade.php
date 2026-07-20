<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Formulario EPI-15 - Consolidado Mensual de Morbilidad</title>
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

        /* Tablas del formulario EPI-15 */
        .epi15-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin: 5px 0;
            font-size: 7pt;
        }

        .epi15-table thead {
            background-color: #e0e0e0;
        }

        .epi15-table th {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: center;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .epi15-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: center;
            vertical-align: middle;
            font-size: 8pt;
        }

        .epi15-table td.text-left {
            text-align: left;
            padding-left: 8px;
            font-size: 7pt;
        }

        .epi15-table td.bold {
            font-weight: bold;
        }

        .epi15-table .section-header {
            background-color: #004080;
            color: white;
            font-weight: bold;
            padding: 4px 6px;
            font-size: 8pt;
            text-transform: uppercase;
            text-align: center;
        }

        .epi15-table .capitulo-header {
            background-color: #666;
            color: white;
            font-weight: bold;
            padding: 3px 6px;
            font-size: 7pt;
            text-align: left;
        }

        .epi15-table .total-row {
            background-color: #fffacd;
            font-weight: bold;
        }

        .epi15-table .grand-total-row {
            background-color: #90EE90;
            font-weight: bold;
            font-size: 8pt;
        }

        /* Columnas específicas para consolidado de sistemas */
        .col-codigo { min-width: 60px; }
        .col-enfermedad { min-width: 150px; text-align: left; }
        .col-cantidad { min-width: 60px; }
        .col-sistema { min-width: 140px; text-align: left; }
        .col-edad { min-width: 50px; }
        .col-total { min-width: 50px; background-color: #fffacd; }

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
    <!-- Cabecera del formulario EPI-15 -->
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
            <span class="header-form-number">EPI-15</span>
        </div>
        
        <div class="header-title">
            CONSOLIDADO MENSUAL DE MORBILIDAD POR ENFERMEDADES, APARATOS Y SISTEMAS
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
                    <span class="info-label">Período del Reporte:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($periodo . '-01')->format('F') }} de {{ \Carbon\Carbon::parse($periodo . '-01')->format('Y') }}</span>
                </div>
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

    <!-- SECCIÓN 1: Consolidado por Enfermedades (Capítulos CIE-10) -->
    <div style="margin: 10px 0;">
        <div class="epi15-table" style="font-size: 8pt; font-weight: bold; text-align: center; padding: 8px; background: #e0e0e0; border: 2px solid #000; margin-bottom: 5px;">
            SECCIÓN 1: CONSOLIDADO POR ENFERMEDADES (CIE-10)
        </div>
        
        <table class="epi15-table">
            <thead>
                <tr>
                    <th class="col-codigo">Capítulo</th>
                    <th class="col-enfermedad">Descripción del Capítulo</th>
                    <th class="col-cantidad">Total</th>
                </tr>
            </thead>
            <tbody>
                @if (empty($consolidadoEnfermedades))
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 20px; font-style: italic;">
                            No hay datos de morbilidad registrados para este período
                        </td>
                    </tr>
                @else
                    @foreach ($consolidadoEnfermedades as $codigo => $capitulo)
                        <tr class="capitulo-header">
                            <td>{{ $codigo }}</td>
                            <td>{{ $capitulo['nombre_capitulo'] }}</td>
                            <td>{{ $capitulo['total'] }}</td>
                        </tr>
                        
                        @foreach ($capitulo['enfermedades'] as $enfermedad)
                            <tr>
                                <td style="padding-left: 20px;">{{ $enfermedad['codigo'] }}</td>
                                <td class="text-left">{{ $enfermedad['nombre'] }}</td>
                                <td>{{ $enfermedad['cantidad'] }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- SECCIÓN 2: Consolidado por Aparatos y Sistemas -->
    <div style="margin: 10px 0;">
        <div class="epi15-table" style="font-size: 8pt; font-weight: bold; text-align: center; padding: 8px; background: #e0e0e0; border: 2px solid #000; margin-bottom: 5px;">
            SECCIÓN 2: CONSOLIDADO POR APARATOS Y SISTEMAS
        </div>
        
        <table class="epi15-table">
            <thead>
                <tr>
                    <th class="col-sistema">Sistema / Aparato</th>
                    <th class="col-edad"><5 años</th>
                    <th class="col-edad">5-14 años</th>
                    <th class="col-edad">15-49 años</th>
                    <th class="col-edad">50-64 años</th>
                    <th class="col-edad">65+ años</th>
                    <th class="col-total">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($consolidadoSistemas as $sistema => $datos)
                    <tr>
                        <td class="text-left">{{ $sistema }}</td>
                        <td>{{ $datos['menores_5_anos'] > 0 ? $datos['menores_5_anos'] : '' }}</td>
                        <td>{{ $datos['5_14_anos'] > 0 ? $datos['5_14_anos'] : '' }}</td>
                        <td>{{ $datos['15_49_anos'] > 0 ? $datos['15_49_anos'] : '' }}</td>
                        <td>{{ $datos['50_64_anos'] > 0 ? $datos['50_64_anos'] : '' }}</td>
                        <td>{{ $datos['65_mas_anos'] > 0 ? $datos['65_mas_anos'] : '' }}</td>
                        <td class="col-total">{{ $datos['total'] > 0 ? $datos['total'] : '' }}</td>
                    </tr>
                @endforeach
                
                <!-- Fila de totales generales -->
                <tr class="grand-total-row">
                    <td class="text-left bold">TOTAL GENERAL</td>
                    <td class="bold">{{ $totalesSistemas['menores_5_anos'] > 0 ? $totalesSistemas['menores_5_anos'] : '' }}</td>
                    <td class="bold">{{ $totalesSistemas['5_14_anos'] > 0 ? $totalesSistemas['5_14_anos'] : '' }}</td>
                    <td class="bold">{{ $totalesSistemas['15_49_anos'] > 0 ? $totalesSistemas['15_49_anos'] : '' }}</td>
                    <td class="bold">{{ $totalesSistemas['50_64_anos'] > 0 ? $totalesSistemas['50_64_anos'] : '' }}</td>
                    <td class="bold">{{ $totalesSistemas['65_mas_anos'] > 0 ? $totalesSistemas['65_mas_anos'] : '' }}</td>
                    <td class="bold">{{ $totalesSistemas['total'] > 0 ? $totalesSistemas['total'] : '' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pie de página -->
    <div class="footer">
        <div class="footer-logos">
            <div class="footer-logo">
                Formulario EPI-15 - Ministerio del Poder Popular para la Salud
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
