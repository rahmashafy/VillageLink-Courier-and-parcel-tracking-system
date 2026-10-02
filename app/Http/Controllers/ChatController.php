<?php

namespace App\Http\Controllers;

use App\Services\AiAssistantService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        return view('customer.chat');
    }

    public function ask(Request $request, AiAssistantService $assistant)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:800'],
        ]);

        return response()->json([
            'reply' => $assistant->answer($request->user(), $validated['message']),
        ]);
    }
}
