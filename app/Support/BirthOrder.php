<?php

namespace App\Support;

/**
 * Birth-order labels among siblings of the same gender:
 * छोरा १ · जेठो, छोरा २ · माहिलो … छोरा N · कान्छो (same for छोरी with जेठी/माहिली…).
 */
class BirthOrder
{
    private const SON_WORDS      = ['जेठो', 'माहिलो', 'साहिलो', 'काहिलो', 'ठाहिलो'];
    private const DAUGHTER_WORDS = ['जेठी', 'माहिली', 'साहिली', 'काहिली', 'ठाहिली'];

    private const NP_DIGITS = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];

    public static function npDigits(int|string $n): string
    {
        return strtr((string) $n, array_combine(range(0, 9), self::NP_DIGITS));
    }

    public static function relation(?string $gender): string
    {
        return match ($gender) {
            'male'   => 'छोरा',
            'female' => 'छोरी',
            default  => 'सन्तान',
        };
    }

    /** जेठो / माहिलो / … / कान्छो; null when there is only one or the rank has no common name. */
    public static function word(?string $gender, int $rank, int $total): ?string
    {
        if ($total < 2 || !in_array($gender, ['male', 'female'], true)) {
            return null;
        }
        if ($rank === $total) {
            return $gender === 'male' ? 'कान्छो' : 'कान्छी';
        }
        $words = $gender === 'male' ? self::SON_WORDS : self::DAUGHTER_WORDS;

        return $words[$rank - 1] ?? null;
    }

    /** Plain place name without the कान्छो rule: 1 → जेठो, 2 → माहिलो … (null past ठाहिलो). */
    public static function placeWord(?string $gender, int $n): ?string
    {
        $words = match ($gender) {
            'male'   => self::SON_WORDS,
            'female' => self::DAUGHTER_WORDS,
            default  => [],
        };

        return $words[$n - 1] ?? null;
    }

    /** e.g. ['rank' => 2, 'total' => 3, 'word' => 'माहिलो', 'label' => 'छोरा २ · माहिलो'] */
    public static function info(?string $gender, int $rank, int $total): array
    {
        $word = self::word($gender, $rank, $total);

        return [
            'rank'  => $rank,
            'total' => $total,
            'word'  => $word,
            'label' => self::relation($gender) . ' ' . self::npDigits($rank) . ($word ? ' · ' . $word : ''),
        ];
    }

    /**
     * Sort siblings eldest first by birth date, then id (the order used when no
     * birth_order has been saved).
     */
    public static function sortByBirth($siblings)
    {
        return collect($siblings)->sortBy([
            fn ($a, $b) => (data_get($a, 'birth_date')?->timestamp ?? PHP_INT_MAX) <=> (data_get($b, 'birth_date')?->timestamp ?? PHP_INT_MAX),
            fn ($a, $b) => data_get($a, 'id') <=> data_get($b, 'id'),
        ])->values();
    }

    /**
     * Rank siblings within their gender. A saved birth_order is used as-is;
     * siblings without one fill the free numbers in birth-date order.
     *
     * @param  iterable  $siblings  items with id, gender, birth_order, birth_date
     * @return array<int, array>    keyed by sibling id
     */
    public static function rank(iterable $siblings): array
    {
        $byGender = [];
        foreach (self::sortByBirth($siblings) as $s) {
            $g = data_get($s, 'gender') ?: 'unknown';
            $byGender[$g][] = $s;
        }

        $out = [];
        foreach ($byGender as $gender => $items) {
            $ranks = [];
            $used = [];
            foreach ($items as $s) {
                $saved = (int) data_get($s, 'birth_order');
                if ($saved > 0 && !isset($used[$saved])) {
                    $ranks[(int) data_get($s, 'id')] = $saved;
                    $used[$saved] = true;
                }
            }
            $next = 1;
            foreach ($items as $s) {
                $id = (int) data_get($s, 'id');
                if (isset($ranks[$id])) continue;
                while (isset($used[$next])) $next++;
                $ranks[$id] = $next;
                $used[$next] = true;
            }

            $total = $ranks ? max($ranks) : 0;
            foreach ($ranks as $id => $rank) {
                $out[$id] = self::info($gender, $rank, $total);
            }
        }

        return $out;
    }

    /** Siblings ordered by their rank from rank() (eldest first). */
    public static function sortByRank($siblings, array $ranks)
    {
        return collect($siblings)
            ->sortBy(fn ($s) => [$ranks[(int) data_get($s, 'id')]['rank'] ?? PHP_INT_MAX, (int) data_get($s, 'id')])
            ->values();
    }
}
