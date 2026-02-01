<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\ExportPDFController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\SolicitacaoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChamadoController;
use App\Events\TestBroadcastNow;
use App\Http\Controllers\ManifestacaoController;

Route::post('/login', function (Request $request) {

    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

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
});

Route::middleware('auth:sanctum')->group(function () {

    Route::post("/cad/dep", [DepartamentoController::class, "store"]);
    Route::post("/cad/sol", [SolicitacaoController::class, "store"]);
    Route::post("/cad/usu", [UserController::class, "store"]);
    Route::post("/cad/cha", [ChamadoController::class, "store"]);

    Route::get('/con/cha/{id}', [ChamadoController::class, "listar"]);

    Route::match(['get', 'delete'], "/con/departamento", [DepartamentoController::class,"index"])->name("con.departamentos");
    Route::match(['get', 'delete'], "/con/solicitacao", [SolicitacaoController::class,"index"])->name("con.solicitacaos");
    Route::match(['get', 'delete'], "/con/usuario", [UserController::class,"index"])->name("con.usuarios");

    Route::post("/my-ticket",[ChamadoController::class, "index2"]);

    Route::get('/load-chat/{id}', [ManifestacaoController::class, 'consultar']);
    Route::post('/send-message', [ManifestacaoController::class, 'store']);

});