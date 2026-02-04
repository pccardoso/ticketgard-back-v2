<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Manifestacao;
use App\Models\Notificacao;
use App\Models\Chamado;
use App\Events\MessageEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ManifestacaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function consultar($id){

        $chamado = Chamado::find($id);

        if(!$chamado){
            return response()->json([
                'message' => 'Ticket não encontrado!',
                'data' => [],
                'status' => 404
            ], 404);
        }

        Gate::authorize('view', $chamado);

        $manifestacoes = DB::select("select * from manifestacoes left join users on users.id_users=manifestacoes.id_user_manifestacoes inner join chamados on manifestacoes.id_chamado_manifestacoes=chamados.id_chamados where id_chamado_manifestacoes = ? ORDER BY manifestacoes.data_cadastro_manifestacoes ", [$id]);
        
        return response()->json([
            "messsage" => "Mensagens encontradas",
            "data" => $manifestacoes,
            "status" => 200
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $path = "";

        // Processamento do arquivo
        if ($request->hasFile('anexo_manifestacoes')) {
            $arq = $request->file('anexo_manifestacoes');
            $nomeArquivo = time().'_'.$arq->getClientOriginalName();
            $path = $arq->move(public_path('uploads'), $nomeArquivo);

            $path = "/uploads/$nomeArquivo";
        }

        $manifest = Manifestacao::create([
            "tipo_manifestacoes" => 1,
            "descricao_manifestacoes" => $request->input("descricao_manifestacoes"),
            "id_chamado_manifestacoes" => $request->input("id_chamados"),
            "id_user_manifestacoes" => Auth::user()->id_users,
            "anexo_manifestacoes" => $path
        ]);

        event(new MessageEvent(
            $manifest,
            (int) $request->input("id_chamados")
        ));

        $notify = Notificacao::create([
            "descricao_notificacao" => Auth::user()->name." nova mensagem no ticket de Nº".$request->input("id_chamados"),
            "tipo_notificacao" => 1,
            "id_manifestacao_notificacao" => $manifest->id_manifestacoes
        ]);

        return response()->json([
            "message" => "Mensagem enviada com sucesso!",
            "data" => $manifest,
            "status" => 200
        ], 200);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
