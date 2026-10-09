<?php

namespace App\Models;

use App\Support\ContentCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Table('educations')]
#[Fillable([
    'institution', 'degree', 'field_of_study', 'location',
    'start_date', 'end_date', 'status', 'grade', 'description', 'sort_order',
])]
class Education extends Model
{
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => ContentCache::flush());
        static::deleted(fn () => ContentCache::flush());
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderByDesc('start_date');
    }

    /**
     * Years only: education dates are rarely known to the month.
     */
    public function periodLabel(): ?string
    {
        if (! $this->start_date && ! $this->end_date) {
            return null;
        }

        $start = $this->start_date?->format('Y');
        $end = $this->end_date?->format('Y') ?? ($this->status ?: null);

        return collect([$start, $end])->filter()->implode(' — ');
    }

    /**
     * Only completed programmes count for schema.org `alumniOf`.
     */
    public function isCompleted(): bool
    {
        return $this->end_date !== null && $this->end_date->isPast()
            && ! in_array(strtolower((string) $this->status), ['pursuing', 'in progress'], true);
    }
}
