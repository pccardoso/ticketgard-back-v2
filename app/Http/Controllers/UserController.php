<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $listaUsuarios = DB::table("users")->get();
        //return Inertia::render("System/ConUsuario", compact("listaUsuarios"));

        return response()->json([
            'message' => "Lista de usuários encontrados",
            'data' => $listaUsuarios,
            'status' => 200,
        ], 200);
    }

    public function consultar(){
        $lista = User::all();
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

        $request->validate([
            "name" => "required|string",
            "tipo" => "required|string",
            "email" => "required|string|email",
            "password" => "required|string",
            "administrador" => "required|integer",
            "res_chamados" => "required|integer",
            "lista_departamento_users" => "required",
            "vip" => "required|integer"
        ]);

        $userCreated = User::create([
            "name" => $request->input("name"),
            "tipo" => $request->input("tipo"),
            "email" => $request->input("email"),
            "password" => $request->input("password"),
            "administrador" => $request->input("administrador"),
            "res_chamados" => $request->input("res_chamados"),
            "lista_departamento_users" => json_encode($request->input("lista_departamento_users")),
            "vip" => $request->input("vip")
        ]);

        return response()->json([
            'message' => 'Usuário criado com sucesso!',
            'status' => 200,
            'data' => $userCreated
        ], 200);
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $result = User::find($id);

        if(!$result){
            return response()->json([
                "status" => 200,
                "message" => "Usuário não encontrado",
                "data" => $result
            ], 200);
        }

        return response()->json([
            "status" => 200,
            "message" => "Usuário encontrado!",
            "data" => $result
        ], 200);
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
        $dep = User::find($id);

        if(!$dep){
            return response()->json([
                'message' => 'Usuário não encontrado.',
                'data' => [],
                'status' => 404
            ], 404);
        }

        $dataValidate = $request->validate([
            "name" => "required|string",
            "tipo" => "required|string",
            "email" => "required|string|email",
            "administrador" => "required|integer",
            "res_chamados" => "required|integer",
            "lista_departamento_users" => "required",
            "vip" => "required|integer"
        ]);

        $dep->update($dataValidate);

        DB::statement("UPDATE chamados SET nome_criador_chamados='".$request->input("name")."', vip_criador_chamados=".$request->input("vip")." WHERE id_criador_chamados=".$id);

        return response()->json([
            'message' => 'Usuário atualizado com sucesso!',
            'data' => $dep,
            'status' => 200
        ], 200);
        
    }

    public function updateConfig(Request $request)
    {
        $validated = $request->validate([
            'notify_email' => 'boolean|required',
            'notify_popup' => 'boolean|required',
        ]);

        $user = Auth::user();

        $user->update($validated);

        return response()->json([
            'status' => 200,
            'message' => 'Configurações atualizadas com sucesso!',
            'data' => $user->refresh(),
        ], 200);
    }


    public function alterarsenha(Request $request){
        $dep = User::find($request->input("id_users"));
        $dep->password = $request->input("senha2");
        $dep->save();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect("/login");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $dep = User::find($id)->delete();
        return to_route("con.usuario");
    }

    public function listar($id=null){


        $lista = User::find($id);

        if(!$lista){
            return response()->json([
                'message' => 'Usuário não encontrado!',
                'data' => [],
                'status' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'Usuário encontrado com sucesso!',
            'data' => $lista,
            'status' => 200
        ], 200);

        return compact("lista");
    }
}
