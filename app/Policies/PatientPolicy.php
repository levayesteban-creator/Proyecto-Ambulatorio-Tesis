<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PatientPolicy
{
    use HandlesAuthorization;

    private function isAdmin(User $user): bool
    {
        return $user->role_id === 1;
    }

    private function isCoordinator(User $user): bool
    {
        return $user->role_id === 2;
    }

    private function isDoctor(User $user): bool
    {
        return $user->role_id === 3;
    }

    private function isAdminOrCoordinator(User $user): bool
    {
        return $user->role_id <= 2;
    }

    public function viewAny(User $user): Response
    {
        return Response::allow();
    }

    public function view(User $user, Patient $patient): Response
    {
        return Response::allow();
    }

    public function create(User $user): Response
    {
        return Response::allow();
    }

    public function update(User $user, ?Patient $patient = null): Response
    {
        if (!$this->isAdminOrCoordinator($user)) {
            return Response::deny('No tiene permiso para editar historias clínicas. Contacte al administrador.');
        }

        if ($patient !== null && $patient->closed_at) {
            return Response::deny('Esta historia clínica está cerrada y solo puede ser editada por administradores o médicos coordinadores.');
        }

        return Response::allow();
    }

    public function close(User $user, ?Patient $patient = null): Response
    {
        if ($this->isAdmin($user) || $this->isCoordinator($user) || $this->isDoctor($user)) {
            return Response::allow();
        }

        return Response::deny('No tiene permiso para cerrar historias clínicas.');
    }

    public function reopen(User $user, ?Patient $patient = null): Response
    {
        if ($this->isAdminOrCoordinator($user)) {
            return Response::allow();
        }

        return Response::deny('Solo el administrador y médico coordinador pueden reabrir historias clínicas.');
    }

    public function delete(User $user, ?Patient $patient = null): Response
    {
        if ($this->isAdminOrCoordinator($user)) {
            return Response::allow();
        }

        return Response::deny('No tiene permiso para eliminar historias clínicas.');
    }

    public function restore(User $user, ?Patient $patient = null): Response
    {
        if ($this->isAdminOrCoordinator($user)) {
            return Response::allow();
        }

        return Response::deny('No tiene permiso para restaurar historias clínicas.');
    }

    public function forceDelete(User $user, ?Patient $patient = null): Response
    {
        if ($this->isAdmin($user)) {
            return Response::allow();
        }

        return Response::deny('Solo los administradores pueden eliminar permanentemente historias clínicas.');
    }

    public function viewTrashed(User $user): Response
    {
        if ($this->isAdminOrCoordinator($user)) {
            return Response::allow();
        }

        return Response::deny('No tiene permiso para ver la papelera.');
    }
}
