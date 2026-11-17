<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    // R2 — login
    public function attempt(Request $request)
    {
        $credenciais = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Auth::attempt busca o usuário pelo email e compara o hash da senha.
        if (Auth::attempt($credenciais)) {
            $request->session()->regenerate(); // segurança (session fixation)

            return redirect()->intended(route('produtos.index'));
        }

        // Credenciais inválidas: volta ao login com erro no campo email
        return back()
            ->withErrors(['email' => 'E-mail ou senha inválidos.'])
            ->onlyInput('email');
    }

    // R2 — logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
