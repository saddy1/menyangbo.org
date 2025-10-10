<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PersonController extends Controller
{
    public function show(Person $person)
    {
        
        $person->load(['parents','children','events','unionsAsSpouse1.spouse2','unionsAsSpouse2.spouse1']);
        return response()->json($person);
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
