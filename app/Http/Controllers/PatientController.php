<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientBackground;
use App\Models\FamilyBackground;
use App\Models\PsychobiologicalHabit;
use App\Models\Religion;
use App\Models\Occupation;
use App\Models\InstructionLevel;
use App\Models\MaritalStatus;
use App\Models\Ethnicity;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PatientController extends Controller
{
    public function index(Request $request): Response
    {
        $patients = Patient::query()
            ->when($request->search, function ($query, $search) {
                $query->where('full_name', 'like', "%{$search}%")
                      ->orWhere('id_number', 'like', "%{$search}%");
            })
            ->when($request->gender, fn($q, $v) => $q->where('gender', $v))
            ->when($request->status === 'open', fn($q) => $q->whereNull('closed_at'))
            ->when($request->status === 'closed', fn($q) => $q->whereNotNull('closed_at'))
            ->when($request->age_min, fn($q, $v) => $q->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= ?', [$v]))
            ->when($request->age_max, fn($q, $v) => $q->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) <= ?', [$v]))
            ->when($request->date_from, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->date_to, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->with([
                'religion:id,name',
                'occupation:id,name',
                'instructionLevel:id,name',
                'maritalStatus:id,name',
                'ethnicity:id,name',
            ])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $user = Auth::user();

        return Inertia::render('Patients/Index', [
            'patients' => $patients,
            'filters'  => $request->only(['search', 'gender', 'status', 'age_min', 'age_max', 'date_from', 'date_to']),
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role_name' => $user->role?->name ?? '',
                ],
                'can_edit_patient' => Gate::allows('update', Patient::class),
                'can_close_patient' => Gate::allows('close', Patient::class),
                'can_reopen_patient' => Gate::allows('reopen', Patient::class),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Patients/Create', $this->formCatalogs());
    }

    public function store(StorePatientRequest $request): RedirectResponse
    {
        Log::info('=== INICIANDO REGISTRO DE PACIENTE ===', [
            'user_id' => Auth::id(),
            'email' => Auth::user()?->email,
        ]);

        try {
            Log::info('Paso 1: Validando datos del formulario');
            $data = $request->validated();
            Log::info('Datos validados correctamente', [
                'full_name' => $data['full_name'] ?? 'N/A',
                'id_number' => $data['id_number'] ?? 'N/A',
            ]);

            Log::info('Paso 2: Iniciando transacción de base de datos');
            DB::beginTransaction();

            Log::info('Paso 3: Creando paciente principal');
            $patient = Patient::create($this->patientAttributes($data));
            Log::info('Paciente creado exitosamente', [
                'patient_id' => $patient->id,
                'patient_name' => $patient->full_name,
            ]);

            Log::info('Paso 4: Cerrando historia clínica automáticamente');
            $patient->closed_at = now();
            $patient->save();
            Log::info('Historia clínica cerrada al crear', ['patient_id' => $patient->id]);

            Log::info('Paso 5: Sincronizando registros clínicos');
            $this->syncClinicalRecords($patient, $data);
            Log::info('Registros clínicos sincronizados exitosamente');

            Log::info('Paso 6: Confirmando transacción');
            DB::commit();
            Log::info('Transacción confirmada exitosamente');

            Log::info('Paso 7: Redirigiendo a creación de consulta');
            Log::info('=== REGISTRO DE PACIENTE COMPLETADO EXITOSAMENTE ===');
            
            return redirect()
                ->route('consultations.create', $patient->id)
                ->with('success', "Historia clínica de {$patient->full_name} registrada exitosamente.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('ERROR: Validación fallida', [
                'errors' => $e->errors(),
                'user_id' => Auth::id(),
            ]);

            return back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('ERROR: Base de datos falló', [
                'message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'user_id' => Auth::id(),
            ]);

            return back()
                ->withErrors(['error' => 'Error de base de datos. Intente de nuevo o contacte al administrador.'])
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ERROR INESPERADO: Registro de paciente falló', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors(['error' => 'Error inesperado. Intente de nuevo o contacte al administrador.'])
                ->withInput();
        }
    }

    public function close(Patient $patient): RedirectResponse
    {
        if (Gate::denies('close', $patient)) {
            return back()->withErrors(['error' => 'No tiene permiso para cerrar historias clínicas.']);
        }

        $patient->closed_at = now();
        $patient->save();

        return back()->with('success', "Historia clínica de {$patient->full_name} cerrada exitosamente.");
    }

    public function reopen(Patient $patient): RedirectResponse
    {
        if (Gate::denies('reopen', $patient)) {
            return back()->withErrors(['error' => 'No tiene permiso para reabrir historias clínicas. Solo el administrador y médico coordinador pueden reabrir historias clínicas.']);
        }

        $patient->closed_at = null;
        $patient->save();

        return back()->with('success', "Historia clínica de {$patient->full_name} reabierta exitosamente.");
    }

    public function edit(Patient $patient, Request $request): Response
    {
        if (Gate::denies('update', $patient)) {
            $error = $patient->closed_at
                ? 'Esta historia clínica está cerrada y solo puede ser editada por administradores o médicos coordinadores.'
                : 'No tiene permiso para editar historias clínicas. Contacte al administrador.';

            return Inertia::render('Patients/Show', [
                'patient' => $patient->load([
                    'maritalStatus:id,name',
                    'ethnicity:id,code,name',
                    'religion:id,name',
                    'occupation:id,name',
                    'instructionLevel:id,code,name',
                ]),
                'error' => $error,
            ]);
        }

        if ($patient->closed_at && $request->input('authenticate') !== 'true') {
            return Inertia::render('Patients/AuthenticateEdit', [
                'patient' => $patient,
            ]);
        }

        $patient->load([
            'maritalStatus:id,name',
            'ethnicity:id,code,name',
            'religion:id,name',
            'occupation:id,name',
            'instructionLevel:id,code,name',
            'patientBackground',
            'familyBackground',
            'psychobiologicalHabit',
        ]);

        return Inertia::render('Patients/Create', [
            ...$this->formCatalogs(),
            'patient' => $patient,
            'mode'    => 'edit',
        ]);
    }

    public function show(Patient $patient): Response
    {
        $user = Auth::user();

        $patient->load([
            'maritalStatus:id,name',
            'ethnicity:id,code,name',
            'religion:id,name',
            'occupation:id,name',
            'instructionLevel:id,code,name',
            'patientBackground',
            'familyBackground',
            'psychobiologicalHabit',
        ]);

        return Inertia::render('Patients/Show', [
            'patient' => $patient,
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role_name' => $user->role?->name ?? '',
                ],
                'can_edit_patient' => Gate::allows('update', $patient),
                'can_close_patient' => Gate::allows('close', $patient),
                'can_reopen_patient' => Gate::allows('reopen', $patient),
            ],
        ]);
    }

    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        if (Gate::denies('update', $patient)) {
            $error = $patient->closed_at
                ? 'Esta historia clínica está cerrada y solo puede ser editada por administradores o médicos coordinadores.'
                : 'No tiene permiso para editar historias clínicas. Contacte al administrador.';

            return back()->withErrors(['error' => $error]);
        }

        $data = $request->validated();

        try {
            DB::beginTransaction();

            $patient->update($this->patientAttributes($data));

            $this->syncClinicalRecords($patient, $data);

            DB::commit();

            return redirect()
                ->route('patients.show', $patient->id)
                ->with('success', "Historia clínica de {$patient->full_name} actualizada correctamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando expediente: ' . $e->getMessage(), [
                'patient_id' => $patient->id,
                'user_id'    => Auth::id(),
            ]);

            return back()
                ->withErrors(['error' => 'Error al actualizar el paciente. Intente de nuevo.'])
                ->withInput();
        }
    }

    public function editContact(Patient $patient): Response
    {
        if (Gate::denies('update', $patient)) {
            return back()->withErrors(['error' => 'No tiene permiso para editar datos de contacto.']);
        }

        return Inertia::render('Patients/EditContact', [
            'patient' => $patient,
        ]);
    }

    public function updateContact(Request $request, Patient $patient): RedirectResponse
    {
        if (Gate::denies('update', $patient)) {
            return back()->withErrors(['error' => 'No tiene permiso para editar datos de contacto.']);
        }
        $validated = $request->validate([
            'phone_number'      => ['nullable', 'string', 'max:20', 'regex:/^[\d\s\+\-\(\)]+$/'],
            'addr_state'        => ['required', 'string', 'max:100'],
            'addr_municipality' => ['required', 'string', 'max:100'],
            'addr_parish'       => ['required', 'string', 'max:100'],
            'addr_locality'     => ['nullable', 'string', 'max:255'],
            'addr_sector'       => ['required', 'string', 'max:255'],
            'addr_street'       => ['nullable', 'string', 'max:255'],
            'addr_house_number' => ['nullable', 'string', 'max:100'],
            'addr_zip_code'     => ['nullable', 'string', 'max:20', 'regex:/^\d{4,10}$/'],
            'addr_reference'    => ['nullable', 'string', 'max:255'],
            'residence_time'    => ['nullable', 'string', 'max:100'],
        ]);

        $patient->update($validated);

        return redirect()
            ->route('patients.show', $patient->id)
            ->with('success', 'Datos de contacto actualizados correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formCatalogs(): array
    {
        return [
            'maritalStatuses'   => MaritalStatus::select('id', 'name')->orderBy('name')->get(),
            'ethnicities'       => Ethnicity::select('id', 'code', 'name')->orderBy('code')->get(),
            'instructionLevels' => InstructionLevel::select('id', 'code', 'name')->orderBy('code')->get(),
            'occupations'       => Occupation::select('id', 'name')->orderBy('name')->get(),
            'religions'         => Religion::select('id', 'name')->orderBy('name')->get(),
            'institution'       => 'Consultorio Popular Tipo III "El Chaparro"',
            'userName'          => Auth::user()->name,
            'mode'              => 'create',
            'patient'           => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function patientAttributes(array $data): array
    {
        return [
            'full_name'            => $data['full_name'],
            'id_number'            => $data['id_number'],
            'nationality'          => $data['nationality'],
            'nationality_country'  => $data['nationality_country'] ?? null,
            'gender'               => $data['gender'],
            'birth_date'           => $data['birth_date'],
            'birth_place'          => $data['birth_place'],
            'marital_status_id'    => $data['marital_status_id'],
            'ethnicity_id'         => $data['ethnicity_id'],
            'instruction_level_id' => $data['instruction_level_id'],
            'occupation_id'        => $data['occupation_id'],
            'occupation_detail'    => $data['occupation_detail'] ?? null,
            'religion_id'          => $data['religion_id'],
            'religion_detail'      => $data['religion_detail'] ?? null,
            'blood_type'           => $data['knows_blood_type'] ? $data['blood_type'] : 'Desconoce',
            'rh_factor'            => $data['knows_blood_type'] ? $data['rh_factor'] : null,
            'phone_number'         => $data['phone_number'],
            'addr_state'           => $data['addr_state'],
            'addr_municipality'    => $data['addr_municipality'],
            'addr_parish'          => $data['addr_parish'],
            'addr_locality'        => $data['addr_locality'] ?? null,
            'addr_sector'          => $data['addr_sector'] ?? null,
            'addr_street'          => $data['addr_street'] ?? null,
            'addr_house_number'    => $data['addr_house_number'] ?? null,
            'addr_zip_code'        => $data['addr_zip_code'] ?? null,
            'addr_reference'       => $data['addr_reference'] ?? null,
            'residence_time'       => $data['residence_time'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncClinicalRecords(Patient $patient, array $data): void
    {
        if (!empty($data['background'])) {
            Log::info('Sincronizando patient background');
            $bgPayload = collect($data['background'])
                ->only((new PatientBackground)->getFillable())
                ->all();

            $patient->patientBackground()
                ->updateOrCreate(['patient_id' => $patient->id], $bgPayload);
            Log::info('Patient background sincronizado');
        }

        if (!empty($data['family_background'])) {
            Log::info('Sincronizando family background');
            $fbPayload = collect($data['family_background'])
                ->only((new FamilyBackground)->getFillable())
                ->all();

            $patient->familyBackground()
                ->updateOrCreate(['patient_id' => $patient->id], $fbPayload);
            Log::info('Family background sincronizado');
        }

        if (!empty($data['habits'])) {
            Log::info('Sincronizando psychobiological habits');
            $habitsPayload = collect($data['habits'])
                ->only((new PsychobiologicalHabit)->getFillable())
                ->all();

            $patient->psychobiologicalHabit()
                ->updateOrCreate(['patient_id' => $patient->id], $habitsPayload);
            Log::info('Psychobiological habits sincronizados');
        }
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        if (Gate::denies('delete', $patient)) {
            return back()->withErrors(['error' => 'No tiene permiso para eliminar historias clínicas. Contacte al administrador.']);
        }

        try {
            $patient->delete();

            return back()->with('success', "Historia clínica de {$patient->full_name} movida a papelera.");
        } catch (\Exception $e) {
            Log::error('Error eliminando paciente: ' . $e->getMessage(), [
                'patient_id' => $patient->id,
                'user_id' => Auth::id(),
            ]);

            return back()->withErrors(['error' => 'Error al eliminar el paciente. Intente de nuevo.']);
        }
    }

    public function restore($id): RedirectResponse
    {
        $patient = Patient::withTrashed()->findOrFail($id);

        if (Gate::denies('restore', $patient)) {
            return back()->withErrors(['error' => 'No tiene permiso para restaurar historias clínicas.']);
        }

        try {
            $patient->restore();

            return back()->with('success', "Historia clínica de {$patient->full_name} restaurada exitosamente.");
        } catch (\Exception $e) {
            Log::error('Error restaurando paciente: ' . $e->getMessage(), [
                'patient_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return back()->withErrors(['error' => 'Error al restaurar el paciente. Intente de nuevo.']);
        }
    }

    public function forceDelete($id): RedirectResponse
    {
        $patient = Patient::withTrashed()->findOrFail($id);

        if (Gate::denies('forceDelete', $patient)) {
            return back()->withErrors(['error' => 'Solo los administradores pueden eliminar permanentemente historias clínicas.']);
        }

        try {
            $patient->forceDelete();

            return redirect()->route('patients.index')->with('success', "Historia clínica de {$patient->full_name} eliminada permanentemente.");
        } catch (\Exception $e) {
            Log::error('Error eliminando permanentemente paciente: ' . $e->getMessage(), [
                'patient_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return back()->withErrors(['error' => 'Error al eliminar permanentemente. Intente de nuevo.']);
        }
    }

    public function trashed(Request $request)
    {
        if (Gate::denies('viewTrashed', Patient::class)) {
            return back()->withErrors(['error' => 'No tiene permiso para ver la papelera.']);
        }

        $user = Auth::user();

        $patients = Patient::onlyTrashed()
            ->when($request->search, function ($query, $search) {
                $query->where('full_name', 'like', "%{$search}%")
                      ->orWhere('id_number', 'like', "%{$search}%");
            })
            ->with([
                'religion:id,name',
                'occupation:id,name',
                'instructionLevel:id,name',
                'maritalStatus:id,name',
                'ethnicity:id,name',
            ])
            ->latest('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Patients/Trashed', [
            'patients' => $patients,
            'filters'  => $request->only(['search']),
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role_name' => $user->role?->name ?? '',
                ],
            ],
        ]);
    }
}
