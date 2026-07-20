<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>{{ $title }}</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; color: #1E293B; padding: 20px; }
    .header { text-align: center; border-bottom: 2px solid #1E40AF; padding-bottom: 10px; margin-bottom: 12px; }
    .header h1 { font-size: 16px; color: #1E40AF; margin-bottom: 2px; }
    .header .subtitle { font-size: 10px; color: #64748B; }
    .meta { display: flex; justify-content: space-between; font-size: 8px; color: #64748B; margin-bottom: 12px; padding: 6px 10px; background: #F8FAFC; border-radius: 4px; border: 1px solid #E2E8F0; }
    .filters { display: flex; gap: 12px; font-size: 8px; margin-bottom: 10px; flex-wrap: wrap; }
    .filter-tag { background: #EFF6FF; border: 1px solid #DBEAFE; border-radius: 4px; padding: 2px 8px; color: #1E40AF; font-weight: 600; }
    .counter { text-align: right; font-size: 9px; color: #64748B; margin-bottom: 8px; }
    .counter strong { color: #1E40AF; }
    table { width: 100%; border-collapse: collapse; font-size: 8px; }
    th { background: #1E40AF; color: #fff; padding: 5px 6px; text-align: left; font-weight: 600; font-size: 7.5px; text-transform: uppercase; letter-spacing: 0.3px; white-space: nowrap; }
    td { padding: 4px 6px; border-bottom: 1px solid #E2E8F0; vertical-align: top; }
    tr:nth-child(even) { background: #F8FAFC; }
    tr:hover { background: #EFF6FF; }
    .diag { font-size: 7.5px; margin: 1px 0; }
    .diag strong { color: #1E40AF; }
    .empty { text-align: center; padding: 30px; color: #94A3B8; font-style: italic; }
    .footer { margin-top: 15px; padding-top: 8px; border-top: 1px solid #E2E8F0; font-size: 7.5px; color: #94A3B8; display: flex; justify-content: space-between; }
  </style>
</head>
<body>

  <div class="header">
    <h1>{{ $title }}</h1>
    <div class="subtitle">Sistema de Gestión de Historias Clínicas — Consultorio Popular Tipo III "El Chaparro"</div>
  </div>

  <div class="meta">
    <span><strong>Generado por:</strong> {{ $generated_by }}</span>
    <span><strong>Fecha de generación:</strong> {{ $generated_at }}</span>
    <span><strong>Total de registros:</strong> {{ $consultations->count() }}</span>
  </div>

  @if(!empty($filters))
    <div class="filters">
      <span style="font-weight:600; color:#475569;">Filtros aplicados:</span>
      @foreach($filters as $key => $value)
        <span class="filter-tag">{{ $key }}: {{ $value }}</span>
      @endforeach
    </div>
  @endif

  <table>
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Paciente</th>
        <th>Cédula</th>
        <th>Edad</th>
        <th>Sexo</th>
        <th>Sector</th>
        <th>Diagnóstico (CIE-10)</th>
        <th>Médico</th>
      </tr>
    </thead>
    <tbody>
      @forelse($consultations as $c)
        <tr>
          <td>{{ $c->consultation_date ? \Carbon\Carbon::parse($c->consultation_date)->format('d/m/Y') : '—' }}</td>
          <td style="font-weight:600;">{{ $c->patient->full_name ?? '—' }}</td>
          <td>{{ $c->patient->id_number ?? '—' }}</td>
          <td>{{ $c->patient->birth_date ? \Carbon\Carbon::parse($c->patient->birth_date)->age : '—' }}</td>
          <td>{{ ($c->patient->gender ?? '') === 'F' ? 'Femenino' : (($c->patient->gender ?? '') === 'M' ? 'Masculino' : '—') }}</td>
          <td>{{ $c->patient->addr_sector ?? '—' }}</td>
          <td>
            @if($c->sisDiagnoses->count())
              @foreach($c->sisDiagnoses as $d)
                <div class="diag"><strong>{{ $d->sis_diagnosis->code ?? '' }}</strong> {{ $d->sis_diagnosis->name ?? $d->unlisted_diagnosis }}</div>
              @endforeach
            @else
              —
            @endif
          </td>
          <td>{{ $c->doctor->name ?? '—' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="empty">No se encontraron registros con esos filtros</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <div class="footer">
    <span>Documento generado automáticamente — {{ config('app.name', 'Sistema de Gestión de Salud') }}</span>
    <span>Página 1 de 1</span>
  </div>

</body>
</html>