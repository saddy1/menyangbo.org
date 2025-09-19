<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\ParentChildEdge;
use App\Models\UnionModel;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'people'   => Person::count(),
            'edges'    => ParentChildEdge::count(),
            'unions'   => UnionModel::count(),
            'deceased' => Person::where('is_deceased', true)->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
