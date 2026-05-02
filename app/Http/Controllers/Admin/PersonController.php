<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\UnionModel;
use App\Support\MemberNumber;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PersonController extends Controller
{
    public function index(Request $request)
    {
        $q            = trim($request->query('q', ''));
        $alive        = $request->query('alive');
        $gender       = $request->query('gender');
        $pusta_filter = trim($request->query('pusta', ''));

        $people = Person::query()
            ->withCount('childEdges as children_count')
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($inner) use ($q) {
                    $inner->where('display_name',     'like', '%'.$q.'%')
                          ->orWhere('given_name',     'like', '%'.$q.'%')
                          ->orWhere('middle_name',    'like', '%'.$q.'%')
                          ->orWhere('family_name',    'like', '%'.$q.'%')
                          ->orWhere('display_name_np','like', '%'.$q.'%')
                          ->orWhere('display_name_limbu','like', '%'.$q.'%')
                          ->orWhere('member_no',      'like', '%'.$q.'%');
                    $np = self::transliterateToNepali($q);
                    if ($np && $np !== strtolower($q)) {
                        $inner->orWhere('display_name_np', 'like', '%'.$np.'%');
                    }
                });
            })
            ->when($gender, fn($qq) => $qq->where('gender', $gender))
            ->when($pusta_filter, function ($qq) use ($pusta_filter) {
                $en = self::toEnglishDigits($pusta_filter);
                $np = self::toNepaliDigits($pusta_filter);
                $qq->where(function ($q) use ($pusta_filter, $en, $np) {
                    $q->where('pusta', 'like', '%'.$pusta_filter.'%');
                    if ($en !== $pusta_filter) {
                        $q->orWhere('pusta', 'like', '%'.$en.'%');
                    }
                    if ($np !== $pusta_filter) {
                        $q->orWhere('pusta', 'like', '%'.$np.'%');
                    }
                });
            })
            ->when($alive !== null && $alive !== '', fn($qq) =>
                $qq->where('is_deceased', $alive === '1' ? 0 : 1)
            )
            ->orderBy('id', 'desc')
            ->paginate(12)
            ->withQueryString();

        $total    = Person::count();
        $alive_ct = Person::where('is_deceased', false)->count();
        $dead_ct  = Person::where('is_deceased', true)->count();

        return view('admin.persons.index', compact(
            'people', 'q', 'alive', 'gender', 'pusta_filter', 'total', 'alive_ct', 'dead_ct'
        ));
    }

    // AJAX JSON search — used by index live-search & spouse picker
    public function searchJson(Request $request)
    {
        $q            = trim($request->query('q', ''));
        $alive        = $request->query('alive');
        $gender       = $request->query('gender');
        $pusta_filter = trim($request->query('pusta', ''));

        if ($request->boolean('recent')) {
            return response()->json(
                Person::query()
                    ->withCount('childEdges as children_count')
                    ->select('id','display_name','display_name_np','display_name_limbu','gender','birth_date','pusta','member_no','is_deceased','created_at')
                    ->latest('id')
                    ->limit(5)
                    ->get()
                    ->map(fn($p) => $this->personSearchRow($p))
            );
        }

        // At least one filter must be present
        if (!$q && !$gender && !$pusta_filter && ($alive === null || $alive === '')) {
            return response()->json([]);
        }

        $np = $q ? self::transliterateToNepali($q) : '';

        $rows = Person::query()
            ->when($q, function ($query) use ($q, $np) {
                $query->where(function ($inner) use ($q, $np) {
                    $inner->where('display_name',     'like', '%'.$q.'%');
                    if (ctype_digit($q)) {
                        $inner->orWhere('id', (int) $q);
                    }

                    $inner->orWhere('given_name',     'like', '%'.$q.'%')
                          ->orWhere('middle_name',    'like', '%'.$q.'%')
                          ->orWhere('family_name',    'like', '%'.$q.'%')
                          ->orWhere('display_name_np','like', '%'.$q.'%')
                          ->orWhere('display_name_limbu','like', '%'.$q.'%')
                          ->orWhere('member_no',      'like', '%'.$q.'%');
                    if ($np && $np !== strtolower($q)) {
                        $inner->orWhere('display_name_np', 'like', '%'.$np.'%');
                    }
                });
            })
            ->when($gender, fn($qq) => $qq->where('gender', $gender))
            ->when($pusta_filter, function ($qq) use ($pusta_filter) {
                $en = self::toEnglishDigits($pusta_filter);
                $np = self::toNepaliDigits($pusta_filter);
                $qq->where(function ($q) use ($pusta_filter, $en, $np) {
                    $q->where('pusta', 'like', '%'.$pusta_filter.'%');
                    if ($en !== $pusta_filter) {
                        $q->orWhere('pusta', 'like', '%'.$en.'%');
                    }
                    if ($np !== $pusta_filter) {
                        $q->orWhere('pusta', 'like', '%'.$np.'%');
                    }
                });
            })
            ->when($alive !== null && $alive !== '', fn($qq) =>
                $qq->where('is_deceased', $alive === '1' ? 0 : 1)
            )
            ->withCount('childEdges as children_count')
            ->select('id','display_name','display_name_np','display_name_limbu','gender','birth_date','pusta','member_no','is_deceased')
            ->orderBy('display_name')
            ->limit(50)
            ->get()
            ->map(fn($p) => $this->personSearchRow($p));

        return response()->json($rows);
    }

    private function personSearchRow(Person $p): array
    {
        return [
            'id'             => $p->id,
            'display_name'   => $p->display_name,
            'display_name_np'=> $p->display_name_np,
            'display_name_limbu'=> $p->display_name_limbu,
            'gender'         => $p->gender,
            'birth_year'     => $p->birth_date?->format('Y'),
            'pusta'          => $p->pusta,
            'member_no'      => $p->member_no,
            'is_deceased'    => (bool) $p->is_deceased,
            'has_children'   => ($p->children_count ?? 0) > 0,
        ];
    }

    public function create()
    {
        $nextMemberNo = MemberNumber::next();

        return view('admin.persons.create', compact('nextMemberNo'));
    }

    public function store(Request $request)
    {
        $this->mergeNamePartsFromDisplayName($request);
        $data = $this->validatePerson($request);
        $data['is_deceased'] = $request->boolean('is_deceased');
        $data['member_no'] = null;
        if ($request->boolean('add_union')) {
            $this->validateUnionRequest($request);
        }

        if (!$data['is_deceased']) {
            $data['death_date']   = null;
            $data['death_place']  = null;
            $data['death_tithi']  = null;
            $data['death_reason'] = null;
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $data['photo_path'] = self::storePhoto($request->file('photo'));
        }

        $person = Person::create($data);
        MemberNumber::assignTo($person);

        if ($request->boolean('add_union')) {
            $this->createUnionFromRequest($request, $person);
        }

        return redirect()->route('admin.persons.index')->with('success', 'व्यक्ति थपियो।');
    }

    public function edit(Person $person)
    {
        return view('admin.persons.edit', compact('person'));
    }

    public function update(Request $request, Person $person)
    {
        $this->mergeNamePartsFromDisplayName($request);
        $data = $this->validatePerson($request);
        $data['is_deceased'] = $request->boolean('is_deceased');

        if (!$data['is_deceased']) {
            $data['death_date']   = null;
            $data['death_place']  = null;
            $data['death_tithi']  = null;
            $data['death_reason'] = null;
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            if ($person->photo_path && file_exists(public_path($person->photo_path))) {
                @unlink(public_path($person->photo_path));
            }
            $data['photo_path'] = self::storePhoto($request->file('photo'));
        }

        $person->update($data);

        if ($request->boolean('add_union')) {
            $this->createUnionFromRequest($request, $person);
        }

        return redirect()->route('admin.persons.index')->with('success', 'अद्यावधिक गरियो।');
    }

    public function destroy(Person $person)
    {
        if ($person->childEdges()->exists()) {
            return back()->with('error',
                "'{$person->display_name}' लाई मेटाउन सकिँदैन — यो व्यक्तिसँग बच्चाहरू छन्। पहिले बच्चाहरूको सम्बन्ध हटाउनुहोस्।"
            );
        }

        $person->delete();
        return back()->with('success', "'{$person->display_name}' मेटाइयो।");
    }

    public function generateMemberNumbers()
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, 'Super admin only.');
        }

        $count = MemberNumber::assignMissing();

        return back()->with('success', "{$count} सदस्य नम्बर generate गरियो।");
    }

    private function validatePerson(Request $request): array
    {
        return $request->validate([
            'display_name'     => ['required','string','max:255'],
            'given_name'       => ['required','string','max:255'],
            'middle_name'      => ['nullable','string','max:255'],
            'family_name'      => ['nullable','string','max:255'],
            'gender'           => ['required', Rule::in(['male','female','other','unknown'])],

            'birth_date'       => ['nullable','date'],
            'birth_date_bs'    => ['nullable','string','max:20'],
            'death_date'       => ['nullable','date','after_or_equal:birth_date'],
            'is_deceased'      => ['nullable','boolean'],

            'pusta'            => ['nullable','string','max:255'],
            'bio'              => ['nullable','string'],
            'photo_path'       => ['nullable','string','max:255'],

            'member_no'        => ['nullable','string','max:50'],
            'display_name_np'  => ['nullable','string','max:255'],
            'display_name_limbu' => ['nullable','string','max:255'],
            'member_type'      => ['nullable','string','max:100'],
            'membership'       => ['nullable','string','max:100'],

            'birth_place'      => ['nullable','string','max:255'],
            'address'          => ['nullable','string','max:255'],
            'mobile'           => ['nullable','string','max:50'],
            'email'            => ['nullable','email','max:255'],

            'education'        => ['nullable','string','max:255'],
            'occupation'       => ['nullable','string','max:255'],

            'lineage'          => ['nullable','string','max:255'],
            'family_type'      => ['nullable','string','max:255'],
            'blood_group'      => ['nullable','string','max:20'],
            'rashifal'         => ['nullable','string','max:50'],
            'religion'         => ['nullable','string','max:100'],
            'special_note'     => ['nullable','string'],

            'death_place'      => ['nullable','string','max:255'],
            'death_tithi'      => ['nullable','string','max:50'],
            'death_reason'     => ['nullable','string','max:255'],

            'registered_by'    => ['nullable','string','max:100'],
            'photo'            => ['nullable','image','mimes:jpeg,jpg,png,webp','max:200'],
        ]);
    }

    private static function storePhoto(\Illuminate\Http\UploadedFile $file): string
    {
        $filename = 'person_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('photos'), $filename);
        return 'photos/' . $filename;
    }

    private function createUnionFromRequest(Request $request, Person $person): void
    {
        $uv = $this->validateUnionRequest($request);

        if (!empty($uv['union_spouse_id'])) {
            $spouseId = (int) $uv['union_spouse_id'];
        } else {
            $spouse = $this->createMinimalSpouse(
                $uv['union_spouse_name'],
                $person,
                $uv['union_spouse_gender'] ?? null,
                $person->pusta,
                $uv['union_spouse_name_np'] ?? null,
                $uv['union_spouse_name_limbu'] ?? null
            );
            $spouseId = $spouse->id;
        }

        if ($spouseId === $person->id) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'union_spouse_id' => 'व्यक्ति आफैंसँग विवाह/Union बनाउन मिल्दैन।',
            ]);
        }

        $already = UnionModel::where(function ($q) use ($person, $spouseId) {
            $q->where('spouse1_id', $person->id)->where('spouse2_id', $spouseId);
        })->orWhere(function ($q) use ($person, $spouseId) {
            $q->where('spouse1_id', $spouseId)->where('spouse2_id', $person->id);
        })->exists();

        if ($already) {
            return;
        }

        UnionModel::create([
            'spouse1_id' => $person->id,
            'spouse2_id' => $spouseId,
            'start_date' => $uv['union_start_date'] ?? null,
            'end_date'   => $uv['union_end_date']   ?? null,
            'type'       => $uv['union_type']       ?? 'married',
            'notes'      => $uv['union_notes']      ?? null,
        ]);
    }

    private function validateUnionRequest(Request $request): array
    {
        $uv = $request->validate([
            'union_spouse_id'         => ['nullable','integer','exists:persons,id'],
            'union_spouse_name'       => ['nullable','string','max:255'],
            'union_spouse_name_np'    => ['nullable','string','max:255'],
            'union_spouse_name_limbu' => ['nullable','string','max:255'],
            'union_spouse_gender'     => ['nullable', Rule::in(['male','female','other','unknown'])],
            'union_start_date'        => ['nullable','date'],
            'union_end_date'          => ['nullable','date','after_or_equal:union_start_date'],
            'union_type'              => ['nullable','string','max:50'],
            'union_notes'             => ['nullable','string','max:500'],
        ]);

        if (empty($uv['union_spouse_id']) && trim((string) ($uv['union_spouse_name'] ?? '')) === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'union_spouse_name' => 'जीवनसाथी छान्नुहोस् वा नयाँ जीवनसाथीको नाम लेख्नुहोस्।',
            ]);
        }

        return $uv;
    }

    private function createMinimalSpouse(string $name, Person $person, ?string $gender = null, ?string $pusta = null, ?string $nameNp = null, ?string $nameLimbu = null): Person
    {
        $name = trim($name) ?: 'Unnamed';
        $parts = preg_split('/\s+/', $name) ?: [];
        $givenName = $parts[0] ?? $name;
        $familyName = count($parts) > 1 ? end($parts) : null;

        $spouse = Person::create([
            'display_name'       => $name,
            'given_name'         => $givenName,
            'family_name'        => $familyName,
            'display_name_np'    => $nameNp ?: null,
            'display_name_limbu' => $nameLimbu ?: null,
            'gender'             => $gender ?: $this->inferSpouseGender($person),
            'is_deceased'        => false,
            'pusta'              => $pusta ?: $person->pusta,
        ]);

        MemberNumber::assignTo($spouse);

        return $spouse;
    }

    private function inferSpouseGender(Person $person): string
    {
        return match ($person->gender) {
            'male' => 'female',
            'female' => 'male',
            default => 'unknown',
        };
    }

    private function mergeNamePartsFromDisplayName(Request $request): void
    {
        $name = trim((string) $request->input('display_name', ''));
        if ($name === '') {
            return;
        }

        $parts = preg_split('/\s+/', $name) ?: [];
        $given = $parts[0] ?? $name;
        $family = count($parts) > 1 ? array_pop($parts) : null;
        $middleParts = array_slice($parts, 1);

        $request->merge([
            'given_name' => $given,
            'middle_name' => $middleParts ? implode(' ', $middleParts) : null,
            'family_name' => $family,
        ]);
    }

    // ── Transliteration helpers ───────────────────────────────────────────────

    /**
     * Convert English phonetic input to approximate Nepali (Devanagari) for LIKE search.
     * Returns empty string if input already contains Devanagari or is not ASCII.
     */
    private static function transliterateToNepali(string $input): string
    {
        $input = trim($input);
        if (!$input) return '';
        if (preg_match('/[\x{0900}-\x{097F}]/u', $input)) return ''; // already Devanagari
        if (!preg_match('/^[a-zA-Z\s]+$/', $input)) return '';        // not pure ASCII

        $map = [
            // 4-char
            'shri' => 'श्री',
            // 3-char digraph+vowel
            'sha' => 'श', 'shi' => 'शि', 'shu' => 'शु', 'she' => 'शे', 'sho' => 'शो',
            'kha' => 'ख', 'khi' => 'खि', 'khu' => 'खु', 'khe' => 'खे', 'kho' => 'खो',
            'gha' => 'घ', 'ghi' => 'घि', 'ghu' => 'घु', 'ghe' => 'घे', 'gho' => 'घो',
            'cha' => 'च', 'chi' => 'चि', 'chu' => 'चु', 'che' => 'चे', 'cho' => 'चो',
            'jha' => 'झ', 'jhi' => 'झि', 'jhu' => 'झु', 'jhe' => 'झे', 'jho' => 'झो',
            'tha' => 'थ', 'thi' => 'थि', 'thu' => 'थु', 'the' => 'थे', 'tho' => 'थो',
            'dha' => 'ध', 'dhi' => 'धि', 'dhu' => 'धु', 'dhe' => 'धे', 'dho' => 'धो',
            'pha' => 'फ', 'phi' => 'फि', 'phu' => 'फु', 'phe' => 'फे', 'pho' => 'फो',
            'bha' => 'भ', 'bhi' => 'भि', 'bhu' => 'भु', 'bhe' => 'भे', 'bho' => 'भो',
            // 2-char digraphs
            'sh'  => 'श', 'kh' => 'ख', 'gh' => 'घ', 'ch' => 'च',
            'jh'  => 'झ', 'th' => 'थ', 'dh' => 'ध', 'ph' => 'फ', 'bh' => 'भ',
            // 2-char vowel runs
            'aa'  => 'आ', 'ii' => 'ई', 'uu' => 'ऊ', 'ee' => 'ई', 'oo' => 'ओ',
            'ai'  => 'ऐ', 'au' => 'औ',
            // consonant+vowel
            'ka'  => 'क', 'ki' => 'कि', 'ku' => 'कु', 'ke' => 'के', 'ko' => 'को',
            'ga'  => 'ग', 'gi' => 'गि', 'gu' => 'गु', 'ge' => 'गे', 'go' => 'गो',
            'ja'  => 'ज', 'ji' => 'जि', 'ju' => 'जु', 'je' => 'जे', 'jo' => 'जो',
            'ta'  => 'त', 'ti' => 'ति', 'tu' => 'तु', 'te' => 'ते', 'to' => 'तो',
            'da'  => 'द', 'di' => 'दि', 'du' => 'दु', 'de' => 'दे', 'do' => 'दो',
            'na'  => 'न', 'ni' => 'नि', 'nu' => 'नु', 'ne' => 'ने', 'no' => 'नो',
            'pa'  => 'प', 'pi' => 'पि', 'pu' => 'पु', 'pe' => 'पे', 'po' => 'पो',
            'ba'  => 'ब', 'bi' => 'बि', 'bu' => 'बु', 'be' => 'बे', 'bo' => 'बो',
            'ma'  => 'म', 'mi' => 'मि', 'mu' => 'मु', 'me' => 'मे', 'mo' => 'मो',
            'ya'  => 'य', 'yi' => 'यि', 'yu' => 'यु', 'ye' => 'ये', 'yo' => 'यो',
            'ra'  => 'र', 'ri' => 'रि', 'ru' => 'रु', 're' => 'रे', 'ro' => 'रो',
            'la'  => 'ल', 'li' => 'लि', 'lu' => 'लु', 'le' => 'ले', 'lo' => 'लो',
            'va'  => 'व', 'vi' => 'वि', 'vu' => 'वु', 've' => 'वे', 'vo' => 'वो',
            'wa'  => 'व', 'wi' => 'वि', 'wu' => 'वु', 'we' => 'वे', 'wo' => 'वो',
            'ha'  => 'ह', 'hi' => 'हि', 'hu' => 'हु', 'he' => 'हे', 'ho' => 'हो',
            'sa'  => 'स', 'si' => 'सि', 'su' => 'सु', 'se' => 'से', 'so' => 'सो',
            'fa'  => 'फ', 'fi' => 'फि', 'fu' => 'फु', 'fe' => 'फे', 'fo' => 'फो',
            // standalone consonants
            'k' => 'क', 'g' => 'ग', 'j' => 'ज', 't' => 'त', 'd' => 'द',
            'n' => 'न', 'p' => 'प', 'b' => 'ब', 'm' => 'म', 'y' => 'य',
            'r' => 'र', 'l' => 'ल', 'v' => 'व', 'w' => 'व', 'h' => 'ह',
            's' => 'स', 'f' => 'फ',
            // standalone vowels
            'a' => 'अ', 'i' => 'इ', 'u' => 'उ', 'e' => 'ए', 'o' => 'ओ',
        ];

        $result = '';
        $lower  = strtolower($input);
        $len    = \strlen($lower);
        $i      = 0;

        while ($i < $len) {
            $found = false;
            for ($l = 4; $l >= 1; $l--) {
                if ($i + $l <= $len) {
                    $chunk = substr($lower, $i, $l);
                    if (isset($map[$chunk])) {
                        $result .= $map[$chunk];
                        $i += $l;
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                $result .= $lower[$i];
                $i++;
            }
        }

        return $result;
    }

    /** Convert English digits to Nepali numerals (e.g. "5" → "५") */
    private static function toNepaliDigits(string $input): string
    {
        return strtr($input, ['0'=>'०','1'=>'१','2'=>'२','3'=>'३','4'=>'४','5'=>'५','6'=>'६','7'=>'७','8'=>'८','9'=>'९']);
    }

    /** Convert Nepali numerals to English digits (e.g. "५" → "5") */
    private static function toEnglishDigits(string $input): string
    {
        return strtr($input, ['०'=>'0','१'=>'1','२'=>'2','३'=>'3','४'=>'4','५'=>'5','६'=>'6','७'=>'7','८'=>'8','९'=>'9']);
    }
}
