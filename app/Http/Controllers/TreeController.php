<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\UnionModel;
use App\Models\ParentChildEdge;
use Illuminate\Http\Request;

class TreeController extends Controller
{
 public function index(Request $request)
{
    $rootId = $request->query('root_id') ?? Person::query()->orderBy('id')->value('id');

    if (!$rootId) {
        return view('tree.index', [
            'root'       => null,
            'levels'     => [],
            'allPeople'  => collect(),
            'pustas'     => collect(),
        ]);
    }

    $root = Person::with([
        'parents','children','childEdges','parentEdges',
        'unionsAsSpouse1.spouse2','unionsAsSpouse2.spouse1'
    ])->findOrFail($rootId);

    // BFS just for the small "levels" view if you use it elsewhere
    $levels = [];
    $visited = [];
    $queue = [[$root, 0]];
    while (!empty($queue)) {
        [$node, $lvl] = array_shift($queue);
        if (isset($visited[$node->id])) continue;
        $visited[$node->id] = true;
        $levels[$lvl] = $levels[$lvl] ?? [];
        $levels[$lvl][] = $node;
        foreach ($node->children as $c) $queue[] = [$c, $lvl+1];
    }

    $allPeople = Person::orderBy('display_name')->get(['id','display_name']);
    $pustas    = Person::whereNotNull('pusta')->distinct()->orderBy('pusta')->pluck('pusta');

    return view('tree.index', compact('root','levels','allPeople','pustas'));
}

/** Name + optional pusta filter (for instant search) */
public function searchPeople(Request $request)
{
    $term  = trim($request->query('term', ''));
    $pusta = trim($request->query('pusta', ''));

    $q = Person::query();
    if ($term !== '')  $q->where('display_name', 'like', "%{$term}%");
    if ($pusta !== '') $q->where('pusta', $pusta);

    return response()->json(
        $q->orderBy('display_name')->limit(50)->get(['id','display_name','pusta'])
    );
}

/** List people in a selected pusta (for the pusta dropdown) */
public function peopleByPusta(Request $request)
{
    $pusta = trim($request->query('pusta',''));
    if ($pusta === '') return response()->json([]);

    // Older/elder first if dates exist, else by name
    $rows = Person::where('pusta', $pusta)
        ->orderByRaw('CASE WHEN birth_date IS NULL THEN 1 ELSE 0 END, birth_date ASC')
        ->orderBy('display_name')
        ->limit(100)
        ->get(['id','display_name','pusta']);

    return response()->json($rows);
}

    // --- Graph endpoint (nodes/links for force/union graph) ---
    public function graph(Request $request)
    {
        $rootId   = $request->query('root_id') ?? Person::query()->orderBy('id')->value('id');
        $maxDepth = (int)($request->query('depth') ?? 1000);
        if (!$rootId) return response()->json(['nodes'=>[], 'links'=>[]]);

        // Load all minimal data once
        $people = Person::query()
            ->select('id','display_name','gender','is_deceased','birth_date','death_date','pusta','photo_path')
            ->get()->keyBy('id');

        $unions = UnionModel::query()
            ->select('id','spouse1_id','spouse2_id','type','start_date')
            ->get();

        $edges = ParentChildEdge::query()
            ->select('parent_id','child_id','relation_type')
            ->get();

        // childrenByParent map
        $childrenByParent = [];
        foreach ($edges as $e) {
            $childrenByParent[$e->parent_id][$e->child_id] = true;
        }

        // unionsByPerson map
        $unionsByPerson = [];
        foreach ($unions as $u) {
            $unionsByPerson[$u->spouse1_id][] = $u;
            $unionsByPerson[$u->spouse2_id][] = $u;
        }

        $nodes       = [];
        $links       = [];
        $seenNode    = [];
        $seenLink    = [];
        $q           = [[$rootId, 0]];
        $visitedPerson = [];

        // Helper: add person node
        $addPerson = function ($pId, $level) use (&$nodes, &$seenNode, $people) {
            if (!isset($people[$pId])) return;
            if (!isset($seenNode[$pId])) {
                $p = $people[$pId];
                $nodes[] = [
                    'id'          => (string)$p->id,
                    'type'        => 'person',
                    'name'        => $p->display_name,
                    'gender'      => $p->gender,
                    'is_deceased' => (bool)$p->is_deceased,
                    'birth_date'  => optional($p->birth_date)->format('Y-m-d'),
                    'death_date'  => optional($p->death_date)->format('Y-m-d'),
                    'pusta'       => $p->pusta,
                    'photo'       => $p->photo_path,
                    'level'       => $level,
                ];
                $seenNode[$pId] = true;
            }
        };

        // Helper: add union node (U###)
        $addUnion = function ($u, $level) use (&$nodes, &$seenNode, $people) {
            $uid = 'U' . $u->id;
            if (!isset($seenNode[$uid])) {
                $sp1   = $people[$u->spouse1_id] ?? null;
                $sp2   = $people[$u->spouse2_id] ?? null;
                $label = trim(($sp1->display_name ?? '---') . ' + ' . ($sp2->display_name ?? '---'));
                $nodes[] = [
                    'id'         => $uid,
                    'type'       => 'union',
                    'name'       => $label,
                    'start_date' => optional($u->start_date)->format('Y-m-d'),
                    'level'      => $level,
                ];
                $seenNode[$uid] = true;
            }
            return $uid;
        };

        // Helper: add directed edge
        $addEdge = function ($src, $dst) use (&$links, &$seenLink) {
            $key = $src . '>' . $dst;
            if (!isset($seenLink[$key])) {
                $links[] = ['source' => $src, 'target' => $dst];
                $seenLink[$key] = true;
            }
        };

        while (!empty($q)) {
            [$pid, $lvl] = array_shift($q);
            if (isset($visitedPerson[$pid]) || $lvl > $maxDepth) continue;
            $visitedPerson[$pid] = true;

            // Person node
            $addPerson($pid, $lvl);

            // For each union of this person: spouse -> union; union -> shared children; enqueue children
            foreach (($unionsByPerson[$pid] ?? []) as $u) {
                $uid = $addUnion($u, $lvl); // union node at same level as spouses

                // spouse links
                $addPerson($u->spouse1_id, $lvl);
                $addPerson($u->spouse2_id, $lvl);
                $addEdge((string)$u->spouse1_id, $uid);
                $addEdge((string)$u->spouse2_id, $uid);

                // shared children = intersection of children(sp1) ∩ children(sp2)
                $c1 = array_keys($childrenByParent[$u->spouse1_id] ?? []);
                $c2 = array_keys($childrenByParent[$u->spouse2_id] ?? []);
                $shared = array_values(array_intersect($c1, $c2));

                foreach ($shared as $cid) {
                    $addPerson($cid, $lvl + 1);
                    $addEdge($uid, (string)$cid);
                    if (!isset($visitedPerson[$cid])) $q[] = [$cid, $lvl + 1];
                }
            }

            // Single-parent children (no union found)
            $kids = array_keys($childrenByParent[$pid] ?? []);
            foreach ($kids as $cid) {
                // If already covered by a union above, skip
                $covered = false;
                foreach (($unionsByPerson[$pid] ?? []) as $u) {
                    $both = isset($childrenByParent[$u->spouse1_id][$cid]) && isset($childrenByParent[$u->spouse2_id][$cid]);
                    if ($both) { $covered = true; break; }
                }
                if ($covered) continue;

                $addPerson($cid, $lvl + 1);
                $addEdge((string)$pid, (string)$cid); // direct fallback
                if (!isset($visitedPerson[$cid])) $q[] = [$cid, $lvl + 1];
            }
        }

        return response()->json(['nodes' => $nodes, 'links' => $links]);
    }

