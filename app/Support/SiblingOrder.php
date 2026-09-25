<?php

namespace App\Support;

use App\Models\Person;
use Illuminate\Support\Collection;

/**
 * Saved सन्तान क्रम (persons.birth_order) among a parent's children of one gender.
 * Used by the relationship form, the member page "add child" form and admin person edit.
 */
class SiblingOrder
{
    /** The parent's birth/adoption children, optionally leaving one out. */
    public static function siblings(Person $parent, ?int $exceptId = null): Collection
    {
        return $parent->children()
            ->wherePivotIn('relation_type', ['birth', 'adoption'])
            ->when($exceptId, fn ($q) => $q->where('persons.id', '!=', $exceptId))
            ->select('persons.id', 'persons.display_name', 'persons.gender', 'persons.birth_date', 'persons.birth_order', 'persons.member_no')
            ->get();
    }

    private static function sameGender(Collection $siblings, ?string $gender): Collection
    {
        $gender = $gender ?: 'unknown';

        return $siblings->filter(fn ($s) => ($s->gender ?: 'unknown') === $gender)->values();
    }

    /** [rank => sibling names] of the parent's other children of this gender (shown as a hint). */
    public static function taken(Person $parent, ?string $gender, ?int $exceptId = null): array
    {
        $siblings = self::sameGender(self::siblings($parent, $exceptId), $gender);
        $ranks = BirthOrder::rank($siblings);

        $out = [];
        foreach ($siblings as $s) {
            $r = $ranks[$s->id]['rank'];
            $out[$r] = isset($out[$r]) ? $out[$r] . ', ' . $s->display_name : $s->display_name;
        }
        ksort($out);

        return $out;
    }

    public const MAX = 10;

    /** Dropdown choices १ … १० (any number may be chosen, even one a sibling already has). */
    public static function options(): array
    {
        return array_map(fn ($n) => ['value' => $n, 'label' => BirthOrder::npDigits($n)], range(1, self::MAX));
    }

    /** Existing places for every gender, for forms where the gender is chosen on the page. */
    public static function takenByGender(Person $parent, ?int $exceptId = null): array
    {
        return collect(['male', 'female', 'unknown', 'other'])
            ->mapWithKeys(fn ($g) => [$g => self::taken($parent, $g, $exceptId)])
            ->all();
    }

    /**
     * Save $order as $child's place under $parent. The other same-gender siblings'
     * current places are saved too, so the order no longer shifts with birth dates.
     */
    public static function assign(Person $parent, Person $child, int $order): void
    {
        $siblings = self::sameGender(self::siblings($parent, $child->id), $child->gender);
        $ranks = BirthOrder::rank($siblings);

        foreach ($siblings as $s) {
            if (!$s->birth_order) {
                $s->update(['birth_order' => $ranks[$s->id]['rank']]);
            }
        }
        $child->update(['birth_order' => $order]);
    }

    /** Parent whose children decide this person's place: father first, else mother, else any. */
    public static function parentFor(Person $person): ?Person
    {
        $parents = $person->parents()->get();

        return $parents->firstWhere('gender', 'male')
            ?? $parents->firstWhere('gender', 'female')
            ?? $parents->first();
    }
}
