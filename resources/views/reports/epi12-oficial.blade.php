<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>SIS-04 / EPI-12 - Consolidado Semanal de Morbilidad</title>
    <style>
        @page {
            size: 14in 8.5in landscape;
            margin: 4mm;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 6pt;
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
            width: 20%;
            padding: 3px 5px;
            border-right: 1px solid #000;
        }

        .header-left .gov-name {
            font-size: 7pt;
            font-weight: bold;
        }

        .header-left .gov-sub {
            font-size: 5pt;
        }

        .header-center {
            width: 50%;
            text-align: center;
            padding: 3px 5px;
        }

        .header-center .form-title {
            font-size: 10pt;
            font-weight: bold;
        }

        .header-center .form-subtitle {
            font-size: 8pt;
            font-weight: bold;
        }

        .header-center .form-page {
            font-size: 7pt;
        }

        .header-right {
            width: 30%;
            padding: 3px 5px;
            font-size: 6pt;
        }

        .header-right table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-right td {
            padding: 1px 3px;
            font-size: 6pt;
        }

        .header-right .label {
            font-weight: bold;
            white-space: nowrap;
        }

        .header-right .value {
            border-bottom: 1px solid #000;
            font-weight: bold;
        }

        /* ===== DATA TABLE ===== */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 1px 2px;
            text-align: center;
            vertical-align: middle;
            font-size: 5.5pt;
        }

        .data-table thead tr:first-child {
            background-color: #e0e0e0;
            font-weight: bold;
        }

        .data-table thead tr:nth-child(2) {
            background-color: #e8e8e8;
            font-weight: bold;
        }

        .data-table th {
            font-weight: bold;
            white-space: nowrap;
        }

        .data-table td.text-left {
            text-align: left;
            padding-left: 3px;
        }

        .data-table td.orden {
            width: 2%;
            text-align: center;
            font-weight: bold;
        }

        .data-table td.enfermedad {
            width: 18%;
            text-align: left;
            padding-left: 3px;
            font-size: 5.5pt;
        }

        .data-table td.col-data {
            width: 2%;
            min-width: 14px;
        }

        .data-table td.col-total {
            background-color: #fffacd;
            font-weight: bold;
            width: 2.5%;
        }

        .data-table td.col-total-gen {
            background-color: #ffe4b5;
            font-weight: bold;
            width: 2.5%;
        }

        .data-table td.empty-row {
            height: 10px;
        }

        /* ===== FOOTER ===== */
        .footer {
            padding: 3px 5px;
            font-size: 5pt;
            text-align: center;
            border-top: 1px solid #000;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- ===== HEADER ===== -->
        <div class="header">
            <div class="header-left">
                @include('reports.partials.logo-mpps')
                <div class="gov-sub">Dirección General de Epidemiología</div>
                <div class="gov-sub">Sistema de Información Epidemiológica Nacional</div>
                <div class="gov-sub">Dirección de Vigilancia Epidemiológica</div>
            </div>
            <div class="header-center">
                <div class="form-title">Formato SIS - 04 / EPI - 12</div>
                <div class="form-subtitle">Consolidado Semanal de Enfermedades y Eventos de Notificación Obligatoria</div>
                <div style="font-weight: bold;">Morbilidad</div>
                <div class="form-page">Página 1 de 1</div>
            </div>
            <div class="header-right">
                <table>
                    <tr>
                        <td class="label">Entidad:</td>
                        <td class="value">{{ $entidad ?? 'ANZOÁTEGUI' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Municipio:</td>
                        <td class="value">{{ $municipio ?? 'GUANTA' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Establecimiento:</td>
                        <td class="value">{{ $establecimiento ?? 'CPT III CHAPARRO DE GUANTA' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Año:</td>
                        <td class="value">{{ $anio ?? date('Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Semana:</td>
                        <td class="value">{{ $numeroSemana ?? '' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- ===== DATA TABLE ===== -->
        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width:2%;">Orden</th>
                    <th rowspan="2" style="width:18%;">Enfermedad / Evento</th>
                    <th colspan="2">&lt; 1 año</th>
                    <th colspan="2">1 a 4 años</th>
                    <th colspan="2">5 a 6 años</th>
                    <th colspan="2">7 a 9 años</th>
                    <th colspan="2">10 a 11 años</th>
                    <th colspan="2">12 a 14 años</th>
                    <th colspan="2">15 a 19 años</th>
                    <th colspan="2">20 a 24 años</th>
                    <th colspan="2">25 a 44 años</th>
                    <th colspan="2">45 a 59 años</th>
                    <th colspan="2">60 a 64 años</th>
                    <th colspan="2">65 años y más</th>
                    <th colspan="2">Edad ignorada</th>
                    <th colspan="2">Total</th>
                    <th rowspan="2">Total General</th>
                </tr>
                <tr>
                    <th>H</th><th>M</th>
                    <th>H</th><th>M</th>
                    <th>H</th><th>M</th>
                    <th>H</th><th>M</th>
                    <th>H</th><th>M</th>
                    <th>H</th><th>M</th>
                    <th>H</th><th>M</th>
                    <th>H</th><th>M</th>
                    <th>H</th><th>M</th>
                    <th>H</th><th>M</th>
                    <th>H</th><th>M</th>
                    <th>H</th><th>M</th>
                    <th>H</th><th>M</th>
                    <th>Hombres</th><th>Mujeres</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $enfermedades = [
                        ['orden' => 1,  'nombre' => 'Cólera', 'codigo' => 'A00'],
                        ['orden' => 2,  'nombre' => 'Diarreas', 'codigo' => 'A08-A09'],
                        ['orden' => 3,  'nombre' => 'Amibiasis', 'codigo' => 'A06'],
                        ['orden' => 4,  'nombre' => 'Fiebre Tifoidea', 'codigo' => 'A01.0'],
                        ['orden' => 5,  'nombre' => 'ETA N° de Brotes', 'codigo' => ''],
                        ['orden' => 6,  'nombre' => 'Casos Asociados a Brotes de ETA', 'codigo' => ''],
                        ['orden' => 7,  'nombre' => 'Hepatitis Aguda Tipo A', 'codigo' => 'B15'],
                        ['orden' => 8,  'nombre' => 'Tuberculosis', 'codigo' => 'A15-A19'],
                        ['orden' => 9,  'nombre' => 'Influenza / Enfermedad Tipo Influenza', 'codigo' => 'J10-J11'],
                        ['orden' => 10, 'nombre' => 'Sífilis Congénita', 'codigo' => 'A50'],
                        ['orden' => 11, 'nombre' => 'Infección Asintomática VIH', 'codigo' => 'Z21'],
                        ['orden' => 12, 'nombre' => 'Enfermedad VIH/SIDA', 'codigo' => 'B20-B24'],
                        ['orden' => 13, 'nombre' => 'Tosferina', 'codigo' => 'A37'],
                        ['orden' => 14, 'nombre' => 'Parotiditis', 'codigo' => 'B26'],
                        ['orden' => 15, 'nombre' => 'Tétanos Neonatal', 'codigo' => 'A33'],
                        ['orden' => 16, 'nombre' => 'Tétanos Obstétrico', 'codigo' => 'A34'],
                        ['orden' => 17, 'nombre' => 'Tétanos (otros)', 'codigo' => 'A35'],
                        ['orden' => 18, 'nombre' => 'Difteria', 'codigo' => 'A36'],
                        ['orden' => 19, 'nombre' => 'Sarampión Sospecha', 'codigo' => 'B05'],
                        ['orden' => 20, 'nombre' => 'Rubéola', 'codigo' => 'B06'],
                        ['orden' => 21, 'nombre' => 'Dengue sin Signo de Alarma', 'codigo' => ''],
                        ['orden' => 22, 'nombre' => 'Dengue con Signo de Alarma', 'codigo' => ''],
                        ['orden' => 23, 'nombre' => 'Dengue Grave', 'codigo' => 'A91'],
                        ['orden' => 24, 'nombre' => 'Chikungunya', 'codigo' => ''],
                        ['orden' => 25, 'nombre' => 'Zika', 'codigo' => 'U08'],
                        ['orden' => 26, 'nombre' => 'Encefalitis Equina Venezolana', 'codigo' => 'A92.2'],
                        ['orden' => 27, 'nombre' => 'Fiebre Amarilla', 'codigo' => 'A95'],
                        ['orden' => 28, 'nombre' => 'Leishmaniasis Visceral', 'codigo' => 'B55.0'],
                        ['orden' => 29, 'nombre' => 'Leishmaniasis Cutánea', 'codigo' => 'B55.1'],
                        ['orden' => 30, 'nombre' => 'Leishmaniasis Mucocutánea', 'codigo' => 'B55.2'],
                        ['orden' => 31, 'nombre' => 'Leishmaniasis no Específica', 'codigo' => 'B55.9'],
                        ['orden' => 32, 'nombre' => 'Enfermedad de Chagas Aguda', 'codigo' => 'B57.0-B57.1'],
                        ['orden' => 33, 'nombre' => 'Enfermedad de Chagas Crónica', 'codigo' => 'B57.2-B57.5'],
                        ['orden' => 34, 'nombre' => 'Rabia Humana', 'codigo' => 'A82'],
                        ['orden' => 35, 'nombre' => 'Fiebre Hemorrágica Venezolana', 'codigo' => 'A96.8'],
                        ['orden' => 36, 'nombre' => 'Leptospirosis', 'codigo' => 'A27'],
                        ['orden' => 37, 'nombre' => 'Meningitis Viral', 'codigo' => 'A87'],
                        ['orden' => 38, 'nombre' => 'Meningitis Bacteriana', 'codigo' => 'G00'],
                        ['orden' => 39, 'nombre' => 'Enfermedad Meningococcica', 'codigo' => 'A39.0'],
                        ['orden' => 40, 'nombre' => 'Enfermedad Meningococcica', 'codigo' => 'A39.9'],
                        ['orden' => 41, 'nombre' => 'Varicela', 'codigo' => 'B01'],
                        ['orden' => 42, 'nombre' => 'Hepatitis Aguda Tipo B', 'codigo' => 'B16'],
                        ['orden' => 43, 'nombre' => 'Hepatitis Aguda Tipo C', 'codigo' => 'B17.1, B18.2'],
                        ['orden' => 44, 'nombre' => 'Hepatitis Otras Agudas', 'codigo' => 'B17'],
                        ['orden' => 45, 'nombre' => 'Hepatitis No Específicas', 'codigo' => 'B19'],
                        ['orden' => 46, 'nombre' => 'Parálisis Flácida < 15 años', 'codigo' => 'G82.0'],
                        ['orden' => 47, 'nombre' => 'Neumonías', 'codigo' => 'J12-J18'],
                        ['orden' => 48, 'nombre' => 'Intoxicación por Plaguicidas', 'codigo' => 'T60'],
                        ['orden' => 49, 'nombre' => 'Mordedura Sospechosa de Rabia', 'codigo' => 'A82'],
                        ['orden' => 50, 'nombre' => 'Fiebre', 'codigo' => 'R50'],
                        ['orden' => 51, 'nombre' => 'Efectos Adversos de Medicamentos', 'codigo' => 'Y40-Y57'],
                        ['orden' => 52, 'nombre' => 'Efectos Adversos de Vacunas', 'codigo' => 'Y58-Y59'],
                    ];

                    // Crear índice rápido de datos del controlador
                    $datosIndex = [];
                    if (!empty($datosEpi12)) {
                        foreach ($datosEpi12 as $dato) {
                            $datosIndex[$dato['codigo']] = $dato;
                        }
                    }
                @endphp

                @foreach ($enfermedades as $enf)
                    @php
                        $dato = $datosIndex[$enf['codigo']] ?? null;
                        $g = $dato['grupos'] ?? [];
                    @endphp
                    <tr>
                        <td class="orden">{{ $enf['orden'] }}</td>
                        <td class="enfermedad">{{ $enf['nombre'] }} {{ $enf['codigo'] ? '(' . $enf['codigo'] . ')' : '' }}</td>
                        {{-- < 1 año --}}
                        <td class="col-data">{{ ($g['lt1_H'] ?? 0) ?: '' }}</td>
                        <td class="col-data">{{ ($g['lt1_M'] ?? 0) ?: '' }}</td>
                        {{-- 1 a 4 años --}}
                        <td class="col-data">{{ ($g['1_4_H'] ?? 0) ?: '' }}</td>
                        <td class="col-data">{{ ($g['1_4_M'] ?? 0) ?: '' }}</td>
                        {{-- 5 a 6 años --}}
                        <td class="col-data">{{ ($g['5_6_H'] ?? 0) ?: '' }}</td>
                        <td class="col-data">{{ ($g['5_6_M'] ?? 0) ?: '' }}</td>
                        {{-- 7 a 9 años --}}
                        <td class="col-data">{{ ($g['7_9_H'] ?? 0) ?: '' }}</td>
                        <td class="col-data">{{ ($g['7_9_M'] ?? 0) ?: '' }}</td>
                        {{-- 10 a 11 años --}}
                        <td class="col-data">{{ ($g['10_11_H'] ?? 0) ?: '' }}</td>
                        <td class="col-data">{{ ($g['10_11_M'] ?? 0) ?: '' }}</td>
                        {{-- 12 a 14 años --}}
                        <td class="col-data">{{ ($g['12_14_H'] ?? 0) ?: '' }}</td>
                        <td class="col-data">{{ ($g['12_14_M'] ?? 0) ?: '' }}</td>
                        {{-- 15 a 19 años --}}
                        <td class="col-data">{{ ($g['15_19_H'] ?? 0) ?: '' }}</td>
                        <td class="col-data">{{ ($g['15_19_M'] ?? 0) ?: '' }}</td>
                        {{-- 20 a 24 años --}}
                        <td class="col-data">{{ ($g['20_24_H'] ?? 0) ?: '' }}</td>
                        <td class="col-data">{{ ($g['20_24_M'] ?? 0) ?: '' }}</td>
                        {{-- 25 a 44 años --}}
                        <td class="col-data">{{ ($g['25_44_H'] ?? 0) ?: '' }}</td>
                        <td class="col-data">{{ ($g['25_44_M'] ?? 0) ?: '' }}</td>
                        {{-- 45 a 59 años --}}
                        <td class="col-data">{{ ($g['45_59_H'] ?? 0) ?: '' }}</td>
                        <td class="col-data">{{ ($g['45_59_M'] ?? 0) ?: '' }}</td>
                        {{-- 60 a 64 años --}}
                        <td class="col-data">{{ ($g['60_64_H'] ?? 0) ?: '' }}</td>
                        <td class="col-data">{{ ($g['60_64_M'] ?? 0) ?: '' }}</td>
                        {{-- 65 años y más --}}
                        <td class="col-data">{{ ($g['65plus_H'] ?? 0) ?: '' }}</td>
                        <td class="col-data">{{ ($g['65plus_M'] ?? 0) ?: '' }}</td>
                        {{-- Edad ignorada --}}
                        <td class="col-data">{{ ($g['ignorado_H'] ?? 0) ?: '' }}</td>
                        <td class="col-data">{{ ($g['ignorado_M'] ?? 0) ?: '' }}</td>
                        {{-- Total --}}
                        <td class="col-total">{{ ($dato['total_hombres'] ?? 0) ?: '' }}</td>
                        <td class="col-total">{{ ($dato['total_mujeres'] ?? 0) ?: '' }}</td>
                        <td class="col-total-gen">{{ ($dato['total'] ?? 0) ?: '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- ===== FOOTER ===== -->
        <div class="footer">
            Formato SIS-04 / EPI-12 — Consolidado Semanal de Enfermedades y Eventos de Notificación Obligatoria — Morbilidad
        </div>
    </div>
</body>
</html>
