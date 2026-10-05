<?php

namespace App\Policies;

use App\Models\User;

/** Somente o administrador gerencia usuários, e não pode excluir a própria conta. */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $alvo): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $alvo): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, User $alvo): bool
    {
        return $user->isAdmin() && $user->isNot($alvo);
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
