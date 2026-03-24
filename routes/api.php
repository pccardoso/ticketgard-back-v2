<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportPDFController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\SolicitacaoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChamadoController;
use App\Http\Controllers\ManifestacaoController;
use App\Http\Controllers\NotificationController;

use App\Http\Controllers\OCRController;

use Illuminate\Support\Collection;

Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    //ROTAS DE CADASTRO
    Route::post("/cad/dep", [DepartamentoController::class, "store"]);
    Route::post("/cad/sol", [SolicitacaoController::class, "store"]);
    Route::post("/cad/usu", [UserController::class, "store"]);
    Route::post("/cad/cha", [ChamadoController::class, "store"]);

    Route::get('/con/cha/{id}', [ChamadoController::class, "listar"]);

    //ROTAS DE LISTAGENS/DELETES
    Route::match(['get', 'delete'], "/con/departamento", [DepartamentoController::class,"index"])->name("con.departamentos");
    Route::match(['get', 'delete'], "/con/solicitacao", [SolicitacaoController::class,"index"])->name("con.solicitacaos");
    Route::match(['get', 'delete'], "/con/usuario", [UserController::class,"index"])->name("con.usuarios");

    //ROTAS DE CONSULTAS
    Route::get("/pes/departamento/{id}", [DepartamentoController::class, "listar"]);
    Route::get("/pes/solicitacao/{id}", [SolicitacaoController::class, "listar"]);
    Route::get("/pes/usuario/{id}", [UserController::class, "listar"]);
    Route::get("/pes/ticket/{id}", [ChamadoController::class, "listar"]);

    //ROTAS DE UPDATE
    Route::put("/upd/dep/{id}", [DepartamentoController::class, "update"]);
    Route::put("/upd/sol/{id}", [SolicitacaoController::class, "update"]);
    Route::put("/upd/use/{id}", [UserController::class, "update"]);
    Route::put("/upd/tic/{id}", [ChamadoController::class, "update"]);
    Route::post("/upd/avatar/{id}", [UserController::class, "updateAvatar"]);
    Route::post("/upd/password", [UserController::class, "changePassword"]);
    Route::post('/upd/notifications', [UserController::class, 'changeNotifications']);

    //ROTAS DE EXCLUSÃO
    Route::delete("/ticket/{id}", [ChamadoController::class, "destroy"]);

    Route::post("/my-ticket",[ChamadoController::class, "getTicketUser"]);
    Route::post("/my-department", [ChamadoController::class, "getTicketDepartmens"]);

    Route::get('/load-chat/{id}', [ManifestacaoController::class, 'consultar']);
    Route::post('/send-message', [ManifestacaoController::class, 'store']);


    //ROTAS PARA ADMINISTRAR O TICKET
    Route::prefix('ticket')->group(function (){
        Route::post('/to-forward/{id}', [ChamadoController::class, 'toForward']);
        Route::get('/to-execute/{id}', [ChamadoController::class, 'toExecute']);
        Route::get('/to-assume/{id}', [ChamadoController::class, 'toAssume']);
        Route::post('/to-finish/{id}', [ChamadoController::class, 'toFinish']);
        Route::post('/to-interrupt/{id}', [ChamadoController::class, 'toInterrupt']);
    });

    //ROTAS DO TOKEN FIREBASE
    Route::post("/send/token", [NotificationController::class, "saveToken"]);

    Route::get('/tes', [ChamadoController::class, 'testeSQL']);

    Route::get('/testeocr', [OCRController::class, "getData"]);

});