<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PersonController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->query('q', ''));
        $alive = $request->query('alive'); // '1' or '0' or null

        $people = Person::query()
            ->when($q, fn($qq) =>
                $qq->where('display_name', 'like', '%'.$q.'%')
                   ->orWhere('given_name', 'like', '%'.$q.'%')
                   ->orWhere('family_name', 'like', '%'.$q.'%')
            )
            ->when($alive !== null, fn($qq) =>
                $qq->where('is_deceased', $alive === '1' ? 0 : 1)
            )
            ->orderBy('id', 'desc')
            ->paginate(12)
            ->withQueryString();

        return view('admin.persons.index', compact('people', 'q', 'alive'));
    }

    public function create()
    {
        return view('admin.persons.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatePerson($request);

        // checkbox handling
        $data['is_deceased'] = $request->boolean('is_deceased');

        // ✅ if not deceased, clear death fields
        if (!$data['is_deceased']) {
            $data['death_date'] = null;
            $data['death_place'] = null;
            $data['death_tithi'] = null;
            $data['death_reason'] = null;
        }

        Person::create($data);

        return redirect()->route('admin.persons.index')->with('success', 'व्यक्ति थपियो।');
    }

    public function edit(Person $person)
    {
        return view('admin.persons.edit', compact('person'));
    }

    public function update(Request $request, Person $person)
    {
        $data = $this->validatePerson($request);

        $data['is_deceased'] = $request->boolean('is_deceased');

        if (!$data['is_deceased']) {
            $data['death_date'] = null;
            $data['death_place'] = null;
            $data['death_tithi'] = null;
            $data['death_reason'] = null;
        }

        $person->update($data);

        return redirect()->route('admin.persons.index')->with('success', 'अद्यावधिक गरियो।');
    }

    public function destroy(Person $person)
    {
        $person->delete();
        return back()->with('success', 'मेटाइयो।');
    }

    private function validatePerson(Request $request): array
    {
        return $request->validate([
            'display_name' => ['required','string','max:255'],
            'given_name'   => ['required','string','max:255'],
            'middle_name'  => ['nullable','string','max:255'],
            'family_name'  => ['nullable','string','max:255'],
            'gender'       => ['required', Rule::in(['male','female','other','unknown'])],

            'birth_date'   => ['nullable','date'],
            'death_date'   => ['nullable','date','after_or_equal:birth_date'],
            'is_deceased'  => ['nullable','boolean'],

            'pusta'        => ['nullable','string','max:255'],
            'bio'          => ['nullable','string'],
            'photo_path'   => ['nullable','string','max:255'],

            // EXTRA PROFILE
            'member_no'        => ['nullable','string','max:50'],
            'display_name_np'  => ['nullable','string','max:255'],
            'member_type'      => ['nullable','string','max:100'],
            'membership'       => ['nullable','string','max:100'],

            'birth_place'      => ['nullable','string','max:255'],
            'address'          => ['nullable','string','max:255'],
            'mobile'           => ['nullable','string','max:50'],
            'email'            => ['nullable','email','max:255'],

            'education'        => ['nullable','string','max:255'],
            'occupation'       => ['nullable','string','max:255'],

            'lineage'          => ['nullable','string','max:255'],
            'family_type'      => ['nullable','string','max:255'],
            'blood_group'      => ['nullable','string','max:20'],
            'rashifal'         => ['nullable','string','max:50'],
            'religion'         => ['nullable','string','max:100'],
            'special_note'     => ['nullable','string'],

            // death extra (shown only if deceased in UI)
            'death_place'      => ['nullable','string','max:255'],
            'death_tithi'      => ['nullable','string','max:50'],
            'death_reason'     => ['nullable','string','max:255'],

            'registered_by'    => ['nullable','string','max:100'],
        ]);
    }
}
