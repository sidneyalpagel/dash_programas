<?php

namespace App\Policies;

use App\Enums\StatusPrograma;
use App\Models\Programa;
use App\Models\User;

/**
 * Administrador vê e edita tudo. Servidor de secretaria só mexe nos
 * programas da própria secretaria e não publica nem exclui o que já foi publicado.
 */
class ProgramaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Programa $programa): bool
    {
        return $user->isAdmin() || $programa->secretaria_id === $user->secretaria_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Programa $programa): bool
    {
        return $this->view($user, $programa);
    }

    public function delete(User $user, Programa $programa): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $programa->secretaria_id === $user->secretaria_id
            && $programa->status !== StatusPrograma::Publicado
            && $programa->publicado_em === null;
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function publicar(User $user): bool
    {
        return $user->isAdmin();
    }
}
