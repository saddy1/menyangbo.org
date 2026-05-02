<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Support\MemberNumber;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PersonController extends Controller
{
public function show(Person $person)
{
    $person->load([
        'parents:id,display_name,gender,pusta',
        'children:id,display_name,gender,pusta',
        'unionsAsSpouse1.spouse2:id,display_name',
        'unionsAsSpouse2.spouse1:id,display_name',
        'events'
    ]);

    $spouses = collect();
    foreach ($person->unionsAsSpouse1 as $u) $spouses->push($u->spouse2);
    foreach ($person->unionsAsSpouse2 as $u) $spouses->push($u->spouse1);
    $spouses = $spouses->filter()->unique('id')->values();

    return response()->json([
        // base
        'id' => (string)$person->id,
        'display_name' => $person->display_name,
        'display_name_np' => $person->display_name_np,
        'gender' => $person->gender ?: 'unknown',
        'pusta' => $person->pusta,
        'bio' => $person->bio,
        'photo_path' => $person->photo_path,
        'is_deceased' => (bool)$person->is_deceased,
        'birth_date' => $person->birth_date?->format('Y-m-d'),
        'death_date' => $person->death_date?->format('Y-m-d'),

        // profile fields
        'member_no' => $person->member_no,
        'member_type' => $person->member_type,
        'membership' => $person->membership,
        'birth_place' => $person->birth_place,
        'father_name' => $person->father_name,
        'mother_name' => $person->mother_name,
        'address' => $person->address,
        'mobile' => $person->mobile,
        'email' => $person->email,
        'education' => $person->education,
        'occupation' => $person->occupation,
        'marital_status' => $person->marital_status,
        'marriage_date_bs' => $person->marriage_date_bs,
        'marriage_date_ad' => $person->marriage_date_ad?->format('Y-m-d'),
        'lineage' => $person->lineage,
        'family_type' => $person->family_type,
        'blood_group' => $person->blood_group,
        'rashifal' => $person->rashifal,
        'religion' => $person->religion,
        'special_note' => $person->special_note,
        'death_place' => $person->death_place,
        'death_tithi' => $person->death_tithi,
        'death_reason' => $person->death_reason,
        'registered_by' => $person->registered_by,
        'registered_at' => $person->created_at?->format('Y-m-d'),
        'updated_at' => $person->updated_at?->format('Y-m-d'),

        // relations (clickable)
        'parents' => $person->parents->map(fn($p)=>[
            'id'=>(string)$p->id,'display_name'=>$p->display_name,'gender'=>$p->gender,'pusta'=>$p->pusta
        ])->values(),
        'children' => $person->children->map(fn($c)=>[
            'id'=>(string)$c->id,'display_name'=>$c->display_name,'gender'=>$c->gender,'pusta'=>$c->pusta
        ])->values(),
        'spouses' => $spouses->map(fn($s)=>[
            'id'=>(string)$s->id,'display_name'=>$s->display_name
        ])->values(),
        'total_children' => $person->children->count(),
    ]);
}


    public function store(Request $request)
    {
        $data = $request->validate([
            'display_name' => ['required','string','max:255'],
            'given_name'   => ['required','string','max:255'],
            'middle_name'  => ['nullable','string','max:255'],
            'family_name'  => ['nullable','string','max:255'],
            'gender'       => ['required', Rule::in(['male','female','other','unknown'])],
            'birth_date'   => ['nullable'],
            'death_date'   => ['nullable','after_or_equal:birth_date'],
            'is_deceased'  => ['boolean'],
            'bio'          => ['nullable','string'],
        ]);

        if (!empty($data['is_deceased']) && empty($data['death_date'])) {
            return back()->withErrors(['death_date' => 'If deceased, मृत्यु मिति आवश्यक छ.']);
        }

        $person = Person::create($data);
        MemberNumber::assignTo($person);

        return redirect()->route('tree.index')->with('success', 'व्यक्ति थपियो।');
    }

    public function update(Request $request, Person $person)
    {
        $data = $request->validate([
            'display_name' => ['required','string','max:255'],
            'given_name'   => ['required','string','max:255'],
            'middle_name'  => ['nullable','string','max:255'],
            'family_name'  => ['nullable','string','max:255'],
            'gender'       => [Rule::in(['male','female','other','unknown'])],
            'birth_date'   => ['nullable','date'],
            'death_date'   => ['nullable','date','after_or_equal:birth_date'],
            'is_deceased'  => ['boolean'],
            'bio'          => ['nullable','string'],
        ]);

        if (!empty($data['is_deceased']) && empty($data['death_date'])) {
            return back()->withErrors(['death_date' => 'If deceased, मृत्यु मिति आवश्यक छ.']);
        }

        $person->update($data);

        return redirect()->route('tree.index')->with('success', 'अद्यावधिक गरियो।');
    }
}
