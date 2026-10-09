<?php

namespace App\Models;

use App\Support\ContentCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company', 'company_url', 'role', 'employment_type', 'location',
    'start_date', 'end_date', 'is_current', 'description',
    'highlights', 'tech_stack', 'related_projects', 'sort_order',
])]
class Experience extends Model
{
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
            'highlights' => 'array',
            'tech_stack' => 'array',
            'related_projects' => 'array',
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

    public function periodLabel(): string
    {
        $end = $this->is_current || ! $this->end_date ? 'Present' : $this->end_date->format('M Y');

        return $this->start_date->format('M Y').' — '.$end;
    }

    /**
     * Machine-readable range for <time> elements.
     *
     * @return array{start: string, end: ?string}
     */
    public function isoRange(): array
    {
        return [
            'start' => $this->start_date->format('Y-m'),
            'end' => $this->is_current ? null : $this->end_date?->format('Y-m'),
        ];
    }

    /**
     * Highlights grouped as [{title, items}]. Plain string entries are gathered into one untitled group.
     *
     * @return list<array{title: ?string, items: list<string>}>
     */
    public function highlightGroups(): array
    {
        $groups = [];
        $loose = [];

        foreach ($this->highlights ?? [] as $entry) {
            if (is_string($entry)) {
                $loose[] = $entry;
            } elseif (is_array($entry) && ! empty($entry['items'])) {
                $groups[] = ['title' => $entry['title'] ?? null, 'items' => array_values($entry['items'])];
            }
        }

        if ($loose) {
            array_unshift($groups, ['title' => null, 'items' => $loose]);
        }

        return $groups;
    }
}
