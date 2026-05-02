<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Committee extends Model
{
    protected $fillable = ['name', 'type', 'term_label', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public static array $types = [
        'central'  => 'केन्द्रीय समिति',
        'district' => 'जिल्ला समिति',
        'local'    => 'स्थानीय समिति',
    ];

    public static function availableTypes(): array
    {
        $existing = static::query()
            ->whereNotNull('type')
            ->where('type', '<>', '')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->mapWithKeys(fn ($type) => [$type => static::$types[$type] ?? $type])
            ->all();

        return array_merge(static::$types, $existing);
    }

    public function members(): HasMany
    {
        return $this->hasMany(CommitteeMember::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePast($query)
    {
        return $query->where('is_active', false);
    }

    public function getTypeLabelAttribute(): string
    {
        return static::$types[$this->type] ?? $this->type;
    }
}
