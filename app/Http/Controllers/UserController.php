<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function login(Request $request){

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)
                ->where('status', true)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        return response()->json([
            "message" => "Usuário autenticado com sucesso!",
            "data" => $user,
            "token" => $user->createToken($request->device_name)->plainTextToken
        ]);

    }

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

    public function updateAvatar(Request $request, $id){

        $userCurrent = User::find($id);

        if(!$userCurrent){
            return response()->json([
                "message" => "Usuário não encontrado!",
                "data" => [],
                "status" => 404
            ], 404);
        }

        $dataValidate = $request->validate([
            "avatar" => "required|file|mimes:jpg,png|max:2048"
        ]);

        $arq = $request->file('avatar');
        $nomeArquivo = time().'_'.$arq->getClientOriginalName();
        $path = $arq->move(public_path('uploads'), $nomeArquivo);

        $path = "/uploads/$nomeArquivo";

        $userCurrent->update([
            "avatar_path_users" => $path
        ]);

        return response()->json([
            "message" => "Avatar atualizado com sucesso!",
            "data" => $userCurrent,
            "status" => 200
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

    public function changeNotifications(Request $request)
    {
        $validated = $request->validate([
            'notify_email' => 'boolean|required',
            'notify_popup' => 'boolean|required',
        ]);

        $userCurrent = Auth::user();

        $userCurrent->update($validated);

        return response()->json([
            'status' => 200,
            'message' => 'Configurações atualizadas com sucesso!',
            'data' => $userCurrent->refresh(),
        ], 200);
    }


    public function changePassword(Request $request){

        $dataValidate = $request->validate([
            "password" => "required|string",
            "confirm_password" => "required|string"
        ]);

        $userCurrent = User::find(Auth::user()->id_users);

        if(!$userCurrent){
            return response()->json([
                "message" => "Usuário não encontrado",
                "data" => [],
                "status" => 404
            ]);
        }

        if($request->input('password') !== $request->input('confirm_password')){
            return response()->json([
                "message" => "As senhas informadas diferem, por favor, validar.",
                "status" => 422,
                "data" => []
            ], 422);
        }

        $userCurrent->update([
            "password" => $request->input("confirm_password")
        ]);

        $request->user()->tokens()->delete();
        
        return response()->json([
            "message" => "Credenciais alteradas com sucesso!",
            "data" => $userCurrent,
            "status" => 200
        ], 200); 
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
