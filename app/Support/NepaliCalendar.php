<?php

namespace App\Support;

use App\Models\NepaliCalendarYear;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class NepaliCalendar
{
    private const NEPALI_DIGITS = ['0' => '०', '1' => '१', '2' => '२', '3' => '३', '4' => '४', '5' => '५', '6' => '६', '7' => '७', '8' => '८', '9' => '९'];

    private const BS_MONTHS = [
        1 => 'बैशाख',
        2 => 'जेठ',
        3 => 'असार',
        4 => 'श्रावण',
        5 => 'भदौ',
        6 => 'आश्विन',
        7 => 'कार्तिक',
        8 => 'मंसिर',
        9 => 'पुष',
        10 => 'माघ',
        11 => 'फागुन',
        12 => 'चैत्र',
    ];

    private const AD_MONTHS = [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];

    private const WEEKDAYS = [
        0 => 'आइतबार',
        1 => 'सोमबार',
        2 => 'मंगलबार',
        3 => 'बुधबार',
        4 => 'बिहीबार',
        5 => 'शुक्रबार',
        6 => 'शनिबार',
    ];

    private static ?array $yearDaysCache = null;

    public static function today(): array
    {
        return self::forDate(now('Asia/Kathmandu'));
    }

    public static function forDate(CarbonInterface $date): array
    {
        $date = CarbonImmutable::instance($date)->timezone('Asia/Kathmandu');
        $bs = self::adToBs($date);

        return [
            'ad' => [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('d') . ' ' . self::AD_MONTHS[(int) $date->format('n')] . ' ' . $date->format('Y'),
                'time' => $date->format('h:i A'),
            ],
            'bs' => $bs,
            'weekday' => self::WEEKDAYS[(int) $date->dayOfWeek],
        ];
    }

    public static function month(int $bsYear, int $bsMonth): array
    {
        $today = self::today()['bs'];
        $days = self::monthDays($bsYear, $bsMonth);
        $firstAd = self::bsToAd($bsYear, $bsMonth, 1);
        $firstWeekday = (int) $firstAd->dayOfWeek;

        return [
            'year' => $bsYear,
            'month' => $bsMonth,
            'month_name' => self::BS_MONTHS[$bsMonth],
            'days' => $days,
            'first_weekday' => $firstWeekday,
            'today_day' => ((int) $today['year'] === $bsYear && (int) $today['month'] === $bsMonth) ? (int) $today['day'] : null,
            'prev' => $bsMonth === 1 ? ['year' => $bsYear - 1, 'month' => 12] : ['year' => $bsYear, 'month' => $bsMonth - 1],
            'next' => $bsMonth === 12 ? ['year' => $bsYear + 1, 'month' => 1] : ['year' => $bsYear, 'month' => $bsMonth + 1],
        ];
    }

    public static function supportedYears(): array
    {
        return array_keys(self::yearDays());
    }

    public static function nepaliNumber(int|string $value): string
    {
        return strtr((string) $value, self::NEPALI_DIGITS);
    }

    public static function monthName(int $month): string
    {
        return self::BS_MONTHS[$month] ?? '';
    }

    public static function clearCache(): void
    {
        self::$yearDaysCache = null;
    }

    private static function adToBs(CarbonImmutable $date): array
    {
        $anchorAd = CarbonImmutable::create(2023, 4, 14, 0, 0, 0, 'Asia/Kathmandu');
        $year = 2080;
        $month = 1;
        $day = 1;
        $diff = $anchorAd->diffInDays($date, false);

        if ($diff < 0) {
            return [
                'year' => $date->year + 57,
                'month' => $date->month,
                'day' => $date->day,
                'label' => self::nepaliNumber($date->year + 57) . ' ' . self::monthName($date->month) . ' ' . self::nepaliNumber($date->day),
                'short' => self::nepaliNumber(($date->year + 57) . '-' . $date->month . '-' . $date->day),
            ];
        }

        while ($diff > 0) {
            $monthDays = self::monthDays($year, $month);
            if ($day < $monthDays) {
                $day++;
            } else {
                $day = 1;
                $month++;
                if ($month > 12) {
                    $month = 1;
                    $year++;
                }
            }
            $diff--;
        }

        return [
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'month_name' => self::BS_MONTHS[$month],
            'label' => self::nepaliNumber($year) . ' ' . self::BS_MONTHS[$month] . ' ' . self::nepaliNumber($day),
            'short' => self::nepaliNumber(sprintf('%04d-%02d-%02d', $year, $month, $day)),
        ];
    }

    private static function bsToAd(int $bsYear, int $bsMonth, int $bsDay): CarbonImmutable
    {
        $date = CarbonImmutable::create(2023, 4, 14, 0, 0, 0, 'Asia/Kathmandu');

        for ($year = 2080; $year < $bsYear; $year++) {
            $date = $date->addDays(array_sum(self::yearDays()[$year] ?? array_fill(0, 12, 30)));
        }

        for ($month = 1; $month < $bsMonth; $month++) {
            $date = $date->addDays(self::monthDays($bsYear, $month));
        }

        return $date->addDays(max(0, $bsDay - 1));
    }

    private static function monthDays(int $bsYear, int $bsMonth): int
    {
        return (int) (self::yearDays()[$bsYear][$bsMonth - 1] ?? 30);
    }

    private static function yearDays(): array
    {
        if (self::$yearDaysCache !== null) {
            return self::$yearDaysCache;
        }

        return self::$yearDaysCache = NepaliCalendarYear::query()
            ->orderBy('year')
            ->get()
            ->mapWithKeys(fn (NepaliCalendarYear $year) => [$year->year => array_values($year->month_days ?? [])])
            ->all();
    }
}
