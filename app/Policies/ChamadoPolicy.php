<?php

namespace App\Policies;

use App\Models\Chamado;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChamadoPolicy
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
        $departmentUser = json_decode($user->lista_departamento_users, true) ?? [];

        $userChamado =  $chamado->id_user_chamados === $user->id_users ||
                        $chamado->id_criador_chamados === $user->id_users;

        $userLeader = 
            (int) $user->tipo === 2 &&
            in_array(
                (int) $chamado->id_departamento_chamados,
                array_map('intval', $departmentUser)
            );

        return $userChamado || $userLeader;
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
    public function update(User $user, Chamado $chamado): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Chamado $chamado): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Chamado $chamado): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Chamado $chamado): bool
    {
        return false;
    }
}
