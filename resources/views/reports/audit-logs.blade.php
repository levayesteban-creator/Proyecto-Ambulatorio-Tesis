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
        .badge-created { background: #d4edda; color: #155724; }
        .badge-updated { background: #fff3cd; color: #856404; }
        .badge-deleted { background: #f8d7da; color: #721c24; }
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

    <p style="margin-bottom:6px;font-size:9px;color:#475569;"><strong>Total de registros:</strong> {{ $logs->count() }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha/Hora</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Modelo</th>
                <th>ID Registro</th>
                <th>IP</th>
                <th>Valores Anteriores</th>
                <th>Valores Nuevos</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $i => $log)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $log->created_at?->format('d/m/Y H:i:s') ?? '—' }}</td>
                <td>{{ $log->user?->name ?? '—' }}</td>
                <td>
                    @if($log->action === 'created')
                        <span class="badge badge-created">Creación</span>
                    @elseif($log->action === 'updated')
                        <span class="badge badge-updated">Actualización</span>
                    @else
                        <span class="badge badge-deleted">Eliminación</span>
                    @endif
                </td>
                <td>{{ class_basename($log->model_type) }}</td>
                <td>{{ $log->model_id }}</td>
                <td>{{ $log->ip_address ?? '—' }}</td>
                <td style="max-width:150px;word-break:break-all;font-size:7px;">
                    {{ $log->old_values ? json_encode($log->old_values, JSON_UNESCAPED_UNICODE) : '—' }}
                </td>
                <td style="max-width:150px;word-break:break-all;font-size:7px;">
                    {{ $log->new_values ? json_encode($log->new_values, JSON_UNESCAPED_UNICODE) : '—' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center;padding:20px;color:#94a3b8;">No se encontraron registros con los filtros seleccionados.</td>
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
