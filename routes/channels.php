<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use App\Models\Chamado;

Broadcast::routes([
    'middleware' => ['auth:sanctum'],
]);

Broadcast::channel('department.{id}', function ($user, $id) {
    $departamentos = json_decode($user->lista_departamento_users, true);

    if (!is_array($departamentos)) {
        return false;
    }
    return in_array((int) $id, array_map('intval', $departamentos));
});

Broadcast::channel('ticket.{id_ticket}', function($user, $id_ticket){

    $departmentUser = json_decode($user->lista_departamento_users, true) ?? [];

    return Chamado::where('id_chamados', $id_ticket)
        ->where(function ($q) use ($user, $departmentUser) {

            // Pode acessar se for o responsável ou criador
            $q->where('id_user_chamados', $user->id_users)
            ->orWhere('id_criador_chamados', $user->id_users);

            // OU se for tipo 2 e o departamento bater
            if ((int) $user->tipo === 2 && !empty($departmentUser)) {
                $q->orWhereIn('id_departamento_chamados', $departmentUser);
            }

        })
        ->exists();
});

Broadcast::channel('user.{id_user}', function($user, $id_user){
    return (int) $user->id_users === (int) $id_user;
});