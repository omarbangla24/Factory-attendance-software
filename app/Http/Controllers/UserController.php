<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('roles')->orderBy('name')->paginate(20);
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role'     => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')
            ->with('success', "User '{$user->name}' created successfully.");
    }

    public function edit(User $user): View
    {
        $roles    = Role::all();
        $userRole = $user->roles->first()?->name;

        return view('users.edit', compact('user', 'roles', 'userRole'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role'  => 'required|exists:roles,name',
        ]);

        $user->update($request->only('name', 'email'));
        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')
            ->with('success', "User '{$user->name}' updated successfully.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "User '{$name}' deleted.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', "Password for '{$user->name}' has been reset.");
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        auth()->user()->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed successfully.');
    }
}
