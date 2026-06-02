<?php

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

        return Inertia::render('Patients/Index', [
            'patients' => $patients,
            'filters'  => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Patients/Create', $this->formCatalogs());
    }

    public function store(StorePatientRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $patient = Patient::create($this->patientAttributes($data));

            $this->syncClinicalRecords($patient, $data);

            DB::commit();

            return redirect()
                ->route('patients.show', $patient->id)
                ->with('success', "Historia clínica de {$patient->full_name} registrada exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error registrando expediente completo: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors(['error' => 'Error al guardar el paciente: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Patient $patient): Response
    {
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

        $consultations = $patient->consultations()
            ->with('doctor:id,name')
            ->latest()
            ->get();

        return Inertia::render('Patients/Show', [
            'patient'       => $patient,
            'consultations' => $consultations,
        ]);
    }

    public function edit(Patient $patient): Response
    {
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

    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
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
                ->withErrors(['error' => 'Error al actualizar el paciente: ' . $e->getMessage()])
                ->withInput();
        }
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
            'addr_locality'        => $data['addr_locality'],
            'addr_sector'          => $data['addr_sector'],
            'addr_street'          => $data['addr_street'],
            'addr_house_number'    => $data['addr_house_number'],
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
            $bgPayload = collect($data['background'])
                ->only((new PatientBackground)->getFillable())
                ->all();

            $patient->patientBackground()
                ->updateOrCreate(['patient_id' => $patient->id], $bgPayload);
        }

        if (!empty($data['family_background'])) {
            $fbPayload = collect($data['family_background'])
                ->only((new FamilyBackground)->getFillable())
                ->all();

            $patient->familyBackground()
                ->updateOrCreate(['patient_id' => $patient->id], $fbPayload);
        }

        if (!empty($data['habits'])) {
            $habitsPayload = collect($data['habits'])
                ->only((new PsychobiologicalHabit)->getFillable())
                ->all();

            $patient->psychobiologicalHabit()
                ->updateOrCreate(['patient_id' => $patient->id], $habitsPayload);
        }
    }
}