    // --- Hierarchical tree (for D3 tree layout) ---
    public function treeJson(Request $request)
    {
        $rootId   = (int)($request->query('root_id') ?? 0);
        $maxDepth = (int)($request->query('depth') ?? 1000);

        if (!$rootId) {
            $rootId = Person::query()->orderBy('id')->value('id') ?? 0;
            if (!$rootId) return response()->json([]);
        }

        // Load once — include pusta here so the node carries it
        $people = Person::query()
            ->select('id','display_name','gender','is_deceased','birth_date','death_date','photo_path','pusta') // ← added pusta
            ->get()->keyBy('id');

        // children by parent (birth/adoption)
        $edges = ParentChildEdge::query()
            ->select('parent_id','child_id','relation_type')
            ->get();

        $childrenByParent = [];
        foreach ($edges as $e) {
            if (!in_array($e->relation_type, ['birth','adoption'])) continue;
            $childrenByParent[$e->parent_id][] = $e->child_id;
        }

        // spouses (for badge/tooltip)
        $unions = UnionModel::query()
            ->select('id','spouse1_id','spouse2_id','start_date')
            ->get();

        $spousesByPerson = [];
        foreach ($unions as $u) {
            $spousesByPerson[$u->spouse1_id][] = $u->spouse2_id;
            $spousesByPerson[$u->spouse2_id][] = $u->spouse1_id;
        }

        $visited = [];
        $build = function ($pid, $level = 0) use (&$build, &$visited, $maxDepth, $people, $childrenByParent, $spousesByPerson) {
            if (isset($visited[$pid]) || !isset($people[$pid])) return null;
            $visited[$pid] = true;

            $p = $people[$pid];

            $node = [
                'id'          => (string)$p->id,
                'type'        => 'person',
                'name'        => $p->display_name,
                'gender'      => $p->gender,
                'is_deceased' => (bool)$p->is_deceased,
                'birth_date'  => optional($p->birth_date)->format('Y-m-d'),
                'death_date'  => optional($p->death_date)->format('Y-m-d'),
                'pusta'       => $p->pusta, // ← included here for the circle label
                'photo'       => $p->photo_path,
                'spouses'     => array_values(array_unique(array_map(function ($sid) use ($people) {
                    return $people[$sid]->display_name ?? null;
                }, $spousesByPerson[$p->id] ?? []))),
                'children'    => [],
            ];

            if ($level < $maxDepth) {
                foreach ($childrenByParent[$p->id] ?? [] as $cid) {
                    $child = $build($cid, $level + 1);
                    if ($child) $node['children'][] = $child;
                }
            }

            return $node;
        };

        $root = $build($rootId, 0);

        return response()->json($root ?: []);
    }

    public function committee()
    {
        return view('committee');
    }
}
