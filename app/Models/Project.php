<?php

namespace App\Models;

use App\Support\ContentCache;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'title', 'slug', 'short_description', 'description', 'long_description',
    'category', 'project_type', 'role', 'client', 'period', 'status', 'is_confidential',
    'image_path', 'image_alt', 'og_image_path',
    'tech_stack', 'highlights', 'responsibilities', 'architecture', 'metrics',
    'project_url', 'project_url_label', 'github_url', 'case_study_content',
    'seo_title', 'seo_description',
    'sort_order', 'is_featured', 'is_published', 'published_at',
])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tech_stack' => 'array',
            'highlights' => 'array',
            'responsibilities' => 'array',
            'architecture' => 'array',
            'metrics' => 'array',
            'is_confidential' => 'boolean',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Project $project) {
            if (blank($project->slug)) {
                $project->slug = Str::slug($project->title);
            }
        });

        static::saved(fn () => ContentCache::flush());
        static::deleted(fn () => ContentCache::flush());
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Published and not scheduled for the future.
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    public function isPublic(): bool
    {
        return $this->is_published && ($this->published_at === null || $this->published_at->isPast());
    }

    public function categoryLabel(): string
    {
        return config("portfolio.project_categories.{$this->category}", Str::headline($this->category));
    }

    public function typeLabel(): string
    {
        return config("portfolio.project_types.{$this->project_type}", Str::headline($this->project_type));
    }

    public function url(): string
    {
        return route('projects.show', ['project' => $this->slug]);
    }

    public function coverUrl(): ?string
    {
        return $this->image_path ? asset($this->image_path) : null;
    }

    public function coverAlt(): string
    {
        return $this->image_alt ?: "Illustration for {$this->title}";
    }

    public function ogImageUrl(): string
    {
        return asset($this->og_image_path ?: config('portfolio.seo.default_image'));
    }

    /** @return list<string> */
    public function techList(): array
    {
        return array_values(array_filter($this->tech_stack ?? [], 'filled'));
    }

    /** @return list<array{layer?: string, title: string, description?: string, items?: list<string>}> */
    public function architectureLayers(): array
    {
        return $this->architecture['layers'] ?? [];
    }

    public function hasExternalLinks(): bool
    {
        return ! $this->is_confidential && ($this->project_url || $this->github_url);
    }
}
