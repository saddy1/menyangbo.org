<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnionModel;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnionController extends Controller
{
public function index(Request $request)
{
    $q = trim($request->query('q', ''));
    $pusta = trim($request->query('pusta', ''));

    // dropdown people list (keep it simple)
    $people = Person::orderBy('display_name')
        ->get(['id','display_name','member_no','pusta']);

    $unions = UnionModel::query()
        ->with([
            'spouse1:id,display_name,display_name_np,member_no,pusta',
            'spouse2:id,display_name,display_name_np,member_no,pusta',
        ])
        ->when($q !== '', function ($qq) use ($q) {
            $term = $q;

            // allow "pusta:3" quick search
            if (preg_match('/^pusta\s*:\s*(\d+)$/i', $term, $m)) {
                $p = $m[1];
                return $qq->whereHas('spouse1', fn($s) => $s->where('pusta', $p))
                          ->orWhereHas('spouse2', fn($s) => $s->where('pusta', $p));
            }

            // allow "#DLUMP01" style (member_no)
            if (str_starts_with($term, '#')) {
                $term = ltrim($term, '#');
            }

            return $qq->where(function ($w) use ($term) {
                $w->whereHas('spouse1', function ($s) use ($term) {
                    $s->where('display_name', 'like', "%{$term}%")
                      ->orWhere('display_name_np', 'like', "%{$term}%")
                      ->orWhere('member_no', 'like', "%{$term}%");
                })->orWhereHas('spouse2', function ($s) use ($term) {
                    $s->where('display_name', 'like', "%{$term}%")
                      ->orWhere('display_name_np', 'like', "%{$term}%")
                      ->orWhere('member_no', 'like', "%{$term}%");
                });
            });
        })
        ->when($pusta !== '', function ($qq) use ($pusta) {
            $qq->where(function ($w) use ($pusta) {
                $w->whereHas('spouse1', fn($s) => $s->where('pusta', $pusta))
                  ->orWhereHas('spouse2', fn($s) => $s->where('pusta', $pusta));
            });
        })
        ->orderByDesc('id')
        ->paginate(20)
        ->withQueryString();

    return view('admin.unions.index', compact('people','unions','q','pusta'));
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
