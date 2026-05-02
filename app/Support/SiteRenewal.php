<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Carbon;

class SiteRenewal
{
    public const KEY = 'renew_until';

    public static function date(): ?Carbon
    {
        $value = SiteSetting::getValue(self::KEY);

        return $value ? Carbon::parse($value)->endOfDay() : null;
    }

    public static function isExpired(): bool
    {
        $date = self::date();

        return $date !== null && now()->greaterThan($date);
    }

    public static function daysLeft(): ?int
    {
        $date = self::date();
        if (!$date) {
            return null;
        }

        return max(0, now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false));
    }
}
