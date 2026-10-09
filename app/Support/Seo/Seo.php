<?php

namespace App\Support\Seo;

use Illuminate\Support\Str;

/**
 * Per-page metadata rendered by <x-seo>.
 */
class Seo
{
    public string $type = 'website';

    public ?string $canonical = null;

    public ?string $image = null;

    public ?string $imageAlt = null;

    public ?string $robots = null;

    /** @var list<array<string, mixed>> */
    public array $schema = [];

    /** @var array<string, string> Extra og:* / article:* properties. */
    public array $properties = [];

    public function __construct(
        public string $title,
        public string $description,
    ) {}

    public static function make(string $title, string $description): self
    {
        return new self($title, $description);
    }

    public function canonical(?string $url): self
    {
        $this->canonical = $url;

        return $this;
    }

    public function image(?string $url, ?string $alt = null): self
    {
        $this->image = $url;
        $this->imageAlt = $alt;

        return $this;
    }

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function noIndex(): self
    {
        $this->robots = 'noindex, follow';

        return $this;
    }

    /**
     * @param  array<string, mixed>  ...$nodes  schema.org nodes (without @context).
     */
    public function schema(array ...$nodes): self
    {
        array_push($this->schema, ...$nodes);

        return $this;
    }

    public function property(string $property, ?string $content): self
    {
        if (filled($content)) {
            $this->properties[$property] = $content;
        }

        return $this;
    }

    public function fullTitle(): string
    {
        $site = config('portfolio.identity.name');

        return Str::contains($this->title, $site) ? $this->title : "{$this->title} — {$site}";
    }

    public function resolvedCanonical(): string
    {
        return $this->canonical ?? url()->current();
    }

    public function resolvedImage(): string
    {
        return $this->image ?? asset(config('portfolio.seo.default_image'));
    }

    public function resolvedImageAlt(): string
    {
        return $this->imageAlt ?? config('portfolio.identity.name').' — '.config('portfolio.identity.tagline');
    }

    public function resolvedRobots(): string
    {
        if (config('portfolio.seo.block_non_production') && ! app()->isProduction()) {
            return 'noindex, nofollow';
        }

        return $this->robots ?? 'index, follow, max-image-preview:large';
    }
}
