<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\SisDiagnosis;
use App\Models\MedicalConduct;
use App\Http\Requests\StoreConsultationRequest;
use App\Http\Requests\UpdateConsultationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ConsultationController extends Controller
{
    /**
     * Formulario de nueva consulta (4 tabs).
     * GET /patients/{patient}/consultations/create
     */
    public function create(Patient $patient): Response
    {
        return Inertia::render('Consultations/Create', [
            'patient'          => $patient->load('occupation'),
            'age_at_moment'    => Carbon::parse($patient->birth_date)->age,
            'diagnosesCatalog' => SisDiagnosis::select('id', 'code', 'name')->orderBy('code')->get(),
            'medicalConducts'  => MedicalConduct::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    /**
     * Persiste la consulta completa de forma ATÓMICA.
     * Si cualquier paso falla → ROLLBACK completo, cero registros huérfanos.
     *
     * POST /patients/{patient}/consultations
     */
    public function store(StoreConsultationRequest $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // ── 1. Registro principal + signos vitales ────────────────────
            $consultation = $patient->consultations()->create([
                'user_id'                 => Auth::id(),
                'age_at_moment'           => Carbon::parse($patient->birth_date)->age,
                'address_at_moment'       => $patient->current_address,
                'phone_at_moment'         => $patient->phone,
                'occupation_id'           => $patient->occupation_id,
                'consultation_type'       => $validated['consultation_type'],
                'is_healthy'              => $validated['is_healthy'],
                'consultation_date'       => ! empty($validated['attended_at'])
                    ? Carbon::parse($validated['attended_at'])
                    : now(),
                'reason_for_consultation' => $validated['reason_for_consultation'],
                'current_illness'         => $validated['current_illness'],
                'therapeutic_plan'        => $validated['therapeutic_plan'] ?? null,
                'treatment_plan'          => $validated['treatment_plan'] ?? null,
                // Signos vitales — todos nullable
                'blood_pressure'          => $validated['blood_pressure'] ?? null,
                'temperature'             => $validated['temperature'] ?? null,
                'temperature_route'       => $validated['temperature_route'] ?? null,
                'heart_rate'              => $validated['heart_rate'] ?? null,
                'respiratory_rate'        => $validated['respiratory_rate'] ?? null,
                'oxygen_saturation'       => $validated['oxygen_saturation'] ?? null,
                'weight'                  => $validated['weight'] ?? null,
                'height'                  => $validated['height'] ?? null,
                'physical_examination'    => $validated['physical_examination'] ?? null,
                'complementary_studies'   => $validated['complementary_studies'] ?? null,
                'epicrisis'               => $validated['epicrisis'] ?? null,
            ]);

            // ── 2. Examen Funcional por Aparatos y Sistemas ───────────────
            $consultation->functionalExam()->create($validated['functional_exam']);

            // ── 3. Examen Físico Estructurado (17 secciones JSON) ─────────
            if (!empty($validated['physical_exam'])) {
                $consultation->physicalExam()->create(
                    array_merge(
                        $validated['physical_exam'],
                        ['consultation_id' => $consultation->id]
                    )
                );
            }

            // ── 4. Referencias / Contra-referencias (opcional) ────────────
            if (!empty($validated['referrals'])) {
                $consultation->referrals()->createMany($validated['referrals']);
            }

            // ── 5. Diagnósticos SIS (mínimo 1) ───────────────────────────
            foreach ($validated['diagnoses'] as $diag) {
                $consultation->sisDiagnoses()->create($diag);
            }

            DB::commit();

            return redirect()
                ->route('patients.show', $patient->id)
                ->with('success', 'Consulta registrada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar consulta: ' . $e->getMessage(), [
                'patient_id' => $patient->id,
                'user_id'    => Auth::id(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return back()->withErrors([
                'error' => 'No se pudo guardar la consulta: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Historial cronológico completo de consultas del paciente.
     * GET /patients/{patient}/consultations/history
     */
    public function showHistory(Patient $patient): Response
    {
        $patient->load(['occupation:id,name', 'maritalStatus:id,name']);

        $consultations = $patient->consultations()
            ->with([
                'doctor:id,name',
                'sisDiagnoses.sisDiagnosis:id,code,name',
                'sisDiagnoses.medicalConduct:id,name',
                'referrals',
                'functionalExam',
            ])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Consultations/History', [
            'patient'       => $patient,
            'consultations' => $consultations,
        ]);
    }

    /**
     * Detalle completo de una consulta individual (solo lectura).
     * GET /consultations/detail/{consultation}
     */
    public function show(Consultation $consultation): Response
    {
        $consultation->load([
            'patient:id,full_name,id_number,nationality,birth_date,gender,phone_number',
            'doctor:id,name',
            'sisDiagnoses.sisDiagnosis:id,code,name',
            'sisDiagnoses.medicalConduct:id,name',
            'referrals',
            'functionalExam',
            'physicalExam',   // ← nueva carga del examen físico estructurado
        ]);

        return Inertia::render('Consultations/Show', [
            'consultation' => $consultation,
        ]);
    }

    /**
     * Formulario para editar consulta existente.
     * GET /patients/{patient}/consultations/{consultation}/edit
     */
    public function edit(Patient $patient, Consultation $consultation): Response
    {
        // Cargar datos de la consulta existente con todas sus relaciones
        $consultation->load([
            'functionalExam',
            'physicalExam',
            'sisDiagnoses.medicalConduct',
            'referrals',
        ]);

        return Inertia::render('Consultations/Create', [
            'patient'          => $patient->load('occupation'),
            'consultation'     => $consultation,
            'mode'             => 'edit',
            'age_at_moment'    => Carbon::parse($patient->birth_date)->age,
            'diagnosesCatalog' => SisDiagnosis::select('id', 'code', 'name')->orderBy('code')->get(),
            'medicalConducts'  => MedicalConduct::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    /**
     * Actualizar consulta existente (ATÓMICA).
     * PUT /patients/{patient}/consultations/{consultation}
     */
    public function update(UpdateConsultationRequest $request, Patient $patient, Consultation $consultation): RedirectResponse
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // ── 1. Actualizar registro principal ────────────────────────
            $consultation->update([
                'reason_for_consultation' => $validated['reason_for_consultation'],
                'current_illness'         => $validated['current_illness'],
                'consultation_type'       => $validated['consultation_type'],
                'is_healthy'              => $validated['is_healthy'],
                'therapeutic_plan'        => $validated['therapeutic_plan'] ?? null,
                'treatment_plan'          => $validated['treatment_plan'] ?? null,
                'blood_pressure'          => $validated['blood_pressure'] ?? null,
                'temperature'             => $validated['temperature'] ?? null,
                'temperature_route'       => $validated['temperature_route'] ?? null,
                'heart_rate'              => $validated['heart_rate'] ?? null,
                'respiratory_rate'        => $validated['respiratory_rate'] ?? null,
                'oxygen_saturation'       => $validated['oxygen_saturation'] ?? null,
                'weight'                  => $validated['weight'] ?? null,
                'height'                  => $validated['height'] ?? null,
                'physical_examination'    => $validated['physical_examination'] ?? null,
                'complementary_studies'   => $validated['complementary_studies'] ?? null,
                'epicrisis'               => $validated['epicrisis'] ?? null,
            ]);

            // ── 2. Actualizar examen funcional ─────────────────────────
            if ($consultation->functionalExam()->exists()) {
                $consultation->functionalExam()->update($validated['functional_exam']);
            } else {
                $consultation->functionalExam()->create($validated['functional_exam']);
            }

            // ── 3. Actualizar examen físico ────────────────────────────
            if (!empty($validated['physical_exam'])) {
                if ($consultation->physicalExam()->exists()) {
                    $consultation->physicalExam()->update($validated['physical_exam']);
                } else {
                    $consultation->physicalExam()->create(
                        array_merge(
                            $validated['physical_exam'],
                            ['consultation_id' => $consultation->id]
                        )
                    );
                }
            }

            // ── 4. Actualizar referencias (borrar y recrear) ────────────
            $consultation->referrals()->delete();
            if (!empty($validated['referrals'])) {
                $consultation->referrals()->createMany($validated['referrals']);
            }

            // ── 5. Actualizar diagnósticos (borrar y recrear) ──────────
            $consultation->sisDiagnoses()->delete();
            foreach ($validated['diagnoses'] as $diag) {
                $consultation->sisDiagnoses()->create($diag);
            }

            DB::commit();

            return redirect()
                ->route('patients.show', $patient->id)
                ->with('success', 'Consulta actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando consulta: ' . $e->getMessage(), [
                'consultation_id' => $consultation->id,
                'patient_id'      => $patient->id,
                'user_id'         => Auth::id(),
                'trace'           => $e->getTraceAsString(),
            ]);

            return back()->withErrors([
                'error' => 'No se pudo actualizar la consulta: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Eliminar consulta de forma segura (en cascada).
     * DELETE /patients/{patient}/consultations/{consultation}
     */
    public function destroy(Patient $patient, Consultation $consultation): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Eliminar en cascada siguiendo el orden de dependencias
            $consultation->referrals()->delete();
            $consultation->sisDiagnoses()->delete();
            $consultation->functionalExam()->delete();
            $consultation->physicalExam()->delete();

            // Finalmente eliminar la consulta principal
            $consultation->delete();

            DB::commit();

            return redirect()
                ->route('patients.show', $patient->id)
                ->with('success', 'Consulta eliminada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error eliminando consulta: ' . $e->getMessage(), [
                'consultation_id' => $consultation->id,
                'patient_id'      => $patient->id,
                'user_id'         => Auth::id(),
            ]);

            return back()->withErrors([
                'error' => 'No se pudo eliminar la consulta: ' . $e->getMessage(),
            ]);
        }
    }
}
