<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use App\Models\Chamado;

Broadcast::routes([
    'middleware' => ['auth:sanctum'],
]);

Broadcast::channel('Department.{id}', function ($user, $id) {
    $departamentos = json_decode($user->lista_departamento_users, true);

    if (!is_array($departamentos)) {
        return false;
    }
    return in_array((int) $id, array_map('intval', $departamentos));
});

Broadcast::channel('Ticket.{id}', function($user, $id_ticket){

    return Chamado::where('id_chamados', $id_ticket)
    ->where(function ($q) use ($user) {
        $q->where('id_user_chamados', $user->id_users)
          ->orWhere('id_criador_chamados', $user->id_users);
    })
    ->exists();

});