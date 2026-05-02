<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\ParentChildEdge;
use App\Models\PersonChangeRequest;
use App\Models\UnionModel;
use App\Support\MemberNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserPersonChangeRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ── Admin: direct photo upload ────────────────────────────────────────────

    public function uploadPhoto(Request $request, Person $person)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Admin only.');
        }

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:200'],
        ]);

        $file     = $request->file('photo');
        $filename = 'person_' . $person->id . '_' . time() . '.' . $file->getClientOriginalExtension();

        // Remove old photo file
        if ($person->photo_path && file_exists(public_path($person->photo_path))) {
            @unlink(public_path($person->photo_path));
        }

        $file->move(public_path('photos'), $filename);
        $person->update(['photo_path' => 'photos/' . $filename]);

        return back()->with('success_message', 'फोटो सफलतापूर्वक अपलोड गरियो।');
    }

    // ── Mark Deceased ─────────────────────────────────────────────────────────

    public function markDeceased(Request $request, $person)
    {
        $person = Person::query()->findOrFail($person);
        if ($person->is_deceased) {
            return back()->with('success_message', $person->display_name . ' पहिले नै मृतकको रूपमा दर्ता छ।');
        }

        $validated = $request->validate([
            'death_date_ad' => ['nullable', 'date'],
            'death_date_bs' => ['nullable', 'string', 'max:20'],
            'death_place'   => ['nullable', 'string', 'max:255'],
            'death_tithi'   => ['nullable', 'string', 'max:50'],
            'death_reason'  => ['nullable', 'string', 'max:1000'],
            'submitted_name'=> ['nullable', 'string', 'max:255'],
        ]);

        if (empty($validated['death_date_ad']) && empty($validated['death_date_bs'])) {
            return back()
                ->withErrors(['death_date_ad' => 'मृत्यु मिति (A.D. वा B.S.) अनिवार्य छ।'])
                ->withInput()->with('open_modal', 'death');
        }

        // ── Admin: apply directly ──────────────────────────────────────────────
        if (Auth::user()->isAdmin()) {
            $person->update([
                'is_deceased'  => true,
                'death_date'   => $validated['death_date_ad'] ?? null,
                'death_place'  => $validated['death_place']   ?? null,
                'death_tithi'  => $validated['death_tithi']   ?? null,
                'death_reason' => $validated['death_reason']  ?? null,
            ]);
            return redirect()->route('member.page', $person->id)
                ->with('success_message', $person->display_name . ' मृतकको रूपमा दर्ता गरियो।');
        }

        // ── Member: create change request ──────────────────────────────────────
        $submittedName = $this->submittedName($validated);

        $req = PersonChangeRequest::create([
            'user_id'   => Auth::id(),
            'person_id' => $person->id,
            'type'      => 'mark_deceased',
            'payload'   => [
                'death_date'    => $validated['death_date_ad'] ?? null,
                'death_date_bs' => $validated['death_date_bs'] ?? null,
                'death_place'   => $validated['death_place']   ?? null,
                'death_tithi'   => $validated['death_tithi']   ?? null,
                'death_reason'  => $validated['death_reason']  ?? null,
            ],
            'submitted_name'  => $submittedName,
            'submitted_email' => Auth::user()->email,
        ]);

        $this->sendNotificationEmail($req, $person);

        return redirect()->route('member.page', $person->id)
            ->with('success_message', $person->display_name . ' को मृत्यु सुतक जानकारी जारी गरियो।');
    }

    // ── Add Child ─────────────────────────────────────────────────────────────

    public function addChild(Request $request, $person)
    {
        $parent = Person::query()->findOrFail($person);

        $validated = $request->validate([
            'display_name'    => ['required', 'string', 'max:255'],
            'display_name_np' => ['nullable', 'string', 'max:255'],
            'display_name_limbu' => ['nullable', 'string', 'max:255'],
            'gender'          => ['required', 'in:male,female,unknown,other'],
            'birth_date'      => ['nullable', 'date'],
            'birth_date_bs'   => ['nullable', 'string', 'max:20'],
            'birth_place'     => ['nullable', 'string', 'max:255'],
            'mobile'          => ['nullable', 'string', 'max:50'],
            'email'           => ['nullable', 'email', 'max:255'],
            'education'       => ['nullable', 'string', 'max:255'],
            'occupation'      => ['nullable', 'string', 'max:255'],
            'lineage'         => ['nullable', 'string', 'max:255'],
            'family_type'     => ['nullable', 'string', 'max:255'],
            'blood_group'     => ['nullable', 'string', 'max:50'],
            'rashifal'        => ['nullable', 'string', 'max:50'],
            'religion'        => ['nullable', 'string', 'max:100'],
            'special_note'    => ['nullable', 'string', 'max:2000'],
            'bio'             => ['nullable', 'string', 'max:5000'],
            'photo'           => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:200'],
            'submitted_name'  => ['nullable', 'string', 'max:255'],
        ]);
        $validated = array_merge($validated, $this->namePartsFromDisplayName($validated['display_name']));
        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $this->storeRequestPhoto($request->file('photo'), 'child');
        }

        // ── Admin: create person + edge directly ───────────────────────────────
        if (Auth::user()->isAdmin()) {
            $childData = collect($validated)->except(['submitted_name', 'photo'])->filter()->toArray();
            if (empty($childData['pusta'])) {
                $parentPusta = $this->pustaToInt($parent->pusta);
                if ($parentPusta) $childData['pusta'] = (string)($parentPusta + 1);
            }
            $childData['member_no'] = null;
            $child = Person::create($childData);
            MemberNumber::assignTo($child);
            ParentChildEdge::create([
                'parent_id' => $parent->id,
                'child_id'  => $child->id,
            ]);
            return redirect()->route('member.page', $parent->id)
                ->with('success_message', $child->display_name . ' सन्तानको रूपमा सिधै थपियो।');
        }

        // ── Member: change request ──────────────────────────────────────────────
        $submittedName = $this->submittedName($validated);

        $payload = collect($validated)->except(['submitted_name', 'photo'])->toArray();
        if (empty($payload['pusta'])) {
            $parentPusta = $this->pustaToInt($parent->pusta);
            if ($parentPusta) $payload['pusta'] = (string)($parentPusta + 1);
        }

        $req = PersonChangeRequest::create([
            'user_id'         => Auth::id(),
            'person_id'       => $parent->id,
            'type'            => 'add_child',
            'payload'         => $payload,
            'submitted_name'  => $submittedName,
            'submitted_email' => Auth::user()->email,
        ]);

        $this->sendNotificationEmail($req, $parent);

        return redirect()->route('member.page', ['person' => $parent->id])
            ->with('success_message', 'नयाँ सन्तान जानकारी ' . $parent->display_name . ' को लागि पठाइयो।');
    }

    // ── Profile Update ────────────────────────────────────────────────────────

    public function requestProfileUpdate(Request $request, $person)
    {
        $person = Person::query()->findOrFail($person);

        $validated = $request->validate([
            'display_name'    => ['nullable', 'string', 'max:255'],
            'display_name_np' => ['nullable', 'string', 'max:255'],
            'display_name_limbu' => ['nullable', 'string', 'max:255'],
            'birth_date'      => ['nullable', 'date'],
            'birth_date_bs'   => ['nullable', 'string', 'max:20'],
            'birth_place'     => ['nullable', 'string', 'max:255'],
            'address'         => ['nullable', 'string', 'max:255'],
            'mobile'          => ['nullable', 'string', 'max:50'],
            'email'           => ['nullable', 'email', 'max:255'],
            'education'       => ['nullable', 'string', 'max:255'],
            'occupation'      => ['nullable', 'string', 'max:255'],
            'blood_group'     => ['nullable', 'string', 'max:50'],
            'rashifal'        => ['nullable', 'string', 'max:50'],
            'religion'        => ['nullable', 'string', 'max:100'],
            'special_note'    => ['nullable', 'string', 'max:2000'],
            'bio'             => ['nullable', 'string', 'max:5000'],
            'photo'           => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:200'],
            'submitted_name'  => ['nullable', 'string', 'max:255'],
            'submitted_note'  => ['nullable', 'string', 'max:1000'],
        ]);
        if (!empty($validated['display_name'])) {
            $validated = array_merge($validated, $this->namePartsFromDisplayName($validated['display_name']));
        }
        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $this->storeRequestPhoto($request->file('photo'), 'profile');
        }

        $payload = collect($validated)
            ->except(['submitted_name', 'submitted_note', 'photo'])
            ->filter(fn($v) => !is_null($v) && $v !== '')
            ->toArray();

        // ── Admin: direct update ───────────────────────────────────────────────
        if (Auth::user()->isAdmin()) {
            $person->update($payload);
            return redirect()->route('member.page', $person->id)
                ->with('success_message', $person->display_name . ' को प्रोफाइल सिधै अपडेट गरियो।');
        }

        // ── Member: change request ─────────────────────────────────────────────
        $submittedName = $this->submittedName($validated);

        $req = PersonChangeRequest::create([
            'user_id'         => Auth::id(),
            'person_id'       => $person->id,
            'type'            => 'update_profile',
            'payload'         => $payload,
            'submitted_name'  => $submittedName,
            'submitted_note'  => $validated['submitted_note'] ?? null,
            'submitted_email' => Auth::user()->email,
        ]);

        $this->sendNotificationEmail($req, $person);

        return redirect()->route('member.page', ['person' => $person->id])
            ->with('success_message', $person->display_name . ' को प्रोफाइल सम्पादन अनुरोध पठाइयो।');
    }

    // ── Request Marriage ──────────────────────────────────────────────────────

    public function requestMarriage(Request $request, $person)
    {
        $person = Person::query()->findOrFail($person);

        $validated = $request->validate([
            'spouse_person_id'   => ['nullable', 'integer', 'exists:persons,id'],
            'spouse_name'        => ['nullable', 'string', 'max:255'],
            'spouse_name_np'     => ['nullable', 'string', 'max:255'],
            'spouse_name_limbu'  => ['nullable', 'string', 'max:255'],
            'spouse_gender'      => ['nullable', 'in:male,female,other,unknown'],
            'type'               => ['nullable', 'in:married,divorced,widowed,partner'],
            'start_date'         => ['nullable', 'date'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'submitted_name'     => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($validated['spouse_person_id']) && empty($validated['spouse_name'])) {
            return back()->withErrors(['spouse_name' => 'जीवनसाथीको नाम वा ID अनिवार्य छ।'])
                ->withInput()->with('open_modal', 'marriage');
        }

        // ── Admin: create spouse person first when only a name is supplied,
        // then create the union directly.
        if (Auth::user()->isAdmin()) {
            $incomingSpouseId = $validated['spouse_person_id'] ?? null;
            if ($incomingSpouseId && (int) $incomingSpouseId === (int) $person->id) {
                return back()->withErrors(['spouse_person_id' => 'आफैंलाई जीवनसाथी बनाउन मिल्दैन।'])
                    ->withInput()->with('open_modal', 'marriage');
            }

            DB::transaction(function () use ($validated, $person) {
                $spouseId = $validated['spouse_person_id'] ?? null;

                if (!$spouseId) {
                    $spouse = $this->createMinimalSpouse(
                        $validated['spouse_name'],
                        $person,
                        $validated['spouse_gender'] ?? null,
                        $person->pusta,
                        $validated['spouse_name_np']    ?? null,
                        $validated['spouse_name_limbu'] ?? null
                    );
                    $spouseId = $spouse->id;
                }

                $exists = UnionModel::where(function ($q) use ($person, $spouseId) {
                    $q->where('spouse1_id', $person->id)->where('spouse2_id', $spouseId);
                })->orWhere(function ($q) use ($person, $spouseId) {
                    $q->where('spouse1_id', $spouseId)->where('spouse2_id', $person->id);
                })->exists();

                if (!$exists) {
                    UnionModel::create([
                        'spouse1_id' => $person->id,
                        'spouse2_id' => $spouseId,
                        'type'       => $validated['type']       ?? 'married',
                        'start_date' => $validated['start_date'] ?? null,
                        'notes'      => $validated['notes']      ?? null,
                    ]);
                }
            });

            return redirect()->route('member.page', $person->id)
                ->with('success_message', $person->display_name . ' को विवाह जानकारी सिधै थपियो।');
        }

        // ── Member: change request ─────────────────────────────────────────────
        $submittedName = $this->submittedName($validated);

        $req = PersonChangeRequest::create([
            'user_id'         => Auth::id(),
            'person_id'       => $person->id,
            'type'            => 'add_union',
            'payload'         => [
                'spouse_person_id'  => $validated['spouse_person_id']  ?? null,
                'spouse_name'       => $validated['spouse_name']       ?? null,
                'spouse_name_np'    => $validated['spouse_name_np']    ?? null,
                'spouse_name_limbu' => $validated['spouse_name_limbu'] ?? null,
                'spouse_gender'     => $validated['spouse_gender']     ?? null,
                'spouse_pusta'      => $person->pusta,
                'type'              => $validated['type']              ?? 'married',
                'start_date'        => $validated['start_date']        ?? null,
                'notes'             => $validated['notes']             ?? null,
            ],
            'submitted_name'  => $submittedName,
            'submitted_email' => Auth::user()->email,
        ]);

        $this->sendNotificationEmail($req, $person);

        return redirect()->route('member.page', ['person' => $person->id])
            ->with('success_message', $person->display_name . ' को विवाह जानकारी अनुरोध पठाइयो।');
    }

    // ── Not Listed ────────────────────────────────────────────────────────────

    public function notListed(Request $request)
    {
        $validated = $request->validate([
            'display_name'    => ['required', 'string', 'max:255'],
            'display_name_np' => ['nullable', 'string', 'max:255'],
            'display_name_limbu' => ['nullable', 'string', 'max:255'],
            'gender'          => ['required', 'in:male,female,unknown'],
            'birth_date'      => ['nullable', 'date'],
            'birth_date_bs'   => ['nullable', 'string', 'max:20'],
            'father_name'     => ['nullable', 'string', 'max:255'],
            'mother_name'     => ['nullable', 'string', 'max:255'],
            'address'         => ['nullable', 'string', 'max:255'],
            'mobile'          => ['nullable', 'string', 'max:50'],
            'special_note'    => ['nullable', 'string', 'max:2000'],
            'submitted_name'  => ['required', 'string', 'max:255'],
        ]);

        $payload = collect($validated)->except(['submitted_name'])->toArray();

        $req = PersonChangeRequest::create([
            'user_id'         => Auth::id(),
            'person_id'       => null,
            'type'            => 'not_listed',
            'payload'         => $payload,
            'submitted_name'  => $validated['submitted_name'],
            'submitted_email' => Auth::user()->email,
        ]);

        $this->sendNotificationEmail($req, null);

        return back()->with('success_message', 'तपाईंको अनुरोध पठाइयो। Admin ले समीक्षा गर्नेछन्।');
    }

    // ── My Requests ───────────────────────────────────────────────────────────

    public function myRequests()
    {
        $requests = PersonChangeRequest::with('person:id,display_name')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('requests.my-requests', compact('requests'));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function sendNotificationEmail(PersonChangeRequest $req, ?Person $person): void
    {
        $raw    = config('services.notification_email', 'menyangbo15@gmail.com');
        $emails = array_values(array_filter(
            array_map('trim', explode(',', (string) $raw)),
            fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL)
        ));

        if (!$emails) {
            Log::warning('Person change request notification skipped: NOTIFICATION_EMAIL is empty or invalid.', [
                'request_id' => $req->id,
                'raw' => $raw,
            ]);
            return;
        }

        $typeLabels = [
            'mark_deceased'  => 'मृत्यु सूचना',
            'add_child'      => 'नयाँ सन्तान',
            'update_profile' => 'प्रोफाइल सम्पादन',
            'add_union'      => 'विवाह जानकारी',
            'not_listed'     => 'सूचीमा नभएको',
        ];
        $typeLabel  = $typeLabels[$req->type] ?? $req->type;
        $subject    = '[Menyanbo] नयाँ अनुरोध: ' . $typeLabel . ' — #' . str_pad($req->id, 5, '0', STR_PAD_LEFT);
        $personLine = $person ? "{$person->display_name} (ID: {$person->id}, पुस्ता: {$person->pusta})" : '— (नयाँ व्यक्ति)';
        $adminUrl   = url('/admin/change-requests/' . $req->id);

        $payloadRows = '';
        foreach ((array) $req->payload as $k => $v) {
            if (!empty($v)) {
                $payloadRows .= '<tr><td style="padding:4px 10px;color:#555;font-weight:600;width:160px;background:#f8f9fa;">'
                    . ucfirst(str_replace('_', ' ', $k))
                    . '</td><td style="padding:4px 10px;">'
                    . (is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : e($v))
                    . '</td></tr>';
            }
        }

        $html = <<<HTML
        <!DOCTYPE html><html><body style="font-family:Arial,sans-serif;background:#f4f6f9;margin:0;padding:20px;">
        <div style="max-width:580px;margin:0 auto;background:white;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
          <div style="background:#1a3a6b;color:white;padding:20px 24px;">
            <div style="font-size:18px;font-weight:bold;">मेन्याङ्बो कल्याणकारी संघ</div>
            <div style="font-size:13px;opacity:0.8;margin-top:2px;">नयाँ परिवर्तन अनुरोध प्राप्त भयो</div>
          </div>
          <div style="padding:20px 24px;">
            <table style="width:100%;font-size:13px;border-collapse:collapse;margin-bottom:16px;">
              <tr><td style="padding:5px 0;color:#555;font-weight:600;">Request Type</td><td style="font-weight:bold;">{$typeLabel}</td></tr>
              <tr><td style="padding:5px 0;color:#555;font-weight:600;">Related Person</td><td>{$personLine}</td></tr>
              <tr><td style="padding:5px 0;color:#555;font-weight:600;">Submitted By</td><td>{$req->submitted_name}</td></tr>
              <tr><td style="padding:5px 0;color:#555;font-weight:600;">Email</td><td>{$req->submitted_email}</td></tr>
            </table>
            <table style="width:100%;font-size:12.5px;border-collapse:collapse;border:1px solid #e0e0e0;border-radius:6px;overflow:hidden;">{$payloadRows}</table>
            <div style="margin-top:20px;">
              <a href="{$adminUrl}" style="display:inline-block;background:#1a3a6b;color:white;padding:10px 22px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:13px;">Admin Panel मा हेर्नुहोस् →</a>
            </div>
          </div>
        </div></body></html>
        HTML;

        try {
            Mail::html($html, function ($m) use ($emails, $subject, $req) {
                $m->to($emails)->subject($subject);

                if ($req->submitted_email && filter_var($req->submitted_email, FILTER_VALIDATE_EMAIL)) {
                    $m->replyTo($req->submitted_email, $req->submitted_name ?: null);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Person change request notification email failed.', [
                'request_id' => $req->id,
                'emails' => $emails,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function createMinimalSpouse(string $name, Person $person, ?string $gender = null, ?string $pusta = null, ?string $nameNp = null, ?string $nameLimbu = null): Person
    {
        $name = trim($name) ?: 'Unnamed';
        $givenName = preg_split('/\s+/', $name)[0] ?: $name;

        $data = [
            'display_name' => $name,
            'given_name'   => $givenName,
            'gender'       => $gender ?: $this->inferSpouseGender($person),
            'is_deceased'  => false,
            'pusta'        => $pusta ?? $person->pusta,
        ];
        if ($nameNp)    $data['display_name_np']    = $nameNp;
        if ($nameLimbu) $data['display_name_limbu'] = $nameLimbu;

        $spouse = Person::create($data);
        MemberNumber::assignTo($spouse);

        return $spouse;
    }

    private function inferSpouseGender(Person $person): string
    {
        return match ($person->gender) {
            'male'   => 'female',
            'female' => 'male',
            default  => 'unknown',
        };
    }

    private function namePartsFromDisplayName(string $name): array
    {
        $name = trim($name);
        $parts = preg_split('/\s+/', $name) ?: [];
        $given = $parts[0] ?? $name;
        $family = count($parts) > 1 ? array_pop($parts) : null;
        $middleParts = array_slice($parts, 1);

        return [
            'given_name' => $given,
            'middle_name' => $middleParts ? implode(' ', $middleParts) : null,
            'family_name' => $family,
        ];
    }

    private function storeRequestPhoto($file, string $prefix): string
    {
        $dir = public_path('photos/requests');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = $prefix . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $filename);

        return 'photos/requests/' . $filename;
    }

    private function submittedName(array $validated): string
    {
        return trim((string) ($validated['submitted_name'] ?? ''))
            ?: (Auth::user()->name ?: Auth::user()->email);
    }

    private function pustaToInt(?string $pusta): ?int
    {
        if ($pusta === null || $pusta === '') return null;
        $english = strtr($pusta, ['०'=>'0','१'=>'1','२'=>'2','३'=>'3','४'=>'4','५'=>'5','६'=>'6','७'=>'7','८'=>'8','९'=>'9']);
        $english = trim($english);
        return is_numeric($english) ? (int)$english : null;
    }
}
