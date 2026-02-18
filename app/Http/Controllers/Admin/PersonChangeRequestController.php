<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\ParentChildEdge;
use Illuminate\Http\Request;

class PersonChangeRequestController extends Controller
{
    public function index()
    {
        $rows = \DB::table('person_change_requests')->orderByDesc('id')->paginate(20);
        return view('admin.person_requests.index', compact('rows'));
    }

    public function approve($id)
    {
        $req = \DB::table('person_change_requests')->where('id',$id)->first();
        if(!$req || $req->status !== 'pending') return back()->with('error','Invalid request.');

        $payload = json_decode($req->payload, true) ?: [];
        $person = $req->person_id ? Person::find($req->person_id) : null;

        \DB::transaction(function() use ($req, $payload, $person) {

            if ($req->type === 'mark_deceased') {
                // expects: death_date, death_place, death_reason, death_tithi
                if($person){
                    $person->update([
                        'is_deceased' => true,
                        'death_date' => $payload['death_date'] ?? $person->death_date,
                        'death_place' => $payload['death_place'] ?? $person->death_place,
                        'death_reason' => $payload['death_reason'] ?? $person->death_reason,
                        'death_tithi' => $payload['death_tithi'] ?? $person->death_tithi,
                    ]);
                }
            }

            if ($req->type === 'update_profile') {
                if($person){
                    $allowed = [
                        'member_no','member_type','membership','display_name_np','bio','photo_path',
                        'birth_place','father_name','mother_name','address','mobile','email',
                        'education','occupation','marital_status','marriage_date_bs','marriage_date_ad',
                        'lineage','family_type','blood_group','rashifal','religion','special_note',
                    ];
                    $update = array_intersect_key($payload, array_flip($allowed));
                    $person->update($update);
                }
            }

            if ($req->type === 'add_child') {
                // expects: child => {display_name, gender, pusta, birth_date, ...}, relation_type
                if(!$person) return;

                $childData = $payload['child'] ?? [];
                $child = Person::create([
                    'display_name' => $childData['display_name'] ?? 'Unnamed',
                    'gender' => $childData['gender'] ?? 'unknown',
                    'pusta' => $childData['pusta'] ?? null,
                    'birth_date' => $childData['birth_date'] ?? null,
                    'bio' => $childData['bio'] ?? null,
                    'photo_path' => $childData['photo_path'] ?? null,
                    // optional extra
                    'member_no' => $childData['member_no'] ?? null,
                    'display_name_np' => $childData['display_name_np'] ?? null,
                ]);

                ParentChildEdge::create([
                    'parent_id' => $person->id,
                    'child_id' => $child->id,
                    'relation_type' => $payload['relation_type'] ?? 'birth',
                    'notes' => $payload['notes'] ?? null,
                ]);
            }

            \DB::table('person_change_requests')->where('id',$req->id)->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('success','Approved.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['review_note'=>['nullable','string','max:5000']]);
        \DB::table('person_change_requests')->where('id',$id)->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_note' => $request->review_note,
            'updated_at' => now(),
        ]);
        return back()->with('success','Rejected.');
    }
}
