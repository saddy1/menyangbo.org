<?php

namespace App\Http\Controllers;

use App\Models\Committee;

class CommitteeController extends Controller
{
    public function index()
    {
        $working = Committee::with('members')
            ->active()
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('type');

        $past = Committee::with('members')
            ->past()
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('type');

        return view('committee.index', [
            'working' => $working,
            'past'    => $past,
            'types'   => Committee::availableTypes(),
        ]);
    }
}
