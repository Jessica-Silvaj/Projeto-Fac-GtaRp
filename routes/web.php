<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItensController;
use App\Http\Controllers\PerfilController;
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
    // PERFIL
    Route::get("/perfil/usuario/{id}", [PerfilController::class,'edit'])->name('perfil.edit');
    Route::post("/perfil/usuario", [PerfilController::class,'store'])->name('perfil.store');
    Route::post("/perfil/usuario/alterarSenha", [PerfilController::class,'alterarSenha'])->name('usuario.alterarSenha');

    // ADMINISTRAÇÃO
        //  -- ESTOQUE
             //  --- Itens
    Route::get("/administracao/estoque/itens/index", [ItensController::class,'index'])->name('administracao.estoque.itens.index');
    Route::get("/administracao/estoque/itens/edit/{id?}", [ItensController::class,'edit'])->name('administracao.estoque.itens.edit');
    Route::delete('/administracao/estoque/itens/delete/{id}', [ItensController::class,'destroy'])->name('administracao.estoque.itens.destroy');
    Route::post("/administracao/estoque/itens/store", [ItensController::class,'store'])->name('administracao.estoque.itens.store');

});

require __DIR__.'/auth.php';
