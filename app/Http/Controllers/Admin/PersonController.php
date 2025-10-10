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
        $data = $request->validate([
            'display_name' => ['required','string','max:255'],
            'given_name'   => ['required','string','max:255'],
            'middle_name'  => ['nullable','string','max:255'],
            'family_name'  => ['nullable','string','max:255'],
            'gender'       => ['required', Rule::in(['male','female','other','unknown'])],
            'birth_date'   => ['nullable','date'],
            'death_date'   => ['nullable','date','after_or_equal:birth_date'],
            'is_deceased'  => ['nullable','boolean'],
            'pusta'         => ['nullable','string','max:255'],
            'bio'          => ['nullable','string'],
            'photo_path'   => ['nullable','string','max:255'],
        ]);

        // $data['is_deceased'] = (bool)($data['is_deceased'] ?? false);
        // if ($data['is_deceased'] && empty($data['death_date'])) {
        //     return back()->withErrors(['death_date' => 'If deceased, मृत्यु मिति आवश्यक छ.'])->withInput();
        // }

        Person::create($data);
        return redirect()->route('admin.persons.index')->with('success', 'व्यक्ति थपियो।');
    }

    public function edit(Person $person)
    {
        return view('admin.persons.edit', compact('person'));
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
            'is_deceased'  => ['nullable','boolean'],
            'pusta'         => ['nullable','string','max:255'],
            'bio'          => ['nullable','string'],
            'photo_path'   => ['nullable','string','max:255'],
        ]);
     

        // $data['is_deceased'] = (bool)($data['is_deceased'] ?? false);
        // if ($data['is_deceased'] && empty($data['death_date'])) {
        //     return back()->withErrors(['death_date' => 'If deceased, मृत्यु मिति आवश्यक छ.'])->withInput();
        // }

        $person->update($data);
        return redirect()->route('admin.persons.index')->with('success', 'अद्यावधिक गरियो।');
    }

    public function destroy(Person $person)
    {
        // Soft delete; cascades on edges/unions handled by FKs in migrations
        $person->delete();
        return back()->with('success', 'मेटाइयो।');
    }
}
