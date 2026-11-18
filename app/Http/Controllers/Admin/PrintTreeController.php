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

    private const NP_DIGITS = ['०' => '0', '१' => '1', '२' => '2', '३' => '3', '४' => '4',
                               '५' => '5', '६' => '6', '७' => '7', '८' => '8', '९' => '9'];

    /** "२६" / " २६" / "26" → 26, anything else → null */
    public static function pustaToInt($value): ?int
    {
        if ($value === null) return null;
        $s = strtr(trim((string) $value), self::NP_DIGITS);
        return ctype_digit($s) ? (int) $s : null;
    }

    /** 26 → "२६" */
    public static function toNepaliDigits(int|string $n): string
    {
        return strtr((string) $n, array_flip(self::NP_DIGITS));
    }

    public function form()
    {
        $persons = Person::select('id', 'display_name', 'display_name_np', 'pusta', 'gender', 'birth_date')
            ->whereNotNull('pusta')
            ->orderBy('birth_date')
            ->orderBy('id')
            ->get();

        $personsByPusta = $persons
            ->groupBy(fn ($p) => self::pustaToInt($p->pusta))
            ->filter(fn ($group, $key) => $key !== '' && $key !== null)
            ->map(fn ($group) => $group->map(fn ($p) => [
                'id'         => $p->id,
                'name'       => $p->display_name,
                'name_np'    => $p->display_name_np,
                'gender'     => $p->gender,
                'pusta'      => self::pustaToInt($p->pusta),
                'birth_year' => $p->birth_date?->format('Y'),
            ])->values())
            ->sortKeys();

        $pustas = $personsByPusta->map(fn ($g) => $g->count()); // [26 => 1085, ...]

        return view('admin.print-tree.form', compact('pustas', 'personsByPusta'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'root_id'    => ['nullable', 'integer', 'exists:persons,id', 'required_without:from_pusta'],
            'from_pusta' => ['nullable', 'integer', 'min:1', 'max:200', 'required_without:root_id'],
            'to_pusta'   => ['required', 'integer', 'min:1', 'max:200'],
        ], [
            'root_id.required_without'    => 'Choose a starting person or a "From पुस्ता".',
            'from_pusta.required_without' => 'Choose a starting person or a "From पुस्ता".',
        ]);

        // ─── Step 1: pre-load ALL parent→child edges ───
        $adjacency = [];
        $parentsOf = [];
        ParentChildEdge::select('parent_id', 'child_id')
            ->orderBy('child_id')
            ->cursor()
            ->each(function ($edge) use (&$adjacency, &$parentsOf) {
                $adjacency[$edge->parent_id][] = $edge->child_id;
                $parentsOf[$edge->child_id][]  = $edge->parent_id;
            });

        // ─── Step 2: work out the root(s) and the start pusta ───
        $root = null;
        if ($request->filled('root_id')) {
            $root       = Person::find((int) $request->root_id);
            $startPusta = self::pustaToInt($root->pusta);
            if ($startPusta === null) {
                return back()->withInput()->withErrors([
                    'root_id' => "{$root->display_name} has no पुस्ता number saved. Add it on their profile, or choose \"From पुस्ता\" instead.",
                ]);
            }
            $rootIds = [$root->id];
        } else {
            $startPusta = (int) $request->from_pusta;
            $rootIds    = $this->rootsForPusta($startPusta, $parentsOf);
            if (!$rootIds) {
                return back()->withInput()->withErrors(['from_pusta' => 'No members found in this पुस्ता.']);
            }
        }

        $toPusta = (int) $request->to_pusta;
        if ($toPusta < $startPusta) {
            return back()->withInput()->withErrors([
                'to_pusta' => "\"To पुस्ता\" must be {$startPusta} or later (the start is पुस्ता {$startPusta}).",
            ]);
        }
        $levels = $toPusta - $startPusta + 1; // number of generations shown

        // ─── Step 3: BFS to collect every descendant ID within the range ───
        $allIds   = array_fill_keys($rootIds, 0);
        $frontier = $rootIds;
        for ($depth = 0; $depth < $levels - 1 && $frontier; $depth++) {
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
        }
        $ids = array_keys($allIds);

        // ─── Step 4: load persons + spouses ───
        $personMap = Person::whereIn('id', $ids)
            ->select('id', 'display_name', 'display_name_np', 'gender',
                     'birth_date', 'death_date', 'is_deceased', 'pusta', 'member_no')
            ->get()
            ->keyBy('id');

        $idSet     = array_flip($ids);
        $spouseMap = [];
        UnionModel::whereIn('spouse1_id', $ids)->orWhereIn('spouse2_id', $ids)
            ->with(['spouse1:id,display_name,gender', 'spouse2:id,display_name,gender'])
            ->get()
            ->each(function ($union) use ($idSet, &$spouseMap) {
                if (isset($idSet[$union->spouse1_id]) && $union->spouse2) {
                    $spouseMap[$union->spouse1_id][] = $union->spouse2;
                }
                if (isset($idSet[$union->spouse2_id]) && $union->spouse1) {
                    $spouseMap[$union->spouse2_id][] = $union->spouse1;
                }
            });

        // ─── Step 5: build the nested tree(s) ───
        $this->visited = [];
        $forest = [];
        foreach ($rootIds as $rid) {
            if (isset($this->visited[$rid])) continue;
            $node = $this->buildNode($rid, $adjacency, $personMap, $spouseMap, $levels, 0);
            if ($node) $forest[] = $this->serializeNode($node);
        }

        return view('admin.print-tree.show', [
            'forest'      => $forest,
            'root'        => $root,
            'startPusta'  => $startPusta,
            'toPusta'     => $toPusta,
            'totalNodes'  => count($this->visited),
            'actualDepth' => $forest ? max(array_map(fn ($t) => $this->maxDepth($t), $forest)) + 1 : 0,
        ]);
    }

    /**
     * Everyone born into this pusta, grouped so siblings stay together.
     * Married-in spouses (no parents in the tree, married to a blood member
     * of the same pusta) are skipped — they already show inside the card.
     */
    private function rootsForPusta(int $pusta, array $parentsOf): array
    {
        $candidates = Person::select('id', 'pusta', 'birth_date')
            ->whereNotNull('pusta')
            ->orderBy('birth_date')
            ->orderBy('id')
            ->get()
            ->filter(fn ($p) => self::pustaToInt($p->pusta) === $pusta);

        $candIds = $candidates->pluck('id')->all();
        $candSet = array_flip($candIds);

        $marriedIn = [];
        UnionModel::whereIn('spouse1_id', $candIds)->orWhereIn('spouse2_id', $candIds)
            ->get(['spouse1_id', 'spouse2_id'])
            ->each(function ($u) use ($candSet, $parentsOf, &$marriedIn) {
                foreach ([[$u->spouse1_id, $u->spouse2_id], [$u->spouse2_id, $u->spouse1_id]] as [$a, $b]) {
                    if (isset($candSet[$a], $candSet[$b]) && empty($parentsOf[$a]) && !empty($parentsOf[$b])) {
                        $marriedIn[$a] = true;
                    }
                }
            });

        return $candidates
            ->reject(fn ($p) => isset($marriedIn[$p->id]))
            ->sortBy(fn ($p) => [min($parentsOf[$p->id] ?? [PHP_INT_MAX]), $p->birth_date?->timestamp ?? PHP_INT_MAX, $p->id])
            ->pluck('id')
            ->values()
            ->all();
    }

    private function buildNode(int $personId, array $adjacency, $personMap, array $spouseMap, int $levels, int $depth): ?array
    {
        if ($depth >= $levels || isset($this->visited[$personId])) {
            return null;
        }
        $person = $personMap[$personId] ?? null;
        if (!$person) return null;

        $this->visited[$personId] = true;

        $children = [];
        foreach ($adjacency[$personId] ?? [] as $childId) {
            $child = $this->buildNode($childId, $adjacency, $personMap, $spouseMap, $levels, $depth + 1);
            if ($child) $children[] = $child;
        }

        usort($children, fn ($a, $b) => strcmp($a['person']->gender ?? 'z', $b['person']->gender ?? 'z'));

        return [
            'person'   => $person,
            'spouses'  => $spouseMap[$personId] ?? [],
            'children' => $children,
            'depth'    => $depth,
        ];
    }

    private function maxDepth(array $node): int
    {
        $max = $node['depth'];
        foreach ($node['children'] as $c) $max = max($max, $this->maxDepth($c));
        return $max;
    }

    private function serializeNode(array $node): array
    {
        $person = $node['person'];

        return [
            'person' => [
                'id'              => $person->id,
                'display_name'    => $person->display_name,
                'display_name_np' => $person->display_name_np,
                'gender'          => $person->gender,
                'birth_year'      => $person->birth_date?->format('Y'),
                'death_year'      => $person->death_date?->format('Y'),
                'is_deceased'     => (bool) $person->is_deceased,
                'pusta'           => $person->pusta ? trim($person->pusta) : null,
                'member_no'       => $person->member_no,
            ],
            'spouses' => collect($node['spouses'])->map(fn ($s) => [
                'id'           => $s->id,
                'display_name' => $s->display_name,
                'gender'       => $s->gender,
            ])->values()->all(),
            'depth'    => $node['depth'],
            'children' => array_map(fn ($child) => $this->serializeNode($child), $node['children']),
        ];
    }
}
