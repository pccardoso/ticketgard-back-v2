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

    public function index2(Request $request)
    {

        $assunto = $request->input("assunto");
        $codigo = $request->input("codigo");
        $prioridade = $request->input("prioridade");
        $situacao = $request->input("situacao");
        $departamento = $request->input("departamento");

        $inicio = $request->input("inicio");
        $fim = $request->input("fim");

        $sql = "SELECT * FROM chamados INNER JOIN departamentos ON chamados.id_departamento_chamados = departamentos.id_departamentos INNER JOIN solicitacoes ON chamados.id_solicitacao_chamados = solicitacoes.id_solicitacoes LEFT JOIN users ON chamados.id_user_chamados = users.id_users WHERE (assunto_chamados LIKE '%$assunto%' OR descricao_chamados LIKE '%$assunto%')";

        /*if($prioridade){
            $sql.= " AND prioridade_solicitacoes = '$prioridade'";
        }*/

        if($situacao != 5){
            $sql.= " AND status_chamados = $situacao";
        }

        foreach ($request->input("sits") as $key => $value) {

            if($key == 0){
                $sql.=" AND ( status_chamados=".$value."";
            }else if(count($request->input("sits")) == $key + 1){
                $sql.=" OR status_chamados=".$value.")";
            }else{
                $sql.=" OR status_chamados=".$value."";
            }

            if(count($request->input("sits")) == 1){
                $sql.=")";
            }
            
        } 

         foreach ($request->input("soli") as $key => $value) {

            if($key == 0){
                $sql.=" AND ( id_solicitacao_chamados=".$value."";
            }else if(count($request->input("soli")) == $key + 1){
                $sql.=" OR id_solicitacao_chamados=".$value.")";
            }else{
                $sql.=" OR id_solicitacao_chamados=".$value."";
            }

            if(count($request->input("soli")) == 1){
                $sql.=")";
            }
            
        }

        foreach ($request->input("prio") as $key => $value) {

            /*if($key == 0){
                $sql.=" AND prioridade_solicitacoes='".$value."'";
            }else{
                $sql.=" OR prioridade_solicitacoes='".$value."'";
            }*/

            if($key == 0){
                $sql.=" AND ( prioridade_solicitacoes=".$value."";
            }else if(count($request->input("prio")) == $key + 1){
                $sql.=" OR prioridade_solicitacoes=".$value.")";
            }else{
                $sql.=" OR prioridade_solicitacoes=".$value."";
            }

            if(count($request->input("prio")) == 1){
                $sql.=")";
            }
            
        }

        foreach ($request->input("resp") as $key => $value) {

            /*if($key == 0){
                $sql.=" AND prioridade_solicitacoes='".$value."'";
            }else{
                $sql.=" OR prioridade_solicitacoes='".$value."'";
            }*/

            if($key == 0){
                $sql.=" AND ( id_user_chamados=".$value."";
            }else if(count($request->input("resp")) == $key + 1){
                $sql.=" OR id_user_chamados=".$value.")";
            }else{
                $sql.=" OR id_user_chamados=".$value."";
            }

            if(count($request->input("resp")) == 1){
                $sql.=")";
            }
            
        }

        if($codigo){
            $sql.= " AND id_chamados = $codigo";
        }

        /*if($departamento){
            $sql.="AND id_departamento_chamados=$departamento";
        }*/

        #percorre o array parar criar um SQL
        foreach ($request->input("deps") as $key => $value) {

            /*if($key == 0){
                $sql.=" AND id_departamento_chamados=".$value;
            }else{
                $sql.=" OR id_departamento_chamados=".$value;
            }*/

            if($key == 0){
                $sql.=" AND ( id_departamento_chamados=".$value."";
            }else if(count($request->input("deps")) == $key + 1){
                $sql.=" OR id_departamento_chamados=".$value.")";
            }else{
                $sql.=" OR id_departamento_chamados=".$value."";
            }

            if(count($request->input("deps")) == 1){
                $sql.=")";
            }
            
        }

        #percorre o array parar criar um SQL
        foreach ($request->input("aber") as $key => $value) {

            /*if($key == 0){
                $sql.=" AND id_departamento_chamados=".$value;
            }else{
                $sql.=" OR id_departamento_chamados=".$value;
            }*/

            if($key == 0){
                $sql.=" AND ( id_criador_chamados=".$value."";
            }else if(count($request->input("aber")) == $key + 1){
                $sql.=" OR id_criador_chamados=".$value.")";
            }else{
                $sql.=" OR id_criador_chamados=".$value."";
            }

            if(count($request->input("aber")) == 1){
                $sql.=")";
            }
            
        }

        #percorre o array parar criar um SQL
        foreach ($request->input("lis_dep") as $key => $value) {

            /*if($key == 0){
                $sql.=" AND id_departamento_chamados=".$value;
            }else{
                $sql.=" OR id_departamento_chamados=".$value;
            }*/

            if($key == 0){
                $sql.=" AND ( id_departamento_chamados=".$value."";
            }else if(count($request->input("lis_dep")) == $key + 1){
                $sql.=" OR id_departamento_chamados=".$value.")";
            }else{
                $sql.=" OR id_departamento_chamados=".$value."";
            }

            if(count($request->input("lis_dep")) == 1){
                $sql.=")";
            }
            
        }

        if($request->input("id_user")){

            $sql.=" AND (id_criador_chamados = ".$request->input("id_user").")";

        }

        // tem data inicial mas sem final
        if($inicio && !$fim){   

            $ini = $this->dataen($inicio);

            $sql .= " AND data_cadastro_chamados BETWEEN '$ini 00:00:00' AND '".date("y-m-d")." 23:59:59'";
        }else if($inicio && $fim){
            $ini = $this->dataen($inicio);
            $fim = $this->dataen($fim);
            $sql .= " AND data_cadastro_chamados BETWEEN '$ini 00:00:00' AND '$fim 23:59:59'";
        }else if(!$inicio && $fim){

            $fim = $this->dataen($fim);

            $sql .= " AND data_cadastro_chamados BETWEEN '0000-00-00 00:00:00' AND '$fim 23:59:59'";
        }

        $sql .= " ORDER BY chamados.data_cadastro_chamados DESC";

        $lista = DB::select($sql);
        
        sleep(1);
        
        return response()->json([
            "message" => "Registros".count($lista)." encontrados",
            "data" => $lista,
            "status" => 200
        ], 200);
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

    public function history($id){
        $chamado = Chamado::find($id);
        $user = User::find($chamado->id_criador_chamados);

        $result = [
            "chamado" => $chamado,
            "user" => $user
        ]; 
        return Inertia::render("System/VisChamado", compact("result"));
    }

    /**
     * Store a newly created resource in storage.
     */
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
        sleep(seconds: 1);
        $lista = Chamado::find($id);
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
        $result = Chamado::find($request->input("id_chamados"));
        $result->assunto_chamados = $request->input("assunto_chamados");
        $result->descricao_chamados = $request->input("descricao_chamados");
        $result->save();
        return to_route("meuchamado");
    }

    public function encaminhar(Request $request){
        $result = Chamado::find($request->input("id_chamados"));
        $result->id_user_chamados = $request->input("id_user_chamados");
        $result->status_chamados = $request->input("status_chamados");
        $result->id_departamento_chamados = $request->input("id_departamento_chamados");
        $result->save();

        $manifest = Manifestacao::create([
            "tipo_manifestacoes" => 0,
            "descricao_manifestacoes" => Auth::user()->name." transferiu o ticket.",
            "id_chamado_manifestacoes" => $request->input("id_chamados")
        ]);

        if($request->input("obs")){

            $manifest = Manifestacao::create([
                "tipo_manifestacoes" => 2,
                "descricao_manifestacoes" => $request->input("obs"),
                "id_chamado_manifestacoes" => $request->input("id_chamados")
            ]);

        }

        return to_route("con.chamados");
    }

    public function executar(Request $request){

        $result = Chamado::find($request->input("id_chamados"));
        $result->status_chamados = $request->input("status_chamados");
        $result->save();

        $manifest = Manifestacao::create([
            "tipo_manifestacoes" => 0,
            "descricao_manifestacoes" => Auth::user()->name." está executando o ticket.",
            "id_chamado_manifestacoes" => $request->input("id_chamados")
        ]);

    }

    public function assumir(Request $request){

        $res = [];

        $result = Chamado::find($request->input("id_chamados"));
        
        if($result->status_chamados == 0){

            $result->status_chamados = $request->input("status_chamados");
            $result->id_user_chamados = $request->input("id_user_chamados");
            $result->save();

            $manifest = Manifestacao::create([
                "tipo_manifestacoes" => 0,
                "descricao_manifestacoes" => Auth::user()->name." aderiu o ticket.",
                "id_chamado_manifestacoes" => $request->input("id_chamados")
            ]);

            $res = [1, "Chamado assumido com sucesso"];

            /*$this->sendEmail([
                "codigo" => $result->id_chamados,
                "status" => 1,
                "user" => Auth::user()->name,
                "email" => "paulo.cardoso2408@gmail.com"
            ]);*/

        }else{

            $res = [0, "Chamado já assumido por outro colaborador, favor, atualize novamente sua pesquisa!"];

        }
        
        return response()->json($res);

    }

    public function finalizar(Request $request){

        $result = Chamado::find($request->input("id_chamados"));
        $result->status_chamados = $request->input("status_chamados");
        $result->data_finalizar_chamados = date('Y-m-d H:i:s');
        $result->observacoes_finalizar_chamados = $request->input("observacao_chamados");
        $result->save();

        $manifest = Manifestacao::create([
            "tipo_manifestacoes" => 0,
            "descricao_manifestacoes" => Auth::user()->name." encerrou o ticket com as seguintes observações: ". $request->input("observacao_chamados"),
            "id_chamado_manifestacoes" => $request->input("id_chamados")
        ]);

    }

    public function interromper(Request $request){
        $result = Chamado::find($request->input("id_chamados"));
        $result->status_chamados = $request->input("status_chamados");
        $result->save();

        $manifest = Manifestacao::create([
            "tipo_manifestacoes" => 3,
            "descricao_manifestacoes" => Auth::user()->name." sinalizou pendência na execução do ticket: ".$request->input("observacoes_transferencia"),
            "id_chamado_manifestacoes" => $request->input("id_chamados")
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $dep = Chamado::find($id)->delete();
        return to_route("meuchamado");
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

}
