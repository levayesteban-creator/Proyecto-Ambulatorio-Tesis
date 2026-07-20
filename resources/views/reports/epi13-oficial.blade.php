<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>SIS-03 / EPI-13 - Registro de Enfermedades Notificables</title>
    <style>
        @page {
            size: 13in 8.5in landscape;
            margin: 4mm;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            line-height: 1;
            color: #000;
        }

        .main-container {
            width: 100%;
            border: 2px solid #000;
        }

        /* ===== HEADER ===== */
        .header {
            display: flex;
            border-bottom: 2px solid #000;
        }

        .header-left {
            width: 55%;
            padding: 4px 6px;
            border-right: 1px solid #000;
        }

        .header-left .title-main {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header-left .title-code {
            font-size: 11pt;
            font-weight: bold;
            text-align: right;
        }

        .header-right {
            width: 45%;
            padding: 4px 6px;
            font-size: 7pt;
        }

        .header-right table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-right td {
            padding: 1px 3px;
            font-size: 7pt;
            vertical-align: top;
        }

        .header-right .label {
            font-weight: bold;
            white-space: nowrap;
            width: 35%;
        }

        .header-right .value {
            border-bottom: 1px solid #000;
            width: 65%;
        }

        .info-line {
            display: flex;
            margin-bottom: 2px;
        }

        .info-line .label {
            font-weight: bold;
            min-width: 120px;
        }

        .info-line .value {
            border-bottom: 1px solid #000;
            flex: 1;
        }

        /* ===== DATA TABLE ===== */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
        }

        .data-table th {
            background-color: #e0e0e0;
            font-weight: bold;
            font-size: 7pt;
            white-space: nowrap;
        }

        .data-table td {
            font-size: 7pt;
        }

        .data-table td.text-left {
            text-align: left;
            padding-left: 4px;
        }

        .data-table td.col-num {
            width: 2%;
            font-weight: bold;
        }

        .data-table td.col-fecha {
            width: 7%;
        }

        .data-table td.col-name {
            width: 14%;
            text-align: left;
            padding-left: 4px;
        }

        .data-table td.col-ci {
            width: 7%;
        }

        .data-table td.col-gender {
            width: 3%;
        }

        .data-table td.col-dob {
            width: 8%;
        }

        .data-table td.col-edad {
            width: 3%;
        }

        .data-table td.col-address {
            width: 14%;
            text-align: left;
            font-size: 6pt;
            padding-left: 3px;
        }

        .data-table td.col-entidad {
            width: 6%;
        }

        .data-table td.col-municipio {
            width: 6%;
        }

        .data-table td.col-parroquia {
            width: 6%;
        }

        .data-table td.col-disease {
            width: 16%;
            text-align: left;
            padding-left: 4px;
            font-size: 6.5pt;
        }

        /* ===== FOOTER ===== */
        .footer {
            padding: 3px 5px;
            font-size: 6pt;
            text-align: center;
            border-top: 1px solid #000;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- ===== HEADER ===== -->
        <div class="header">
            <div class="header-left">
                @include('reports.partials.logo-mpps')
                <div class="title-main">REGISTRO DE ENFERMEDAD POR ENFERMEDADES NOTIFICABLES</div>
                <div class="title-code">SIS-03 / EPI-13</div>
            </div>
            <div class="header-right">
                <table>
                    <tr>
                        <td class="label">Entidad:</td>
                        <td class="value">{{ $entidad ?? 'ANZOÁTEGUI' }}</td>
                        <td class="label" style="width:20%;">Año:</td>
                        <td class="value" style="width:20%;">{{ $anio ?? date('Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Municipio:</td>
                        <td class="value">{{ $municipio ?? 'GUANTA' }}</td>
                        <td class="label">Semana:</td>
                        <td class="value">{{ $numeroSemana ?? '' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Parroquia:</td>
                        <td class="value">{{ $parroquia ?? 'CHORRERÓN' }}</td>
                        <td class="label">Localidad:</td>
                        <td class="value">{{ $localidad ?? 'EL CHAPARRO' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Establecimiento:</td>
                        <td class="value" colspan="3">{{ $establecimiento ?? 'CONSULTORIO POPULAR' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tipo:</td>
                        <td class="value">III</td>
                        <td class="label">Total Casos:</td>
                        <td class="value">{{ $totalCasos ?? 0 }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- ===== DATA TABLE ===== -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Fecha</th>
                    <th>Nombre y Apellido</th>
                    <th>C.I</th>
                    <th>Género</th>
                    <th>Fecha de Nacimiento</th>
                    <th>Edad</th>
                    <th>Dirección de Residencia</th>
                    <th>Entidad</th>
                    <th>Municipio</th>
                    <th>Parroquia</th>
                    <th>Enfermedad Notificable</th>
                </tr>
            </thead>
            <tbody>
                @php $contador = 1; @endphp
                @forelse ($casos as $caso)
                    <tr>
                        <td class="col-num">{{ $contador++ }}</td>
                        <td class="col-fecha">{{ $caso['fecha'] }}</td>
                        <td class="col-name">{{ $caso['nombre'] }}</td>
                        <td class="col-ci">{{ $caso['ci'] }}</td>
                        <td class="col-gender">{{ $caso['genero'] }}</td>
                        <td class="col-dob">{{ $caso['fecha_nac'] }}</td>
                        <td class="col-edad">{{ $caso['edad'] }}</td>
                        <td class="col-address">{{ $caso['direccion'] }}</td>
                        <td class="col-entidad">{{ $caso['entidad'] ?? 'ANZOÁTEGUI' }}</td>
                        <td class="col-municipio">{{ $caso['municipio'] ?? 'GUANTA' }}</td>
                        <td class="col-parroquia">{{ $caso['parroquia'] ?? 'CHORRERÓN' }}</td>
                        <td class="col-disease">{{ $caso['enfermedad'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" style="text-align:center; padding:10px; font-style:italic;">
                            No hay casos registrados para esta semana
                        </td>
                    </tr>
                @endforelse

                @for ($i = $contador; $i <= 16; $i++)
                    <tr>
                        <td class="col-num">{{ $i }}</td>
                        <td class="col-fecha">&nbsp;</td>
                        <td class="col-name">&nbsp;</td>
                        <td class="col-ci">&nbsp;</td>
                        <td class="col-gender">&nbsp;</td>
                        <td class="col-dob">&nbsp;</td>
                        <td class="col-edad">&nbsp;</td>
                        <td class="col-address">&nbsp;</td>
                        <td class="col-entidad">&nbsp;</td>
                        <td class="col-municipio">&nbsp;</td>
                        <td class="col-parroquia">&nbsp;</td>
                        <td class="col-disease">&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <!-- ===== FOOTER ===== -->
        <div class="footer">
            Formato SIS-03 / EPI-13 — Registro de Enfermedades de Notificación Obligatoria — Morbilidad
        </div>
    </div>
</body>
</html>
