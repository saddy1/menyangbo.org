<?php

namespace App\Support;

use App\Models\Person;
use Illuminate\Http\UploadedFile;

/**
 * Extra details for a new spouse entered on the marriage (विवाह) form:
 * photo, birth, माइती (father / mother), contact, education and occupation.
 * Form fields are prefixed "spouse_"; they map onto the spouse's own person columns.
 */
class SpouseDetails
{
    /** form field => persons column */
    public const FIELDS = [
        'spouse_birth_date'    => 'birth_date',
        'spouse_birth_date_bs' => 'birth_date_bs',
        'spouse_birth_place'   => 'birth_place',
        'spouse_father_name'   => 'father_name',
        'spouse_mother_name'   => 'mother_name',
        'spouse_mobile'        => 'mobile',
        'spouse_address'       => 'address',
        'spouse_education'     => 'education',
        'spouse_occupation'    => 'occupation',
    ];

    public static function rules(): array
    {
        return [
            'spouse_photo'         => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:500'],
            'spouse_birth_date'    => ['nullable', 'date'],
            'spouse_birth_date_bs' => ['nullable', 'string', 'max:20'],
            'spouse_birth_place'   => ['nullable', 'string', 'max:255'],
            'spouse_father_name'   => ['nullable', 'string', 'max:255'],
            'spouse_mother_name'   => ['nullable', 'string', 'max:255'],
            'spouse_mobile'        => ['nullable', 'string', 'max:50'],
            'spouse_address'       => ['nullable', 'string', 'max:255'],
            'spouse_education'     => ['nullable', 'string', 'max:255'],
            'spouse_occupation'    => ['nullable', 'string', 'max:255'],
        ];
    }

    /** The filled-in spouse_* details from validated input (plus spouse_photo_path if present). */
    public static function fromInput(array $validated): array
    {
        return collect($validated)
            ->only([...array_keys(self::FIELDS), 'spouse_photo_path'])
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->all();
    }

    /** Copy the details onto the (newly created) spouse. */
    public static function apply(Person $spouse, array $details): void
    {
        $data = [];
        foreach (self::FIELDS as $field => $column) {
            if (!empty($details[$field])) $data[$column] = $details[$field];
        }
        if (!empty($details['spouse_photo_path'])) $data['photo_path'] = $details['spouse_photo_path'];

        if ($data) $spouse->update($data);
    }

    /** Save an uploaded photo under public/<dir> and return its relative path. */
    public static function storePhoto(UploadedFile $file, string $dir = 'photos'): string
    {
        $path = public_path($dir);
        if (!is_dir($path)) mkdir($path, 0755, true);

        $filename = 'spouse_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($path, $filename);

        return $dir . '/' . $filename;
    }
}
