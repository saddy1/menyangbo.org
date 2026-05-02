<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\ParentChildEdge;
use App\Models\UnionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class TreeController extends Controller
{
public function memberPage(Person $person)
{
    $person->load([
        'parents:id,display_name,gender,pusta,birth_date,death_date,is_deceased,photo_path,member_no',
        'children:id,display_name,gender,pusta,birth_date,is_deceased,member_no',
        'unionsAsSpouse1.spouse2:id,display_name,gender,birth_date,is_deceased,photo_path',
        'unionsAsSpouse2.spouse1:id,display_name,gender,birth_date,is_deceased,photo_path',
        'events',
    ]);

    $father = $person->parents->firstWhere('gender', 'male');
    $mother = $person->parents->firstWhere('gender', 'female');

    // Load grandfather & grandmother (father's parents)
    $grandfather = null;
    $grandmother = null;
    if ($father) {
        $father->load(['parents:id,display_name,gender,birth_date,death_date,is_deceased,photo_path']);
        $grandfather = $father->parents->firstWhere('gender', 'male');
        $grandmother = $father->parents->firstWhere('gender', 'female');
    }

    // Spouses
    $spouses = collect();
    foreach ($person->unionsAsSpouse1 as $u) {
        if ($u->spouse2) $spouses->push($u->spouse2);
    }
    foreach ($person->unionsAsSpouse2 as $u) {
        if ($u->spouse1) $spouses->push($u->spouse1);
    }
    $spouses = $spouses->unique('id')->values();
    $spouses->each(fn ($spouse) => $spouse->loadMissing([
        'children:id,display_name,gender,pusta,birth_date,is_deceased',
    ]));
    $children = $this->sharedChildrenFor($person, $spouses);

    return view('tree.member', [
        'person'      => $person,
        'father'      => $father,
        'mother'      => $mother,
        'grandfather' => $grandfather,
        'grandmother' => $grandmother,
        'spouses'     => $spouses,
        'children'    => $children,
    ]);
}

    private function tinyPerson(Person $person): array
    {
        return [
            'id' => (string) $person->id,
            'display_name' => $person->display_name,
            'display_name_np' => $person->display_name_np,
            'display_name_limbu' => $person->display_name_limbu,
            'gender' => $person->gender ?: 'unknown',
            'pusta' => $person->pusta,
            'photo_path' => $person->photo_path,
        ];
    }

    public function committee()
    {
        return view('committee');
    }

    public function index(Request $request)
    {
        $rootId = (int)($request->query('root_id') ?: (Person::query()->orderBy('id')->value('id') ?? 0));

        $pustas = Person::query()
            ->whereNotNull('pusta')
            ->distinct()
            ->orderBy('pusta')
            ->pluck('pusta')
            ->values();

        return view('tree.simple', [
            'rootId' => $rootId,
            'pustas' => $pustas,
        ]);
    }

    /** Search by ID / Nepali name / English name + optional pusta filter */
    public function searchPeople(Request $request)
    {
        $term  = trim($request->query('term', ''));
        $pusta = trim($request->query('pusta', ''));

        if ($term === '' && $pusta === '') return response()->json([]);

        $hasEn1 = Schema::hasColumn('people', 'display_name_en');
        $hasEn2 = Schema::hasColumn('people', 'name_en');

        $q = Person::query()
            ->with(['parents:id,display_name,gender'])
            ->select('id', 'display_name', 'display_name_np', 'display_name_limbu', 'member_no', 'membership', 'gender', 'pusta');
        if ($hasEn1) $q->addSelect('display_name_en');
        if ($hasEn2) $q->addSelect('name_en');

        if ($pusta !== '') $q->where('pusta', $pusta);

        if ($term !== '') {
            $q->where(function ($w) use ($term, $hasEn1, $hasEn2) {
                if (ctype_digit($term)) $w->orWhere('id', (int)$term);
                $w->orWhere('display_name', 'like', "%{$term}%");
                $w->orWhere('display_name_np', 'like', "%{$term}%");
                $w->orWhere('display_name_limbu', 'like', "%{$term}%");
                $w->orWhere('member_no', 'like', "%{$term}%");
                $w->orWhere('membership', 'like', "%{$term}%");
                if ($hasEn1) $w->orWhere('display_name_en', 'like', "%{$term}%");
                if ($hasEn2) $w->orWhere('name_en', 'like', "%{$term}%");
            });
        }

        $rows = $q->orderBy('display_name')->limit(30)->get()->values();

        $mapped = $rows->map(function ($r) use ($hasEn1, $hasEn2) {
            $en = null;
            if ($hasEn1 && !empty($r->display_name_en)) $en = $r->display_name_en;
            if (!$en && $hasEn2 && !empty($r->name_en)) $en = $r->name_en;

            return [
                'id' => (string)$r->id,
                'display_name' => $r->display_name,
                'display_name_np' => $r->display_name_np,
                'display_name_limbu' => $r->display_name_limbu,
                'name_en' => $en,
                'member_no' => $r->member_no,
                'member_number' => $r->member_no ?: $r->membership,
                'father_name' => optional($r->parents->firstWhere('gender', 'male'))->display_name,
                'gender' => $r->gender ?: 'unknown',
                'pusta'  => $r->pusta,
            ];
        });

        return response()->json($mapped->values());
    }

    /** First person (elder) of a pusta => used by ▲/▼ buttons */
    public function firstPersonByPusta(Request $request)
    {
        $pusta = trim($request->query('pusta', ''));
        if ($pusta === '') return response()->json(null);

        $person = Person::query()
            ->where('pusta', $pusta)
            ->orderByRaw('CASE WHEN birth_date IS NULL THEN 1 ELSE 0 END, birth_date ASC')
            ->orderBy('id')
            ->first(['id', 'display_name', 'gender', 'pusta']);

        return response()->json($person);
    }

    /** Hover / detail panel JSON */
    public function personShow(Person $person)
    {
        $person->load([
            'parents:id,display_name,display_name_np,display_name_limbu,gender,pusta,photo_path',
            'parents.parents:id,display_name,display_name_np,display_name_limbu,gender,pusta,photo_path',
            'children:id,display_name,display_name_np,display_name_limbu,gender,pusta,photo_path',
            'unionsAsSpouse1.spouse2:id,display_name,display_name_np,display_name_limbu,gender,pusta,photo_path',
            'unionsAsSpouse2.spouse1:id,display_name,display_name_np,display_name_limbu,gender,pusta,photo_path',
        ]);

        $father = $person->parents->firstWhere('gender', 'male');
        $mother = $person->parents->firstWhere('gender', 'female');
        $grandfather = $father?->parents?->firstWhere('gender', 'male');

        $spouses = $person->unionsAsSpouse1
            ->map(fn($u) => $u->spouse2)
            ->merge($person->unionsAsSpouse2->map(fn($u) => $u->spouse1))
            ->filter()
            ->unique('id')
            ->values();
        $spouses->each(fn ($spouse) => $spouse->loadMissing([
            'children:id,display_name,display_name_np,display_name_limbu,gender,pusta,photo_path',
        ]));
        $children = $this->sharedChildrenFor($person, $spouses);

        return response()->json([
            'id' => (string)$person->id,
            'display_name' => $person->display_name,
            'display_name_np' => $person->display_name_np,
            'display_name_limbu' => $person->display_name_limbu,
            'gender' => $person->gender ?: 'unknown',
            'pusta' => $person->pusta,
            'bio' => $person->bio ?? null,
            'photo_path' => $person->photo_path ?? null,
            'is_deceased' => (bool)($person->is_deceased ?? false),
            'birth_date' => $person->birth_date ? $person->birth_date->format('Y-m-d') : null,
            'death_date' => $person->death_date ? $person->death_date->format('Y-m-d') : null,
            'father' => $father ? $this->tinyPerson($father) : null,
            'mother' => $mother ? $this->tinyPerson($mother) : null,
            'grandfather' => $grandfather ? $this->tinyPerson($grandfather) : null,
            'parents' => ($person->parents ?? collect())->map(fn($p) => $this->tinyPerson($p))->values(),
            'spouses' => $spouses->map(fn($s) => $this->tinyPerson($s))->values(),
            'children' => $children->map(fn($c) => $this->tinyPerson($c))->values(),
        ]);
    }

    private function sharedChildrenFor(Person $person, $spouses)
    {
        return collect($person->children ?? [])
            ->merge(collect($spouses)->flatMap(fn ($spouse) => $spouse->children ?? collect()))
            ->filter()
            ->unique('id')
            ->sortBy([
                fn ($a, $b) => ($a->birth_date?->timestamp ?? PHP_INT_MAX) <=> ($b->birth_date?->timestamp ?? PHP_INT_MAX),
                fn ($a, $b) => $a->id <=> $b->id,
            ])
            ->values();
    }

    /** Tree JSON (spouse embedded in person node; NO union nodes) */
    public function treeJson(Request $request)
    {
        $rootId = (int)($request->query('root_id') ?: 0);
        $depth  = (int)($request->query('depth') ?: 5);
        if (!in_array($depth, [2,5,10,15,20,30], true)) {
            $depth = 5;
        }

        if (!$rootId) {
            $rootId = (int)(Person::query()->orderBy('id')->value('id') ?? 0);
            if (!$rootId) return response()->json([]);
        }

        // Load all people minimal fields
        $people = Person::query()
            ->select('id','display_name','display_name_np','display_name_limbu','gender','pusta','birth_date','is_deceased','photo_path')
            ->get()
            ->keyBy('id');

        // Parent-child edges
        $edges = ParentChildEdge::query()
            ->select('parent_id','child_id','relation_type')
            ->whereIn('relation_type', ['birth','adoption'])
            ->get();

        $childrenByParent = [];
        foreach ($edges as $e) {
            $childrenByParent[(int)$e->parent_id][] = (int)$e->child_id;
        }

        // Sort children by birth_date then id
        foreach ($childrenByParent as $pid => $childIds) {
            usort($childIds, function ($a, $b) use ($people) {
                $pa = $people[$a] ?? null;
                $pb = $people[$b] ?? null;
                $da = $pa?->birth_date?->format('Y-m-d') ?? '9999-99-99';
                $db = $pb?->birth_date?->format('Y-m-d') ?? '9999-99-99';
                return $da === $db ? ($a <=> $b) : ($da <=> $db);
            });
            $childrenByParent[$pid] = $childIds;
        }

        // Unions
        $unions = UnionModel::query()->select('id','spouse1_id','spouse2_id')->get();

        $unionsByPerson = [];
        foreach ($unions as $u) {
            $unionsByPerson[(int)$u->spouse1_id][] = $u;
            $unionsByPerson[(int)$u->spouse2_id][] = $u;
        }

        // Helper: common children of a couple (intersection, preserve parent A order)
        $commonChildren = function(int $a, int $b) use ($childrenByParent) {
            $ca = $childrenByParent[$a] ?? [];
            $cb = $childrenByParent[$b] ?? [];
            if (!$ca || !$cb) return [];
            $setB = array_fill_keys($cb, true);
            $out = [];
            foreach ($ca as $cid) if (isset($setB[$cid])) $out[] = $cid;
            return $out;
        };

        $buildPersonNode = function(int $pid, int $level, array $stack = []) use (
            &$buildPersonNode, $depth, $people, $childrenByParent, $unionsByPerson, $commonChildren
        ) {
            if (!isset($people[$pid])) return null;
            if (isset($stack[$pid])) return null; // prevent loops
            $stack[$pid] = true;

            $p = $people[$pid];

            $node = [
                'id' => (string)$p->id,
                'type' => 'person',
                'name' => $p->display_name,
                'name_np' => $p->display_name_np,
                'name_limbu' => $p->display_name_limbu,
                'gender' => $p->gender ?: 'unknown',
                'pusta' => $p->pusta,
                'photo_path' => $p->photo_path,
                'is_deceased' => (bool)($p->is_deceased ?? false),
                'children' => [],
            ];

            // The selected depth means visible generations including the root.
            // Example: depth=2 => root + children only.
            if ($level >= $depth - 1) return $node;

            // All spouses embedded
            $spousesArr    = [];
            $unionKids     = [];
            $addedUnionKids = [];

            foreach (($unionsByPerson[$pid] ?? []) as $u) {
                $spouseId = ((int)$u->spouse1_id === $pid) ? (int)$u->spouse2_id : (int)$u->spouse1_id;

                if (isset($people[$spouseId])) {
                    $sp = $people[$spouseId];

                    $spousesArr[] = [
                        'id'       => (string)$sp->id,
                        'name'     => $sp->display_name,
                        'name_np'  => $sp->display_name_np,
                        'name_limbu' => $sp->display_name_limbu,
                        'gender'   => $sp->gender ?: 'unknown',
                        'pusta'    => $sp->pusta,
                        'photo_path' => $sp->photo_path,
                        'is_deceased' => (bool)($sp->is_deceased ?? false),
                    ];

                    foreach ($commonChildren($pid, $spouseId) as $cid) {
                        if (!isset($addedUnionKids[$cid])) {
                            $addedUnionKids[$cid] = true;
                            $unionKids[] = $cid;
                        }
                    }
                }
            }

            if (!empty($spousesArr)) {
                $node['spouses'] = $spousesArr;
                $node['spouse']  = $spousesArr[0]; // backward compat
            }

            // ✅ children: couple kids first, then remaining
            $added = [];

            foreach ($unionKids as $cid) {
                $added[$cid] = true;
                $childNode = $buildPersonNode((int)$cid, $level + 1, $stack);
                if ($childNode) $node['children'][] = $childNode;
            }

            foreach (($childrenByParent[$pid] ?? []) as $cid) {
                if (isset($added[$cid])) continue;
                $childNode = $buildPersonNode((int)$cid, $level + 1, $stack);
                if ($childNode) $node['children'][] = $childNode;
            }

            return $node;
        };

        $tree = $buildPersonNode($rootId, 0) ?: [];

        // expose root pusta for your UI buttons
        if (!empty($tree['id']) && isset($people[$rootId])) {
            $tree['pusta'] = $people[$rootId]->pusta;
        }

        return response()->json($tree);
    }
}
