<?php

namespace App\Policies;

use App\Models\Manifestacao;
use App\Models\Chamado;
use App\Models\User;
use Illuminate\Auth\Access\Response;


class ManifestacaoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Chamado $chamado): bool
    {
        return  $chamado->id_user_chamados === $user->id_users ||
                $chamado->id_criador_chamados === $user->id_users;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Manifestacao $manifestacao): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Manifestacao $manifestacao): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Manifestacao $manifestacao): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Manifestacao $manifestacao): bool
    {
        return false;
    }
}
