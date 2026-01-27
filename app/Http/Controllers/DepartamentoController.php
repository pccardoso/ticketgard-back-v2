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
        sleep(seconds: 1);
        $lista = Departamento::find($id);
        return compact("lista");
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
        $dep = Departamento::find($request->input("id_departamentos"));
        $dep->nome_departamentos = $request->input("nome_departamentos");
        $dep->descricao_departamentos = $request->input("descricao_departamentos");
        $dep->save();
        return to_route("con.departamento");
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
