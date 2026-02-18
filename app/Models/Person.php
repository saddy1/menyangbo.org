<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int         $id
 * @property string|null $given_name
 * @property string|null $middle_name
 * @property string|null $family_name
 * @property string      $display_name
 * @property string      $gender      // male | female | other | unknown
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property \Illuminate\Support\Carbon|null $death_date
 * @property bool        $is_deceased
 * @property string|null $photo_path
 * @property string|null $pusta
 * @property string|null $bio
 *
 * Accessors:
 *  - $person->label        (fallback name if display_name missing)
 *  - $person->life_span    (YYYY-MM-DD – YYYY-MM-DD or —)
 *  - $person->is_alive     (inverse of is_deceased)
 *  - $person->spouses      (Collection<Person>)
 *  - $person->all_unions   (Collection<UnionModel>)
 */
class Person extends Model
{
    use SoftDeletes;

    protected $table = 'persons';

    protected $fillable = [
        'given_name',
        'middle_name',
        'family_name',
        'display_name',
        'gender',
        'birth_date',
        'death_date',
        'is_deceased',
        'pusta',
        'photo_path',
        'bio',
        'member_no',
        'member_type',
        'membership',
        'display_name_np',
        'birth_place',
        'father_name',
        'mother_name',
        'address',
        'mobile',
        'email',
        'education',
        'occupation',
        'marital_status',
        'marriage_date_bs',
        'marriage_date_ad',
        'lineage',
        'family_type',
        'blood_group',
        'rashifal',
        'religion',
        'special_note',
        'death_place',
        'death_tithi',
        'death_reason',
        'registered_by',
    ];

    protected $casts = [
        'birth_date'  => 'date',
        'death_date'  => 'date',
        'marriage_date_ad' => 'date',

        'is_deceased' => 'boolean',
    ];

    // Sensible defaults
    protected $attributes = [
        'gender'      => 'unknown',
        'is_deceased' => false,
    ];

    /* -----------------------------
     | Relationships
     * ----------------------------- */

    // Parents of this person
    public function parentEdges()
    {
        return $this->hasMany(ParentChildEdge::class, 'child_id');
    }

    public function parents()
    {
        return $this->belongsToMany(Person::class, 'parent_child_edges', 'child_id', 'parent_id');
    }

    // Children of this person
    public function childEdges()
    {
        return $this->hasMany(ParentChildEdge::class, 'parent_id');
    }

    public function children()
    {
        return $this->belongsToMany(Person::class, 'parent_child_edges', 'parent_id', 'child_id');
    }

    // Unions where this person is spouse1 or spouse2
    public function unionsAsSpouse1()
    {
        return $this->hasMany(UnionModel::class, 'spouse1_id');
    }

    public function unionsAsSpouse2()
    {
        return $this->hasMany(UnionModel::class, 'spouse2_id');
    }

    // Convenience accessor: all unions merged (not an Eloquent Relation)
    public function getAllUnionsAttribute()
    {
        return $this->unionsAsSpouse1->merge($this->unionsAsSpouse2)->values();
    }

    // Convenience accessor: all spouses (Collection<Person>)
    public function getSpousesAttribute()
    {
        $spouses = collect();
        foreach ($this->unionsAsSpouse1 as $u) $spouses->push($u->spouse2);
        foreach ($this->unionsAsSpouse2 as $u) $spouses->push($u->spouse1);
        return $spouses->filter()->unique('id')->values();
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /* -----------------------------
     | Scopes
     * ----------------------------- */

    public function scopeAlive($q)
    {
        return $q->where('is_deceased', false);
    }

    public function scopeDeceased($q)
    {
        return $q->where('is_deceased', true);
    }

    public function scopeSearch($q, ?string $term)
    {
        if (!trim((string) $term)) return $q;
        return $q->where(function ($qq) use ($term) {
            $qq->where('display_name', 'like', "%{$term}%")
                ->orWhere('given_name',  'like', "%{$term}%")
                ->orWhere('family_name', 'like', "%{$term}%");
        });
    }

    public function scopeTiny($q)
    {
        // minimal columns for dropdowns/selects
        return $q->select('id', 'display_name', 'birth_date', 'is_deceased', 'gender');
    }

    /* -----------------------------
     | Accessors
     * ----------------------------- */

    // Fallback label if display_name is empty
    public function getLabelAttribute(): string
    {
        $parts = array_filter([$this->given_name, $this->middle_name, $this->family_name]);
        return $this->display_name ?: (count($parts) ? implode(' ', $parts) : "Person #{$this->id}");
    }

    public function getLifeSpanAttribute(): string
    {
        $b = $this->birth_date ? $this->birth_date->format('Y-m-d') : '—';
        $d = $this->is_deceased ? ($this->death_date?->format('Y-m-d') ?? '—') : '—';
        return "{$b}–{$d}";
    }

    public function getIsAliveAttribute(): bool
    {
        return !$this->is_deceased;
    }

    /* -----------------------------
     | Model events / safety rails
     * ----------------------------- */

    protected static function booted(): void
    {
        // Keep is_deceased & dates consistent
        static::saving(function (Person $p) {
            // If death_date is set, always mark deceased
            if (!is_null($p->death_date)) {
                $p->is_deceased = true;
            }

            // If toggled back to alive, drop death_date (so UI stays consistent)
            if ($p->is_deceased === false) {
                $p->death_date = null;
            }

            // Basic date sanity: birth <= death (when both present)
            if ($p->birth_date && $p->death_date && $p->death_date->lt($p->birth_date)) {
                throw new \InvalidArgumentException('Death date cannot be before birth date.');
            }

            // Ensure display_name is never empty
            if (!trim((string) $p->display_name)) {
                $parts = array_filter([$p->given_name, $p->middle_name, $p->family_name]);
                $p->display_name = count($parts) ? implode(' ', $parts) : ($p->display_name ?: 'Unnamed');
            }
        });

        // Soft-delete cleanup: remove edges/unions/events to avoid ghost links
        static::deleting(function (Person $p) {
            if ($p->isForceDeleting()) {
                // hard delete
                $p->childEdges()->delete();
                $p->parentEdges()->delete();
                $p->unionsAsSpouse1()->delete();
                $p->unionsAsSpouse2()->delete();
                $p->events()->delete();
            } else {
                // soft delete person, but hard-delete edges/unions so the graph stays valid
                $p->childEdges()->delete();
                $p->parentEdges()->delete();
                $p->unionsAsSpouse1()->delete();
                $p->unionsAsSpouse2()->delete();
                $p->events()->delete();
            }
        });
    }
}
