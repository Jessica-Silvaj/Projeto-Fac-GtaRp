<?php

namespace App\Http\Requests\Auth;

use App\Models\Usuario;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;
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
            'matricula' => ['required', 'string', 'regex:/^\d{3,20}$/'],
            'senha' =>  ['required', 'string', 'min:6', 'max:128'],
        ];
    }

    public function messages(): array
    {
        return [
            'matricula.required' => 'Informe seu, passaporte',
            'matricula.max'      => 'A identificação deve ter no máximo :max caracteres.',
            'matricula.regex'    => 'Use um passaporte numérico (3–20 dígitos) válido.',
            'senha.required'     => 'Informe sua senha.',
            'senha.min'          => 'A senha deve ter pelo menos :min caracteres.',
            'senha.max'          => 'A senha deve ter no máximo :max caracteres.',
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

        $cred = $this->only('matricula', 'senha');

        // Login via método seguro no Model (Hash::check, sem concatenação)
        $usuario = Usuario::realizarLogin($cred);

        if (empty($usuario)) {
            RateLimiter::hit($this->throttleKey(), 60);
            throw ValidationException::withMessages([
                'matricula' => __('Passaporte ou senha inválida!'),
            ]);
        }

        // Regras de acesso: sem perfil OU perfil 9 => bloqueia
        if (is_null($usuario->perfil_id) || (int)$usuario->perfil_id === 9) {
            RateLimiter::hit($this->throttleKey(), 60);
            throw ValidationException::withMessages([
                'matricula' => __('O perfil do usuário não permite acessar o sistema!'),
            ]);
        }

        // Regenera a sessão (mitiga fixation); sessão é setada no controller
        $this->session()->regenerate();

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
            'matricula' => "Muitas tentativas. Aguarde {$seconds}s e tente novamente.",
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('matricula')) . '|' . $this->ip();
    }
}
