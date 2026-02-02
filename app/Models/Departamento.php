<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    //

    const CREATED_AT = 'data_cadastro_departamentos';
    const UPDATED_AT = 'data_atualizado_departamentos';

    protected $fillable = [
        'id_departamentos',
        'nome_departamentos',
        'descricao_departamentos'
    ];

    protected $table = 'departamentos';

    protected $primaryKey = 'id_departamentos';

    public function solicitacoes()
    {
        return $this->hasMany(Solicitacao::class, "id_departamento_solicitacoes", "id_departamentos");
    }

}
