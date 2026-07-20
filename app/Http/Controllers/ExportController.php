<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Consultation;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════
    // PACIENTES
    // ═══════════════════════════════════════════════════════════════════

    public function patientsPdf(Request $request)
    {
        $patients = $this->getFilteredPatients($request);
        $filters = $this->getPatientFilters($request);

        $pdf = Pdf::loadView('reports.patients', [
            'patients' => $patients,
            'filters'  => $filters,
            'title'    => 'Reporte de Pacientes',
            'generated_at' => now()->format('d/m/Y H:i'),
            'generated_by' => auth()->user()->name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('pacientes_' . now()->format('Y-m-d_His') . '.pdf');
    }

    public function patientsCsv(Request $request)
    {
        $patients = $this->getFilteredPatients($request);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pacientes_' . now()->format('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($patients) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel
            fprintf($handle, "sep=;\n"); // Explicit separator for Excel

            fputcsv($handle, [
                'ID', 'Nombre Completo', 'Cédula', 'Género', 'Fecha Nacimiento', 'Edad',
                'Estado Civil', 'Ocupación', 'Nivel Instrucción', 'Grupo Sanguíneo',
                'Teléfono', 'Estado', 'Fecha Registro',
            ]);

            foreach ($patients as $p) {
                fputcsv($handle, [
                    $p->id,
                    $p->full_name,
                    $p->nationality . '-' . $p->id_number,
                    $p->gender === 'M' ? 'Masculino' : ($p->gender === 'F' ? 'Femenino' : 'Otro'),
                    $p->birth_date?->format('d/m/Y') ?? '',
                    $p->age ?? '',
                    $p->maritalStatus?->name ?? '',
                    $p->occupation?->name ?? '',
                    $p->instructionLevel?->name ?? '',
                    ($p->blood_type ?? '') . ($p->rh_factor ?? ''),
                    $p->phone_number ?? '',
                    $p->closed_at ? 'Cerrado' : 'Abierto',
                    $p->created_at?->format('d/m/Y H:i') ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getFilteredPatients(Request $request)
    {
        return Patient::query()
            ->with(['maritalStatus:id,name', 'occupation:id,name', 'instructionLevel:id,name'])
            ->when($request->filled('gender'), fn($q) => $q->where('gender', $request->gender))
            ->when($request->filled('marital_status_id'), fn($q) => $q->where('marital_status_id', $request->marital_status_id))
            ->when($request->filled('occupation_id'), fn($q) => $q->where('occupation_id', $request->occupation_id))
            ->when($request->filled('instruction_level_id'), fn($q) => $q->where('instruction_level_id', $request->instruction_level_id))
            ->when($request->filled('blood_type'), fn($q) => $q->where('blood_type', $request->blood_type))
            ->when($request->filled('status'), function ($q) use ($request) {
                $request->status === 'closed' ? $q->whereNotNull('closed_at') : $q->whereNull('closed_at');
            })
            ->when($request->filled('age_min'), function ($q) use ($request) {
                $q->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= ?', [$request->age_min]);
            })
            ->when($request->filled('age_max'), function ($q) use ($request) {
                $q->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) <= ?', [$request->age_max]);
            })
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('full_name', 'like', "%{$request->search}%")
                         ->orWhere('id_number', 'like', "%{$request->search}%");
                });
            })
            ->orderBy('full_name', 'asc')
            ->limit(5000)
            ->get();
    }

    private function getPatientFilters(Request $request): array
    {
        $filters = [];
        if ($request->gender) $filters['Género'] = $request->gender === 'M' ? 'Masculino' : 'Femenino';
        if ($request->status) $filters['Estado'] = $request->status === 'closed' ? 'Cerrado' : 'Abierto';
        if ($request->age_min || $request->age_max) $filters['Edad'] = ($request->age_min ?? '0') . ' - ' . ($request->age_max ?? '∞');
        if ($request->date_from || $request->date_to) $filters['Fecha Registro'] = ($request->date_from ?? 'Inicio') . ' a ' . ($request->date_to ?? 'Fin');
        return $filters;
    }

    // ═══════════════════════════════════════════════════════════════════
    // CONSULTAS
    // ═══════════════════════════════════════════════════════════════════

    public function consultationsPdf(Request $request)
    {
        $consultations = $this->getFilteredConsultations($request);
        $filters = $this->getConsultationFilters($request);

        $pdf = Pdf::loadView('reports.consultations', [
            'consultations' => $consultations,
            'filters'       => $filters,
            'title'         => 'Reporte de Consultas',
            'generated_at'  => now()->format('d/m/Y H:i'),
            'generated_by'  => auth()->user()->name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('consultas_' . now()->format('Y-m-d_His') . '.pdf');
    }

    public function consultationsCsv(Request $request)
    {
        $consultations = $this->getFilteredConsultations($request);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="consultas_' . now()->format('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($consultations) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fprintf($handle, "sep=;\n");

            fputcsv($handle, [
                'ID', 'Paciente', 'Cédula', 'Fecha Consulta', 'Tipo', 'Médico',
                'Motivo de Consulta', 'Enfermedad Actual', 'Presión Arterial',
                'Temperatura', 'Frec. Cardíaca', 'Frec. Respiratoria', 'SpO2',
                'Peso', 'Altura', 'Sano', 'Fecha Registro',
            ]);

            foreach ($consultations as $c) {
                fputcsv($handle, [
                    $c->id,
                    $c->patient?->full_name ?? '',
                    $c->patient?->id_number ?? '',
                    $c->consultation_date?->format('d/m/Y H:i') ?? '',
                    match($c->consultation_type) { 'P' => 'Primera Vez', 'S' => 'Sucesiva', 'X' => 'Asociada', default => '' },
                    $c->doctor?->name ?? '',
                    $c->reason_for_consultation ?? '',
                    $c->current_illness ?? '',
                    $c->blood_pressure ?? '',
                    $c->temperature ?? '',
                    $c->heart_rate ?? '',
                    $c->respiratory_rate ?? '',
                    $c->oxygen_saturation ?? '',
                    $c->weight ?? '',
                    $c->height ?? '',
                    $c->is_healthy ? 'Sí' : 'No',
                    $c->created_at?->format('d/m/Y H:i') ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getFilteredConsultations(Request $request)
    {
        return Consultation::with([
            'patient:id,full_name,id_number',
            'doctor:id,name',
        ])
        ->when($request->filled('consultation_type'), fn($q) => $q->where('consultation_type', $request->consultation_type))
        ->when($request->filled('is_healthy'), fn($q) => $q->where('is_healthy', $request->boolean('is_healthy')))
        ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
        ->when($request->filled('date_from'), fn($q) => $q->whereDate('consultation_date', '>=', $request->date_from))
        ->when($request->filled('date_to'), fn($q) => $q->whereDate('consultation_date', '<=', $request->date_to))
        ->when($request->filled('age_min'), function ($q) use ($request) {
            $q->whereHas('patient', fn($pq) => $pq->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= ?', [$request->age_min]));
        })
        ->when($request->filled('age_max'), function ($q) use ($request) {
            $q->whereHas('patient', fn($pq) => $pq->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) <= ?', [$request->age_max]));
        })
        ->when($request->filled('search'), function ($q) use ($request) {
            $q->whereHas('patient', fn($pq) => $pq->where(function ($sub) use ($request) {
                $sub->where('full_name', 'like', "%{$request->search}%")
                    ->orWhere('id_number', 'like', "%{$request->search}%");
            }));
        })
        ->orderBy('consultation_date', 'desc')
        ->limit(5000)
        ->get();
    }

    private function getConsultationFilters(Request $request): array
    {
        $filters = [];
        if ($request->consultation_type) $filters['Tipo'] = match($request->consultation_type) { 'P' => 'Primera Vez', 'S' => 'Sucesiva', 'X' => 'Asociada', default => '' };
        if ($request->filled('is_healthy')) $filters['Estado'] = $request->boolean('is_healthy') ? 'Sano' : 'Enfermo';
        if ($request->date_from || $request->date_to) $filters['Fecha'] = ($request->date_from ?? 'Inicio') . ' a ' . ($request->date_to ?? 'Fin');
        return $filters;
    }

    // ═══════════════════════════════════════════════════════════════════
    // HISTORIAL EPI
    // ═══════════════════════════════════════════════════════════════════

    public function historicalPdf(Request $request)
    {
        $consultations = $this->getFilteredHistorical($request);
        $filters = $this->getHistoricalFilters($request);

        $pdf = Pdf::loadView('reports.historical', [
            'consultations' => $consultations,
            'filters'       => $filters,
            'title'         => 'Historial Epidemiológico',
            'generated_at'  => now()->format('d/m/Y H:i'),
            'generated_by'  => auth()->user()->name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('historial_epi_' . now()->format('Y-m-d_His') . '.pdf');
    }

    private function getFilteredHistorical(Request $request)
    {
        $query = Consultation::with([
            'patient:id,full_name,id_number,gender,birth_date,addr_sector',
            'doctor:id,name',
            'sisDiagnoses.sisDiagnosis:id,code,name',
        ])->orderBy('consultation_date', 'desc');

        if ($code = $request->input('code')) {
            $query->whereHas('sisDiagnoses.sisDiagnosis', fn($q) => $q->where('code', 'like', "$code%")->orWhere('name', 'like', "%$code%"));
        }

        if ($sector = $request->input('sector')) {
            $query->whereHas('patient', fn($q) => $q->where('addr_sector', $sector));
        }

        return $query->limit(5000)->get();
    }

    private function getHistoricalFilters(Request $request): array
    {
        $filters = [];
        if ($request->filled('code')) $filters['CIE-10'] = $request->code;
        if ($request->filled('sector')) $filters['Sector'] = $request->sector;
        return $filters;
    }

    // ═══════════════════════════════════════════════════════════════════
    // AUDITORÍA
    // ═══════════════════════════════════════════════════════════════════

    public function auditLogsPdf(Request $request)
    {
        if (Gate::denies('view-audit-logs')) {
            abort(403, 'No tiene permiso para ver la bitácora del sistema.');
        }

        $logs = $this->getFilteredAuditLogs($request);
        $filters = $this->getAuditFilters($request);

        $pdf = Pdf::loadView('reports.audit-logs', [
            'logs'          => $logs,
            'filters'       => $filters,
            'title'         => 'Reporte de Bitácora del Sistema',
            'generated_at'  => now()->format('d/m/Y H:i'),
            'generated_by'  => auth()->user()->name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('bitacora_' . now()->format('Y-m-d_His') . '.pdf');
    }

    public function auditLogsCsv(Request $request)
    {
        if (Gate::denies('view-audit-logs')) {
            abort(403, 'No tiene permiso para ver la bitácora del sistema.');
        }

        $logs = $this->getFilteredAuditLogs($request);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="bitacora_' . now()->format('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fprintf($handle, "sep=;\n");

            fputcsv($handle, [
                'Fecha/Hora', 'Usuario', 'Acción', 'Modelo', 'ID Registro',
                'IP', 'Valores Anteriores', 'Valores Nuevos',
            ]);

            $actionLabels = ['created' => 'Creación', 'updated' => 'Actualización', 'deleted' => 'Eliminación'];
            $modelLabels = [
                'App\\Models\\Patient' => 'Paciente',
                'App\\Models\\Consultation' => 'Consulta',
                'App\\Models\\User' => 'Usuario',
                'App\\Models\\SisDiagnosis' => 'Diagnóstico SIS',
            ];

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->created_at?->format('d/m/Y H:i:s') ?? '',
                    $log->user?->name ?? '—',
                    $actionLabels[$log->action] ?? $log->action,
                    $modelLabels[$log->model_type] ?? class_basename($log->model_type),
                    $log->model_id,
                    $log->ip_address ?? '—',
                    $log->old_values ? json_encode($log->old_values, JSON_UNESCAPED_UNICODE) : '—',
                    $log->new_values ? json_encode($log->new_values, JSON_UNESCAPED_UNICODE) : '—',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getFilteredAuditLogs(Request $request)
    {
        return AuditLog::with('user:id,name')
            ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('action'), fn($q) => $q->where('action', $request->action))
            ->when($request->filled('model_type'), fn($q) => $q->where('model_type', $request->model_type))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->limit(10000)
            ->get();
    }

    private function getAuditFilters(Request $request): array
    {
        $filters = [];
        if ($request->user_id) {
            $user = \App\Models\User::find($request->user_id);
            $filters['Usuario'] = $user?->name ?? $request->user_id;
        }
        if ($request->action) $filters['Acción'] = match($request->action) { 'created' => 'Creación', 'updated' => 'Actualización', 'deleted' => 'Eliminación', default => $request->action };
        if ($request->date_from || $request->date_to) $filters['Fecha'] = ($request->date_from ?? 'Inicio') . ' a ' . ($request->date_to ?? 'Fin');
        return $filters;
    }
}
