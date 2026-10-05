<?php

namespace App\Policies;

use App\Models\Secretaria;
use App\Models\User;

/** Somente o administrador gerencia secretarias; secretaria com programas não pode ser excluída. */
class SecretariaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Secretaria $secretaria): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Secretaria $secretaria): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Secretaria $secretaria): bool
    {
        return $user->isAdmin() && ! $secretaria->programas()->exists();
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
