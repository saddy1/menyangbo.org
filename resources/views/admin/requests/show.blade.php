@extends('admin.layout')
@section('title', 'Request #' . str_pad($r->id, 5, '0', STR_PAD_LEFT))

@section('content')
@php
/* ── Shared config ─────────────────────────────────────────────── */
$typeConfig = [
    'add_child'      => ['label'=>'नयाँ सन्तान दर्ता',  'en'=>'New Child Registration', 'icon'=>'fa-baby',      'hue'=>'blue',   'border'=>'#2563eb','bg'=>'#eff6ff','dot'=>'bg-blue-500'],
    'mark_deceased'  => ['label'=>'मृत्यु सूचना',        'en'=>'Death Notice',           'icon'=>'fa-cross',     'hue'=>'slate',  'border'=>'#475569','bg'=>'#f1f5f9','dot'=>'bg-slate-500'],
    'add_union'      => ['label'=>'विवाह दर्ता',          'en'=>'Marriage Registration',  'icon'=>'fa-heart',     'hue'=>'rose',   'border'=>'#e11d48','bg'=>'#fff1f2','dot'=>'bg-rose-500'],
    'update_profile' => ['label'=>'प्रोफाइल सम्पादन',    'en'=>'Profile Update',         'icon'=>'fa-pen-to-square','hue'=>'amber','border'=>'#d97706','bg'=>'#fffbeb','dot'=>'bg-amber-500'],
    'not_listed'     => ['label'=>'नयाँ सदस्य',           'en'=>'New Member',             'icon'=>'fa-user-plus', 'hue'=>'purple', 'border'=>'#7c3aed','bg'=>'#faf5ff','dot'=>'bg-purple-500'],
    'link_parent'    => ['label'=>'अभिभावक जोड्ने',       'en'=>'Link Parent',            'icon'=>'fa-link',      'hue'=>'teal',   'border'=>'#0d9488','bg'=>'#f0fdfa','dot'=>'bg-teal-500'],
];
$linkParent = $r->type === 'link_parent' && !empty($payload['parent_id']) ? \App\Models\Person::find($payload['parent_id']) : null;
$tc = $typeConfig[$r->type] ?? ['label'=>ucfirst($r->type),'en'=>ucfirst($r->type),'icon'=>'fa-file','hue'=>'gray','border'=>'#9ca3af','bg'=>'#f9fafb','dot'=>'bg-gray-400'];

$payload = (array) $r->payload;

/* Spouse lookup for add_union */
$spousePerson = null;
if ($r->type === 'add_union' && !empty($payload['spouse_person_id'])) {
    $spousePerson = \App\Models\Person::find($payload['spouse_person_id']);
}

$statusColor  = match($r->status) { 'approved'=>'#16a34a','rejected'=>'#dc2626',default=>'#d97706' };
$statusBg     = match($r->status) { 'approved'=>'bg-green-100 text-green-700 border-green-200','rejected'=>'bg-red-100 text-red-700 border-red-200',default=>'bg-amber-100 text-amber-700 border-amber-200' };

/* Human-readable field labels */
$FL = [
    'display_name'       => 'नाम (नेपाली)',
    'display_name_np'    => 'Full Name (English)',
    'display_name_limbu' => 'Limbu Name',
    'given_name'         => 'First Name',
    'middle_name'        => 'Middle Name',
    'family_name'        => 'Family / Sur Name',
    'gender'             => 'Gender',
    'birth_date'         => 'Birth Date (A.D.)',
    'birth_date_bs'      => 'Birth Date (B.S.)',
    'birth_place'        => 'Birth Place',
    'pusta'              => 'Pusta (Generation)',
    'mobile'             => 'Mobile',
    'email'              => 'Email',
    'address'            => 'Address',
    'education'          => 'Education',
    'occupation'         => 'Occupation',
    'lineage'            => 'Lineage / Gotra',
    'family_type'        => 'Family Type',
    'blood_group'        => 'Blood Group',
    'rashifal'           => 'Zodiac / Rashifal',
    'religion'           => 'Religion',
    'special_note'       => 'Special Note',
    'bio'                => 'Bio',
    'death_date'         => 'Death Date (A.D.)',
    'death_date_bs'      => 'Death Date (B.S.)',
    'death_place'        => 'Death Place',
    'death_tithi'        => 'Death Tithi',
    'death_reason'       => 'Cause of Death',
    'spouse_person_id'   => 'Existing Spouse (ID)',
    'spouse_name'        => 'Spouse Name (Nepali)',
    'spouse_name_np'     => 'Spouse Name (English)',
    'spouse_name_limbu'  => 'Spouse Name (Limbu)',
    'spouse_gender'      => 'Spouse Gender',
    'spouse_pusta'       => 'Spouse Pusta',
    'type'               => 'Relationship Type',
    'start_date'         => 'Marriage Date',
    'notes'              => 'Notes',
    'father_name'        => 'Father Name',
    'mother_name'        => 'Mother Name',
    'photo_path'         => 'Photo',
];

$lbl = fn($k) => $FL[$k] ?? ucwords(str_replace('_',' ',$k));

