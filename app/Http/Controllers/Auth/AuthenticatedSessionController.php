<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Usuario;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        // Valida + throttle + verifica perfil (feito no LoginRequest)
        $request->authenticate();

        // Carrega usuário autenticado pelo seu fluxo
        // Aqui assumimos que você usa sessão própria (não guard padrão do Laravel)
        // Se você usa guard padrão, chame Auth::login($usuario) onde for apropriado.
        $usuario = Usuario::where('matricula', $request->input('matricula'))->first();

        // Preenche sessão de forma controlada
        Session::put('usuario_id', $usuario->id);
        Session::put('matricula', $usuario->matricula);
        Session::put('nome', $usuario->nome);
        Session::put('perfil', $usuario->perfil_id);
        // guarda apenas os IDs de função (mínimo necessário)
        Session::put('funcoes', $usuario->funcoes()->pluck('FUNCAO.id')->toArray());

        // $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        // Session::put('matricula', '');
        // Session::put('nome', '');
        // Session::put('perfil', '');
        // Session::put('usuario_id', '');
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
