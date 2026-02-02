<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class DepartamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        $lista = Departamento::with("solicitacoes")->get();

        return response()->json([
            'data' => $lista
        ]);

        //return Inertia::render("System/ConDepartamento", compact("lista"));
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

        Gate::authorize('create', Departamento::class);

        Departamento::create($request->validate([
            "nome_departamentos" => ['required'],
            "descricao_departamentos" => ['required']
        ]));
        sleep(seconds: 1);

        return response()->json(['message' => "Departamento cadastrado com sucesso!"]);
    }

    public function consultar(){
        $lista = Departamento::with('solicitacoes')->get();
        return compact("lista");
    }

    public function listar($id=null){


        $lista = Departamento::find($id);

        if(!$lista){{
            return response()->json([
                'message' => 'Departamento não encontrado!',
                'status' => 404,
                'data' => []
            ], 404);
        }}

        return response()->json([
            'message' => 'Departamento encontrado!',
            'data' => $lista,
            'status' => 200
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
        $dep = Departamento::find($id);

        if(!$dep){
            return response()->json([
                'message' => 'Departamento não encontrado.',
                'status' => 404
            ], 404);
        }

        $validate = $request->validate([
            'nome_departamentos' => 'required|string',
            'descricao_departamentos' => 'required|string'
        ]);

        $dep->update($validate);

        return response()->json([
            'message' => 'Departamento atualizado com sucesso!',
            'data' => $dep,
            'status' => 200
        ], 200);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $dep = Departamento::find($id)->delete();
        return to_route("con.departamento");
    }
}
