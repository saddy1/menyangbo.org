<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentChildEdge;
use App\Models\Person;
use App\Models\UnionModel;
use Illuminate\Http\Request;

class PrintTreeController extends Controller
{
    private array $visited = [];

    public function form()
    {
        $pustas = Person::whereNotNull('pusta')
            ->distinct()
            ->orderBy('pusta')
            ->pluck('pusta');

        $firstPersons = Person::select('id', 'display_name', 'display_name_np', 'pusta', 'gender', 'birth_date')
            ->orderBy('pusta')
            ->orderBy('birth_date')
            ->orderBy('id')
            ->get();

        $personsByPusta = $firstPersons->groupBy('pusta')->map(function ($group) {
            return $group->map(function ($p) {
                return [
                    'id'         => $p->id,
                    'name'       => $p->display_name,
                    'name_np'    => $p->display_name_np,
                    'gender'     => $p->gender,
                    'pusta'      => $p->pusta,
                    'birth_year' => $p->birth_date?->format('Y'),
                ];
            })->values();
        });

        return view('admin.print-tree.form', compact('pustas', 'firstPersons', 'personsByPusta'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'root_id'   => ['required', 'integer', 'exists:persons,id'],
            'max_pusta' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $rootId   = (int) $request->root_id;
        $maxPusta = (int) $request->max_pusta;

        // ─── Step 1: pre-load ALL parent→child edges as adjacency list ───
        $adjacency = [];
        ParentChildEdge::select('parent_id', 'child_id')
            ->cursor()
            ->each(function ($edge) use (&$adjacency) {
                $adjacency[$edge->parent_id][] = $edge->child_id;
            });

        // ─── Step 2: BFS to collect every descendant ID up to maxPusta hops ───
        $allIds    = [$rootId => 0]; // id => depth
        $frontier  = [$rootId];
        $depth     = 0;

        while ($depth < $maxPusta && !empty($frontier)) {
            $next = [];
            foreach ($frontier as $pid) {
                foreach ($adjacency[$pid] ?? [] as $cid) {
                    if (!isset($allIds[$cid])) {
                        $allIds[$cid] = $depth + 1;
                        $next[]       = $cid;
                    }
                }
            }
            $frontier = $next;
            $depth++;
        }

        $maxActualDepth = max($allIds);

        // ─── Step 3: load all persons in 1 query ───
        $personMap = Person::whereIn('id', array_keys($allIds))
            ->select('id', 'display_name', 'display_name_np', 'gender',
                     'birth_date', 'death_date', 'is_deceased', 'pusta', 'member_no')
            ->get()
            ->keyBy('id');

        // ─── Step 4: load unions only for those persons (spouses) ───
        $ids = array_keys($allIds);
        $spouseMap = []; // person_id => [spouse, ...]

        UnionModel::whereIn('spouse1_id', $ids)->orWhereIn('spouse2_id', $ids)
            ->with([
                'spouse1:id,display_name,gender',
                'spouse2:id,display_name,gender',
            ])
            ->get()
            ->each(function ($union) use ($ids, &$spouseMap) {
                if (in_array($union->spouse1_id, $ids) && $union->spouse2) {
                    $spouseMap[$union->spouse1_id][] = $union->spouse2;
                }
                if (in_array($union->spouse2_id, $ids) && $union->spouse1) {
                    $spouseMap[$union->spouse2_id][] = $union->spouse1;
                }
            });

        // ─── Step 5: build nested PHP tree in memory ───
        $this->visited = [];
        $tree = $this->buildNode($rootId, $adjacency, $personMap, $spouseMap, $maxPusta, 0);

        return view('admin.print-tree.show', [
            'tree'           => $tree,
            'treeData'       => $tree ? $this->serializeNode($tree) : null,
            'root'           => $personMap[$rootId] ?? null,
            'maxPusta'       => $maxPusta,
            'actualDepth'    => $maxActualDepth,
            'totalNodes'     => count($allIds),
        ]);
    }

    private function buildNode(
        int $personId,
        array $adjacency,
        $personMap,
        array $spouseMap,
        int $maxPusta,
        int $depth
    ): ?array {
        if ($depth >= $maxPusta || isset($this->visited[$personId])) {
            return null;
        }

        $this->visited[$personId] = true;

        $person = $personMap[$personId] ?? null;
        if (!$person) return null;

        $children = [];
        foreach ($adjacency[$personId] ?? [] as $childId) {
            if (!isset($this->visited[$childId])) {
                $child = $this->buildNode($childId, $adjacency, $personMap, $spouseMap, $maxPusta, $depth + 1);
                if ($child) $children[] = $child;
            }
        }

        // Sort children: daughters last (optional — comment out if unwanted)
        usort($children, fn($a, $b) => strcmp($a['person']->gender ?? 'z', $b['person']->gender ?? 'z'));

        return [
            'person'   => $person,
            'spouses'  => $spouseMap[$personId] ?? [],
            'children' => $children,
            'depth'    => $depth,
        ];
    }

    private function serializeNode(array $node): array
    {
        $person = $node['person'];

        return [
            'person' => [
                'id' => $person->id,
                'display_name' => $person->display_name,
                'display_name_np' => $person->display_name_np,
                'gender' => $person->gender,
                'birth_year' => $person->birth_date?->format('Y'),
                'death_year' => $person->death_date?->format('Y'),
                'is_deceased' => (bool) $person->is_deceased,
                'pusta' => $person->pusta,
                'member_no' => $person->member_no,
            ],
            'spouses' => collect($node['spouses'])->map(function ($spouse) {
                return [
                    'id' => $spouse->id,
                    'display_name' => $spouse->display_name,
                    'gender' => $spouse->gender,
                ];
            })->values()->all(),
            'depth' => $node['depth'],
            'children' => array_map(fn ($child) => $this->serializeNode($child), $node['children']),
        ];
    }
}
