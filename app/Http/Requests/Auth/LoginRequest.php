<?php

namespace App\Http\Requests\Auth;

use App\Models\Usuario;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'matricula' => 'required',
            'senha' => 'required|string',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate()
    {
        $this->ensureIsNotRateLimited();

        $obj = $this->only('matricula', 'senha');
        $usuario = Usuario::realizarLogin($obj);
        if(empty($usuario))
        {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'matricula' => __('Usuário ou senha inválido!'),
            ]);
        }

        //TODO CRIAR REGRA PARA PERFIL NÃO TER ACESSO AO SISTEMA
        if($usuario->perfil_id == NULL){
            throw ValidationException::withMessages([
                'matricula' => __('O perfil do usuário não permite acessar o sistema!'),
            ]);
        }

        Session::put('matricula', $usuario->matricula);
        Session::put('nome', $usuario->nome);
        Session::put('perfil', $usuario->perfil_id);
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'matricula' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('matricula')).'|'.$this->ip();
    }
}
