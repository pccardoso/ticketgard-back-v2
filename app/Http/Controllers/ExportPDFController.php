<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Chamado;

class ExportPDFController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        $request->validate([
            "departamento" => "required|integer",
            "solicitacao" => "nullable|integer",
            "data_inicial" => "nullable|string",
            "data_final" => "nullable|string",
            "responsavel" => "nullable|integer",
        ]);

        $dataInicial = $request->input("data_inicial") ?? null;
        $dataFinal = $request->input("data_final") ?? null;
        $idDepartamento = $request->input("departamento");
        $idSolicitacao = $request->input("solicitacao") ?? null;
        $idResponsavel = $request->input("responsavel") ?? null;

        $chamadoResult = Chamado::with(['departamento', 'solicitacao', 'user'])
            ->where('id_departamento_chamados', $idDepartamento) // sempre obrigatório
            ->when($idSolicitacao, function ($query, $idSolicitacao) {
                $query->where('id_solicitacao_chamados', $idSolicitacao);
            })
            ->when($idResponsavel, function ($query, $idResponsavel) {
                $query->where('id_user_chamados', $idResponsavel);
            })
            ->when($dataInicial && $dataFinal, function ($query) use ($dataInicial, $dataFinal) {
                $query->whereBetween('data_cadastro_chamados', [
                    $dataInicial . ' 00:00:00',
                    $dataFinal . ' 23:59:59',
                ]);
            })
            ->orderBy("data_cadastro_chamados")
            ->get();


        $pdf = Pdf::loadView('template.pdf', [
            "result" => $chamadoResult,
            "sum" => count($chamadoResult),
            "start" => $dataInicial,
            "end" => $dataFinal
        ])->setPaper('a4', 'landscape');
        return $pdf->stream('Relatorio.pdf');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
