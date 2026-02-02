<?php

use App\Http\Controllers\ChamadoController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SolicitacaoController;
use App\Http\Controllers\ManifestacaoController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GraficoController;
use App\Http\Controllers\PesquisaCursoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Jobs\TestQueueJob;
use App\Jobs\NotificationDepartamentJob;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ExportPDFController;
use Illuminate\Support\Facades\Broadcast;


Route::get('/', function () {
    return view('welcome');
})->withoutMiddleware('auth');

//ROTAS DESPROTEGIDAS
Route::post("/teste",[ChamadoController::class, "index2"]);
Route::get("/teste",[ChamadoController::class, "index2"]);

Route::post("/validar/login", [LoginController::class, "login"]);

Route::get('/despro', function(){
    return response()->json(['Oie']);
});

//ROTA PARA LOGIN (DESPROTEGIDA)
Route::match(["get", "post"],"/login", function(){
    return Inertia::render("Login/Login");
})->name("login");

Route::get("/pp", [ChamadoController::class, "export"]);

// ROTAS PROTEGIDAS
Route::middleware('auth')->group(function () {

    Route::post("/export-pdf", [ExportPDFController::class, "create"]);

    Route::post("/send/notification", [NotificationController::class, "sendTestNotification"]);
    Route::post("/send/token", [NotificationController::class, "saveToken"]);

    Route::match(['post','get'],"/", function(){
        return Inertia::render("Home");
    })->name("home");

    Route::get("/cad/departamento", function(){
        return Inertia::render("System/CadDepartamento");
    })->name("form.cad.departamento");

    Route::get("/relatorio", function(){
        return Inertia::render("System/Relatorio");
    })->name("relatorio");

    Route::get("/cad/solicitacao", function(){
        return Inertia::render("System/CadSolicitacao");
    })->name("form.cad.solicitacao");

    Route::get("/edi/notify", function(){
        return Inertia::render("System/Configuracao");
    })->name("form.cad.configuracao");

    Route::get("/cad/chamado", function(){
        return Inertia::render("System/CadChamado");
    })->name("form.cad.chamado");

    Route::get("/cad/usuario", function(){
        return Inertia::render("System/CadUsuario");
    })->name("form.cad.usuario");

    Route::get("/edi/usuario", function(){
        return Inertia::render("System/EdiUsuario");
    })->name("form.edi.usuario");

    Route::get("/edi/password", function(){
        return Inertia::render("System/EdiPassword");
    })->name("form.edi.password");

    Route::get("/lis/chamado/{id}", [ChamadoController::class,"listar"]);

    Route::post("/pes/departamento/{id}", [DepartamentoController::class, "listar"]);
    Route::post("/pes/solicitacao/{id}", [SolicitacaoController::class, "listar"]);
    Route::post("/pes/usuario/{id}", [UserController::class, "listar"]);

    // ROTAS PARA CONSULTA
    Route::match(['get', 'delete'], "/con/departamento", [DepartamentoController::class,"index"])->name("con.departamento");
    Route::match(['get', 'delete'], "/con/solicitacao", [SolicitacaoController::class,"index"])->name("con.solicitacao");
    Route::match(['get', 'delete'], "/con/usuario", [UserController::class,"index"])->name("con.usuario");
    Route::match(['get', 'delete', 'post'], "/con/chamado", function(){
        return Inertia::render("System/ConChamado");
    })->name("con.chamados");
    Route::match(['get', 'delete', 'post'], '/meuchamado', function(){
        return Inertia::render("System/MeuChamado");
    })->name("meuchamado");
    Route::post("/con/pendencia/{id}", [ChamadoController::class, "listarPendencia"]);
    Route::get("/con/user/{id}", [UserController::class, "show"]);

    // ROTAS PARA CADASTROS
    Route::post("/cad/dep", [DepartamentoController::class, "store"]);
    Route::post("/cad/sol", [SolicitacaoController::class, "store"]);
    Route::post('/cad/cha', [ChamadoController::class, "store"]);
    Route::post('/cad/usu', [UserController::class, "store"]);
    Route::post('/cad/man', [ManifestacaoController::class, "store"]);
    Route::post("/cad/usuario", [UserController::class, "store"]);
    Route::post("/cad/pesquisa", [PesquisaCursoController::class, "store"]);

    Route::post("/lis/departamento", [DepartamentoController::class, "consultar"]);
    Route::post('/lis/solicitacao', [SolicitacaoController::class, "consultar"]);
    Route::post("/lis/usuario", [UserController::class, "consultar"]);
    Route::post("/lis/usuario/{id_departamento}", [UserController::class, "listarPorDepart"]);
    Route::post("/lis/manifestacoes/{id}", [ManifestacaoController::class, "consultar"]);
    Route::post("/lis/anexos/{id}", [ChamadoController::class, "listarAnexos"]);
    Route::get("/val/pesquisa/{id}", [PesquisaCursoController::class, "show"]);

    // ROTAS PARA ALTERAR OS STATUS DOS ATENDIMENTOS
    Route::post("/upd/cha", [ChamadoController::class, "update"]);
    Route::post("/enc/cha", [ChamadoController::class, "encaminhar"]);
    Route::post("/exe/cha", [ChamadoController::class, "executar"]);
    Route::post("/int/cha", [ChamadoController::class, "interromper"]);
    Route::post("/fin/cha", [ChamadoController::class, "finalizar"]);
    Route::post("/adr/cha", [ChamadoController::class, "assumir"]);

    // ROTAS PARA DELETAR REGISTROS
    Route::delete("/del/dep/{id}", [DepartamentoController::class,"destroy"]);
    Route::delete("/del/sol/{id}", [SolicitacaoController::class,"destroy"]);
    Route::delete("/del/usu/{id}", [UserController::class,"destroy"]);
    Route::delete("/del/cha/{id}", [ChamadoController::class,"destroy"]);
    Route::get("/history/{id}", [ChamadoController::class, "history"]);

    //ROTAS PARA ATUALIZAR REGISTRO (UPDATES)

    Route::post("/upd/dep", [DepartamentoController::class, "update"]);
    Route::post("/upd/sol", [SolicitacaoController::class, "update"]);
    Route::post("/upd/usu", [UserController::class, "update"]);
    Route::post("/upd/cha", [ChamadoController::class, "update"]);
    Route::post("/salvar/senha", [UserController::class, "alterarsenha"]);
    Route::post("/upd/notify", [UserController::class, "updateConfig"]);

    // ROTAS PARA GERENCIAR LOGIN
    Route::get("/logout", [LoginController::class, "logout"]);

    // ROTAS DE UPLOAD
    Route::post("/upload", [FileController::class, "store"]);

    // ROTAS DE RELATÓRIOS/DADOS
    Route::post("/count/chamado", [ChamadoController::class, "getCountTicket"]);
    Route::post("/history/notification", [NotificacaoController::class, "showNotificationNow"]);

    //ROTAS DE GRÁFISCOS
    Route::get("/charts/count", [GraficoController::class, "countFullChamados"]);

});