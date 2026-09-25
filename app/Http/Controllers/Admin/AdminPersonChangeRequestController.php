<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\PersonChangeRequest;
use App\Models\ParentChildEdge;
use App\Models\UnionModel;
use App\Support\MemberNumber;
use App\Support\SiblingOrder;
use App\Support\SpouseDetails;
use App\Support\Lineage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPersonChangeRequestController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        if (!in_array($perPage, [10,20,50,100])) $perPage = 20;

        $q = PersonChangeRequest::query()
            ->with(['person:id,display_name,pusta,gender', 'reviewer:id,name'])
            ->latest();

        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }

        if ($request->filled('type')) {
            $q->where('type', $request->string('type'));
        }

        $requests = $q->paginate($perPage)->withQueryString();

        return view('admin.requests.index', compact('requests','perPage'));
    }

    public function show(PersonChangeRequest $r)
    {
        $r->load(['person', 'reviewer:id,name', 'user:id,name,email']);

        // Build a flat current-values map from the existing person record
        // so we can show before/after comparison for each payload field
        $currentValues = [];
        if ($r->person) {
            $currentValues = $r->person->only(array_keys((array) $r->payload));
        }

        return view('admin.requests.show', compact('r', 'currentValues'));
    }

    public function approve(PersonChangeRequest $r, Request $request)
    {
        if ($r->status !== 'pending') return back()->with('success', 'Already processed.');

        $payload = (array) $r->payload;
        if (
            $r->type === 'add_union'
            && !empty($payload['spouse_person_id'])
            && (int) $payload['spouse_person_id'] === (int) $r->person_id
        ) {
            return back()->with('error', 'A person cannot be married to themselves.');
        }

        DB::transaction(function () use ($r) {

            $payload = (array)$r->payload;

            if ($r->type === 'mark_deceased') {
                $person = Person::findOrFail($r->person_id);
                $person->update([
                    'death_date'   => $payload['death_date'] ?? null,
                    'death_place'  => $payload['death_place'] ?? null,
                    'death_tithi'  => $payload['death_tithi'] ?? null,
                    'death_reason' => $payload['death_reason'] ?? null,
                    'is_deceased'  => true,
                ]);
            }

            if ($r->type === 'add_child') {
                $parent = Person::findOrFail($r->person_id);

                $childData = $payload;

                // Ensure display_name exists
                if (empty(trim((string)($childData['display_name'] ?? '')))) {
                    throw new \RuntimeException('Child display_name missing.');
                }
                $childData = array_merge($childData, $this->namePartsFromDisplayName($childData['display_name']));

                // auto pusta if missing
                if (empty($childData['pusta'])) {
                    $parentPusta = $this->pustaToInt($parent->pusta);
                    $childData['pusta'] = $parentPusta ? \App\Support\BirthOrder::npDigits($parentPusta + 1) : null;
                }

                $birthOrder = (int) ($childData['birth_order'] ?? 0);
                unset($childData['birth_order']);

                $childData['member_no'] = null;
                $child = Person::create($childData);
                MemberNumber::assignTo($child);

                ParentChildEdge::create([
                    'parent_id'     => $parent->id,
                    'child_id'      => $child->id,
                    'relation_type' => 'birth',
                ]);

                if ($birthOrder) {
                    SiblingOrder::assign($parent, $child, $birthOrder);
                }
            }

            if ($r->type === 'link_parent') {
                $child = Person::findOrFail($r->person_id);
                $parent = Person::findOrFail($payload['parent_id'] ?? 0);
                $problem = Lineage::linkProblem($parent->id, $child->id);
                if ($problem && !str_contains($problem, 'पहिले नै')) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['parent_id' => $problem]);
                }
                if (!$problem) {
                    ParentChildEdge::create([
                        'parent_id'     => $parent->id,
                        'child_id'      => $child->id,
                        'relation_type' => $payload['relation_type'] ?? 'birth',
                    ]);
                    if (empty($child->pusta) && ($p = $this->pustaToInt($parent->pusta))) {
                        $child->update(['pusta' => \App\Support\BirthOrder::npDigits($p + 1)]);
                    }
                }
            }

            if ($r->type === 'not_listed') {
                if (empty(trim((string)($payload['display_name'] ?? '')))) {
                    throw new \RuntimeException('Display name missing.');
                }

                $personData = array_merge($payload, $this->namePartsFromDisplayName($payload['display_name']));
                $personData['member_no'] = null;
                $personData['is_deceased'] = false;

                $person = Person::create($personData);
                MemberNumber::assignTo($person);
            }

            if ($r->type === 'update_profile') {
                $person = Person::findOrFail($r->person_id);
                if (!empty($payload['display_name'])) {
                    $payload = array_merge($payload, $this->namePartsFromDisplayName($payload['display_name']));
                }
                $birthOrder = (int) ($payload['birth_order'] ?? 0);
                unset($payload['birth_order']);
                $person->update($payload);
                if ($birthOrder && ($parent = SiblingOrder::parentFor($person))) {
                    SiblingOrder::assign($parent, $person, $birthOrder);
                }
            }

            if ($r->type === 'add_union') {
                $person = Person::findOrFail($r->person_id);

                $spouseId = $payload['spouse_person_id'] ?? null;

                // If spouse not in system, create minimal person
                if (!$spouseId) {
                    $spouse = $this->createMinimalSpouse(
                        $payload['spouse_name'] ?? 'Unnamed',
                        $person,
                        $payload['spouse_gender']     ?? null,
                        $payload['spouse_pusta']      ?? $person->pusta,
                        $payload['spouse_name_np']    ?? null,
                        $payload['spouse_name_limbu'] ?? null
                    );
                    SpouseDetails::apply($spouse, $payload);
                    $spouseId = $spouse->id;
                }

                if ((int) $spouseId === (int) $person->id) {
                    throw new \RuntimeException('A person cannot be married to themselves.');
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
                        'type'       => $payload['type'] ?? 'married',
                        'start_date' => $payload['start_date'] ?? null,
                        'notes'      => $payload['notes'] ?? null,
                    ]);
                }
            }

            $r->update([
                'status'      => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        });

        return back()->with('success', 'Approved');
    }

    public function reject(PersonChangeRequest $r, Request $request)
    {
        if ($r->status !== 'pending') return back()->with('success', 'Already processed.');

        $request->validate([
            'review_note' => ['nullable','string','max:2000'],
        ]);

        $r->update([
            'status'      => 'rejected',
            'review_note' => $request->review_note,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Rejected');
    }

    public function destroy(PersonChangeRequest $r)
    {
        // allow delete only rejected (or allow approved too if you want)
        if (!in_array($r->status, ['rejected','approved'])) {
            return back()->with('error', 'Only rejected/approved requests can be deleted.');
        }

        $r->delete();
        return redirect()->route('admin.requests.index')->with('success', 'Deleted');
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

    private function pustaToInt(?string $pusta): ?int
    {
        if ($pusta === null || $pusta === '') return null;
        $english = strtr($pusta, ['०'=>'0','१'=>'1','२'=>'2','३'=>'3','४'=>'4','५'=>'5','६'=>'6','७'=>'7','८'=>'8','९'=>'9']);
        $english = trim($english);
        return is_numeric($english) ? (int)$english : null;
    }
}
