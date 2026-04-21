<?php
// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Docente;
use App\Models\Alumno;
use App\Models\Apoderado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->paginate(15);
        return view('users.index', compact('users'));
    }
    
    public function create()
    {
        $roles = Role::where('activo', true)->get();
        $docentes = Docente::all();
        $alumnos = Alumno::all();
        $apoderados = Apoderado::all();
        
        return view('users.create', compact('roles', 'docentes', 'alumnos', 'apoderados'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'docente_id' => $request->docente_id,
            'alumno_id' => $request->alumno_id,
            'apoderado_id' => $request->apoderado_id,
            'activo' => true,
        ]);
        
        return redirect()->route('admin.users.index')->with('success', 'Usuario creado exitosamente');
    }
    
    public function edit(User $user)
    {
        $roles = Role::where('activo', true)->get();
        $docentes = Docente::all();
        $alumnos = Alumno::all();
        $apoderados = Apoderado::all();
        
        return view('users.edit', compact('user', 'roles', 'docentes', 'alumnos', 'apoderados'));
    }
    
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
        ]);
        
        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'docente_id' => $request->docente_id,
            'alumno_id' => $request->alumno_id,
            'apoderado_id' => $request->apoderado_id,
        ]);
        
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }
        
        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente');
    }
    
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario');
        }
        
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado exitosamente');
    }
    
    public function toggleActive(User $user)
    {
        $user->update(['activo' => !$user->activo]);
        return back()->with('success', 'Estado del usuario actualizado');
    }
}