<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>EPI-15 - Consolidado Mensual de Morbilidad</title>
    <style>
        @page { size:legal landscape; margin:4mm; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial,Helvetica,sans-serif; font-size:7pt; line-height:1; color:#000; }
        table { width:100%; border-collapse:collapse; }
        td,th { border:1px solid #000; padding:1px 3px; text-align:center; vertical-align:middle; }
        .main-border { border:2px solid #000; margin-bottom:3px; }

        /* === HEADER SUPERIOR === */
        .hdr-top { display:flex; border-bottom:2px solid #000; }
        .hdr-logos { flex:1; padding:3px 5px; border-right:1px solid #000; display:flex; align-items:flex-start; gap:8px; }
        .hdr-logos .gobierno { font-size:7pt; margin-bottom:2px; }
        .hdr-logos .gobierno b { font-size:8pt; }
        .hdr-logos .ministerio { font-size:7.5pt; font-weight:bold; margin-bottom:4px; border-left:2px solid #000; padding-left:4px; }
        .hdr-logos .sis-line { display:flex; align-items:center; gap:4px; }
        .hdr-logos .sis-icon { background:#1a7a3a; color:#fff; font-weight:bold; font-size:9pt; padding:2px 4px; border-radius:3px; }
        .hdr-logos .sis-label { font-size:6pt; color:#555; }

        .hdr-org { padding:3px 6px; width:55%; }
        .hdr-org .line1 { font-weight:bold; font-size:8pt; }
        .hdr-org .line2 { font-weight:bold; font-size:7pt; }
        .hdr-org .line3 { font-weight:bold; font-size:7pt; }

        .hdr-form-code { display:flex; flex-direction:column; justify-content:flex-start; padding:4px 8px; width:auto; align-items:flex-end; }
        .hdr-form-code .code { font-size:10pt; font-weight:bold; }
        .hdr-form-code .mes-row, .hdr-form-code .anio-row { display:flex; align-items:center; gap:3px; margin-top:3px; }
        .hdr-form-code .lbl { font-weight:bold; font-size:8pt; }
        .hdr-form-code .field { border-bottom:1px solid #000; min-width:80px; padding:1px 3px; font-size:8pt; }

        /* === TITULO === */
        .title-bar { background:#000; color:#fff; font-weight:bold; text-align:center; padding:3px 5px; font-size:8pt; text-transform:uppercase; }

        /* === IDENTIFICACION === */
        .ident-section { border:2px solid #000; margin:0 0 3px 0; }
        .ident-title { font-weight:bold; font-size:7pt; padding:2px 5px; background:#e0e0e0; border-bottom:1px solid #000; }
        .ident-table { width:100%; }
        .ident-table td { border:none; padding:2px 4px; font-size:7pt; }
        .ident-label { font-weight:bold; white-space:nowrap; width:18%; }
        .ident-value { border-bottom:1px solid #000; width:32%; font-weight:bold; font-size:7pt; }

        /* === TABLA ENFERMEDADES === */
        .sec-header { background:#004080; color:#fff; font-weight:bold; font-size:7pt; text-align:left; padding-left:4px; }
        .sub-header { background:#666; color:#fff; font-weight:bold; font-size:6.5pt; text-align:left; padding-left:4px; }
        .grand-total { background:#90EE90; font-weight:bold; font-size:7pt; }
        .disease-row td:nth-child(1) { width:18px; font-size:6pt; }
        .disease-row td:nth-child(2) { text-align:left; padding-left:3px; font-size:6.5pt; }
        .disease-row td:nth-child(3),
        .disease-row td:nth-child(4),
        .disease-row td:nth-child(5),
        .disease-row td:nth-child(6),
        .disease-row td:nth-child(7) { width:30px; }

        /* === FOOTER === */
        .footer { margin-top:3px; border-top:1px solid #000; padding:2px 4px; font-size:5.5pt; }
        .footer-note { margin-bottom:1px; }
    </style>
</head>
<body>
    {{-- ===== HEADER SUPERIOR ===== --}}
    <div class="main-border">
        <div class="hdr-top">
            <div class="hdr-logos">
                @include('reports.partials.logo-mpps')
                <div class="sis-line">
                    <span class="sis-icon">SIS</span>
                    <span>
                        <b>Sistema de Información en Salud</b><br>
                        <span class="sis-label">Venezuela</span>
                    </span>
                </div>
            </div>
            <div class="hdr-org">
                <div class="line1">Viceministerio de Redes de Salud Colectiva</div>
                <div class="line2">Dirección General de Epidemiología</div>
                <div class="line3">Dirección de Vigilancia Epidemiológica</div>
            </div>
            <div class="hdr-form-code">
                <div class="code">SIS-04/EPI-15</div>
                <div class="mes-row">
                    <span class="lbl">MES:</span>
                    <span class="field">{{ \Carbon\Carbon::parse($periodo . '-01')->locale('es')->isoFormat('MMMM') }}</span>
                </div>
                <div class="anio-row">
                    <span class="lbl">AÑO:</span>
                    <span class="field">{{ \Carbon\Carbon::parse($periodo . '-01')->format('Y') }}</span>
                </div>
            </div>
        </div>

        <div class="title-bar">CONSOLIDADO MENSUAL MORBILIDAD REGISTRADA POR ENFERMEDADES, APARATOS Y SISTEMAS.</div>

        {{-- ===== IDENTIFICACION DEL ESTABLECIMIENTO ===== --}}
        <div class="ident-section">
            <div class="ident-title">Identificación del Establecimiento</div>
            <table class="ident-table">
                <tr>
                    <td class="ident-label">Entidad Federal:</td>
                    <td class="ident-value">ANZOATEGUI</td>
                    <td class="ident-label">Municipio:</td>
                    <td class="ident-value">GUANTA</td>
                </tr>
                <tr>
                    <td class="ident-label">Parroquia:</td>
                    <td class="ident-value">CHORRERON</td>
                    <td class="ident-label">Localidad:</td>
                    <td class="ident-value">CHORRERON</td>
                </tr>
                <tr>
                    <td class="ident-label">Nombre del Establecimiento:</td>
                    <td class="ident-value" colspan="3">ASIC-CHORRERON</td>
                </tr>
            </table>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th style="text-align:left;padding-left:4px;">ENFERMEDADES</th>
                <th>P</th>
                <th>S</th>
                <th>X</th>
                <th>P+X</th>
                <th>Acum.<br>año</th>
            </tr>
        </thead>
        <tbody>
            @php
                $runningP = 0; $runningS = 0; $runningX = 0; $runningAcum = 0;
            @endphp
            @foreach ($rows as $row)
                @php
                    if ($row['t'] === 'cap' && $row['nom'] === 'TOTAL CAUSAS DE CONSULTA') {
                        $totalRowP = $runningP; $totalRowS = $runningS; $totalRowX = $runningX;
                        $totalRowAcum = $runningAcum;
                    }
                @endphp

                @if ($row['t'] === 'cap')
                    <tr class="sec-header">
                        <td colspan="7">{{ $row['nom'] }}</td>
                    </tr>
                @elseif ($row['t'] === 'sub')
                    <tr class="sub-header">
                        <td colspan="7">{{ $row['nom'] }}</td>
                    </tr>
                @elseif ($row['t'] === 'enf' || $row['t'] === 'enf-3e')
                    @php
                        $n = $row['n'];
                        $p = $mesData[$n]['p'] ?? 0;
                        $s = $mesData[$n]['s'] ?? 0;
                        $x = $mesData[$n]['x'] ?? 0;
                        $total = $p + $s + $x;
                        $acum = ($anioData[$n]['p'] ?? 0) + ($anioData[$n]['s'] ?? 0) + ($anioData[$n]['x'] ?? 0);
                        $runningP += $p; $runningS += $s; $runningX += $x; $runningAcum += $acum;
                    @endphp
                    <tr class="disease-row">
                        <td>{{ $n }}</td>
                        <td>{{ $row['nom'] }}</td>
                        <td>{{ $p > 0 ? $p : '' }}</td>
                        <td>{{ $s > 0 ? $s : '' }}</td>
                        <td>{{ $x > 0 ? $x : '' }}</td>
                        <td>{{ $total > 0 ? $total : '' }}</td>
                        <td>{{ $acum > 0 ? $acum : '' }}</td>
                    </tr>
                @endif
            @endforeach

            <tr class="grand-total">
                <td colspan="2" style="text-align:right;padding-right:4px;">TOTAL CAUSAS DE CONSULTA</td>
                <td>{{ $totalP }}</td>
                <td>{{ $totalS }}</td>
                <td>{{ $totalX }}</td>
                <td>{{ $totalP + $totalS + $totalX }}</td>
                <td>{{ $totalAcum }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div class="footer-note">(*) TASAS POR 100.000 N.V.R.</div>
        <div class="footer-note">(**) TASAS ESPECIFICAS POR 100.000 MUJERES ENTRE 15 Y 49 AÑOS DE EDAD.</div>
        <div class="footer-note">(***) TASAS ESPECIFICAS POR 100.000 HABS. DEL CORRESPONDIENTE GRUPO DE EDAD.</div>
        <div class="footer-note">NOTA: LA TASA GENERAL DE MORBILIDAD REGISTRADA (TOTAL CAUSAS) ES POR 1.000 HABITANTES.</div>
        <div style="margin-top:2px;font-size:5pt;font-style:italic;">
            Formulario EPI-15 - MPPS. Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} - Consultorio Popular El Chaparro
        </div>
    </div>
</body>
</html>
