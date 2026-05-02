<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\CommitteeMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommitteeController extends Controller
{
    public function index()
    {
        $committees = Committee::with('members')
            ->orderBy('is_active', 'desc')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.committees.index', [
            'committees' => $committees,
            'types'      => Committee::availableTypes(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:200',
            'type'       => 'required|string|max:100',
            'term_label' => 'nullable|string|max:100',
            'is_active'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data['type'] = preg_replace('/\s+/', ' ', trim($data['type']));
        if ($data['type'] === '') {
            return back()->withErrors(['type' => 'Committee type is required.'])->withInput();
        }
        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        Committee::create($data);

        return back()->with('success', 'Committee added successfully.');
    }

    public function update(Request $request, Committee $committee)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:200',
            'type'       => 'required|string|max:100',
            'term_label' => 'nullable|string|max:100',
            'is_active'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data['type'] = preg_replace('/\s+/', ' ', trim($data['type']));
        if ($data['type'] === '') {
            return back()->withErrors(['type' => 'Committee type is required.'])->withInput();
        }
        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $committee->update($data);

        return back()->with('success', 'Committee updated.');
    }

    public function destroy(Committee $committee)
    {
        foreach ($committee->members as $member) {
            $this->deletePhoto($member->photo_path);
        }
        $committee->delete();

        return back()->with('success', 'Committee deleted.');
    }

    // ── Members ──────────────────────────────────────────────

    public function storeMember(Request $request, Committee $committee)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:200',
            'position'   => 'nullable|string|max:200',
            'email'      => 'nullable|email|max:200',
            'contact'    => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'photo'      => 'nullable|image|max:3072',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $this->savePhoto($request->file('photo'));
        }

        unset($data['photo']);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $committee->members()->create($data);

        return back()->with('success', 'Member added.');
    }

    public function updateMember(Request $request, Committee $committee, CommitteeMember $member)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:200',
            'position'   => 'nullable|string|max:200',
            'email'      => 'nullable|email|max:200',
            'contact'    => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'photo'      => 'nullable|image|max:3072',
        ]);

        if ($request->hasFile('photo')) {
            $this->deletePhoto($member->photo_path);
            $data['photo_path'] = $this->savePhoto($request->file('photo'));
        }

        unset($data['photo']);
        $data['sort_order'] = $data['sort_order'] ?? $member->sort_order;

        $member->update($data);

        return back()->with('success', 'Member updated.');
    }

    public function destroyMember(Committee $committee, CommitteeMember $member)
    {
        $this->deletePhoto($member->photo_path);
        $member->delete();

        return back()->with('success', 'Member removed.');
    }

    // ── Helpers ───────────────────────────────────────────────

    private function savePhoto($file): string
    {
        $dest = public_path('committee-photos');
        if (!is_dir($dest)) mkdir($dest, 0755, true);

        $filename = uniqid('cm_') . '.' . $file->getClientOriginalExtension();
        $file->move($dest, $filename);

        return 'committee-photos/' . $filename;
    }

    private function deletePhoto(?string $path): void
    {
        if ($path && file_exists(public_path($path))) {
            unlink(public_path($path));
        }
    }
}
