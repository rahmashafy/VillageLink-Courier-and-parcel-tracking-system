<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function switch(Request $request, string $theme): RedirectResponse
    {
        if (! in_array($theme, ['dark', 'light'], true)) {
            abort(400);
        }

        session(['theme' => $theme]);

        if ($request->user()) {
            $request->user()->update(['theme_preference' => $theme]);
        }

        return back();
    }
}
