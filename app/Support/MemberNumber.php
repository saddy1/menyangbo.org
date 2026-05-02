<?php

namespace App\Support;

use App\Models\Person;
use Illuminate\Support\Facades\DB;

class MemberNumber
{
    public const PREFIX = 'M-';
    public const START = 1000;

    public static function next(): string
    {
        $max = self::maxExistingNumber();

        return self::PREFIX . max(self::START, $max + 1);
    }

    public static function assignTo(Person $person): string
    {
        if (!empty($person->member_no)) {
            return $person->member_no;
        }

        return DB::transaction(function () use ($person) {
            $fresh = Person::query()->lockForUpdate()->findOrFail($person->id);

            if (!empty($fresh->member_no)) {
                return $fresh->member_no;
            }

            $number = self::next();
            $fresh->forceFill(['member_no' => $number])->save();
            $person->setAttribute('member_no', $number);

            return $number;
        });
    }

    public static function assignMissing(): int
    {
        $next = max(self::START, self::maxExistingNumber() + 1);
        $count = 0;

        Person::query()
            ->where(function ($q) {
                $q->whereNull('member_no')->orWhere('member_no', '');
            })
            ->orderBy('id')
            ->select('id')
            ->chunkById(500, function ($people) use (&$next, &$count) {
                $now = now();
                $cases = [];
                $ids = [];

                foreach ($people as $person) {
                    $ids[] = (int) $person->id;
                    $number = self::PREFIX . $next++;
                    $cases[] = 'WHEN ' . (int) $person->id . " THEN '" . addslashes($number) . "'";
                }

                if (!$ids) {
                    return;
                }

                DB::table('persons')
                    ->whereIn('id', $ids)
                    ->update([
                        'member_no' => DB::raw('CASE id ' . implode(' ', $cases) . ' END'),
                        'updated_at' => $now,
                    ]);

                $count += count($ids);
            });

        return $count;
    }

    public static function missingCount(): int
    {
        return Person::query()
                ->where(function ($q) {
                    $q->whereNull('member_no')->orWhere('member_no', '');
                })
                ->count();
    }

    private static function maxExistingNumber(): int
    {
        return (int) (Person::query()
            ->where('member_no', 'regexp', '^' . preg_quote(self::PREFIX, '/') . '[0-9]+$')
            ->selectRaw("MAX(CAST(SUBSTRING(member_no, ?) AS UNSIGNED)) as max_no", [strlen(self::PREFIX) + 1])
            ->value('max_no') ?? (self::START - 1));
    }
}
