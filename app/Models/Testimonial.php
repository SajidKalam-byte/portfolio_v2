<?php

namespace App\Models;

use App\Support\ContentCache;
use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A testimonial from a real client, manager, or colleague.
 *
 * Only testimonials with recorded consent and status "published" are ever
 * shown publicly. They are never emitted as schema.org reviews: self-hosted
 * reviews of yourself aren't eligible for rich results and would be misleading.
 */
#[Fillable([
    'project_id', 'author_name', 'author_title', 'author_company', 'relationship', 'show_name',
    'quote', 'avatar_path', 'source_url', 'status', 'is_featured', 'sort_order', 'request_note',
])]
class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory;

    public const STATUSES = [
        'requested' => 'Link sent',
        'received' => 'Needs review',
        'published' => 'Published',
        'archived' => 'Archived',
    ];

    public const RELATIONSHIPS = [
        'client' => 'Client',
        'manager' => 'Manager',
        'colleague' => 'Colleague',
        'collaborator' => 'Collaborator',
        'other' => 'Worked together',
    ];

    public const REQUEST_LIFETIME_DAYS = 30;

    protected function casts(): array
    {
        return [
            'show_name' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'consent_at' => 'datetime',
            'request_expires_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => ContentCache::flush());
        static::deleted(fn () => ContentCache::flush());
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Visible on the site: published, with consent and a quote.
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')
            ->whereNotNull('consent_at')
            ->whereNotNull('quote')
            ->whereNotNull('author_name');
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('submitted_at')->orderByDesc('id');
    }

    /**
     * Create a pending request and return it with the one-time plain token.
     *
     * @return array{0: self, 1: string}
     */
    public static function createRequest(?int $projectId, ?string $name, ?string $note, int $days = self::REQUEST_LIFETIME_DAYS): array
    {
        $token = Str::random(48);

        $testimonial = new self([
            'project_id' => $projectId,
            'author_name' => $name,
            'request_note' => $note,
            'relationship' => 'client',
        ]);
        $testimonial->forceFill([
            'status' => 'requested',
            'request_token_hash' => self::hashToken($token),
            'request_expires_at' => now()->addDays($days),
        ])->save();

        return [$testimonial, $token];
    }

    /**
     * Issue a fresh link for an existing open request (the old link stops working).
     */
    public function regenerateRequestToken(int $days = self::REQUEST_LIFETIME_DAYS): string
    {
        $token = Str::random(48);

        $this->forceFill([
            'request_token_hash' => self::hashToken($token),
            'request_expires_at' => now()->addDays($days),
        ])->save();

        return $token;
    }

    public static function findOpenRequest(string $token): ?self
    {
        if (strlen($token) < 40 || strlen($token) > 64) {
            return null;
        }

        $testimonial = self::where('request_token_hash', self::hashToken($token))->first();

        return $testimonial?->isRequestOpen() ? $testimonial : null;
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public function isRequestOpen(): bool
    {
        return $this->status === 'requested'
            && $this->request_token_hash !== null
            && $this->request_expires_at?->isFuture();
    }

    public function hasConsent(): bool
    {
        return $this->consent_at !== null;
    }

    public function isPublic(): bool
    {
        return $this->status === 'published' && $this->hasConsent() && filled($this->quote) && filled($this->author_name);
    }

    public function statusLabel(): string
    {
        if ($this->status === 'requested' && ! $this->isRequestOpen()) {
            return 'Link expired';
        }

        return self::STATUSES[$this->status] ?? Str::headline($this->status);
    }

    public function relationshipLabel(): ?string
    {
        return $this->relationship ? (self::RELATIONSHIPS[$this->relationship] ?? Str::headline($this->relationship)) : null;
    }

    /**
     * Name as shown publicly. Falls back to role/company when the person prefers not to be named.
     */
    public function displayName(): string
    {
        if ($this->show_name && filled($this->author_name)) {
            return $this->author_name;
        }

        return $this->author_title ?: ($this->relationshipLabel() ?? 'Client');
    }

    /**
     * "Title, Company" (or just company when the name is hidden and the title is already the display name).
     */
    public function byline(): ?string
    {
        $parts = $this->show_name
            ? [$this->author_title, $this->author_company]
            : [$this->author_company];

        $line = collect($parts)->filter()->implode(', ');

        return $line !== '' ? $line : null;
    }

    public function initials(): string
    {
        $source = $this->show_name && filled($this->author_name) ? $this->author_name : ($this->author_company ?: 'Client');

        return Str::of($source)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }

    public function avatarUrl(): ?string
    {
        return $this->show_name && $this->avatar_path ? asset($this->avatar_path) : null;
    }

    /**
     * @return list<string>
     */
    public function paragraphs(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\n\s*\n/', (string) $this->quote) ?: [])));
    }

    public function excerpt(int $limit = 160): string
    {
        return Str::limit(preg_replace('/\s+/', ' ', (string) $this->quote), $limit);
    }
}
