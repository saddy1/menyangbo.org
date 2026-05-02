@extends('admin.layout')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-5">

    <div class="flex items-center justify-between gap-3 flex-wrap mb-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Change Requests</h1>
            <div class="text-sm text-slate-600">Approve / Reject / View details</div>
        </div>

        <form method="GET" class="flex items-center gap-2 flex-wrap">
            <select name="status" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">All Status</option>
                <option value="pending"  @selected(request('status')==='pending')>Pending</option>
                <option value="approved" @selected(request('status')==='approved')>Approved</option>
                <option value="rejected" @selected(request('status')==='rejected')>Rejected</option>
            </select>

            <select name="type" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">All Types</option>
                <option value="mark_deceased" @selected(request('type')==='mark_deceased')>mark_deceased</option>
                <option value="add_child"     @selected(request('type')==='add_child')>add_child</option>
                <option value="update_profile"@selected(request('type')==='update_profile')>update_profile</option>
                <option value="add_union"     @selected(request('type')==='add_union')>add_union</option>
            </select>

            <select name="per_page" class="border rounded-lg px-3 py-2 text-sm">
                @foreach([10,20,50,100] as $n)
                    <option value="{{ $n }}" @selected((int)request('per_page',20)===$n)>{{ $n }}/page</option>
                @endforeach
            </select>

            <button class="bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                Apply
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800 font-semibold">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-900 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left">SN</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Person</th>
                        <th class="px-4 py-3 text-left">Submitted By</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Created</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @php
                        $snStart = ($requests->currentPage() - 1) * $requests->perPage();
                    @endphp

                    @forelse($requests as $i => $r)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $snStart + $i + 1 }}</td>

                            <td class="px-4 py-3">
                                <span class="font-semibold text-slate-900">{{ $r->type }}</span>
                            </td>

                            <td class="px-4 py-3">
                                @if($r->person)
                                    <a class="font-semibold text-blue-700 hover:underline"
                                       href="{{ route('member.page', ['person'=>$r->person->id]) }}" target="_blank">
                                        {{ $r->person->display_name }}
                                    </a>
                                    <div class="text-xs text-slate-500">#{{ $r->person->id }}</div>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ $r->submitted_name ?? '—' }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ $r->submitted_mobile ?? '' }} {{ $r->submitted_email ? '• '.$r->submitted_email : '' }}
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                @php
                                    $badge = [
                                        'pending'  => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'approved' => 'bg-green-50 text-green-700 border-green-200',
                                        'rejected' => 'bg-red-50 text-red-700 border-red-200',
                                    ][$r->status] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded-full border text-xs font-bold {{ $badge }}">
                                    {{ strtoupper($r->status) }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-slate-700">
                                {{ $r->created_at?->format('Y-m-d H:i') }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2 flex-wrap">

                                    <a href="{{ route('admin.requests.show', $r->id) }}"
                                       class="px-3 py-2 rounded-lg border text-sm font-semibold hover:bg-slate-50">
                                        View
                                    </a>

                                    @if($r->status === 'pending')
                                        <form method="POST" action="{{ route('admin.requests.approve', $r->id) }}"
                                              onsubmit="return confirm('Approve this request?');">
                                            @csrf
                                            <button class="px-3 py-2 rounded-lg bg-green-600 text-white text-sm font-semibold">
                                                Approve
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.requests.reject', $r->id) }}"
                                              onsubmit="return confirm('Reject this request?');">
                                            @csrf
                                            <button class="px-3 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold">
                                                Reject
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.requests.destroy', $r->id) }}"
                                              onsubmit="return confirm('Delete this request permanently?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="px-3 py-2 rounded-lg border border-red-200 text-red-700 text-sm font-semibold hover:bg-red-50">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                No requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t bg-slate-50">
            {{ $requests->links() }}
        </div>
    </div>

</div>
@endsection