<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentChildEdge;
use App\Models\Person;
use App\Models\UnionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class PeopleTableController extends Controller
{
    public function index()
    {
        return view('admin.members.index');
    }

    public function all(Request $request)
    {
        $has = $this->columnChecker();

        $perPage = min(max((int) $request->integer('per_page', 50), 10), 200);
        $page = max((int) $request->integer('page', 1), 1);
        $sort = $request->query('sort', 'id');
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['id', 'member_no', 'display_name', 'pusta', 'gender', 'member_type', 'total_children'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }

        $select = ['id', 'display_name', 'gender', 'pusta', 'is_deceased'];
        foreach (['member_no', 'member_type', 'display_name_np', 'display_name_limbu'] as $col) {
            if ($has($col)) {
                $select[] = $col;
            }
        }

        $query = Person::query()->select($select);

        $this->applyFilters($query, $request, $has);

        if ($sort === 'total_children') {
            $query->withCount(['childEdges as children_count' => function ($q) {
                $q->whereIn('relation_type', ['birth', 'adoption']);
            }])->orderBy('children_count', $dir);
        } elseif ($sort === 'member_no' && !$has('member_no')) {
            $query->orderBy('id', 'desc');
        } elseif ($sort === 'member_type' && !$has('member_type')) {
            $query->orderBy('id', 'desc');
        } else {
            $query->orderBy($sort, $dir);
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $people = collect($paginator->items());

        return response()->json([
            'count'        => $paginator->total(),
            'page'         => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'last_page'    => $paginator->lastPage(),
            'rows'         => $this->buildRowsForPeople($people, $has),
            'member_types' => $this->memberTypes(),
        ]);
    }

    private function applyFilters($query, Request $request, callable $has): void
    {
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($inner) use ($q, $has) {
                if (is_numeric($q)) {
                    $inner->orWhere('id', (int) $q);
                }

                $inner->orWhere('display_name', 'like', "%{$q}%")
                    ->orWhere('pusta', 'like', "%{$q}%");

                foreach (['member_no', 'member_type', 'display_name_np', 'display_name_limbu'] as $col) {
                    if ($has($col)) {
                        $inner->orWhere($col, 'like', "%{$q}%");
                    }
                }
            });
        }

        foreach (['member_no', 'display_name', 'pusta', 'member_type'] as $field) {
            $value = trim((string) $request->query($field, ''));
            if ($value === '') {
                continue;
            }
            if ($field === 'member_type' && $has('member_type')) {
                $query->where('member_type', $value);
            } elseif ($field === 'display_name') {
                $query->where('display_name', 'like', "%{$value}%");
            } elseif ($has($field) || $field === 'pusta') {
                $query->where($field, 'like', "%{$value}%");
            }
        }

        $alive = $request->query('alive_status');
        if ($alive === 'alive') {
            $query->where('is_deceased', false);
        } elseif ($alive === 'deceased') {
            $query->where('is_deceased', true);
        }

        $gender = $request->query('gender');
        if (in_array($gender, ['male', 'female', 'other', 'unknown'], true)) {
            $query->where('gender', $gender);
        }

        $this->applyRelationNameFilter($query, trim((string) $request->query('father_name', '')), 'male');
        $this->applyRelationNameFilter($query, trim((string) $request->query('mother_name', '')), 'female');
        $this->applyGrandfatherNameFilter($query, trim((string) $request->query('grandfather_name', '')));
        $this->applySpouseNameFilter($query, trim((string) $request->query('spouse_name', '')));
    }

    private function applyRelationNameFilter($query, string $name, string $gender): void
    {
        if ($name === '') {
            return;
        }

        $parentIds = Person::query()
            ->where('gender', $gender)
            ->where('display_name', 'like', "%{$name}%")
            ->pluck('id');

        $childIds = ParentChildEdge::query()
            ->whereIn('parent_id', $parentIds->isEmpty() ? [0] : $parentIds)
            ->whereIn('relation_type', ['birth', 'adoption'])
            ->pluck('child_id');

        $query->whereIn('id', $childIds->isEmpty() ? [0] : $childIds);
    }

    private function applyGrandfatherNameFilter($query, string $name): void
    {
        if ($name === '') {
            return;
        }

        $grandfatherIds = Person::query()
            ->where('gender', 'male')
            ->where('display_name', 'like', "%{$name}%")
            ->pluck('id');

        $fatherIds = ParentChildEdge::query()
            ->whereIn('parent_id', $grandfatherIds->isEmpty() ? [0] : $grandfatherIds)
            ->whereIn('relation_type', ['birth', 'adoption'])
            ->pluck('child_id');

        $childIds = ParentChildEdge::query()
            ->whereIn('parent_id', $fatherIds->isEmpty() ? [0] : $fatherIds)
            ->whereIn('relation_type', ['birth', 'adoption'])
            ->pluck('child_id');

        $query->whereIn('id', $childIds->isEmpty() ? [0] : $childIds);
    }

    private function applySpouseNameFilter($query, string $name): void
    {
        if ($name === '') {
            return;
        }

        $spouseIds = Person::query()
            ->where('display_name', 'like', "%{$name}%")
            ->pluck('id');

        $unionPersonIds = UnionModel::query()
            ->whereIn('spouse1_id', $spouseIds->isEmpty() ? [0] : $spouseIds)
            ->orWhereIn('spouse2_id', $spouseIds->isEmpty() ? [0] : $spouseIds)
            ->get(['spouse1_id', 'spouse2_id'])
            ->flatMap(fn ($u) => [(int) $u->spouse1_id, (int) $u->spouse2_id])
            ->diff($spouseIds->map(fn ($id) => (int) $id))
            ->values();

        $query->whereIn('id', $unionPersonIds->isEmpty() ? [0] : $unionPersonIds);
    }

    private function buildRowsForPeople($people, callable $has): array
    {
        if ($people->isEmpty()) {
            return [];
        }

        $ids = $people->pluck('id')->map(fn ($v) => (int) $v)->values();

        $childrenCounts = ParentChildEdge::query()
            ->whereIn('parent_id', $ids)
            ->whereIn('relation_type', ['birth', 'adoption'])
            ->selectRaw('parent_id, COUNT(*) as cnt')
            ->groupBy('parent_id')
            ->pluck('cnt', 'parent_id');

        $edges = ParentChildEdge::query()
            ->whereIn('child_id', $ids)
            ->whereIn('relation_type', ['birth', 'adoption'])
            ->get(['parent_id', 'child_id']);

        $parentsByChild = [];
        $parentIds = [];
        foreach ($edges as $edge) {
            $cid = (int) $edge->child_id;
            $pid = (int) $edge->parent_id;
            $parentsByChild[$cid][] = $pid;
            $parentIds[$pid] = true;
        }

        $parentMap = Person::query()
            ->whereIn('id', array_keys($parentIds) ?: [0])
            ->get(['id', 'display_name', 'gender'])
            ->keyBy('id');

        $grandparentEdges = ParentChildEdge::query()
            ->whereIn('child_id', array_keys($parentIds) ?: [0])
            ->whereIn('relation_type', ['birth', 'adoption'])
            ->get(['parent_id', 'child_id']);

        $grandparentsByParent = [];
        $grandparentIds = [];
        foreach ($grandparentEdges as $edge) {
            $childId = (int) $edge->child_id;
            $parentId = (int) $edge->parent_id;
            $grandparentsByParent[$childId][] = $parentId;
            $grandparentIds[$parentId] = true;
        }

        $grandparentMap = Person::query()
            ->whereIn('id', array_keys($grandparentIds) ?: [0])
            ->get(['id', 'display_name', 'gender'])
            ->keyBy('id');

        $unions = UnionModel::query()
            ->whereIn('spouse1_id', $ids)
            ->orWhereIn('spouse2_id', $ids)
            ->get(['spouse1_id', 'spouse2_id']);

        $spousesByPerson = [];
        $spouseIds = [];
        foreach ($unions as $union) {
            $a = (int) $union->spouse1_id;
            $b = (int) $union->spouse2_id;
            $spousesByPerson[$a][] = $b;
            $spousesByPerson[$b][] = $a;
            $spouseIds[$a] = true;
            $spouseIds[$b] = true;
        }

        $spouseMap = Person::query()
            ->whereIn('id', array_keys($spouseIds) ?: [0])
            ->get(['id', 'display_name', 'gender'])
            ->keyBy('id');

        return $people->map(function ($person) use ($has, $parentsByChild, $parentMap, $grandparentsByParent, $grandparentMap, $spousesByPerson, $spouseMap, $childrenCounts) {
            $id = (int) $person->id;

            $father = '';
            $fatherId = null;
            $mother = '';
            foreach (($parentsByChild[$id] ?? []) as $pid) {
                $parent = $parentMap[$pid] ?? null;
                if (!$parent) {
                    continue;
                }
                if (!$father && $parent->gender === 'male') {
                    $father = $parent->display_name;
                    $fatherId = (int) $parent->id;
                }
                if (!$mother && $parent->gender === 'female') {
                    $mother = $parent->display_name;
                }
            }

            $grandfather = '';
            if ($fatherId) {
                foreach (($grandparentsByParent[$fatherId] ?? []) as $gpid) {
                    $grandparent = $grandparentMap[$gpid] ?? null;
                    if ($grandparent && $grandparent->gender === 'male') {
                        $grandfather = $grandparent->display_name;
                        break;
                    }
                }
            }

            $spouses = [];
            foreach (($spousesByPerson[$id] ?? []) as $sid) {
                $spouse = $spouseMap[$sid] ?? null;
                if ($spouse?->display_name) {
                    $spouses[] = $spouse->display_name;
                }
            }

            return [
                'id'             => $id,
                'member_no'      => $has('member_no') ? ($person->member_no ?? '') : '',
                'display_name'   => $person->display_name ?? '',
                'pusta'          => (string) ($person->pusta ?? ''),
                'gender'         => $person->gender ?? 'unknown',
                'father_name'    => $father,
                'grandfather_name' => $grandfather,
                'mother_name'    => $mother,
                'member_type'    => $has('member_type') ? ($person->member_type ?? '') : '',
                'spouse_name'    => implode(', ', array_values(array_unique($spouses))),
                'total_children' => (int) ($childrenCounts[$id] ?? 0),
                'is_deceased'    => (bool) ($person->is_deceased ?? false),
            ];
        })->values()->all();
    }

    private function memberTypes(): array
    {
        return Cache::remember('people_directory_member_types_v1', 3600, function () {
            $table = (new Person)->getTable();
            if (!Schema::hasColumn($table, 'member_type')) {
                return [];
            }

            return Person::query()
                ->whereNotNull('member_type')
                ->where('member_type', '<>', '')
                ->distinct()
                ->orderBy('member_type')
                ->pluck('member_type')
                ->values()
                ->all();
        });
    }

    private function columnChecker(): callable
    {
        $table = (new Person)->getTable();
        $columns = Cache::remember('people_directory_person_columns_v1', 3600, fn () => Schema::getColumnListing($table));
        $set = array_fill_keys($columns, true);

        return fn ($col) => isset($set[$col]);
    }

    public static function forgetCache(): void
    {
        Cache::forget('people_directory_member_types_v1');
        Cache::forget('people_directory_person_columns_v1');
    }
}
