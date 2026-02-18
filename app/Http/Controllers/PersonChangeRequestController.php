<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;

class PersonChangeRequestController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'person_id' => ['required','exists:persons,id'],
            'type'      => ['required','in:mark_deceased,add_child,update_profile'],

            // submitter
            'submitted_name'   => ['nullable','string','max:255'],
            'submitted_email'  => ['nullable','string','max:255'],
            'submitted_mobile' => ['nullable','string','max:50'],
            'submitted_note'   => ['nullable','string','max:5000'],

            // payload (flexible)
            'payload' => ['required','array'],
        ]);

        \DB::table('person_change_requests')->insert([
            'person_id' => $data['person_id'],
            'type' => $data['type'],
            'payload' => json_encode($data['payload'], JSON_UNESCAPED_UNICODE),
            'status' => 'pending',
            'submitted_name' => $data['submitted_name'] ?? null,
            'submitted_email' => $data['submitted_email'] ?? null,
            'submitted_mobile' => $data['submitted_mobile'] ?? null,
            'submitted_note' => $data['submitted_note'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['ok'=>true,'message'=>'Request submitted for admin approval.']);
    }
}
