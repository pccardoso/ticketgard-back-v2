<?php

namespace App\Http\Controllers;

use App\Models\Solicitacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SolicitacaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $listaSolicitacoes = DB::table("solicitacoes")->join("departamentos", "solicitacoes.id_departamento_solicitacoes", "=", "departamentos.id_departamentos")->get();

        return response()->json([
            'message' => 'Resultados encontrados!',
            'data' => $listaSolicitacoes,
            'status' => 200
        ], 200);
    }

    public function consultar(){
        $lista = DB::table("solicitacoes")->join("departamentos", "solicitacoes.id_departamento_solicitacoes", "=", "departamentos.id_departamentos")->get();
        return compact("lista");
    }

    public function listar($id=null){
        sleep(seconds: 1);
        $lista = Solicitacao::find($id);
        return compact("lista");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $result = Solicitacao::create($request->validate([
            "id_departamento_solicitacoes" => ['required'],
            "titulo_solicitacoes" => ["required", "min:5"],
            "prioridade_solicitacoes" => ['required'],
            "tempo_solicitacoes" => ['required']
        ]));

        return response()->json([
            'message' => 'Tipo de solicitação criado com sucesso!',
            'status' => 200,
            'data' => $result
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
    public function update(Request $request)
    {
        $dep = Solicitacao::find($request->input("id_solicitacoes"));
        $dep->titulo_solicitacoes = $request->input("titulo_solicitacoes");
        $dep->prioridade_solicitacoes = $request->input("prioridade_solicitacoes");
        $dep->tempo_solicitacoes = $request->input("tempo_solicitacoes");
        $dep->save();
        return to_route("con.solicitacao");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $dep = Solicitacao::find($id)->delete();
        return to_route("con.solicitacao");
    }
}
