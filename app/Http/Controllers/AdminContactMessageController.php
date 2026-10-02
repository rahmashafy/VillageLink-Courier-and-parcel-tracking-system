<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class AdminContactMessageController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::latest()->get();
        $unreadCount = ContactMessage::whereNull('read_at')->count();

        return view('admin.contact-messages.index', compact('messages', 'unreadCount'));
    }

    public function markRead(ContactMessage $contactMessage)
    {
        $contactMessage->update(['read_at' => now()]);

        return back()->with('success', 'Message marked as read.');
    }
}
