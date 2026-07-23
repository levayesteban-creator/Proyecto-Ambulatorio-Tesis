<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ConsultationPolicy
{
    use HandlesAuthorization;

    private function isAdmin(User $user): bool
    {
        return $user->role_id === Role::ADMIN;
    }

    private function isCoordinator(User $user): bool
    {
        return $user->role_id === Role::COORDINATOR;
    }

    private function isDoctor(User $user): bool
    {
        return $user->role_id === Role::DOCTOR;
    }

    private function isMedicalStaff(User $user): bool
    {
        return in_array($user->role_id, [Role::ADMIN, Role::COORDINATOR, Role::DOCTOR]);
    }

    private function canViewConsultations(User $user): bool
    {
        return in_array($user->role_id, [Role::ADMIN, Role::COORDINATOR, Role::DOCTOR, Role::NURSE]);
    }

    public function viewAny(User $user): Response
    {
        return $this->canViewConsultations($user)
            ? Response::allow()
            : Response::deny('No tiene permiso para ver consultas.');
    }

    public function view(User $user, Consultation $consultation): Response
    {
        return $this->canViewConsultations($user)
            ? Response::allow()
            : Response::deny('No tiene permiso para ver esta consulta.');
    }

    public function create(User $user): Response
    {
        return $this->isMedicalStaff($user)
            ? Response::allow()
            : Response::deny('No tiene permiso para registrar consultas.');
    }

    public function update(User $user, Consultation $consultation): Response
    {
        if (!$this->isMedicalStaff($user)) {
            return Response::deny('No tiene permiso para editar consultas.');
        }

        if ($consultation->patient && $consultation->patient->closed_at) {
            return Response::deny('No se puede modificar consultas de una historia clínica cerrada.');
        }

        return Response::allow();
    }

    public function delete(User $user, Consultation $consultation): Response
    {
        if (!$this->isAdmin($user) && !$this->isCoordinator($user)) {
            return Response::deny('Solo administradores y médicos coordinadores pueden eliminar consultas.');
        }

        if ($consultation->patient && $consultation->patient->closed_at) {
            return Response::deny('No se puede eliminar consultas de una historia clínica cerrada.');
        }

        return Response::allow();
    }
}
