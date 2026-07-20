<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>SIS 02 EPI 10 - Registro Diario de Atención Integral del Establecimiento de Salud</title>
    <style>
        @page { size: 13in 8.5in landscape; margin: 3mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 6pt; line-height: 1; color: #000; }
        table { border-collapse: collapse; }
        td, th { border: 1px solid #000; padding: 1px 2px; vertical-align: top; }
        .main { width: 100%; border: 2px solid #000; }

        /* === SECTION 1: Header === */
        .hdr-left { width: 14%; text-align: center; vertical-align: middle; padding: 3px; border-right: 2px solid #000; }
        .hdr-left .sis-title { font-size: 13pt; font-weight: bold; margin-top: 4px; letter-spacing: 1px; }
        .hdr-left .sis-sub { font-size: 7pt; font-weight: bold; }
        .hdr-left .min-text { font-size: 6pt; font-style: italic; line-height: 1.1; }
        .ref-box { border: 1px solid #000; padding: 1px 2px; font-size: 5pt; vertical-align: top; }
        .ref-title { font-weight: bold; font-size: 5.5pt; text-transform: uppercase; border-bottom: 1px solid #000; margin-bottom: 1px; padding-bottom: 1px; }
        .ref-item { white-space: nowrap; line-height: 1.15; }

        /* === SECTION 2: Form fields + Column headers === */
        .form-cell { width: 14%; padding: 2px 3px; border-right: 2px solid #000; vertical-align: top; font-size: 5.5pt; }
        .form-label { font-weight: bold; font-size: 5.5pt; }
        .form-value { border-bottom: 1px solid #000; min-height: 8px; margin-top: 1px; font-size: 5.5pt; }
        .col-num { text-align: center; font-weight: bold; font-size: 5.5pt; border-right: 1px solid #000; padding: 1px; }
        .col-hdr { border-right: 1px solid #000; font-size: 4.5pt; font-weight: bold; text-align: center; padding: 2px 1px; writing-mode: vertical-lr; white-space: nowrap; height: 55px; vertical-align: middle; }
        .col-hdr-h { border-right: 1px solid #000; font-size: 5pt; font-weight: bold; text-align: center; padding: 2px 1px; vertical-align: middle; }

        /* === SECTION 3: Data table === */
        .title-row { background: #e0e0e0; font-weight: bold; font-size: 7pt; text-transform: uppercase; text-align: center; padding: 2px 5px; border: 1px solid #000; }
        .data-td { border: 1px solid #000; padding: 1px 2px; text-align: center; vertical-align: middle; font-size: 5.5pt; }
        .data-td.tl { text-align: left; padding-left: 2px; }
        .data-row td { height: 12px; }

        /* === Footer === */
        .footer { border-top: 2px solid #000; padding: 2px 4px; font-size: 5.5pt; text-align: center; font-style: italic; }
    </style>
</head>
<body>
<table class="main" cellpadding="0" cellspacing="0">
<tr>
    {{-- ======================= SECTION 1: HEADER ======================= --}}
    <td colspan="25" style="padding:0; border: 1px solid #000;">
        <table width="100%" cellpadding="0" cellspacing="0" style="border:none;">
        <tr>
            {{-- LEFT: Logo + SIS --}}
            <td class="hdr-left">
                @include('reports.partials.logo-mpps')
                <div class="min-text">Ministerio del Poder Popular<br>para la Salud</div>
                <div class="sis-sub">Sistema de<br>Información de<br>Salud</div>
                <div class="sis-title">SIS 02 EPI 10</div>
            </td>
            {{-- RIGHT: Reference Tables --}}
            <td style="padding:0; border:none; vertical-align:top;">
                <table width="100%" cellpadding="0" cellspacing="0" style="border:none;">
                <tr>
                    {{-- Nivel Educativo --}}
                    <td class="ref-box" width="9%">
                        <div class="ref-title">Nivel Educativo</div>
                        <div class="ref-item">1. Inicial</div>
                        <div class="ref-item">2. Primaria</div>
                        <div class="ref-item">3. Secundaria</div>
                        <div class="ref-item">4. Técnico Medio</div>
                        <div class="ref-item">5. Educativo Especial</div>
                        <div class="ref-item">6. Universitario</div>
                        <div class="ref-item">7. Ninguno</div>
                    </td>
                    {{-- Lugar de Atención --}}
                    <td class="ref-box" width="11%">
                        <div class="ref-title">Lugar de Atención</div>
                        <div class="ref-item">1. Consultorio</div>
                        <div class="ref-item">2. Salud Escolar (Escuela)</div>
                        <div class="ref-item">3. Consultorio Comunal (Trabajo)</div>
                        <div class="ref-item">4. Círculo de Embarazadas</div>
                        <div class="ref-item">5. Círculo de Abuelos</div>
                        <div class="ref-item">6. Círculo Materno Infantil</div>
                        <div class="ref-item">7. Otros</div>
                    </td>
                    {{-- Pueblo Indígena / Etnias --}}
                    <td class="ref-box" width="22%">
                        <div class="ref-title">Pueblo Indígena / Etnias</div>
                        <table width="100%" cellpadding="0" cellspacing="0" style="border:none;">
                        <tr>
                            <td style="border:none; padding:0; vertical-align:top; width:50%;">
                                <div class="ref-item">1. Akawayo</div>
                                <div class="ref-item">2. Amorua</div>
                                <div class="ref-item">3. Añú (Paraujano)</div>
                                <div class="ref-item">4. Arawako</div>
                                <div class="ref-item">5. Ayaman</div>
                                <div class="ref-item">6. Baniva</div>
                                <div class="ref-item">7. Baré (Bale)</div>
                                <div class="ref-item">8. Barí</div>
                                <div class="ref-item">9. Chaima</div>
                                <div class="ref-item">10. Cubeo</div>
                                <div class="ref-item">11. Cumanagoto</div>
                                <div class="ref-item">12. Eñepá (Panare)</div>
                                <div class="ref-item">13. Guanono</div>
                                <div class="ref-item">14. Jodi (Hoti)</div>
                                <div class="ref-item">15. Inga</div>
                                <div class="ref-item">16. Japreria</div>
                                <div class="ref-item">17. Jivi (Guajiro)</div>
                                <div class="ref-item">18. Kariña</div>
                                <div class="ref-item">19. Kuiba</div>
                                <div class="ref-item">20. Kurripako</div>
                                <div class="ref-item">21. Mako</div>
                                <div class="ref-item">22. Mapoyo (Wianai)</div>
                                <div class="ref-item">23. Marichane</div>
                                <div class="ref-item">24. Para (Guajibo)</div>
                            </td>
                            <td style="border:none; padding:0; vertical-align:top; width:50%;">
                                <div class="ref-item">25. Piapoco (Chaire)</div>
                                <div class="ref-item">26. Piraoa</div>
                                <div class="ref-item">27. Piaroa</div>
                                <div class="ref-item">28. Pumé (Yaruro)</div>
                                <div class="ref-item">29. Puinave</div>
                                <div class="ref-item">30. Saliba</div>
                                <div class="ref-item">31. Sánemá (Sanumá)</div>
                                <div class="ref-item">32. Sape</div>
                                <div class="ref-item">33. Timoto-cuica</div>
                                <div class="ref-item">34. Tomusa</div>
                                <div class="ref-item">35. Uruak (Arutani)</div>
                                <div class="ref-item">36. Wanai (Mapoyo)</div>
                                <div class="ref-item">37. Warekena</div>
                                <div class="ref-item">38. Wayuu (Goajiro)</div>
                                <div class="ref-item">39. Wotjuja</div>
                                <div class="ref-item">40. Yabarana</div>
                                <div class="ref-item">41. Yanomami</div>
                                <div class="ref-item">42. Yekuana</div>
                                <div class="ref-item">43. Yukpa</div>
                                <div class="ref-item">44. Blanco o criollo</div>
                                <div class="ref-item">45. Afrodescendiente</div>
                                <div class="ref-item">46. Mestizo</div>
                                <div class="ref-item">47. Otros</div>
                            </td>
                        </tr>
                        </table>
                    </td>
                    {{-- Discapacidad --}}
                    <td class="ref-box" width="12%">
                        <div class="ref-title">Discapacidad, Tipo</div>
                        <div class="ref-item">1. Mentales</div>
                        <div class="ref-item">2. Visuales</div>
                        <div class="ref-item">3. Auditivas</div>
                        <div class="ref-item">4. Dolor</div>
                        <div class="ref-item">5. Habla</div>
                        <div class="ref-item">6. Cardiovascular y Respiratorio</div>
                        <div class="ref-item">7. Hematológicas e Inmunológicas</div>
                        <div class="ref-item">8. Físicas</div>
                        <div class="ref-item">9. Digestivo, metabólico y endocrino</div>
                        <div class="ref-item">10. Neuro musculo-esqueléticos</div>
                        <div class="ref-item">11. Piel y otras estructuras</div>
                    </td>
                    {{-- Circunferencia Cefálica --}}
                    <td class="ref-box" width="7%">
                        <div class="ref-title">Circunf. Cefálica</div>
                        <div class="ref-item">1. Normal</div>
                        <div class="ref-item">2. Microcefalia</div>
                        <div class="ref-item">3. Macrocefalia</div>
                    </td>
                    {{-- Lactancia Materna --}}
                    <td class="ref-box" width="9%">
                        <div class="ref-title">Lactancia Materna</div>
                        <div class="ref-item">1. 1ra hora (iniciación)</div>
                        <div class="ref-item">2. Materna + Biberón</div>
                        <div class="ref-item">3. Materna + Complementaria</div>
                        <div class="ref-item">4. Solo Complementaria</div>
                    </td>
                    {{-- N° Gestas --}}
                    <td class="ref-box" width="7%">
                        <div class="ref-title">N° Gestas</div>
                        <div class="ref-item">1. Nulipara</div>
                        <div class="ref-item">2. I Gestas</div>
                        <div class="ref-item">3. II Gestas</div>
                        <div class="ref-item">4. III Gestas</div>
                        <div class="ref-item">5. IV Gestas y +</div>
                    </td>
                    {{-- Exámen de Mamas --}}
                    <td class="ref-box" width="8%">
                        <div class="ref-title">Exámen de Mamas</div>
                        <div class="ref-item"><b>Realizado:</b> 1. Sí 2. No</div>
                        <div class="ref-item"><b>Resultado:</b> Normal</div>
                        <div class="ref-item">Con alteración</div>
                    </td>
                    {{-- Riesgo Embarazada --}}
                    <td class="ref-box" width="7%">
                        <div class="ref-title">Riesgo Embarazada</div>
                        <div class="ref-item">1. Bajo Riesgo</div>
                        <div class="ref-item">2. Alto Riesgo</div>
                    </td>
                    {{-- Tipo Diagnóstico --}}
                    <td class="ref-box" width="7%">
                        <div class="ref-title">Tipo Diagnóstico</div>
                        <div class="ref-item">1. Caso Sospechoso</div>
                        <div class="ref-item">2. Caso Probable</div>
                        <div class="ref-item">3. Caso Confirmado</div>
                        <div class="ref-item">4. No Aplica</div>
                    </td>
                    {{-- Citología --}}
                    <td class="ref-box" width="8%">
                        <div class="ref-title">Citología (Pesquisa)</div>
                        <div class="ref-item"><b>Realizadas:</b></div>
                        <div class="ref-item">1. &gt;3 años realizada</div>
                        <div class="ref-item">2. No Citada</div>
                        <div class="ref-item">3. Tomada Normal</div>
                        <div class="ref-item">4. Tomada Alterada</div>
                        <div class="ref-item"><b>Resultado:</b> 5. Normal</div>
                        <div class="ref-item">6. Con alteración</div>
                    </td>
                    {{-- Puerperio --}}
                    <td class="ref-box" width="6%">
                        <div class="ref-title">Puerperio</div>
                        <div class="ref-item">1. Inmediato &lt;24 horas</div>
                        <div class="ref-item">2. Mediano &lt;40 días</div>
                    </td>
                </tr>
                </table>
            </td>
        </tr>
        </table>
    </td>
</tr>

<tr>
    {{-- ======================= SECTION 2: FORM FIELDS + COLUMN HEADERS ======================= --}}
    <td colspan="25" style="padding:0; border: 1px solid #000;">
        <table width="100%" cellpadding="0" cellspacing="0" style="border:none;">
        <tr>
            {{-- LEFT: Form fields --}}
            <td class="form-cell" rowspan="2">
                <div style="margin-bottom:3px;">
                    <span class="form-label">1. Fecha:</span><br>
                    <span style="font-size:5pt;">Día: _____ Mes: ________ Año: __________</span>
                </div>
                <div style="margin-bottom:3px;">
                    <span class="form-label">2. Establecimiento de Salud:</span>
                    <div class="form-value">{{ $establecimiento ?? 'Consultorio Popular El Chaparro' }}</div>
                </div>
                <div style="margin-bottom:3px;">
                    <span class="form-label">3. Nombre del Médico ó Profesional:</span>
                    <div class="form-value">{{ $medico ?? '' }}</div>
                </div>
                <div style="margin-bottom:3px;">
                    <span class="form-label">4. Nombre Enfermera o Auxiliar:</span>
                    <div class="form-value"></div>
                </div>
                <div style="margin-bottom:3px;">
                    <span class="form-label">5. Lugar de Atención:</span>
                    <div class="form-value">{{ $lugarAtencion ?? '' }}</div>
                </div>
                <div>
                    <span class="form-label">6. ASIC:</span>
                    <div class="form-value">{{ $asic ?? '' }}</div>
                </div>
            </td>
            {{-- RIGHT: Column numbers --}}
            <td class="col-num" width="1.5%">7</td>
            <td class="col-num" width="2%">8</td>
            <td class="col-num" width="4%">9</td>
            <td class="col-num" width="2.5%">10</td>
            <td class="col-num" width="5%">11</td>
            <td class="col-num" width="1%">12</td>
            <td class="col-num" width="1%">13.1</td>
            <td class="col-num" width="1.5%">13.2</td>
            <td class="col-num" width="2%">13.3</td>
            <td class="col-num" width="2%">13.4</td>
            <td class="col-num" width="1.5%">13.5</td>
            <td class="col-num" width="1.5%">13.6</td>
            <td class="col-num" width="1%">13.7</td>
            <td class="col-num" width="1%">14.1</td>
            <td class="col-num" width="1%">14.2</td>
            <td class="col-num" width="1.5%">15.1</td>
            <td class="col-num" width="1.5%">15.2</td>
            <td class="col-num" width="1.5%">15.3</td>
            <td class="col-num" width="1.5%">15.4</td>
            <td class="col-num" width="1%">16.1</td>
            <td class="col-num" width="1%">16.2</td>
            <td class="col-num" width="1%">17</td>
            <td class="col-num" width="3%">18</td>
            <td class="col-num" width="2%">19</td>
            <td class="col-num" width="5%">20</td>
        </tr>
        <tr>
            {{-- Vertical column headers --}}
            <td class="col-hdr" height="55">N°</td>
            <td class="col-hdr" height="55">C.I. (V, E, I)</td>
            <td class="col-hdr" height="55">Nombres y Apellidos</td>
            <td class="col-hdr" height="55">Fecha de Nacimiento</td>
            <td class="col-hdr" height="55">Dirección de la Residencia (Comunidad, Calle, N° casa, Teléfono)</td>
            <td class="col-hdr" height="55">F</td>
            <td class="col-hdr" height="55">M</td>
            <td class="col-hdr" height="55">SANO SI (1) NO (2)</td>
            <td class="col-hdr" height="55">NIVEL EDUCATIVO ALCANZADO</td>
            <td class="col-hdr" height="55">PUEBLO INDÍGENA / ÉTNIA</td>
            <td class="col-hdr" height="55">REFERENCIA (R) / CONTRAREFERENCIA (C)</td>
            <td class="col-hdr" height="55">DISCAPACIDAD, SEGÚN TIPO</td>
            <td class="col-hdr" height="55">PESO</td>
            <td class="col-hdr" height="55">TALLA</td>
            <td class="col-hdr" height="55">CIRCUNFERENCIA CEFÁLICA</td>
            <td class="col-hdr" height="55">LACTANCIA MATERNA EN &lt;2 AÑO</td>
            <td class="col-hdr" height="55">EMBARAZADA POR SEMANA GESTACIÓN</td>
            <td class="col-hdr" height="55">EMBARAZADAS SEGÚN N° GESTAS</td>
            <td class="col-hdr" height="55">TIPO DE RIESGO DE LA EMBARAZADA</td>
            <td class="col-hdr" height="55">PUERPERIO</td>
            <td class="col-hdr" height="55">EXÁMEN DE MAMAS REALIZADO/ RESULTADO</td>
            <td class="col-hdr" height="55">CITOLOGÍA REALIZADA/ RESULTADO (PESQUISA)</td>
            <td class="col-hdr-h" height="55">TIPO DE DIAGNÓSTICO</td>
            <td class="col-hdr" height="55">MUERTE</td>
            <td class="col-hdr-h" height="55">CONDUCTA SEGUIDA Y/O TRATAMIENTO</td>
        </tr>
        </table>
    </td>
</tr>

<tr>
    {{-- ======================= SECTION 3: TITLE + DATA TABLE ======================= --}}
    <td colspan="25" style="padding:0; border: 1px solid #000;">
        {{-- Title --}}
        <div class="title-row">REGISTRO DIARIO DE ATENCIÓN INTEGRAL DEL ESTABLECIMIENTO DE SALUD</div>
        {{-- Data Table --}}
        <table width="100%" cellpadding="0" cellspacing="0">
        @php $contador = 1; @endphp
        @foreach ($consultations as $consultation)
            @php
                $patient = $consultation->patient;
                $sexo = $patient ? strtoupper($patient->gender) : '';
                $isHealthy = $consultation->is_healthy ?? false;
                $diagnostico = '';
                $tipoDiagnostico = '';
                $conducta = '';
                $ref = '';
                if ($consultation->referrals && $consultation->referrals->isNotEmpty()) {
                    $ref = $consultation->referrals->first()->type === 'R' ? 'R' : 'C';
                }
                if ($consultation->sisDiagnoses && $consultation->sisDiagnoses->isNotEmpty()) {
                    $codes = [];
                    $types = [];
                    $conducts = [];
                    foreach ($consultation->sisDiagnoses as $sd) {
                        if ($sd->sisDiagnosis) { $codes[] = $sd->sisDiagnosis->code; }
                        if ($sd->diagnosis_type) { $types[] = $sd->diagnosis_type; }
                        if ($sd->medicalConduct) { $conducts[] = $sd->medicalConduct->name; }
                        elseif ($sd->unlisted_diagnosis) { $codes[] = $sd->unlisted_diagnosis; }
                    }
                    $diagnostico = implode(', ', array_filter($codes));
                    $tipoDiagnostico = implode(', ', array_filter(array_unique($types)));
                    $conducta = implode(', ', array_filter($conducts));
                }
                $peso = $consultation->weight ?? '';
                $talla = $consultation->height ?? '';
                $etnia = $patient && $patient->ethnicity ? $patient->ethnicity->code : '';
                $educ = $patient && $patient->instructionLevel ? $patient->instructionLevel->code : '';
                $phone = $patient->phone_number ?? '';
                $direccion = ($patient->addr_sector ?? '') . ($patient->addr_street ? ', ' . $patient->addr_street : '') . ($patient->addr_house_number ? ' #' . $patient->addr_house_number : '') . ($phone ? ' Telf: ' . $phone : '');
            @endphp
            <tr class="data-row">
                <td class="data-td" width="1.5%">{{ $contador++ }}</td>
                <td class="data-td tl" width="2%" style="font-size:5pt;">{{ $patient->id_number ?? '' }}</td>
                <td class="data-td tl" width="4%" style="font-size:5pt;">{{ $patient->full_name ?? '' }}</td>
                <td class="data-td" width="2.5%" style="font-size:5pt;">{{ $patient && $patient->birth_date ? \Carbon\Carbon::parse($patient->birth_date)->format('d/m/Y') : '' }}</td>
                <td class="data-td tl" width="5%" style="font-size:4.5pt;">{{ $direccion }}</td>
                <td class="data-td" width="1%">{{ $sexo === 'F' ? 'X' : '' }}</td>
                <td class="data-td" width="1%">{{ $sexo === 'M' ? 'X' : '' }}</td>
                <td class="data-td" width="1.5%">{{ $isHealthy ? '1' : '2' }}</td>
                <td class="data-td" width="2%">{{ $educ }}</td>
                <td class="data-td" width="2%">{{ $etnia }}</td>
                <td class="data-td" width="1.5%">{{ $ref }}</td>
                <td class="data-td" width="1.5%"></td>
                <td class="data-td" width="1%">{{ $peso }}</td>
                <td class="data-td" width="1%">{{ $talla }}</td>
                <td class="data-td" width="1%"></td>
                <td class="data-td" width="1.5%"></td>
                <td class="data-td" width="1.5%"></td>
                <td class="data-td" width="1.5%"></td>
                <td class="data-td" width="1.5%"></td>
                <td class="data-td" width="1%"></td>
                <td class="data-td" width="1%"></td>
                <td class="data-td" width="1%"></td>
                <td class="data-td tl" width="3%" style="font-size:5pt;">{{ $tipoDiagnostico }}</td>
                <td class="data-td" width="2%"></td>
                <td class="data-td tl" width="5%" style="font-size:5pt;">{{ $conducta }}{{ $conducta && $consultation->treatment_plan ? ' - ' : '' }}{{ $consultation->treatment_plan ?? '' }}</td>
            </tr>
        @endforeach
        @if ($consultations->isEmpty())
            <tr><td colspan="25" class="data-td" style="text-align:center; padding:8px; font-style:italic;">No hay consultas registradas para esta fecha</td></tr>
        @endif
        </table>
    </td>
</tr>

<tr>
    {{-- ======================= FOOTER ======================= --}}
    <td colspan="25" class="footer">
        <b>Tipo de Consulta:</b> (P=Primera) (S=Sucesiva) ó (X=Asociado) &nbsp;&nbsp;|&nbsp;&nbsp;
        <b>Señale:</b> ENTREGADO (E) - CANTIDAD - PRESENTACIÓN &nbsp;&nbsp;|&nbsp;&nbsp;
        Total (P+X): {{ $consultations->count() }}
    </td>
</tr>
</table>
</body>
</html>
