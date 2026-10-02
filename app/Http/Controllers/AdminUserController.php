<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:customer,driver,admin'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        if ($user->role === 'driver') {
            $user->driverProfile()->create([
                'phone' => $user->phone,
                'availability_status' => 'available',
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', 'in:customer,driver,admin'],
        ]);

        if ($user->id === Auth::id() && $request->role !== 'admin') {
            return back()->withErrors(['role' => 'You cannot remove your own admin role.']);
        }

        $user->update(['role' => $request->role]);

        if ($user->role === 'driver') {
            $user->driverProfile()->firstOrCreate([
                'user_id' => $user->id,
            ], [
                'phone' => $user->phone,
                'availability_status' => 'available',
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User role updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User removed successfully.');
    }
}
