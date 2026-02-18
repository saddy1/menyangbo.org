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

    // people list for dropdowns
    $people = Person::orderBy('display_name')
        ->get(['id','display_name','member_no','pusta']);

    $edges = ParentChildEdge::query()
        ->with([
            'parent:id,display_name,display_name_np,member_no,pusta,birth_date',
            'child:id,display_name,display_name_np,member_no,pusta,birth_date',
        ])
        ->when($type !== '', fn($qq) => $qq->where('relation_type', $type))
        ->when($q !== '', function ($qq) use ($q) {
            $term = $q;

            // allow "#DLUMP01" (member no)
            if (str_starts_with($term, '#')) $term = ltrim($term, '#');

            // allow "pusta:3"
            if (preg_match('/^pusta\s*:\s*(\d+)$/i', $term, $m)) {
                $p = $m[1];
                return $qq->whereHas('parent', fn($pQ) => $pQ->where('pusta', $p))
                          ->orWhereHas('child', fn($cQ) => $cQ->where('pusta', $p));
            }

            return $qq->where(function ($w) use ($term) {
                $w->whereHas('parent', function ($pQ) use ($term) {
                    $pQ->where('display_name', 'like', "%{$term}%")
                       ->orWhere('display_name_np', 'like', "%{$term}%")
                       ->orWhere('member_no', 'like', "%{$term}%")
                       ->orWhere('pusta', 'like', "%{$term}%");
                })->orWhereHas('child', function ($cQ) use ($term) {
                    $cQ->where('display_name', 'like', "%{$term}%")
                       ->orWhere('display_name_np', 'like', "%{$term}%")
                       ->orWhere('member_no', 'like', "%{$term}%")
                       ->orWhere('pusta', 'like', "%{$term}%");
                });
            });
        })
        ->orderByDesc('id')
        ->paginate(20)
        ->withQueryString();

    return view('admin.relationships.index', compact('people','edges','q','type'));
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