/* Field groups per request type */
$groups = match($r->type) {
    'add_child', 'not_listed' => [
        'Basic Information'  => ['display_name','display_name_np','display_name_limbu','gender','pusta'],
        'Birth Details'      => ['birth_date','birth_date_bs','birth_place'],
        'Contact'            => ['mobile','email','address'],
        'Personal Details'   => ['education','occupation','lineage','family_type','blood_group','rashifal','religion'],
        'Notes & Bio'        => ['special_note','bio'],
    ],
    'mark_deceased' => [
        'Death Details' => ['death_date','death_date_bs','death_place','death_tithi','death_reason'],
    ],
    'add_union' => [
        'Spouse (Existing)'  => ['spouse_person_id'],
        'New Spouse Details' => ['spouse_name','spouse_name_np','spouse_name_limbu','spouse_gender','spouse_pusta'],
        'Union Details'      => ['type','start_date','notes'],
    ],
    'update_profile' => [
        'Basic Information'  => ['display_name','display_name_np','display_name_limbu','gender'],
        'Birth & Address'    => ['birth_date','birth_date_bs','birth_place','address'],
        'Contact'            => ['mobile','email'],
        'Personal Details'   => ['education','occupation','lineage','family_type','blood_group','rashifal','religion'],
        'Notes & Bio'        => ['special_note','bio'],
    ],
    default => ['Details' => array_keys($payload)],
};

/* Helper: value display */
$fv = function($v) {
    if (is_null($v) || $v === '') return null;
    if (is_array($v)) return json_encode($v, JSON_UNESCAPED_UNICODE);
    return (string) $v;
};
@endphp

