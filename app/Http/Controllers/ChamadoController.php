<?php

namespace App\Http\Controllers;

use App\Models\Chamado;
use App\Models\File;
use App\Models\Manifestacao;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Notificacao;
use App\Jobs\NotificationDepartamentJob;
use App\Http\Controllers\ExportPDFController;
use Illuminate\Support\Facades\Log;
use App\Events\TestBroadcastNow;
use Illuminate\Support\Facades\Gate;

use App\Jobs\TesteJob;

class ChamadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected $pdf;

    public function __construct (ExportPDFController $pdf){
        $this->pdf = $pdf;
    }
    
    public function dataen($data){

        return explode("/", $data)[2]."-".explode("/", $data)[1]."-".explode("/", $data)[0];

    }

    public function getTicketUser(Request $request)
    {

        $dataTicket = Chamado::with(
            'departamento',
            'solicitacao',
            'user'
        )
        ->orderByDesc('id_chamados')
        ->ticketUser()
        ->get();
        
        return response()->json([
            "message" => "Registros ".count($dataTicket)." encontrados",
            "data" => $dataTicket,
            "status" => 200
        ], 200);
    }

    public function getTicketDepartmens(Request $request){

        try{

            $query = Chamado::with([
                'departamento',
                'solicitacao',
                'user',
                'user_create'
            ])
            ->orderByDesc('id_chamados')
            ->ticketDepartment()

            ->when($request->assunto, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('assunto_chamados', 'like', "%{$request->assunto}%")
                        ->orWhere('descricao_chamados', 'like', "%{$request->assunto}%");
                });
            })

            ->when($request->situacao, fn($q) => $q->whereIn('status_chamados', $request->situacao))

            ->when($request->id_solicitacao, fn($q) => 
                $q->whereIn('id_solicitacao_chamados', $request->id_solicitacao)
            )

            ->when($request->prioridade, fn($q) => 
                $q->whereHas('solicitacao', fn($sub) => 
                    $sub->whereIn('prioridade_solicitacoes', $request->prioridade)
                )
            )

            ->when($request->id_user_respond, fn($q) => 
                $q->whereIn('id_user_chamados', $request->id_user_respond)
            )

            ->when($request->id_departamento, fn($q) => 
                $q->whereIn('id_departamento_chamados', $request->id_departamento)
            )

            ->when($request->id_user_create, fn($q) => 
                $q->whereIn('id_criador_chamados', $request->id_user_create)
            )

            ->when($request->codigo, fn($q) => 
                $q->where('id_chamados', $request->codigo)
            )

            ->when($request->inicio || $request->fim, function ($q) use ($request) {

                $inicio = $request->inicio
                    ? $request->inicio . " 00:00:00"
                    : '0000-00-00 00:00:00';

                $fim = $request->fim
                    ? $request->fim . " 23:59:59"
                    : now()->format('Y-m-d') . " 23:59:59";

                $q->whereBetween('data_cadastro_chamados', [$inicio, $fim]);
            });

            $dataTicket = $query->get();

            return response()->json([
                "message" => "Registros ".$dataTicket->count()." encontrados",
                "data" => $dataTicket,
                "status" => 200
            ], 200);

        }catch(\Throwable $erro){

            return response()->json([
                "message" => $erro->getMessage()
            ]);

        }

    }

    public function meuchamado(){
        $lista = DB::table("chamados")
        ->join("departamentos", "chamados.id_departamento_chamados", "=","departamentos.id_departamentos")
        ->join("solicitacoes", "chamados.id_solicitacao_chamados", "=", "solicitacoes.id_solicitacoes")
        ->leftJoin("users", "chamados.id_user_chamados", "=", "users.id_users")
        ->get();
        return Inertia::render("System/MeuChamado", compact("lista"));
    }

    public function listarAnexos(int $id_chamado){
        $lista = DB::select("SELECT * FROM file WHERE id_chamado_file=$id_chamado");
        return compact("lista");
    }

    public function listarPendencia($id_chamado){
        $lista = DB::select("SELECT * FROM manifestacoes WHERE id_chamado_manifestacoes=$id_chamado && tipo_manifestacoes=3 ORDER BY data_cadastro_manifestacoes DESC");
        sleep(1);
        return compact("lista");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function store(Request $request)
    {

        try{
            // Valida o formulário principal
            $request->validate([
                "assunto_chamados" => ['required', 'string', 'min:3'],
                "id_departamento_chamados" => ['required'],
                "id_solicitacao_chamados" => ['required'],
                "descricao_chamados" => ['required', 'string', 'min:3'],
                "id_criador_chamados" => ['nullable'],
                "nome_criador_chamados" => ['nullable'],
                "vip_criador_chamados" => ['nullable']
            ]);

            $idUser = Auth::user()->id_users;
            $nameUser = Auth::user()->name;
            $vipUser = Auth::user()->vip;

            //Cria o chamado
            $chamado = Chamado::create([
                "assunto_chamados" => ucfirst(strtolower($request->input("assunto_chamados"))),
                "id_departamento_chamados" => $request->input("id_departamento_chamados"),
                "id_solicitacao_chamados" => $request->input("id_solicitacao_chamados"),
                "descricao_chamados" => $request->input("descricao_chamados"),
                "id_criador_chamados" => $idUser,
                "nome_criador_chamados" => $nameUser,
                "file" => "",
                "vip_criador_chamados" => $vipUser
            ]);

            Log::info($request);

            //Se o chamado foi criado
            if($chamado->id_chamados){

                /*if ($request->hasFile('file')) {
                    $arq = $request->file('file');
                    $nomeArquivo = time().'_'.$arq->getClientOriginalName();
                    $path = $arq->move(public_path('uploads'), $nomeArquivo);

                    $path = "/uploads/$nomeArquivo";
                }*/

                TestBroadcastNow::dispatch("Novo Ticket aberto. Cód: ".$chamado->id_chamados, $request->input("id_departamento_chamados"));

                //se houver anexos
                if($request->hasFile("file")){

                    //vasculha a lista de anexos
                    foreach ($request->file("file") as $key => $value) {
                        
                        $arq = $value;
                        $nomeArquivo = time().'_'.$arq->getClientOriginalName();
                        $path = $arq->move(public_path('uploads'), $nomeArquivo);

                        $path = "/uploads/$nomeArquivo";

                        //cadastra no banco o anexo
                        File::create([
                            "caminho_file" => $path,
                            "id_chamado_file" => $chamado->id_chamados
                        ]);

                        //Realiza o processamento do OCR para cada arquivo enviado, extraindo o texto e exibindo no log
                        //TesteJob::dispatch(public_path($path));

                    }

                }

                $manifest = Manifestacao::create([
                    "tipo_manifestacoes" => 0,
                    "descricao_manifestacoes" => Auth::user()->name." abriu o ticket.",
                    "id_chamado_manifestacoes" => $chamado->id_chamados
                ]);

                $notify = Notificacao::create([
                    "descricao_notificacao" => Auth::user()->name." abriu o ticket de Nª ".$chamado->id_chamados,
                    "tipo_notificacao" => 0,
                    "id_manifestacao_notificacao" => $manifest->id_manifestacoes
                ]);

                NotificationDepartamentJob::dispatch($chamado->id_chamados);


                return response()->json([
                    'message' => 'Ticket aberto com sucesso!',
                    'data' => $chamado,
                    'status' => 201
                ], 201);

            }

        } catch (\Exception $e) {

            Log::error('Erro ao buscar usuário', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno'
            ], 500);
        }
        
    }

    public function listar($id=null){

        $lista = Chamado::with('departamento', 'solicitacao', 'user', 'file')
                    ->where('id_chamados', $id)
                    ->find($id);

        if(!$lista){
            return response()->json([
                'message' => 'Ticket não encontrado!',
                'data' => [],
                'status' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'Ticket encontrado',
            'data' => $lista,
            'status' => 200
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
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

        $result = Chamado::find($id);

        if(!$result){
            return response()->json([
                'message' => 'Ticket não encontrado!',
                'data' => [],
                'status' => 404
            ], 404);
        }

        if( (int) $result->status_chamados  != 0){
            return response()->json([
                'message' => 'O Ticket não pode mais ser atualizado!',
                'data' => [],
                'status' => 422
            ], 422); 
        }

        $dataValidate = $request->validate([
            'assunto_chamados' => 'required|string',
            'descricao_chamados' => 'required|string'
        ]);

        $result->update($dataValidate);

        return response()->json([
            'message' => 'Ticket atualizado com sucesso!',
            'data' => $result,
            'status' => 200
        ], 200);

    }

    public function toForward(Request $request, $id){

        $ticket = Chamado::find($id);

        if(!$ticket){
            return response()->json([
                'message' => 'Ticket não encontrado!',
                'data' => [],
                'status' => 404
            ], 404);
        }

        $statusTicket = (int) $ticket->status_chamados;

        if($statusTicket >= 4){
            return response()->json([
                'message' => 'Ticket finalizado não pode ser transferido!',
                'data' => [],
                'status' => 422
            ], 422);
        }

        $dataValidate = $request->validate([
            'id_user_chamados' => 'required|integer|exists:users,id_users',
            'id_departamento_chamados' => 'required|integer|exists:departamentos,id_departamentos',
            'obs' => 'required|string|min:5'
        ]);

        $ticket->update([
            ...$dataValidate,
            'status_chamados' => 1,
        ]);

        $manifest = Manifestacao::create([
            "tipo_manifestacoes" => 0,
            "descricao_manifestacoes" => Auth::user()->name." transferiu o ticket.",
            "id_chamado_manifestacoes" => $id
        ]);

        if($request->input("obs")){

            $manifest = Manifestacao::create([
                "tipo_manifestacoes" => 2,
                "descricao_manifestacoes" => $request->input("obs"),
                "id_chamado_manifestacoes" => $id
            ]);

        }

        return response()->json([
            'message' => 'Ticket encaminhado com sucesso!',
            'data' => $ticket,
            'status' => 200
        ], 200);
    }

    public function toExecute($id){

        $ticket = Chamado::findOrFail($id);

        $statusTicket = (int) $ticket->status_chamados;

        if(in_array($statusTicket, [0, 4, 5, 6])){
            return response()->json([
                'message' => 'Tickets abertos ou finalizados não podem ser executados!',
                'data' => [],
                'status' => 422
            ], 422);
        }

        $ticket->update([
            'status_chamados' => 2
        ]);

        $manifest = Manifestacao::create([
            "tipo_manifestacoes" => 0,
            "descricao_manifestacoes" => Auth::user()->name." está executando o ticket.",
            "id_chamado_manifestacoes" => $id
        ]);

        return response()->json([
            'message' => 'Ticket em execução!',
            'data' => $ticket,
            'status' => 200
        ], 200);

    }

    public function toAssume($id){

        $ticket = Chamado::findOrFail($id);

        $statusTicket = (int) $ticket->status_chamados;

        if($statusTicket){
            return response()->json([
                'message' => 'O chamado já foi aderido por outro colaborador!',
                'data' => [],
                'status' => 422
            ], 422);
        }

        $ticket->update([
            'status_chamados' => 1,
            'id_user_chamados' => Auth::user()->id_users
        ]);

        $manifest = Manifestacao::create([
            "tipo_manifestacoes" => 0,
            "descricao_manifestacoes" => Auth::user()->name." aderiu o ticket.",
            "id_chamado_manifestacoes" => $id
        ]);
        
        return response()->json([
            'message' => 'Ticket aderido com sucesso!',
            'data' => $ticket,
            'status' => 200
        ], 200);

    }

    public function toFinish(Request $request, $id){


        $ticket = Chamado::findOrFail($id);

        $statusTicket = (int) $ticket->status_chamados;

        if(!$statusTicket){
            return response()->json([
                'message' => 'Tickets que estão em aberto não podem ser finalizados!',
                'data' => [],
                'status' => 422
            ], 422);
        }
    
        $validate = $request->validate([
            'observacoes_finalizar_chamados' => 'required|string|min:5',
            'status_chamados' => 'required|integer|in:4,5,6'
        ]);

        $ticket->update([
            ...$validate,
            "data_finalizar_chamados" => date('Y-m-d H:i:s')
        ]);

        $manifest = Manifestacao::create([
            "tipo_manifestacoes" => 0,
            "descricao_manifestacoes" => Auth::user()->name." encerrou o ticket com as seguintes observações: ". $request->input("observacoes_finalizar_chamados"),
            "id_chamado_manifestacoes" => $id
        ]);

        return response()->json([
            'message' => 'Ticket finalizado com sucesso!',
            'data' => $ticket,
            'status' => 200
        ], 200);

    }

    public function toInterrupt(Request $request, $id){

        $ticket = Chamado::findOrFail($id);

        $statusTicket = (int) $ticket->status_chamados;

        $validateTicket = $request->validate([
            'observacoes_transferencia' => 'required|string|min:5'
        ]);

        if($statusTicket != 2 && Auth::user()->id_users != $ticket->id_user_chamados){
            return response()->json([
                'message' => 'O Ticket não pode ser interrompido, verifique se o ticket está em execução ou se você é responsável pelo atendimento.',
                'data' => [],
                'status' => 422
            ], 422);
        }

        $ticket->update([
            'status_chamados' => 3
        ]);

        $manifest = Manifestacao::create([
            "tipo_manifestacoes" => 3,
            "descricao_manifestacoes" => Auth::user()->name." sinalizou pendência na execução do ticket: ".$request->input("observacoes_transferencia"),
            "id_chamado_manifestacoes" => $id
        ]);

        return response()->json([
            'message' => 'Ticket interrompido com sucesso!',
            'data' => $ticket,
            'status' => 200
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $ticketCurrent = Chamado::find($id);

        if(!$ticketCurrent){
            return response()->json([
                "message" => "Ticket de não encontrado",
                "data" => [],
                "status" => 404
            ], 404);
        }

        if($ticketCurrent->status_chamados != 0){
            return response()->json([
                "message" => "Apenas ticket com status aguardando é que podem ser removidos.",
                "status" => 422,
                "data" => $ticketCurrent
            ], 422);
        }

        $ticketCurrent->delete();

        return response()->json([
            "message" => "Ticket removido com sucesso!",
            "data" => $ticketCurrent,
            "status" => 200
        ], 200);
    }

    public function getCountTicket(Request $request){

        $data = date("Y-m-d");
        $timeStart = $request->input("timeStart");
        $timeEnd = $request->input("timeEnd");

        //carrega todoas os novos tickets para os departamentos que o usuário logado pertence.
        $sql = "SELECT * FROM notificacao INNER JOIN manifestacoes ON notificacao.id_manifestacao_notificacao=manifestacoes.id_manifestacoes INNER JOIN chamados ON manifestacoes.id_chamado_manifestacoes=chamados.id_chamados WHERE tipo_notificacao=0 AND data_cadastro_notificacao BETWEEN '$data $timeStart' AND '$data $timeEnd'";

        foreach ($request->input("listDepartament") as $key => $value) {
            
            if($key == 0){
                $sql.=" AND ( id_departamento_chamados=".$value."";
            }else if(count($request->input("listDepartament")) == $key + 1){
                $sql.=" OR id_departamento_chamados=".$value.")";
            }else{
                $sql.=" OR id_departamento_chamados=".$value."";
            }

            if(count($request->input("listDepartament")) == 1){
                $sql.=")";
            }
            
        }

        $ticket = DB::select($sql);

        //carregar mensagem que pertencam ao usuário responsável pelo atendimento, não incluindo aquelas que foram enviadas pelo próprio usuário logado...
        $mensagem = DB::select("SELECT * FROM notificacao INNER JOIN manifestacoes ON notificacao.id_manifestacao_notificacao=manifestacoes.id_manifestacoes INNER JOIN chamados ON manifestacoes.id_chamado_manifestacoes=chamados.id_chamados WHERE tipo_notificacao=1 AND id_user_chamados=".Auth::user()->id_users." AND id_user_manifestacoes!=".Auth::user()->id_users." AND data_cadastro_notificacao BETWEEN '$data $timeStart' AND '$data $timeEnd'");

        //carregar mensagem que pertencam ao usuário que abriu o ticket, não incluindo aquelas que foram enviadas pelo próprio usuário logado...
        $teste = DB::select("SELECT * FROM notificacao INNER JOIN manifestacoes ON notificacao.id_manifestacao_notificacao=manifestacoes.id_manifestacoes INNER JOIN chamados ON manifestacoes.id_chamado_manifestacoes=chamados.id_chamados WHERE tipo_notificacao=1 AND id_criador_chamados=".Auth::user()->id_users." AND id_user_manifestacoes!=".Auth::user()->id_users." AND data_cadastro_notificacao BETWEEN '$data $timeStart' AND '$data $timeEnd'");

        $lista = [
            "ticket" => $ticket,
            "mensagem" => $mensagem,
            "teste" => $teste
        ];

        return compact("lista");


    }

    public function export(){

        /*$request->validate([
            "departamento" => "required|integer",
            "solicitacao" => "required|integer",
            "data_inicial" => "required|text",
            "data_final" => "required|text",
            "responsavel" => "required|integer"
        ]);*/

        $chamadoResult = Chamado::with("departamento", "solicitacao")->where("id_departamento_chamados", 1)->get();

        $this->pdf->create();

    }

    public function testeSQL(){
        $sql = DB::table("chamados")->where("status_chamados", 1)
        ->orWhere("status_chamados", 2)
        ->count();
        return $sql;
    }

}
