<?php

namespace App\Support;

use App\Models\Person;

/**
 * सदस्यको प्रकार (persons.member_type):
 *   दाजुभाइ   — sons of the family (male)
 *   दिदीबहिनी — daughters of the family (female)
 *   बुहारी    — women who married into the family (female, married, no parents in the family)
 */
class MemberType
{
    public const DAJU_BHAI   = 'दाजुभाइ';
    public const DIDI_BAHINI = 'दिदीबहिनी';
    public const BUHARI      = 'बुहारी';

    public static function all(): array
    {
        return [self::DAJU_BHAI, self::DIDI_BAHINI, self::BUHARI];
    }

    /** Default for a gender (before any marriage is known); null for other/unknown. */
    public static function forGender(?string $gender): ?string
    {
        return match ($gender) {
            'male'   => self::DAJU_BHAI,
            'female' => self::DIDI_BAHINI,
            default  => null,
        };
    }

    /** Married-in woman: female, has a spouse, and no parent linked in the family tree. */
    public static function isBuhari(Person $person): bool
    {
        return $person->gender === 'female'
            && ($person->unionsAsSpouse1()->exists() || $person->unionsAsSpouse2()->exists())
            && !$person->parents()->exists();
    }

    /**
     * After a marriage is recorded: a married-in woman becomes बुहारी unless an admin already
     * gave her a different type (only empty / the automatic दिदीबहिनी is replaced).
     */
    public static function markBuhariIfMarriedIn(Person $person): void
    {
        if (in_array($person->member_type, [null, '', self::DIDI_BAHINI], true) && self::isBuhari($person)) {
            $person->forceFill(['member_type' => self::BUHARI])->save();
        }
    }
}
