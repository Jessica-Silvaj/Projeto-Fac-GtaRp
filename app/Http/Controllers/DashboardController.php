<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public static function index (Request $request)
    {
        $request->flash();
        return view('dashboard.index');
    }
}
