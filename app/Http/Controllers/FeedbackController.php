<?php

// app/Http/Controllers/FeedbackController.php
namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    // Public form
    public function create() { return view('feedback.create'); }

    // Store
    public function store(Request $r) {
        if ($r->filled('hp_field')) {
            return back()->withErrors(['description'=>'Spam detected.'])->withInput();
        }

        $data = $r->validate([
            'name'        => ['nullable','string','max:120'],
            'email'       => ['nullable','email','max:190'],
            'contact'     => ['nullable','string','max:60'],
            'description' => ['required','string','min:5','max:5000'],
            'hp_field'    => ['nullable','string','max:120'],
        ]);

        $data['ip'] = $r->ip();
        $data['user_agent'] = $r->userAgent();

        Feedback::create($data);

        return redirect()->route('feedback.thanks');
    }

    public function thanks() { return view('feedback.thanks'); }

    // Admin (very simple list + view + delete)
    public function adminIndex() {
        $rows = Feedback::orderByDesc('id')->paginate(20);
        return view('admin.feedback.index', compact('rows'));
    }

    public function adminShow(Feedback $feedback) {
        if (!$feedback->read_at) {
            $feedback->update(['read_at' => now()]);
        }

        return view('admin.feedback.show', compact('feedback'));
    }

    public function destroy(Feedback $feedback) {
        $feedback->delete();
        return redirect()->route('admin.feedback.index')->with('success', 'Feedback entry deleted.');
    }
}
