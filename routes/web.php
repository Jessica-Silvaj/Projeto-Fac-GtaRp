<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsuarioController;
use App\Http\Middleware\CheckAuth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});
Route::get("/logout",[AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::middleware(CheckAuth::class)->group(function () {
    // DASBHOARD
    Route::get("/dashboard", [DashboardController::class,'index'])->name('dashboard');
    // Perfil
    Route::get("/usuario/{id}", [UsuarioController::class,'index'])->name('usuario.index');
    Route::post("/usuario", [UsuarioController::class,'store'])->name('usuario.store');
    Route::post("/usuario/alterarSenha", [UsuarioController::class,'alterarSenha'])->name('usuario.alterarSenha');
});

require __DIR__.'/auth.php';
