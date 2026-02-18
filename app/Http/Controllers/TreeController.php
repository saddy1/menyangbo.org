<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\ParentChildEdge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class TreeController extends Controller
{
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

        $q = Person::query()->select('id', 'display_name', 'gender', 'pusta');

        if ($hasEn1) $q->addSelect('display_name_en');
        if ($hasEn2) $q->addSelect('name_en');

        if ($pusta !== '') {
            $q->where('pusta', $pusta);
        }

        if ($term !== '') {
            $q->where(function ($w) use ($term, $hasEn1, $hasEn2) {
                // numeric id match
                if (ctype_digit($term)) {
                    $w->orWhere('id', (int)$term);
                }

                // Nepali name
                $w->orWhere('display_name', 'like', "%{$term}%");

                // English name columns (if exist)
                if ($hasEn1) $w->orWhere('display_name_en', 'like', "%{$term}%");
                if ($hasEn2) $w->orWhere('name_en', 'like', "%{$term}%");
            });
        }

        $rows = $q->orderBy('display_name')->limit(30)->get()->values();

        // return unified "name_en" for UI
        $mapped = $rows->map(function ($r) use ($hasEn1, $hasEn2) {
            $en = null;
            if ($hasEn1 && !empty($r->display_name_en)) $en = $r->display_name_en;
            if (!$en && $hasEn2 && !empty($r->name_en)) $en = $r->name_en;

            return [
                'id' => (string)$r->id,
                'display_name' => $r->display_name, // Nepali (shown always)
                'name_en' => $en,                   // English (optional)
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

    /** Click detail panel */
    public function personShow(Person $person)
    {
        $person->load([
            'parents:id,display_name,gender,pusta',
            'children:id,display_name,gender,pusta',
        ]);

        return response()->json([
            'id' => (string)$person->id,
            'display_name' => $person->display_name,
            'gender' => $person->gender ?: 'unknown',
            'pusta' => $person->pusta,
            'bio' => $person->bio ?? null,
            'photo_path' => $person->photo_path ?? null,
            'is_deceased' => (bool)($person->is_deceased ?? false),
            'birth_date' => $person->birth_date ? $person->birth_date->format('Y-m-d') : null,
            'death_date' => $person->death_date ? $person->death_date->format('Y-m-d') : null,
            'parents' => ($person->parents ?? collect())->map(fn($p)=>[
                'id'=>(string)$p->id,'display_name'=>$p->display_name,'gender'=>$p->gender,'pusta'=>$p->pusta
            ])->values(),
            'children' => ($person->children ?? collect())->map(fn($c)=>[
                'id'=>(string)$c->id,'display_name'=>$c->display_name,'gender'=>$c->gender,'pusta'=>$c->pusta
            ])->values(),
        ]);
    }

    /** Tree JSON (depth = generation) */
public function treeJson(Request $request)
{
    $rootId = (int)($request->query('root_id') ?: 0);
    $depth  = (int)($request->query('depth') ?: 5);

    if (!$rootId) {
        $rootId = (int)(Person::query()->orderBy('id')->value('id') ?? 0);
        if (!$rootId) return response()->json([]);
    }

    $people = Person::query()
        ->select('id', 'display_name', 'gender', 'pusta', 'birth_date')
        ->get()
        ->keyBy('id');

    $edges = ParentChildEdge::query()
        ->select('parent_id', 'child_id', 'relation_type')
        ->whereIn('relation_type', ['birth', 'adoption'])
        ->get();

    $childrenByParent = [];
    foreach ($edges as $e) {
        $childrenByParent[$e->parent_id][] = $e->child_id;
    }

    // Optional: sort children by birth_date then id (stable)
    foreach ($childrenByParent as $pid => $childIds) {
        usort($childIds, function ($a, $b) use ($people) {
            $pa = $people[$a] ?? null;
            $pb = $people[$b] ?? null;
            if (!$pa && !$pb) return 0;
            if (!$pa) return 1;
            if (!$pb) return -1;

            $da = $pa->birth_date ? $pa->birth_date->format('Y-m-d') : '9999-99-99';
            $db = $pb->birth_date ? $pb->birth_date->format('Y-m-d') : '9999-99-99';

            if ($da === $db) return ($pa->id <=> $pb->id);
            return ($da <=> $db);
        });
        $childrenByParent[$pid] = $childIds;
    }

    $build = function ($pid, $level = 0, $stack = []) use (&$build, $depth, $people, $childrenByParent) {
        if (!isset($people[$pid])) return null;

        // prevent infinite loops only on current path
        if (isset($stack[$pid])) return null;
        $stack[$pid] = true;

        $p = $people[$pid];

        $node = [
            'id' => (string)$p->id,
            'name' => $p->display_name,
            'gender' => $p->gender ?: 'unknown',
            'pusta' => $p->pusta,
            'children' => [],
        ];

        if ($level < $depth) {
            foreach ($childrenByParent[$p->id] ?? [] as $cid) {
                $child = $build($cid, $level + 1, $stack);
                if ($child) $node['children'][] = $child;
            }
        }

        return $node;
    };

    return response()->json($build($rootId, 0) ?: []);
}

}
