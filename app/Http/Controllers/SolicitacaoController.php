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

        
        $lista = Solicitacao::find($id);

        if(!$lista){
            return response()->json([
                'message' => 'Solicitação não encontrada',
                'status' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'Solicitação encontrada',
            'data' => $lista,
            'status' => 200
        ], 200);
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
    public function update(Request $request, $id)
    {

        $dep = Solicitacao::find($id);

        if(!$dep){
            return response()->json([
                'message' => 'Solicitação não encontrada',
                'data' => [],
                'status' => 404
            ], 404);
        }

        $data = $request->validate([
            'id_departamento_solicitacoes' => 'required|integer|exists:departamentos,id_departamentos',
            'titulo_solicitacoes' => 'required|string|min:5',
            'prioridade_solicitacoes' => 'required',
            'tempo_solicitacoes' => 'required|integer'
        ]);

        $dep->update($data);

        return response()->json([
            'message' => 'Tipo de solicitação atualizada com sucesso!',
            'data' => $data,
            'status' => 200
        ], 200);
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
