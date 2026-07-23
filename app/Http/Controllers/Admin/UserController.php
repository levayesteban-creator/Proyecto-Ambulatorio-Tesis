<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (!$user || !in_array($user->role_id, [1, 2])) {
                abort(403, 'No autorizado');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $users = User::with('role')->orderBy('name')->get();
        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        $roles = Role::where('id', '!=', 1)->orderBy('name')->get();
        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        if (!Hash::check($request->admin_password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'admin_password' => 'Tu contraseña no es correcta.',
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'id_number' => 'nullable|string|max:20|unique:users',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20|unique:users',
            'role_id' => 'required|exists:roles,id|not_in:1',
        ], [
            'id_number.unique' => 'La cédula «:input» ya se encuentra registrada.',
            'email.unique' => 'El correo «:input» ya se encuentra registrado.',
            'phone.unique' => 'El teléfono «:input» ya se encuentra registrado.',
        ]);

        $tempPassword = \Illuminate\Support\Str::random(10);

        User::create([
            'name' => $request->name,
            'id_number' => $request->id_number,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($tempPassword),
            'role_id' => $request->role_id,
            'email_verified_at' => now(),
            'must_change_password' => true,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Usuario creado. Contraseña temporal: $tempPassword. El usuario debe cambiarla al iniciar sesión.");
    }

    public function edit(User $user)
    {
        $roles = Role::where('id', '!=', 1)->orderBy('name')->get();
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user->load('role'),
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        if (!Hash::check($request->admin_password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'admin_password' => 'Tu contraseña no es correcta.',
            ]);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'id_number' => 'nullable|string|max:20|unique:users,id_number,'.$user->id,
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20|unique:users,phone,'.$user->id,
            'role_id' => 'required|exists:roles,id',
        ];
        if ($user->role_id !== 1) {
            $rules['role_id'] .= '|not_in:1';
        }
        $request->validate($rules, [
            'id_number.unique' => 'La cédula «:input» ya se encuentra registrada.',
            'email.unique' => 'El correo «:input» ya se encuentra registrado.',
            'phone.unique' => 'El teléfono «:input» ya se encuentra registrado.',
        ]);

        $data = ['name' => $request->name, 'id_number' => $request->id_number, 'email' => $request->email, 'phone' => $request->phone, 'role_id' => $request->role_id];

        if ($request->filled('password')) {
            $request->validate(['password' => ['confirmed', Rules\Password::defaults()]]);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }

        if (!Hash::check($request->admin_password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'admin_password' => 'Tu contraseña no es correcta.',
            ]);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }

    public function resetPassword(Request $request, User $user)
    {
        if (!Hash::check($request->admin_password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'admin_password' => 'Tu contraseña no es correcta.',
            ]);
        }

        $tempPassword = \Illuminate\Support\Str::random(10);

        $user->update([
            'password' => Hash::make($tempPassword),
            'must_change_password' => true,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Contraseña temporal: $tempPassword. El usuario debe cambiarla al iniciar sesión.");
    }
}
