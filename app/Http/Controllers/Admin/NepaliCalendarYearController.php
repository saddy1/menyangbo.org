<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NepaliCalendarYear;
use App\Support\NepaliCalendar;
use Illuminate\Http\Request;

class NepaliCalendarYearController extends Controller
{
    public function index()
    {
        $years = NepaliCalendarYear::orderBy('year')->get();

        return view('admin.calendar-years.index', compact('years'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        NepaliCalendarYear::updateOrCreate(
            ['year' => $data['year']],
            ['month_days' => array_values($data['month_days'])]
        );

        NepaliCalendar::clearCache();

        return back()->with('success', 'Calendar year saved.');
    }

    public function update(Request $request, NepaliCalendarYear $calendarYear)
    {
        $data = $this->validated($request, $calendarYear->id);
        $calendarYear->update([
            'year' => $data['year'],
            'month_days' => array_values($data['month_days']),
        ]);

        NepaliCalendar::clearCache();

        return back()->with('success', 'Calendar year updated.');
    }

    public function destroy(NepaliCalendarYear $calendarYear)
    {
        $calendarYear->delete();
        NepaliCalendar::clearCache();

        return back()->with('success', 'Calendar year deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $unique = 'unique:nepali_calendar_years,year';
        if ($ignoreId) {
            $unique .= ',' . $ignoreId;
        }

        return $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2200', $unique],
            'month_days' => ['required', 'array', 'size:12'],
            'month_days.*' => ['required', 'integer', 'min:28', 'max:33'],
        ]);
    }
}
