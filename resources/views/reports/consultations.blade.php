<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 9px; color: #1e293b; }
        .header { text-align: center; padding: 10px 0; border-bottom: 2px solid #1e3a5f; margin-bottom: 10px; }
        .header h1 { font-size: 16px; color: #1e3a5f; margin-bottom: 2px; }
        .header p { font-size: 10px; color: #64748b; }
        .filters { background: #f1f5f9; padding: 8px 12px; border-radius: 4px; margin-bottom: 10px; font-size: 8px; }
        .filters span { margin-right: 16px; }
        .filters strong { color: #475569; }
        table { width: 100%; border-collapse: collapse; font-size: 8px; }
        th { background: #1e3a5f; color: white; padding: 6px 4px; text-align: left; font-size: 7px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 5px 4px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        tr:nth-child(even) { background: #f8fafc; }
        .footer { margin-top: 10px; padding-top: 8px; border-top: 1px solid #e2e8f0; font-size: 8px; color: #94a3b8; display: flex; justify-content: space-between; }
        .badge { padding: 1px 6px; border-radius: 3px; font-size: 7px; font-weight: 600; }
        .badge-p { background: #d1fae5; color: #065f46; }
        .badge-s { background: #dbeafe; color: #1e40af; }
        .badge-x { background: #ede9fe; color: #5b21b6; }
        .text-small { font-size: 7px; color: #64748b; max-width: 180px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Sistema de Gestión de Historias Clínicas · Consultorio Popular Tipo III, Municipio Guanta</p>
        <p>Generado el {{ $generated_at }} por {{ $generated_by }}</p>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <strong>Filtros aplicados:</strong>
        @foreach($filters as $key => $value)
            <span><strong>{{ $key }}:</strong> {{ $value }}</span>
        @endforeach
    </div>
    @endif

    <p style="margin-bottom:6px;font-size:9px;color:#475569;"><strong>Total de registros:</strong> {{ $consultations->count() }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Paciente</th>
                <th>Cédula</th>
                <th>Tipo</th>
                <th>Médico</th>
                <th>Motivo de Consulta</th>
                <th>PA</th>
                <th>Temp</th>
                <th>FC</th>
                <th>FR</th>
                <th>SpO2</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($consultations as $i => $c)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $c->consultation_date?->format('d/m/Y') ?? '—' }}</td>
                <td>{{ $c->patient?->full_name ?? '—' }}</td>
                <td>{{ $c->patient?->id_number ?? '—' }}</td>
                <td>
                    @if($c->consultation_type === 'P')
                        <span class="badge badge-p">1ra Vez</span>
                    @elseif($c->consultation_type === 'S')
                        <span class="badge badge-s">Sucesiva</span>
                    @else
                        <span class="badge badge-x">Asociada</span>
                    @endif
                </td>
                <td>{{ $c->doctor?->name ?? '—' }}</td>
                <td class="text-small">{{ \Str::limit($c->reason_for_consultation ?? '', 50) }}</td>
                <td>{{ $c->blood_pressure ?? '—' }}</td>
                <td>{{ $c->temperature ?? '—' }}</td>
                <td>{{ $c->heart_rate ?? '—' }}</td>
                <td>{{ $c->respiratory_rate ?? '—' }}</td>
                <td>{{ $c->oxygen_saturation ?? '—' }}</td>
                <td>{{ $c->is_healthy ? 'Sano' : 'Enfermo' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="13" style="text-align:center;padding:20px;color:#94a3b8;">No se encontraron consultas con los filtros seleccionados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <span>SistemaMed · MPPS · Consultorio Popular Tipo III, El Chaparro</span>
        <span>Página 1 de 1</span>
    </div>
</body>
</html>
