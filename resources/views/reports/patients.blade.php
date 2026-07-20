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
        td { padding: 5px 4px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) { background: #f8fafc; }
        .footer { margin-top: 10px; padding-top: 8px; border-top: 1px solid #e2e8f0; font-size: 8px; color: #94a3b8; display: flex; justify-content: space-between; }
        .badge { padding: 1px 6px; border-radius: 3px; font-size: 7px; font-weight: 600; }
        .badge-open { background: #d1fae5; color: #065f46; }
        .badge-closed { background: #fee2e2; color: #991b1b; }
        .gender-m { color: #2563eb; }
        .gender-f { color: #db2777; }
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

    <p style="margin-bottom:6px;font-size:9px;color:#475569;"><strong>Total de registros:</strong> {{ $patients->count() }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre Completo</th>
                <th>Cédula</th>
                <th>Género</th>
                <th>Nacimiento</th>
                <th>Edad</th>
                <th>Estado Civil</th>
                <th>Ocupación</th>
                <th>Instrucción</th>
                <th>Grupo S.</th>
                <th>Teléfono</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($patients as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->full_name }}</td>
                <td>{{ $p->nationality }}-{{ $p->id_number }}</td>
                <td class="gender-{{ strtolower($p->gender) }}">{{ $p->gender === 'M' ? 'Masculino' : ($p->gender === 'F' ? 'Femenino' : 'Otro') }}</td>
                <td>{{ $p->birth_date?->format('d/m/Y') ?? '—' }}</td>
                <td>{{ $p->age ?? '—' }}</td>
                <td>{{ $p->maritalStatus?->name ?? '—' }}</td>
                <td>{{ $p->occupation?->name ?? '—' }}</td>
                <td>{{ $p->instructionLevel?->name ?? '—' }}</td>
                <td>{{ ($p->blood_type ?? '') . ($p->rh_factor ?? '') }}</td>
                <td>{{ $p->phone_number ?? '—' }}</td>
                <td>
                    @if($p->closed_at)
                        <span class="badge badge-closed">Cerrado</span>
                    @else
                        <span class="badge badge-open">Abierto</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" style="text-align:center;padding:20px;color:#94a3b8;">No se encontraron pacientes con los filtros seleccionados.</td>
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
