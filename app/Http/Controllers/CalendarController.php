<?php

namespace App\Http\Controllers;

use App\Support\NepaliCalendar;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $today = NepaliCalendar::today();
        $year = (int) $request->query('year', $today['bs']['year']);
        $month = (int) $request->query('month', $today['bs']['month']);
        $years = NepaliCalendar::supportedYears();

        if (!in_array($year, $years, true)) {
            $year = (int) $today['bs']['year'];
        }
        if ($month < 1 || $month > 12) {
            $month = (int) $today['bs']['month'];
        }

        $calendar = NepaliCalendar::month($year, $month);

        return view('calendar.index', compact('today', 'calendar', 'years'));
    }
}
