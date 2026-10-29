<?php

namespace App\Policies;

use App\Models\PagamentoServidor;
use App\Models\User;

/** Somente o administrador mantém os pagamentos a servidores (dados do RH). */
class PagamentoServidorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, PagamentoServidor $pagamento): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, PagamentoServidor $pagamento): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, PagamentoServidor $pagamento): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
