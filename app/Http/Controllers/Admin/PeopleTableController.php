<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\ParentChildEdge;
use App\Models\UnionModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class PeopleTableController extends Controller
{
    public function index()
    {
        return view('admin.members.index');
    }

    public function all()
    {
        // cache the final JSON array for fast initial load
        $payload = Cache::remember('people_directory_v1', now()->addMinutes(5), function () {
            return $this->buildRows();
        });

        return response()->json($payload);
    }

    private function buildRows(): array
    {
        $table = (new Person)->getTable();
        $has = fn ($col) => Schema::hasColumn($table, $col);

        // keep base select minimal
        $select = ['id', 'display_name', 'gender', 'pusta', 'is_deceased'];
        if ($has('member_no'))   $select[] = 'member_no';
        if ($has('member_type')) $select[] = 'member_type';

        $people = Person::query()->select($select)->orderBy('id', 'desc')->get();
        if ($people->isEmpty()) return ['count' => 0, 'rows' => []];

        $ids = $people->pluck('id')->map(fn ($v) => (int)$v)->values();

        // children count per parent
        $childrenCounts = ParentChildEdge::query()
            ->whereIn('parent_id', $ids)
            ->whereIn('relation_type', ['birth', 'adoption'])
            ->selectRaw('parent_id, COUNT(*) as cnt')
            ->groupBy('parent_id')
            ->pluck('cnt', 'parent_id');

        // parent edges: parent_id -> child_id
        $edges = ParentChildEdge::query()
            ->whereIn('child_id', $ids)
            ->whereIn('relation_type', ['birth', 'adoption'])
            ->get(['parent_id', 'child_id']);

        $parentsByChild = [];
        $parentIds = [];
        foreach ($edges as $e) {
            $cid = (int)$e->child_id;
            $pid = (int)$e->parent_id;
            $parentsByChild[$cid][] = $pid;
            $parentIds[$pid] = true;
        }
        $parentIds = array_keys($parentIds);

        $parentMap = Person::query()
            ->whereIn('id', $parentIds ?: [0])
            ->get(['id', 'display_name', 'gender'])
            ->keyBy('id');

        // unions for spouse lookup
        $unions = UnionModel::query()
            ->whereIn('spouse1_id', $ids)->orWhereIn('spouse2_id', $ids)
            ->orWhereIn('spouse1_id', $parentIds ?: [0])->orWhereIn('spouse2_id', $parentIds ?: [0])
            ->get(['id', 'spouse1_id', 'spouse2_id', 'start_date']);

        $spousesByPerson = [];
        $allSpouseIds = [];
        foreach ($unions as $u) {
            $a = (int)$u->spouse1_id;
            $b = (int)$u->spouse2_id;
            $spousesByPerson[$a][] = $b;
            $spousesByPerson[$b][] = $a;
            $allSpouseIds[$a] = true;
            $allSpouseIds[$b] = true;
        }
        $allSpouseIds = array_keys($allSpouseIds);

        $spouseMap = Person::query()
            ->whereIn('id', $allSpouseIds ?: [0])
            ->get(['id', 'display_name', 'gender'])
            ->keyBy('id');

        $pickSpouseByGender = function (int $personId, string $wantGender) use ($spousesByPerson, $spouseMap, $unions) {
            $candidateIds = $spousesByPerson[$personId] ?? [];
            if (!$candidateIds) return null;

            $rank = [];
            foreach ($unions as $u) {
                $a = (int)$u->spouse1_id;
                $b = (int)$u->spouse2_id;
                if ($a !== $personId && $b !== $personId) continue;
                $other = ($a === $personId) ? $b : $a;
                $date = $u->start_date ? (string)$u->start_date : '';
                $rank[$other][] = [$date, (int)$u->id];
            }

            $best = null;
            $bestKey = null;

            foreach ($candidateIds as $sid) {
                $s = $spouseMap[$sid] ?? null;
                if (!$s) continue;
                if (($s->gender ?? '') !== $wantGender) continue;

                $pairs = $rank[$sid] ?? [['', 0]];
                $max = null;
                foreach ($pairs as $pair) {
                    if ($max === null) $max = $pair;
                    else {
                        if ($pair[0] > $max[0]) $max = $pair;
                        elseif ($pair[0] === $max[0] && $pair[1] > $max[1]) $max = $pair;
                    }
                }

                $key = ($max[0] ?: '0000-00-00') . ':' . str_pad((string)$max[1], 10, '0', STR_PAD_LEFT);
                if ($bestKey === null || $key > $bestKey) {
                    $bestKey = $key;
                    $best = $s;
                }
            }

            return $best;
        };

        // father/mother for each child
        $fatherByChild = [];
        $motherByChild = [];

        foreach ($parentsByChild as $childId => $pids) {
            $father = null;
            $mother = null;

            foreach ($pids as $pid) {
                $pp = $parentMap[$pid] ?? null;
                if (!$pp) continue;
                if (!$father && ($pp->gender ?? '') === 'male') $father = $pp;
                if (!$mother && ($pp->gender ?? '') === 'female') $mother = $pp;
            }

            // spouse fill rule
            if (!$father && $mother) {
                $sp = $pickSpouseByGender((int)$mother->id, 'male');
                if ($sp) $father = $sp;
            }
            if (!$mother && $father) {
                $sp = $pickSpouseByGender((int)$father->id, 'female');
                if ($sp) $mother = $sp;
            }

            $fatherByChild[$childId] = $father?->display_name ?? '';
            $motherByChild[$childId] = $mother?->display_name ?? '';
        }

        // spouse_name (all spouses)
        $spouseNameByPerson = [];
        foreach ($ids as $pid) {
            $sids = $spousesByPerson[$pid] ?? [];
            if (!$sids) { $spouseNameByPerson[$pid] = ''; continue; }

            $names = [];
            foreach ($sids as $sid) {
                $sp = $spouseMap[$sid] ?? null;
                if ($sp && $sp->display_name) $names[] = $sp->display_name;
            }
            $names = array_values(array_unique($names));
            $spouseNameByPerson[$pid] = implode(', ', $names);
        }

        $rows = $people->map(function ($p) use ($has, $fatherByChild, $motherByChild, $spouseNameByPerson, $childrenCounts) {
            $id = (int)$p->id;
            return [
                'id'             => $id,
                'member_no'      => $has('member_no') ? ($p->member_no ?? '') : '',
                'display_name'   => $p->display_name ?? '',
                'pusta'          => (string)($p->pusta ?? ''),
                'father_name'    => $fatherByChild[$id] ?? '',
                'mother_name'    => $motherByChild[$id] ?? '',
                'member_type'    => $has('member_type') ? ($p->member_type ?? '') : '',
                'spouse_name'    => $spouseNameByPerson[$id] ?? '',
                'total_children' => (int)($childrenCounts[$id] ?? 0),
                'is_deceased'    => (bool)($p->is_deceased ?? false),
            ];
        })->values();

        return ['count' => $rows->count(), 'rows' => $rows];
    }

    public static function forgetCache(): void
    {
        Cache::forget('people_directory_v1');
    }
}
