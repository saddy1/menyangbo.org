<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * The original seed month lengths were wrong (2083 had Baisakh–Bhadra = 157 days, so
     * 2083-06-09 showed as आश्विन ८; 2081/2082 lengths were swapped). Corrected values from the
     * nepali-datetime calendar table, checked against published month start dates
     * (e.g. 2083: Bhadra 1 = 2026-08-17, Ashoj 1 = 2026-09-17, new year 2084 = 2027-04-14).
     */
    private const CORRECT = [
        2080 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        2081 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2082 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2083 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2084 => [31, 31, 32, 31, 31, 30, 30, 30, 29, 30, 30, 30],
    ];

    private const OLD = [
        2080 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 30],
        2081 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2082 => [31, 31, 32, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2083 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2084 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
    ];

    public function up(): void
    {
        $this->write(self::CORRECT);
    }

    public function down(): void
    {
        $this->write(self::OLD);
    }

    private function write(array $years): void
    {
        foreach ($years as $year => $days) {
            DB::table('nepali_calendar_years')->updateOrInsert(
                ['year' => $year],
                ['month_days' => json_encode($days), 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
};
