<?php
// app/Http/Controllers/Auth/LoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ConfiguracionInstitucion;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        $configInstitucion = ConfiguracionInstitucion::getConfig();
        return view('auth.login', compact('configInstitucion'));
    }
    
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        
        $field = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        if (Auth::attempt([$field => $request->username, 'password' => $request->password, 'activo' => true])) {
            $request->session()->regenerate();
            
            // Actualizar último acceso
            auth()->user()->update(['ultimo_acceso' => now()]);
            
            return redirect()->intended(route('dashboard'));
        }
        
        return back()->withErrors([
            'username' => 'Las credenciales no coinciden con nuestros registros o el usuario está inactivo.',
        ])->onlyInput('username');
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}