{{-- ═══════════════════════════════════════════════
     PRINT STYLES
══════════════════════════════════════════════════ --}}
<style>
@media print {
  header, aside, #sidebarBackdrop, .no-print { display:none !important; }
  main { margin-left:0 !important; padding:0 !important; }
  #screen-view { display:none !important; }
  #print-doc   { display:block !important; }
  @page { margin:15mm 18mm; size:A4; }
  body { background:#fff !important; }
}
#print-doc { display:none; }

/* Print typography */
#print-doc { font-family:'Georgia',serif; font-size:11.5px; color:#111; line-height:1.65; }
#print-doc .p-section { border:1px solid #c8d0dc; border-radius:4px; margin-bottom:14px; overflow:hidden; }
#print-doc .p-section-head { background:#1a3a6b; color:#fff; padding:6px 12px; font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:.6px; }
#print-doc .p-grid { display:grid; grid-template-columns:1fr 1fr; }
#print-doc .p-field { padding:6px 10px; border-bottom:1px solid #e8ecf0; border-right:1px solid #e8ecf0; }
#print-doc .p-field:nth-child(even) { border-right:none; }
#print-doc .p-field.full { grid-column:1/-1; border-right:none; }
#print-doc .p-label { font-size:9.5px; color:#555; font-weight:700; text-transform:uppercase; letter-spacing:.4px; margin-bottom:2px; }
#print-doc .p-value { font-weight:600; color:#111; }
#print-doc .p-empty { color:#aaa; font-style:italic; }
</style>

{{-- ═══════════════════════════════════════════════
     PRINTABLE DOCUMENT
══════════════════════════════════════════════════ --}}
<div id="print-doc">

  {{-- Letterhead --}}
  <div style="display:flex;align-items:center;gap:14px;border-bottom:3px solid #1a3a6b;padding-bottom:12px;margin-bottom:16px;">
    <div style="flex:1;">
      <div style="font-size:20px;font-weight:900;color:#1a3a6b;letter-spacing:.3px;">मेन्याङ्बो कल्याणकारी संघ</div>
      <div style="font-size:10px;color:#555;margin-top:1px;">धरान–१५, सुनसरी, नेपाल &nbsp;|&nbsp; परिवार वृक्ष प्रणाली</div>
    </div>
    <div style="text-align:right;font-size:10px;color:#555;">
      <div style="font-size:14px;font-weight:800;color:#1a3a6b;">{{ $tc['label'] }}</div>
      <div style="font-style:italic;">{{ $tc['en'] }}</div>
    </div>
  </div>

  {{-- Reference row --}}
  <table style="width:100%;font-size:11px;margin-bottom:14px;border-collapse:collapse;border:1px solid #c8d0dc;border-radius:4px;overflow:hidden;">
    <tr style="background:#f0f4f9;">
      <td style="padding:5px 10px;color:#555;font-weight:700;width:130px;">Reference No.</td>
      <td style="padding:5px 10px;font-weight:800;">#{{ str_pad($r->id,5,'0',STR_PAD_LEFT) }}</td>
      <td style="padding:5px 10px;color:#555;font-weight:700;width:120px;">Date</td>
      <td style="padding:5px 10px;">{{ $r->created_at->format('d M Y, h:i A') }}</td>
    </tr>
    <tr>
      <td style="padding:5px 10px;color:#555;font-weight:700;">Status</td>
      <td style="padding:5px 10px;font-weight:800;color:{{ $statusColor }};text-transform:uppercase;">{{ $r->status }}</td>
      <td style="padding:5px 10px;color:#555;font-weight:700;">Related Person</td>
      <td style="padding:5px 10px;font-weight:700;">
        {{ $r->person?->display_name ?? '—' }}
        @if($r->person?->pusta) &nbsp;(पुस्ता {{ $r->person->pusta }}) @endif
      </td>
    </tr>
    <tr style="background:#f0f4f9;">
      <td style="padding:5px 10px;color:#555;font-weight:700;">Submitted By</td>
      <td style="padding:5px 10px;">{{ $r->submitted_name ?? $r->user?->name ?? '—' }}</td>
      <td style="padding:5px 10px;color:#555;font-weight:700;">Email / Mobile</td>
      <td style="padding:5px 10px;">{{ $r->submitted_email ?? $r->user?->email ?? '—' }}</td>
    </tr>
  </table>

  {{-- Type-specific content --}}
  @if($r->type === 'add_union')
    {{-- Couple section --}}
    <div class="p-section">
      <div class="p-section-head" style="background:#831843;">Spouse 1 — Current Person</div>
      <div class="p-grid">
        <div class="p-field"><div class="p-label">Name</div><div class="p-value">{{ $r->person?->display_name ?? '—' }}</div></div>
        <div class="p-field"><div class="p-label">Pusta</div><div class="p-value">{{ $r->person?->pusta ?? '—' }}</div></div>
        <div class="p-field"><div class="p-label">Gender</div><div class="p-value">{{ $r->person?->gender ?? '—' }}</div></div>
        <div class="p-field"><div class="p-label">Member No.</div><div class="p-value">{{ $r->person?->member_no ?? '—' }}</div></div>
      </div>
    </div>
    <div class="p-section">
      <div class="p-section-head" style="background:#9f1239;">Spouse 2 — {{ $spousePerson ? 'Existing Person' : 'New Person' }}</div>
      <div class="p-grid">
        @if($spousePerson)
          <div class="p-field"><div class="p-label">Name</div><div class="p-value">{{ $spousePerson->display_name }}</div></div>
          <div class="p-field"><div class="p-label">Pusta</div><div class="p-value">{{ $spousePerson->pusta ?? '—' }}</div></div>
          <div class="p-field"><div class="p-label">Gender</div><div class="p-value">{{ $spousePerson->gender ?? '—' }}</div></div>
          <div class="p-field"><div class="p-label">Member No.</div><div class="p-value">{{ $spousePerson->member_no ?? '—' }}</div></div>
        @else
          @if(!empty($payload['spouse_photo_path']))
            <div class="p-field full"><div class="p-label">Photo</div><div class="p-value"><img src="{{ asset($payload['spouse_photo_path']) }}" alt="" style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:1px solid #e5e7eb;"></div></div>
          @endif
          @foreach(['spouse_name'=>'नाम (Nepali)','spouse_name_np'=>'Name (English)','spouse_name_limbu'=>'Limbu Name','spouse_gender'=>'Gender','spouse_pusta'=>'Pusta',
                    'spouse_birth_date'=>'जन्म मिति (A.D.)','spouse_birth_date_bs'=>'जन्म मिति (B.S.)','spouse_birth_place'=>'माइती / जन्मस्थान',
                    'spouse_father_name'=>'बुबाको नाम','spouse_mother_name'=>'आमाको नाम','spouse_mobile'=>'मोबाइल','spouse_address'=>'ठेगाना',
                    'spouse_education'=>'शिक्षा','spouse_occupation'=>'पेशा'] as $fk=>$flbl)
            @if(!empty($payload[$fk]))
              <div class="p-field"><div class="p-label">{{ $flbl }}</div><div class="p-value">{{ $payload[$fk] }}</div></div>
            @endif
          @endforeach
        @endif
      </div>
    </div>
    <div class="p-section">
      <div class="p-section-head">Union Details</div>
      <div class="p-grid">
        <div class="p-field"><div class="p-label">Relationship Type</div><div class="p-value">{{ $payload['type'] ?? '—' }}</div></div>
        <div class="p-field"><div class="p-label">Marriage Date</div><div class="p-value">{{ $payload['start_date'] ?? '—' }}</div></div>
        @if(!empty($payload['notes']))<div class="p-field full"><div class="p-label">Notes</div><div class="p-value">{{ $payload['notes'] }}</div></div>@endif
      </div>
    </div>

  @elseif($r->type === 'link_parent')
    <div class="p-section">
      <div class="p-section-head" style="background:#0f766e;">Child — Current Person</div>
      <div class="p-grid">
        <div class="p-field"><div class="p-label">Name</div><div class="p-value">{{ $r->person?->display_name ?? '—' }}</div></div>
        <div class="p-field"><div class="p-label">Pusta</div><div class="p-value">{{ $r->person?->pusta ?? '—' }}</div></div>
        <div class="p-field"><div class="p-label">Gender</div><div class="p-value">{{ $r->person?->gender ?? '—' }}</div></div>
        <div class="p-field"><div class="p-label">Member No.</div><div class="p-value">{{ $r->person?->member_no ?? '—' }}</div></div>
      </div>
    </div>
    <div class="p-section">
      <div class="p-section-head" style="background:#115e59;">Requested Parent</div>
      <div class="p-grid">
        <div class="p-field"><div class="p-label">Name</div><div class="p-value">
          @if($linkParent)<a href="{{ route('member.page', $linkParent->id) }}" target="_blank" style="color:#0f766e;text-decoration:underline;">{{ $linkParent->display_name }}</a>@else{{ $payload['parent_name'] ?? '—' }} <span style="color:#dc2626;">(deleted)</span>@endif
        </div></div>
        <div class="p-field"><div class="p-label">Pusta</div><div class="p-value">{{ $linkParent?->pusta ?? '—' }}</div></div>
        <div class="p-field"><div class="p-label">Member No.</div><div class="p-value">{{ $linkParent?->member_no ?? '—' }}</div></div>
        <div class="p-field"><div class="p-label">Relation</div><div class="p-value">{{ ucfirst($payload['relation_type'] ?? 'birth') }}</div></div>
        @if(!empty($payload['note']))<div class="p-field full"><div class="p-label">Note</div><div class="p-value">{{ $payload['note'] }}</div></div>@endif
      </div>
    </div>

  @elseif($r->type === 'mark_deceased')
    <div class="p-section">
      <div class="p-section-head" style="background:#374151;">Deceased Person</div>
      <div class="p-grid">
        <div class="p-field"><div class="p-label">Name</div><div class="p-value">{{ $r->person?->display_name ?? '—' }}</div></div>
        <div class="p-field"><div class="p-label">Pusta</div><div class="p-value">{{ $r->person?->pusta ?? '—' }}</div></div>
        <div class="p-field"><div class="p-label">Gender</div><div class="p-value">{{ $r->person?->gender ?? '—' }}</div></div>
        <div class="p-field"><div class="p-label">Member No.</div><div class="p-value">{{ $r->person?->member_no ?? '—' }}</div></div>
      </div>
    </div>
    <div class="p-section">
      <div class="p-section-head" style="background:#374151;">Death Details</div>
      <div class="p-grid">
        @foreach(['death_date'=>'Death Date (A.D.)','death_date_bs'=>'Death Date (B.S.)','death_place'=>'Death Place','death_tithi'=>'Death Tithi','death_reason'=>'Cause of Death'] as $fk=>$flbl)
          <div class="p-field @if(in_array($fk,['death_reason'])) full @endif">
            <div class="p-label">{{ $flbl }}</div>
            <div class="p-value {{ empty($payload[$fk]) ? 'p-empty' : '' }}">{{ $payload[$fk] ?? '—' }}</div>
          </div>
        @endforeach
      </div>
    </div>

  @elseif($r->type === 'update_profile')
    <div class="p-section">
      <div class="p-section-head">Profile Changes (Before → After)</div>
      <table style="width:100%;border-collapse:collapse;font-size:11px;">
        <tr style="background:#f0f4f9;">
          <th style="padding:5px 10px;text-align:left;color:#555;font-weight:700;width:30%;">Field</th>
          <th style="padding:5px 10px;text-align:left;color:#555;font-weight:700;width:35%;">Current</th>
          <th style="padding:5px 10px;text-align:left;color:#555;font-weight:700;width:35%;">Requested</th>
        </tr>
        @foreach($payload as $fk => $fval)
          @if($fval !== null && $fval !== '')
          @php $cur = $currentValues[$fk] ?? null; $changed = (string)$cur !== (string)$fval; @endphp
          <tr style="border-top:1px solid #e8ecf0;{{ $changed ? 'background:#fffbeb;' : '' }}">
            <td style="padding:5px 10px;font-weight:600;color:#444;">{{ $lbl($fk) }}</td>
            <td style="padding:5px 10px;color:#666;">{{ $cur ?? '—' }}</td>
            <td style="padding:5px 10px;font-weight:600;{{ $changed ? 'color:#b45309;' : 'color:#111;' }}">{{ is_array($fval) ? json_encode($fval,JSON_UNESCAPED_UNICODE) : $fval }}</td>
          </tr>
          @endif
        @endforeach
      </table>
    </div>

  @else
    {{-- add_child / not_listed --}}
    @if($r->person)
    <div class="p-section">
      <div class="p-section-head">Parent / Related Person</div>
      <div class="p-grid">
        <div class="p-field"><div class="p-label">Name</div><div class="p-value">{{ $r->person->display_name }}</div></div>
        <div class="p-field"><div class="p-label">Pusta</div><div class="p-value">{{ $r->person->pusta ?? '—' }}</div></div>
        <div class="p-field"><div class="p-label">Gender</div><div class="p-value">{{ $r->person->gender ?? '—' }}</div></div>
        <div class="p-field"><div class="p-label">Member No.</div><div class="p-value">{{ $r->person->member_no ?? '—' }}</div></div>
      </div>
    </div>
    @endif
    @foreach($groups as $groupName => $groupKeys)
      @php $rows = collect($groupKeys)->filter(fn($k) => !empty($payload[$k]))->values(); @endphp
      @if($rows->count())
      <div class="p-section">
        <div class="p-section-head">{{ $groupName }}</div>
        <div class="p-grid">
          @foreach($rows as $fk)
            <div class="p-field @if(in_array($fk,['special_note','bio','address'])) full @endif">
              <div class="p-label">{{ $lbl($fk) }}</div>
              <div class="p-value">{{ is_array($payload[$fk]) ? json_encode($payload[$fk],JSON_UNESCAPED_UNICODE) : $payload[$fk] }}</div>
            </div>
          @endforeach
        </div>
      </div>
      @endif
    @endforeach
  @endif

  {{-- Review decision --}}
  @if($r->status !== 'pending')
  <div class="p-section">
    <div class="p-section-head" style="background:{{ $statusColor }};">Review Decision — {{ strtoupper($r->status) }}</div>
    <div class="p-grid">
      <div class="p-field"><div class="p-label">Reviewed By</div><div class="p-value">{{ $r->reviewer?->name ?? '—' }}</div></div>
      <div class="p-field"><div class="p-label">Date</div><div class="p-value">{{ $r->reviewed_at?->format('d M Y, h:i A') ?? '—' }}</div></div>
      @if($r->review_note)<div class="p-field full"><div class="p-label">Note</div><div class="p-value">{{ $r->review_note }}</div></div>@endif
    </div>
  </div>
  @endif

  {{-- Signature row --}}
  <div style="margin-top:44px;display:flex;justify-content:space-between;gap:32px;">
    <div style="flex:1;text-align:center;border-top:1px solid #555;padding-top:6px;font-size:10px;color:#555;">अनुरोधकर्ताको हस्ताक्षर<br>Submitter Signature &amp; Date</div>
    <div style="flex:1;text-align:center;border-top:1px solid #555;padding-top:6px;font-size:10px;color:#555;">समीक्षकको हस्ताक्षर<br>Reviewer Signature &amp; Date</div>
    <div style="flex:1;text-align:center;border-top:1px solid #555;padding-top:6px;font-size:10px;color:#555;">कार्यालय छाप<br>Office Stamp</div>
  </div>

  <div style="margin-top:16px;text-align:center;font-size:9px;color:#aaa;border-top:1px solid #e5e7eb;padding-top:8px;">
    Printed on {{ now()->format('d M Y, h:i A') }} &nbsp;·&nbsp; मेन्याङ्बो कल्याणकारी संघ परिवार वृक्ष प्रणाली
  </div>
</div>


{{-- ═══════════════════════════════════════════════
     SCREEN VIEW
══════════════════════════════════════════════════ --}}
<div id="screen-view" class="max-w-4xl mx-auto py-6 space-y-5 no-print">

  {{-- ── Header bar ── --}}
  <div class="flex items-start justify-between gap-3 flex-wrap">
    <div class="flex items-start gap-3">
      <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm shrink-0"
           style="background:{{ $tc['border'] }}">
        <i class="fas {{ $tc['icon'] }}"></i>
      </div>
      <div>
        <div class="flex items-center gap-2 flex-wrap">
          <h1 class="text-xl font-bold text-gray-900">Request #{{ str_pad($r->id,5,'0',STR_PAD_LEFT) }}</h1>
          <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusBg }} uppercase">{{ $r->status }}</span>
          <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                style="background:{{ $tc['bg'] }};color:{{ $tc['border'] }};border-color:{{ $tc['border'] }}40;">
            {{ $tc['label'] }} — {{ $tc['en'] }}
          </span>
        </div>
        <p class="text-xs text-gray-400 mt-1">
          Submitted {{ $r->created_at->format('d M Y, h:i A') }}
          &nbsp;·&nbsp; {{ $r->created_at->diffForHumans() }}
        </p>
      </div>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
      <button onclick="window.print()"
        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-semibold hover:bg-gray-900 transition shadow-sm">
        <i class="fa-solid fa-print"></i> Print / Download
      </button>
      <a href="{{ route('admin.requests.index') }}"
        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">
        ← Back
      </a>
    </div>
  </div>

  {{-- ── Info cards ── --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

    {{-- Related person --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
      <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">
        <i class="fas fa-user text-blue-400 mr-1"></i>
        @if($r->type==='add_child' || $r->type==='not_listed') Parent @elseif($r->type==='mark_deceased') Deceased @else Related Person @endif
      </div>
      @if($r->person)
        <a href="{{ route('member.page',['person'=>$r->person->id]) }}" target="_blank"
           class="font-bold text-blue-700 hover:underline text-sm">{{ $r->person->display_name }}</a>
        <div class="flex flex-wrap gap-2 mt-1">
          @if($r->person->pusta)<span class="text-xs px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full font-medium">पुस्ता {{ $r->person->pusta }}</span>@endif
          @if($r->person->gender)<span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">{{ $r->person->gender }}</span>@endif
          @if($r->person->member_no)<span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full font-mono">{{ $r->person->member_no }}</span>@endif
        </div>
      @else
        <span class="text-gray-400 text-sm italic">— new person —</span>
      @endif
    </div>

    {{-- Submitter --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
      <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">
        <i class="fas fa-paper-plane text-indigo-400 mr-1"></i> Submitted By
      </div>
      <div class="font-bold text-gray-800 text-sm">{{ $r->submitted_name ?? $r->user?->name ?? '—' }}</div>
      @if($r->submitted_mobile)
        <div class="text-xs text-gray-500 mt-0.5"><i class="fas fa-phone text-gray-300 mr-1"></i>{{ $r->submitted_mobile }}</div>
      @endif
      @if($e = ($r->submitted_email ?? $r->user?->email))
        <div class="text-xs text-gray-500 mt-0.5"><i class="fas fa-envelope text-gray-300 mr-1"></i>{{ $e }}</div>
      @endif
    </div>

    {{-- Review status --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
      <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">
        <i class="fas fa-stamp text-purple-400 mr-1"></i> Review
      </div>
      @if($r->status !== 'pending')
        <div class="font-bold text-gray-800 text-sm">{{ $r->reviewer?->name ?? '—' }}</div>
        @if($r->reviewed_at)
          <div class="text-xs text-gray-400 mt-0.5">{{ $r->reviewed_at->format('d M Y, h:i A') }}</div>
        @endif
        @if($r->review_note)
          <div class="text-xs text-gray-600 mt-1 italic bg-gray-50 rounded p-1.5">{{ $r->review_note }}</div>
        @endif
      @else
        <div class="text-sm text-amber-600 font-semibold">⏳ Pending review</div>
      @endif
    </div>
  </div>

  @if($r->submitted_note)
  <div class="bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 text-sm text-blue-800">
    <span class="font-semibold">Note from submitter:</span> {{ $r->submitted_note }}
  </div>
  @endif

  {{-- ── Type-specific content ── --}}

  @if($r->type === 'add_union')
  {{-- ── MARRIAGE ── --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    {{-- Spouse 1 (current person) --}}
    <div class="bg-white rounded-2xl border-2 shadow-sm overflow-hidden" style="border-color:#fda4af;">
      <div class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-white flex items-center gap-2" style="background:#be123c;">
        <i class="fas fa-user"></i> Spouse 1
        <span class="ml-auto font-normal normal-case">Current Person</span>
      </div>
      <div class="p-4 space-y-2 text-sm">
        <div class="font-bold text-gray-900">{{ $r->person?->display_name ?? '—' }}</div>
        @if($r->person?->display_name_np)<div class="text-gray-500 text-xs">{{ $r->person->display_name_np }}</div>@endif
        <div class="flex flex-wrap gap-2 mt-1">
          @if($r->person?->pusta)<span class="px-2 py-0.5 bg-rose-50 text-rose-700 rounded-full text-xs font-medium">पुस्ता {{ $r->person->pusta }}</span>@endif
          @if($r->person?->gender)<span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs">{{ $r->person->gender }}</span>@endif
        </div>
      </div>
    </div>

    {{-- Spouse 2 --}}
    <div class="bg-white rounded-2xl border-2 shadow-sm overflow-hidden" style="border-color:#fda4af;">
      <div class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-white flex items-center gap-2" style="background:#9f1239;">
        <i class="fas fa-user"></i> Spouse 2
        <span class="ml-auto font-normal normal-case">{{ $spousePerson ? 'Existing Person' : 'New Person' }}</span>
      </div>
      <div class="p-4 space-y-2 text-sm">
        @if($spousePerson)
          <div class="font-bold text-gray-900">{{ $spousePerson->display_name }}</div>
          @if($spousePerson->display_name_np)<div class="text-gray-500 text-xs">{{ $spousePerson->display_name_np }}</div>@endif
          <div class="flex flex-wrap gap-2 mt-1">
            @if($spousePerson->pusta)<span class="px-2 py-0.5 bg-rose-50 text-rose-700 rounded-full text-xs font-medium">पुस्ता {{ $spousePerson->pusta }}</span>@endif
            @if($spousePerson->gender)<span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs">{{ $spousePerson->gender }}</span>@endif
            @if($spousePerson->member_no)<span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-xs font-mono">{{ $spousePerson->member_no }}</span>@endif
          </div>
        @else
          @php $hasSpouseData = collect(['spouse_name','spouse_name_np','spouse_name_limbu'])->some(fn($k)=>!empty($payload[$k])); @endphp
          @if($hasSpouseData)
            <div class="font-bold text-gray-900">{{ $payload['spouse_name'] ?? '—' }}</div>
            @if(!empty($payload['spouse_name_np']))<div class="text-gray-500 text-xs">{{ $payload['spouse_name_np'] }}</div>@endif
            @if(!empty($payload['spouse_name_limbu']))<div class="text-gray-400 text-xs">{{ $payload['spouse_name_limbu'] }}</div>@endif
          @else
            <div class="text-gray-400 italic text-xs">No existing person — will be created on approval</div>
          @endif
          <div class="flex flex-wrap gap-2 mt-1">
            @if(!empty($payload['spouse_pusta']))<span class="px-2 py-0.5 bg-rose-50 text-rose-700 rounded-full text-xs font-medium">पुस्ता {{ $payload['spouse_pusta'] }}</span>@endif
            @if(!empty($payload['spouse_gender']))<span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs">{{ $payload['spouse_gender'] }}</span>@endif
          </div>
          {{-- new-spouse photo and details from the marriage form --}}
          @if(!empty($payload['spouse_photo_path']))
            <img src="{{ asset($payload['spouse_photo_path']) }}" alt="" class="mt-3 h-20 w-20 rounded-full object-cover border border-rose-100">
          @endif
          @php
            $spouseExtra = collect(['spouse_birth_date'=>'जन्म मिति (A.D.)','spouse_birth_date_bs'=>'जन्म मिति (B.S.)','spouse_birth_place'=>'माइती / जन्मस्थान',
                'spouse_father_name'=>'बुबा','spouse_mother_name'=>'आमा','spouse_mobile'=>'मोबाइल','spouse_address'=>'ठेगाना',
                'spouse_education'=>'शिक्षा','spouse_occupation'=>'पेशा'])->filter(fn($l, $k) => !empty($payload[$k]));
          @endphp
          @if($spouseExtra->isNotEmpty())
            <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-xs">
              @foreach($spouseExtra as $k => $l)
                <div><dt class="text-gray-400 font-semibold">{{ $l }}</dt><dd class="font-semibold text-gray-800 break-words">{{ $payload[$k] }}</dd></div>
              @endforeach
            </dl>
          @endif
        @endif
      </div>
    </div>
  </div>

  {{-- Union details --}}
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wide">
      <i class="fas fa-ring text-rose-400 mr-1.5"></i> Union Details
    </div>
    <div class="p-4 grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
      @foreach(['type'=>'Relationship Type','start_date'=>'Marriage Date','notes'=>'Notes'] as $fk=>$flbl)
        @if(!empty($payload[$fk]))
        <div class="{{ $fk==='notes' ? 'col-span-2 sm:col-span-3' : '' }}">
          <div class="text-xs text-gray-400 font-semibold mb-0.5">{{ $flbl }}</div>
          <div class="font-semibold text-gray-800">{{ $payload[$fk] }}</div>
        </div>
        @endif
      @endforeach
    </div>
  </div>


  @elseif($r->type === 'mark_deceased')
  {{-- ── DEATH NOTICE ── --}}
  <div class="bg-white rounded-2xl border-2 border-slate-300 shadow-sm overflow-hidden">
    <div class="px-5 py-4 flex items-center gap-3" style="background:#1e293b;">
      <div class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center">
        <i class="fas fa-cross text-white text-sm"></i>
      </div>
      <div>
        <div class="text-white font-bold text-sm">मृत्यु सूचना — Death Notice</div>
        <div class="text-slate-400 text-xs">{{ $r->person?->display_name }}</div>
      </div>
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
      @foreach([
        'death_date'   => ['Death Date (A.D.)',  'fa-calendar-xmark', 'text-red-400'],
        'death_date_bs'=> ['Death Date (B.S.)',  'fa-calendar-days',  'text-red-300'],
        'death_place'  => ['Death Place',        'fa-location-pin',   'text-slate-400'],
        'death_tithi'  => ['Death Tithi',        'fa-moon',           'text-slate-400'],
        'death_reason' => ['Cause of Death',     'fa-file-medical',   'text-slate-400'],
      ] as $fk => [$flbl, $ficon, $fcolor])
        <div class="{{ $fk==='death_reason' ? 'sm:col-span-2' : '' }} flex items-start gap-2">
          <div class="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center mt-0.5 shrink-0">
            <i class="fas {{ $ficon }} text-xs {{ $fcolor }}"></i>
          </div>
          <div>
            <div class="text-xs text-gray-400 font-semibold">{{ $flbl }}</div>
            <div class="font-semibold text-gray-800 {{ empty($payload[$fk]) ? 'italic text-gray-300' : '' }}">
              {{ $payload[$fk] ?? '—' }}
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>


  @elseif($r->type === 'update_profile')
  {{-- ── PROFILE EDIT ── --}}
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
      <i class="fas fa-code-compare text-amber-400"></i>
      <span class="font-semibold text-gray-800 text-sm">Profile Changes</span>
      <span class="text-xs text-gray-400 ml-1">— current vs. requested</span>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr class="text-xs text-gray-500 uppercase tracking-wide">
            <th class="px-5 py-3 text-left w-1/4">Field</th>
            <th class="px-5 py-3 text-left w-5/12">Current</th>
            <th class="px-5 py-3 text-left w-5/12">Requested</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          @forelse($payload as $fk => $fval)
            @if($fval !== null && $fval !== '')
            @php
              $cur     = $currentValues[$fk] ?? null;
              $changed = !empty($currentValues) && (string)$cur !== (string)$fval;
              $dv      = is_array($fval) ? json_encode($fval, JSON_UNESCAPED_UNICODE) : (string)$fval;
              $dc      = is_array($cur)  ? json_encode($cur,  JSON_UNESCAPED_UNICODE) : ((string)($cur ?? ''));
            @endphp
            <tr class="{{ $changed ? 'bg-amber-50/50' : 'hover:bg-gray-50/60' }}">
              <td class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $lbl($fk) }}</td>
              <td class="px-5 py-3 text-gray-500">{{ $dc ?: '—' }}</td>
              <td class="px-5 py-3 font-semibold {{ $changed ? 'text-amber-700' : 'text-gray-800' }}">
                {{ $dv }}
                @if($changed)<span class="ml-1 text-[10px] px-1.5 py-0.5 bg-amber-100 text-amber-600 rounded font-bold">changed</span>@endif
              </td>
            </tr>
            @endif
          @empty
            <tr><td colspan="3" class="px-5 py-6 text-center text-gray-400">No payload data.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>


  {{-- ── LINK PARENT ── --}}
  @elseif($r->type === 'link_parent')
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    @foreach([['सन्तान (यो व्यक्ति)', $r->person], ['अभिभावक (जोड्न माग गरिएको)', $linkParent]] as [$cardTitle, $cardPerson])
    <div class="bg-white rounded-2xl border border-teal-100 shadow-sm p-5">
      <div class="text-xs font-bold text-teal-600 uppercase tracking-wide mb-2">{{ $cardTitle }}</div>
      @if($cardPerson)
        <a href="{{ route('member.page', $cardPerson->id) }}" target="_blank" class="font-bold text-gray-900 hover:text-teal-700">{{ $cardPerson->display_name }}</a>
        <div class="flex flex-wrap gap-2 mt-1">
          @if($cardPerson->pusta)<span class="px-2 py-0.5 bg-teal-50 text-teal-700 rounded-full text-xs font-medium">पुस्ता {{ $cardPerson->pusta }}</span>@endif
          @if($cardPerson->gender)<span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs">{{ $cardPerson->gender }}</span>@endif
          @if($cardPerson->member_no)<span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-xs font-mono">{{ $cardPerson->member_no }}</span>@endif
        </div>
      @else
        <div class="text-rose-600 text-sm">{{ $payload['parent_name'] ?? '—' }} (मेटिएको)</div>
      @endif
    </div>
    @endforeach
  </div>
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 text-sm">
    <span class="text-gray-400 font-semibold text-xs">सम्बन्ध:</span> <span class="font-semibold">{{ ucfirst($payload['relation_type'] ?? 'birth') }}</span>
    @if(!empty($payload['note']))<div class="mt-2"><span class="text-gray-400 font-semibold text-xs">नोट:</span> {{ $payload['note'] }}</div>@endif
  </div>

  @else
  {{-- ── ADD CHILD / NOT LISTED ── --}}
  @foreach($groups as $groupName => $groupKeys)
    @php $rows = collect($groupKeys)->filter(fn($k) => isset($payload[$k]) && $payload[$k] !== null && $payload[$k] !== '')->values(); @endphp
    @if($rows->count())
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="px-5 py-3 border-b border-gray-100">
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">{{ $groupName }}</span>
      </div>
      <div class="p-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($rows as $fk)
          @php $wideFields = ['special_note','bio','address']; @endphp
          <div class="{{ in_array($fk,$wideFields) ? 'col-span-2 sm:col-span-3 lg:col-span-4' : '' }}">
            <div class="text-xs text-gray-400 font-semibold mb-0.5">{{ $lbl($fk) }}</div>
            <div class="font-semibold text-gray-800 text-sm break-words">
              @if($fk === 'photo_path')
                <img src="{{ asset($payload[$fk]) }}" class="h-16 w-16 rounded-lg object-cover border border-gray-200" alt="photo">
              @else
                {{ is_array($payload[$fk]) ? json_encode($payload[$fk], JSON_UNESCAPED_UNICODE) : $payload[$fk] }}
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </div>
    @endif
  @endforeach
  @endif

  {{-- ── Actions ── --}}
  @if($r->status === 'pending')
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
    <div class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-4">Actions</div>
    <div class="flex flex-wrap gap-3">
      <form method="POST" action="{{ route('admin.requests.approve', $r->id) }}"
            onsubmit="return confirm('Approve this request? The person record will be updated immediately.')">
        @csrf
        <button type="submit"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white rounded-xl font-semibold text-sm hover:bg-green-700 transition shadow-sm">
          <i class="fas fa-check"></i> Approve &amp; Apply
        </button>
      </form>
      <form method="POST" action="{{ route('admin.requests.reject', $r->id) }}"
            class="flex items-center gap-2"
            onsubmit="return confirm('Reject this request?')">
        @csrf
        <input type="text" name="review_note" placeholder="Reason for rejection (optional)"
          class="border border-gray-200 rounded-xl px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-red-200 w-56">
        <button type="submit"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 text-white rounded-xl font-semibold text-sm hover:bg-red-700 transition shadow-sm">
          <i class="fas fa-times"></i> Reject
        </button>
      </form>
    </div>
  </div>
  @else
  <form method="POST" action="{{ route('admin.requests.destroy', $r->id) }}"
        onsubmit="return confirm('Permanently delete this request?')">
    @csrf @method('DELETE')
    <button type="submit"
      class="inline-flex items-center gap-2 px-4 py-2 border border-red-200 text-red-600 rounded-xl text-sm font-semibold hover:bg-red-50 transition">
      <i class="fas fa-trash"></i> Delete Request
    </button>
  </form>
  @endif

</div>
@endsection
