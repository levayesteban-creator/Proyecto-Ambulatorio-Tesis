<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Formulario EPI-13 - Registro de Consulta Externa por Causa</title>
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

        /* Tabla principal del formulario EPI-13 */
        .epi13-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin: 5px 0;
            font-size: 7pt;
        }

        .epi13-table thead {
            background-color: #e0e0e0;
        }

        .epi13-table th {
            border: 1px solid #000;
            padding: 4px 8px;
            text-align: center;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .epi13-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: center;
            vertical-align: middle;
            font-size: 8pt;
        }

        .epi13-table td.no-border {
            border: none;
        }

        .epi13-table td.text-left {
            text-align: left;
            padding-left: 8px;
            font-size: 7pt;
        }

        .epi13-table td.bold {
            font-weight: bold;
        }

        .epi13-table .section-header {
            background-color: #004080;
            color: white;
            font-weight: bold;
            padding: 4px 6px;
            font-size: 7pt;
            text-transform: uppercase;
            text-align: center;
        }

        .epi13-table .total-row {
            background-color: #fffacd;
            font-weight: bold;
        }

        .epi13-table .grand-total-row {
            background-color: #90EE90;
            font-weight: bold;
            font-size: 8pt;
        }

        /* Columnas específicas */
        .epi13-table .col-categoria {
            min-width: 200px;
            text-align: left;
        }

        .epi13-table .col-primera {
            min-width: 100px;
            background-color: #f0f8ff;
        }

        .epi13-table .col-control {
            min-width: 100px;
            background-color: #f5f5dc;
        }

        .epi13-table .col-total {
            min-width: 80px;
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
    <!-- Cabecera del formulario EPI-13 -->
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
            <span class="header-form-number">EPI-13</span>
        </div>
        
        <div class="header-title">
            REGISTRO DE ENFERMEDADES DE NOTIFICACIÓN OBLIGATORIA
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

    <!-- Tabla principal EPI-13 -->
    <table class="epi13-table">
        <thead>
            <tr>
                <th class="col-categoria">Enfermedad Notificable</th>
                <th class="col-primera">Primera Vez</th>
                <th class="col-control">Repetidos</th>
                <th class="col-total">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($enfermedadesNotificables as $enfermedad => $datos)
                <tr>
                    <td class="text-left">{{ $enfermedad }}</td>
                    <td class="col-primera">{{ $datos['primera_vez'] > 0 ? $datos['primera_vez'] : '' }}</td>
                    <td class="col-control">{{ $datos['repetidos'] > 0 ? $datos['repetidos'] : '' }}</td>
                    <td class="col-total">{{ $datos['total'] > 0 ? $datos['total'] : '' }}</td>
                </tr>
            @endforeach
            
            <!-- Fila de totales generales -->
            <tr class="grand-total-row">
                <td class="text-left bold">TOTAL GENERAL</td>
                <td class="bold">{{ $totalesGenerales['primera_vez'] > 0 ? $totalesGenerales['primera_vez'] : '' }}</td>
                <td class="bold">{{ $totalesGenerales['repetidos'] > 0 ? $totalesGenerales['repetidos'] : '' }}</td>
                <td class="bold">{{ $totalesGenerales['total'] > 0 ? $totalesGenerales['total'] : '' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Pie de página -->
    <div class="footer">
        <div class="footer-logos">
            <div class="footer-logo">
                Formulario EPI-13 - Ministerio del Poder Popular para la Salud
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
