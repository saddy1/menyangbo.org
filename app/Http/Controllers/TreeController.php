<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;

class TreeController extends Controller
{
    public function index(Request $request)
    {
       $rootId = $request->query('root_id') ?? Person::query()->orderBy('id')->value('id');

    // If no people yet, show an empty state instead of crashing
    if (!$rootId) {
        return view('tree.index', [
            'root' => null,
            'levels' => [],
            'allPeople' => collect(),
        ]);
    }

    $root = Person::with([
        'parents','children','childEdges','parentEdges',
        'unionsAsSpouse1.spouse2','unionsAsSpouse2.spouse1'
    ])->findOrFail($rootId);

        // simple BFS to levels for demo
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

        // all people for quick “Root select”
        $allPeople = Person::orderBy('display_name')->get(['id','display_name']);

        return view('tree.index', compact('root','levels','allPeople'));
    }
// app/Http/Controllers/TreeController.php

public function graph(Request $request)
{
    $rootId   = $request->query('root_id') ?? \App\Models\Person::query()->orderBy('id')->value('id');
    $maxDepth = (int)($request->query('depth') ?? 6);
    if (!$rootId) return response()->json(['nodes'=>[], 'links'=>[]]);

    // Load all minimal data once
    $people = \App\Models\Person::query()
        ->select('id','display_name','gender','is_deceased','birth_date','death_date','photo_path')
        ->get()->keyBy('id');

    $unions = \App\Models\UnionModel::query()
        ->select('id','spouse1_id','spouse2_id','type','start_date')
        ->get();

    $edges = \App\Models\ParentChildEdge::query()
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

    $nodes = [];           // id => node
    $links = [];           // list of edges
    $seenNode = [];        // node-id => true
    $seenLink = [];        // "src>dst" => true

    $q = [[$rootId, 0]];
    $visitedPerson = [];

    // Helper: add person node
    $addPerson = function($pId, $level) use (&$nodes,&$seenNode,$people) {
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
                'photo'       => $p->photo_path,
                'level'       => $level,
            ];
            $seenNode[$pId] = true;
        }
    };

    // Helper: add union node (U###)
    $addUnion = function($u, $level) use (&$nodes,&$seenNode,$people) {
        $uid = 'U'.$u->id;
        if (!isset($seenNode[$uid])) {
            $sp1 = $people[$u->spouse1_id] ?? null;
            $sp2 = $people[$u->spouse2_id] ?? null;
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
    $addEdge = function($src, $dst) use (&$links,&$seenLink) {
        $key = $src.'>'.$dst;
        if (!isset($seenLink[$key])) {
            $links[] = ['source'=>$src, 'target'=>$dst];
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

    return response()->json(['nodes'=>$nodes, 'links'=>$links]);
}
public function treeJson(Request $request)
{
    $rootId   = (int)($request->query('root_id') ?? 0);
    $maxDepth = (int)($request->query('depth') ?? 8);
    if (!$rootId) {
        $rootId = \App\Models\Person::query()->orderBy('id')->value('id') ?? 0;
        if (!$rootId) return response()->json([]);
    }

    // Load once
    $people = \App\Models\Person::query()
        ->select('id','display_name','gender','is_deceased','birth_date','death_date','photo_path')
        ->get()->keyBy('id');

    // children by parent (birth/adoption)
    $edges = \App\Models\ParentChildEdge::query()
        ->select('parent_id','child_id','relation_type')->get();
    $childrenByParent = [];
    foreach ($edges as $e) {
        if (!in_array($e->relation_type, ['birth','adoption'])) continue;
        $childrenByParent[$e->parent_id][] = $e->child_id;
    }

    // spouses (for badge/tooltip)
    $unions = \App\Models\UnionModel::query()->select('id','spouse1_id','spouse2_id','start_date')->get();
    $spousesByPerson = [];
    foreach ($unions as $u) {
        $spousesByPerson[$u->spouse1_id][] = $u->spouse2_id;
        $spousesByPerson[$u->spouse2_id][] = $u->spouse1_id;
    }

    $visited = [];
    $build = function($pid, $level = 0) use (&$build,&$visited,$maxDepth,$people,$childrenByParent,$spousesByPerson) {
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
            'photo'       => $p->photo_path,
            // spouse names (optional)
            'spouses'     => array_values(array_unique(array_map(function($sid) use ($people){
                                return $people[$sid]->display_name ?? null;
                              }, $spousesByPerson[$p->id] ?? []))),
            'children'    => []
        ];

        if ($level < $maxDepth) {
            foreach ($childrenByParent[$p->id] ?? [] as $cid) {
                $child = $build($cid, $level+1);
                if ($child) $node['children'][] = $child;
            }
        }
        return $node;
    };

    $root = $build($rootId, 0);
    return response()->json($root ?: []);
}


}
