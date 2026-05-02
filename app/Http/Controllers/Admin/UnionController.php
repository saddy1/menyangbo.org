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

        $unions = $this->filteredUnions($q, $pusta)
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.unions.index', compact('unions','q','pusta'));
    }

    public function searchJson(Request $request)
    {
        $q = trim($request->query('q', ''));
        $pusta = trim($request->query('pusta', ''));

        $rows = $this->filteredUnions($q, $pusta)
            ->orderByDesc('id')
            ->limit(80)
            ->get()
            ->map(fn ($union) => [
                'id' => $union->id,
                'type' => $union->type,
                'start_date' => $union->start_date instanceof \Carbon\CarbonInterface
                    ? $union->start_date->format('Y-m-d')
                    : $union->start_date,
                'spouse1' => $this->personPayload($union->spouse1),
                'spouse2' => $this->personPayload($union->spouse2),
            ])
            ->values();

        return response()->json([
            'count' => $rows->count(),
            'rows' => $rows,
        ]);
    }

    private function filteredUnions(string $q = '', string $pusta = '')
    {
        return UnionModel::query()
            ->with([
                'spouse1:id,display_name,display_name_np,display_name_limbu,member_no,pusta',
                'spouse2:id,display_name,display_name_np,display_name_limbu,member_no,pusta',
            ])
            ->when($q !== '', function ($qq) use ($q) {
                $term = $q;

                // allow "pusta:3" quick search
                if (preg_match('/^pusta\s*:\s*(\d+)$/i', $term, $m)) {
                    $p = $m[1];
                    return $qq->where(function ($w) use ($p) {
                        $w->whereHas('spouse1', fn($s) => $s->where('pusta', $p))
                          ->orWhereHas('spouse2', fn($s) => $s->where('pusta', $p));
                    });
                }

                // allow "#DLUMP01" style (member_no)
                if (str_starts_with($term, '#')) {
                    $term = ltrim($term, '#');
                }

                return $qq->where(function ($w) use ($term) {
                    $w->whereHas('spouse1', function ($s) use ($term) {
                        $s->where('display_name', 'like', "%{$term}%")
                          ->orWhere('display_name_np', 'like', "%{$term}%")
                          ->orWhere('display_name_limbu', 'like', "%{$term}%")
                          ->orWhere('member_no', 'like', "%{$term}%");
                    })->orWhereHas('spouse2', function ($s) use ($term) {
                        $s->where('display_name', 'like', "%{$term}%")
                          ->orWhere('display_name_np', 'like', "%{$term}%")
                          ->orWhere('display_name_limbu', 'like', "%{$term}%")
                          ->orWhere('member_no', 'like', "%{$term}%");
                    });
                });
            })
            ->when($pusta !== '', function ($qq) use ($pusta) {
                $qq->where(function ($w) use ($pusta) {
                    $w->whereHas('spouse1', fn($s) => $s->where('pusta', $pusta))
                      ->orWhereHas('spouse2', fn($s) => $s->where('pusta', $pusta));
                });
            });
    }

    private function personPayload(?Person $person): array
    {
        if (!$person) {
            return [
                'id' => null,
                'display_name' => '—',
                'display_name_np' => null,
                'display_name_limbu' => null,
                'member_no' => null,
                'pusta' => null,
            ];
        }

        return [
            'id' => $person->id,
            'display_name' => $person->display_name,
            'display_name_np' => $person->display_name_np,
            'display_name_limbu' => $person->display_name_limbu,
            'member_no' => $person->member_no,
            'pusta' => $person->pusta,
        ];
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'spouse1_id' => ['required','integer','different:spouse2_id','exists:persons,id'],
            'spouse2_id' => ['required','integer','exists:persons,id'],
            'type'       => ['required', Rule::in(['marriage','partnership','other','married','partner','divorced','widowed','separated'])],
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
