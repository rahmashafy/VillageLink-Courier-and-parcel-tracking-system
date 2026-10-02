<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if (in_array($user->role, ['driver', 'agent'], true)) {
            return redirect()->route('agent.dashboard');
        }

        return redirect()->route('customer.dashboard');
    }
}
