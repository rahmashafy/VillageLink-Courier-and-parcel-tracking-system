<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintReply;
use App\Models\Parcel;
use App\Notifications\ComplaintReplied;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComplaintController extends Controller
{
    public function create()
    {
        $parcels = Parcel::where('user_id', auth()->id())->latest()->get();

        return view('customer.complaints.create', compact('parcels'));
    }

    public function customerIndex()
    {
        $complaints = Complaint::with('parcel', 'replies')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('customer.complaints.index', compact('complaints'));
    }

    public function show(Complaint $complaint)
    {
        if ($complaint->user_id !== auth()->id()) {
            abort(403);
        }

        $complaint->load('parcel', 'replies.user');

        return view('customer.complaints.show', compact('complaint'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'parcel_id' => [
                'nullable',
                Rule::exists('parcels', 'id')->where('user_id', auth()->id()),
            ],
            'category' => ['required', 'in:damage,missing,late_delivery,wrong_delivery,general'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('complaints', 'public');
        }

        Complaint::create([
            'user_id' => auth()->id(),
            'parcel_id' => $request->parcel_id,
            'category' => $request->category,
            'subject' => $request->subject,
            'message' => $request->message,
            'image_path' => $imagePath,
            'status' => 'open',
        ]);

        return redirect()->route('customer.dashboard')
            ->with('success', 'Complaint submitted successfully.');
    }

    public function customerReply(Request $request, Complaint $complaint)
    {
        if ($complaint->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        ComplaintReply::create([
            'complaint_id' => $complaint->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'is_admin' => false,
        ]);

        if ($complaint->status === 'closed') {
            $complaint->update(['status' => 'open']);
        }

        return back()->with('success', 'Reply added successfully.');
    }

    public function adminIndex()
    {
        $complaints = Complaint::with('user', 'parcel', 'replies')->latest()->get();

        return view('admin.complaints.index', compact('complaints'));
    }

    public function adminShow(Complaint $complaint)
    {
        $complaint->load('user', 'parcel', 'replies.user');

        return view('admin.complaints.show', compact('complaint'));
    }

    public function adminUpdate(Request $request, Complaint $complaint)
    {
        $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        $complaint->update(['status' => $request->status]);

        return back()->with('success', 'Complaint status updated.');
    }

    public function adminReply(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        ComplaintReply::create([
            'complaint_id' => $complaint->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'is_admin' => true,
        ]);

        $complaint->update(['status' => $validated['status']]);
        $complaint->user?->notify(new ComplaintReplied($complaint, $validated['message']));

        return back()->with('success', 'Reply sent to customer.');
    }
}
