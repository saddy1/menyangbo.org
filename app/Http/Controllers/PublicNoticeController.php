<?php

namespace App\Http\Controllers;

use App\Models\Notice;

class PublicNoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::where('is_active', true)
            ->sorted()
            ->get();

        return view('notices.index', compact('notices'));
    }

    public function show(Notice $notice)
    {
        abort_unless($notice->is_active, 404);

        $moreNotices = Notice::where('is_active', true)
            ->whereKeyNot($notice->id)
            ->sorted()
            ->limit(5)
            ->get();

        return view('notices.show', compact('notice', 'moreNotices'));
    }
}
