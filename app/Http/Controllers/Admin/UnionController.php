<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnionModel;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnionController extends Controller
{
    public function index()
    {
        $people = Person::orderBy('display_name')->get(['id','display_name']);
        $unions = UnionModel::with(['spouse1:id,display_name','spouse2:id,display_name'])
                  ->orderByDesc('id')->paginate(20);

        return view('admin.unions.index', compact('people','unions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'spouse1_id' => ['required','integer','different:spouse2_id','exists:persons,id'],
            'spouse2_id' => ['required','integer','exists:persons,id'],
            'type'       => ['required', Rule::in(['marriage','partnership','other'])],
            'start_date' => ['nullable'],
            'end_date'   => ['nullable','date','after_or_equal:start_date'],
            'notes'      => ['nullable','string','max:2000'],
        ]);

        // soft uniqueness (no exact dup with same start_date)
        $dup = UnionModel::where('spouse1_id', $data['spouse1_id'])
            ->where('spouse2_id', $data['spouse2_id'])
            ->where('start_date', $data['start_date'])
            ->exists();
        if ($dup) return back()->withErrors(['spouse2_id'=>'उही जोडी र मिति दोहोरियो।'])->withInput();

        UnionModel::create($data);
        return back()->with('success','युनियन थपियो।');
    }

    public function destroy(UnionModel $union)
    {
        $union->delete();
        return back()->with('success','युनियन मेटाइयो।');
    }
}
