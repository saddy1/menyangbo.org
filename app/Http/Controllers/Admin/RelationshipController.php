<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentChildEdge;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RelationshipController extends Controller
{
 public function index(Request $request)
{
    $q = trim($request->query('q', ''));
    $type = trim($request->query('type', ''));

    $edges = $this->filteredEdges($q, $type)
        ->orderByDesc('id')
        ->paginate(20)
        ->withQueryString();

    return view('admin.relationships.index', compact('edges','q','type'));
}

    public function searchJson(Request $request)
    {
        $q = trim($request->query('q', ''));
        $type = trim($request->query('type', ''));

        $rows = $this->filteredEdges($q, $type)
            ->orderByDesc('id')
            ->limit(80)
            ->get()
            ->map(fn ($edge) => [
                'id' => $edge->id,
                'relation_type' => $edge->relation_type,
                'parent' => $this->personPayload($edge->parent),
                'child' => $this->personPayload($edge->child),
            ])
            ->values();

        return response()->json([
            'count' => $rows->count(),
            'rows' => $rows,
        ]);
    }

    private function filteredEdges(string $q = '', string $type = '')
    {
        return ParentChildEdge::query()
        ->with([
            'parent:id,display_name,display_name_np,display_name_limbu,member_no,pusta,birth_date',
            'child:id,display_name,display_name_np,display_name_limbu,member_no,pusta,birth_date',
        ])
        ->when($type !== '', fn($qq) => $qq->where('relation_type', $type))
        ->when($q !== '', function ($qq) use ($q) {
            $term = $q;

            // allow "#DLUMP01" (member no)
            if (str_starts_with($term, '#')) $term = ltrim($term, '#');

            // allow "pusta:3"
            if (preg_match('/^pusta\s*:\s*(\d+)$/i', $term, $m)) {
                $p = $m[1];
                return $qq->where(function ($w) use ($p) {
                    $w->whereHas('parent', fn($pQ) => $pQ->where('pusta', $p))
                      ->orWhereHas('child', fn($cQ) => $cQ->where('pusta', $p));
                });
            }

            return $qq->where(function ($w) use ($term) {
                $w->whereHas('parent', function ($pQ) use ($term) {
                    $pQ->where('display_name', 'like', "%{$term}%")
                       ->orWhere('display_name_np', 'like', "%{$term}%")
                       ->orWhere('display_name_limbu', 'like', "%{$term}%")
                       ->orWhere('member_no', 'like', "%{$term}%")
                       ->orWhere('pusta', 'like', "%{$term}%");
                })->orWhereHas('child', function ($cQ) use ($term) {
                    $cQ->where('display_name', 'like', "%{$term}%")
                       ->orWhere('display_name_np', 'like', "%{$term}%")
                       ->orWhere('display_name_limbu', 'like', "%{$term}%")
                       ->orWhere('member_no', 'like', "%{$term}%")
                       ->orWhere('pusta', 'like', "%{$term}%");
                });
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
                'birth_year' => null,
            ];
        }

        return [
            'id' => $person->id,
            'display_name' => $person->display_name,
            'display_name_np' => $person->display_name_np,
            'display_name_limbu' => $person->display_name_limbu,
            'member_no' => $person->member_no,
            'pusta' => $person->pusta,
            'birth_year' => $person->birth_date?->format('Y'),
        ];
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'parent_id'     => ['required','integer','exists:persons,id'],
            'child_id'      => ['required','integer','different:parent_id','exists:persons,id'],
            'relation_type' => ['required', Rule::in(['birth','adoption','step','guardianship'])],
            'notes'         => ['nullable','string','max:2000'],
        ]);

        // unique edge
        $exists = ParentChildEdge::where('parent_id',$data['parent_id'])
            ->where('child_id',$data['child_id'])->exists();
        if ($exists) return back()->withErrors(['child_id'=>'यो सम्बन्ध पहिले नै दर्ता छ।'])->withInput();

        // no cycle: child cannot be an ancestor of parent
        if ($this->createsCycle($data['parent_id'], $data['child_id'])) {
            return back()->withErrors(['child_id'=>'यो सम्बन्धले चक्र बनाउँछ (अवैध वंशावली)।'])->withInput();
        }

        // min age check (if both birth dates known): parent ≥ child + 12y
        $parent = Person::find($data['parent_id']);
        $child  = Person::find($data['child_id']);
        if ($parent && $child && $parent->birth_date && $child->birth_date) {
            $min = $parent->birth_date->copy()->addYears(12);
            if ($min->greaterThan($child->birth_date)) {
                return back()->withErrors(['child_id'=>'अभिभावक र सन्तान उमेर अन्तर कम्तिमा १२ वर्ष हुनुपर्छ।'])->withInput();
            }
        }

        ParentChildEdge::create($data);
        return back()->with('success','सम्बन्ध थपियो।');
    }

    public function destroy(ParentChildEdge $edge)
    {
        $edge->delete();
        return back()->with('success','सम्बन्ध मेटाइयो।');
    }

    // DFS from child to see if we can reach parent -> would create a cycle
    private function createsCycle(int $newParentId, int $childId): bool
    {
        // build adjacency child->parents? we need reachability from child back up to parent
        // We'll traverse upwards: from newParentId, can we reach childId? (i.e., is child an ancestor of parent?)
        // Use existing edges: parent->child. So check if there is a path childId -> ... -> newParentId using reverse traversal.
        $stack = [$newParentId];
        $visited = [];
        // map parent->children (forward)
        $edges = ParentChildEdge::select('parent_id','child_id')->get();
        $childrenByParent = [];
        foreach ($edges as $e) $childrenByParent[$e->parent_id][] = $e->child_id;

        while ($stack) {
            $current = array_pop($stack);
            if (isset($visited[$current])) continue;
            $visited[$current] = true;
            if ($current === $childId) return true; // cycle
            foreach ($childrenByParent[$current] ?? [] as $c) $stack[] = $c;
        }
        return false;
    }
}
