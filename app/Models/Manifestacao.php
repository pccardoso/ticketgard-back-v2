<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Manifestacao extends Model
{

    const CREATED_AT = 'data_cadastro_manifestacoes';
    const UPDATED_AT = 'data_atualizado_manifestacoes';
    
    protected $fillable = [
        'id_manifestacoes',
        'tipo_manifestacoes',
        'descricao_manifestacoes',
        'id_user_manifestacoes',
        'id_chamado_manifestacoes',
        'anexo_manifestacoes',
        'id_response'
    ];

    protected $table = "manifestacoes";
    protected $primaryKey = 'id_manifestacoes';

    protected $casts = [
        "id_response" => "integer"
    ];

    public function user(){
        return $this->belongsTo(User::class, 'id_user_manifestacoes', 'id_users');
    }
}
