<?php

namespace App\Support;

use App\Models\ParentChildEdge;

/** Walks parent → child links. */
class Lineage
{
    /** $id and everyone below them (children, grandchildren …). */
    public static function descendantIds(int $id): array
    {
        $childrenOf = [];
        foreach (ParentChildEdge::select('parent_id', 'child_id')->get() as $e) {
            $childrenOf[(int) $e->parent_id][] = (int) $e->child_id;
        }

        $seen = [];
        $stack = [$id];
        while ($stack) {
            $cur = array_pop($stack);
            if (isset($seen[$cur])) continue;
            $seen[$cur] = true;
            foreach ($childrenOf[$cur] ?? [] as $c) $stack[] = $c;
        }

        return array_keys($seen);
    }

    /**
     * Why $parentId can't be linked as a parent of $childId, or null if it can:
     * same person, already linked, or the parent is the child's own descendant (a loop).
     */
    public static function linkProblem(int $parentId, int $childId): ?string
    {
        if ($parentId === $childId) return 'आफैंलाई अभिभावक बनाउन मिल्दैन।';
        if (ParentChildEdge::where('parent_id', $parentId)->where('child_id', $childId)->exists()) {
            return 'यो सम्बन्ध पहिले नै छ।';
        }
        if (in_array($parentId, self::descendantIds($childId), true)) {
            return 'यो व्यक्ति सन्तानकै सन्तान हो — अभिभावक बनाउन मिल्दैन।';
        }

        return null;
    }
}
