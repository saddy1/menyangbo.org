<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::sorted()->get();
        return view('admin.notices.index', compact('notices'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'body'       => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'link'       => ['nullable', 'url', 'max:500'],
            'link_text'  => ['nullable', 'string', 'max:100'],
            'is_active'  => ['nullable', 'boolean'],
        ]);
        unset($data['attachment']);

        $data['is_active'] = $request->boolean('is_active', true);
        $data = array_merge($data, $this->storeAttachment($request));

        Notice::create($data);
        return redirect()->route('admin.notices.index')->with('success', 'Notice added.');
    }

    public function update(Request $request, Notice $notice)
    {
        $data = $request->validate([
            'title'             => ['required', 'string', 'max:255'],
            'body'              => ['nullable', 'string'],
            'attachment'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'remove_attachment' => ['nullable', 'boolean'],
            'link'              => ['nullable', 'url', 'max:500'],
            'link_text'         => ['nullable', 'string', 'max:100'],
            'is_active'         => ['nullable', 'boolean'],
        ]);
        unset($data['attachment'], $data['remove_attachment']);

        $data['is_active'] = $request->boolean('is_active');
        if ($request->boolean('remove_attachment') && $notice->attachment_path) {
            $this->deleteAttachment($notice->attachment_path);
            $data['attachment_path'] = null;
            $data['attachment_type'] = null;
            $data['attachment_name'] = null;
        }
        if ($attachment = $this->storeAttachment($request)) {
            if ($notice->attachment_path) {
                $this->deleteAttachment($notice->attachment_path);
            }
            $data = array_merge($data, $attachment);
        }
        $notice->update($data);
        return redirect()->route('admin.notices.index')->with('success', 'Notice updated.');
    }

    public function destroy(Notice $notice)
    {
        if ($notice->attachment_path) {
            $this->deleteAttachment($notice->attachment_path);
        }
        $notice->delete();
        return redirect()->route('admin.notices.index')->with('success', 'Notice deleted.');
    }

    private function storeAttachment(Request $request): array
    {
        if (! $request->hasFile('attachment')) {
            return [];
        }

        $file = $request->file('attachment');
        $destinationPath = public_path('notices');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $mime      = (string) $file->getMimeType();
        $filename  = now()->format('YmdHis') . '_' . (Str::slug($originalName) ?: 'notice') . '_' . uniqid() . '.' . $extension;
        $file->move($destinationPath, $filename);

        $path = 'notices/' . $filename;

        return [
            'attachment_path' => $path,
            'attachment_type' => str_starts_with($mime, 'image/') ? 'image' : 'pdf',
            'attachment_name' => $file->getClientOriginalName(),
        ];
    }

    private function deleteAttachment(string $path): void
    {
        $filePath = public_path($path);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }
}
