<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PageController extends Controller
{
    public function about()
    {
        return view('pages.about');
    }

    public function services()
    {
        return view('pages.services');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function contactStore(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $contactMessage = ContactMessage::create($validated);

        try {
            Mail::to(config('services.contact.inbox'))
                ->send(new ContactMessageReceived($contactMessage));
        } catch (Throwable $exception) {
            Log::error('Contact form email failed', [
                'contact_message_id' => $contactMessage->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with(
                'success',
                __('vl.contact_saved_mail_pending')
            );
        }

        return back()->with('success', __('vl.contact_sent'));
    }
}
