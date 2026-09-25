<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentChildEdge;
use App\Models\Person;
use App\Support\BirthOrder;
use App\Support\SiblingOrder;
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
            'birth_order'   => ['nullable','integer','min:1','max:30'],
        ]);
        $birthOrder = $data['birth_order'] ?? null;
        unset($data['birth_order']);

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

        // Chosen place must be free among the parent's other children of the same gender
        if ($birthOrder && $parent && $child && isset(SiblingOrder::taken($parent, $child->gender, $child->id)[(int) $birthOrder])) {
            return back()->withErrors(['birth_order' => SiblingOrder::assign($parent, $child, (int) $birthOrder)])->withInput();
        }

        ParentChildEdge::create($data);

        if ($birthOrder && $parent && $child) {
            SiblingOrder::assign($parent, $child, (int) $birthOrder);
        }

        // Child has no pusta → one generation below the parent (२८ → २९)
        $message = 'सम्बन्ध थपियो।';
        $nextPusta = $this->nextPusta($parent?->pusta);
        if ($child && $this->isBlank($child->pusta) && $nextPusta !== null) {
            $child->update(['pusta' => $nextPusta]);
            $message .= " {$child->display_name} को पुस्ता {$nextPusta} राखियो।";
        }

        return back()->with('success', $message);
    }

    /**
     * Data for the add form once a parent (and child) is picked: the parent's
     * existing children with their places, and the free places for the child.
     */
    public function preview(Request $request)
    {
        $parent = Person::find((int) $request->query('parent_id'));
        if (!$parent) return response()->json(null);

        $child = Person::find((int) $request->query('child_id'));
        $siblings = SiblingOrder::siblings($parent, $child?->id);
        $ranks = BirthOrder::rank($siblings);
        $siblings = BirthOrder::sortByRank($siblings, $ranks);

        $row = fn (Person $p) => [
            'id'           => $p->id,
            'display_name' => $p->display_name,
            'gender'       => $p->gender ?: 'unknown',
            'member_no'    => $p->member_no,
            'birth'        => $ranks[$p->id] ?? null,
        ];

        $payload = [
            'parent'   => ['id' => $parent->id, 'display_name' => $parent->display_name, 'pusta' => $parent->pusta],
            'siblings' => $siblings->map($row)->values(),
            'child'    => null,
        ];

        if ($child) {
            $gender = $child->gender ?: 'unknown';
            $taken = SiblingOrder::taken($parent, $gender, $child->id);
            $options = SiblingOrder::options($gender, $taken);
            $default = $child->birth_order && !isset($taken[$child->birth_order]) ? $child->birth_order : ($options[0]['value'] ?? null);

            $payload['child'] = [
                'id'            => $child->id,
                'display_name'  => $child->display_name,
                'gender'        => $gender,
                'relation'      => BirthOrder::relation($gender),
                'pusta'         => $child->pusta,
                'pusta_will_be' => $this->isBlank($child->pusta) ? $this->nextPusta($parent->pusta) : null,
                'options'       => $options,
                'default'       => $default,
            ];
        }

        return response()->json($payload);
    }

    /**
     * People who must not be picked on the other side of the form:
     *  - parent chosen → hide the parent itself and all their ancestors from the child picker
     *  - child chosen  → hide the child itself and all their descendants from the parent picker
     */
    public function blocked(Request $request)
    {
        $id = (int) $request->query('person_id');
        if (!$id) return response()->json([]);

        $upwards = $request->query('as') === 'parent';
        $next = [];
        foreach (ParentChildEdge::select('parent_id', 'child_id')->get() as $e) {
            $upwards
                ? $next[(int) $e->child_id][] = (int) $e->parent_id
                : $next[(int) $e->parent_id][] = (int) $e->child_id;
        }

        $seen = [];
        $stack = [$id];
        while ($stack) {
            $cur = array_pop($stack);
            if (isset($seen[$cur])) continue;
            $seen[$cur] = true;
            foreach ($next[$cur] ?? [] as $n) $stack[] = $n;
        }

        return response()->json(array_keys($seen));
    }

    private function isBlank($value): bool
    {
        return $value === null || trim((string) $value) === '';
    }

    /** "२८" → "२९" (Nepali digits, as pusta is stored); null if the parent's pusta isn't a number. */
    private function nextPusta($pusta): ?string
    {
        $en = strtr(trim((string) $pusta), array_combine(['०','१','२','३','४','५','६','७','८','९'], range(0, 9)));
        if (!ctype_digit($en)) return null;

        return BirthOrder::npDigits((int) $en + 1);
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
