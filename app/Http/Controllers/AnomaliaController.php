<?php

namespace App\Http\Controllers;

use App\Services\AnomaliaService;
use Illuminate\Http\Request;

class AnomaliaController extends Controller
{
    public function __construct(private AnomaliaService $service)
    {
    }

    public function index(Request $request)
    {
        $data = $this->service->dashboard($request);
        return view('controleBau.bau.lancamentos.anomalias', $data);
    }

    public function navbar()
    {
        return response()->json($this->service->navbarAlertas());
    }
}
