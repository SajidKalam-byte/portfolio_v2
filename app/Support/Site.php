<?php

namespace App\Support;

/**
 * Read-only view of the site identity and public contact details.
 * Shared with every Blade view as `$site`.
 */
class Site
{
    public function __construct(
        private Settings $settings,
        public readonly ResumeFile $resume,
    ) {}

    public function name(): string
    {
        return config('portfolio.identity.name');
    }

    public function jobTitle(): string
    {
        return config('portfolio.identity.job_title');
    }

    public function shortTitle(): string
    {
        return config('portfolio.identity.short_title');
    }

    public function tagline(): string
    {
        return config('portfolio.identity.tagline');
    }

    public function description(): string
    {
        return config('portfolio.identity.description');
    }

    public function location(): ?string
    {
        return $this->settings->get('location');
    }

    public function timezoneLabel(): string
    {
        return config('portfolio.identity.timezone_label');
    }

    /** Public email, or null when hidden or unset. */
    public function email(): ?string
    {
        $email = $this->settings->get('contact_email');

        return $this->settings->bool('show_email') && filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /** Public phone, or null unless explicitly enabled. */
    public function phone(): ?string
    {
        $phone = $this->settings->get('phone');

        return $this->settings->bool('show_phone') && filled($phone) ? $phone : null;
    }

    public function phoneHref(): ?string
    {
        $phone = $this->phone();

        return $phone ? 'tel:'.preg_replace('/[^\d+]/', '', $phone) : null;
    }

    public function statusText(): ?string
    {
        return $this->settings->get('status_text');
    }

    public function openToWork(): bool
    {
        return $this->settings->bool('open_to_work');
    }

    public function openToWorkText(): ?string
    {
        return $this->settings->get('open_to_work_text');
    }

    public function githubUrl(): ?string
    {
        return $this->url('github_url');
    }

    public function linkedinUrl(): ?string
    {
        return $this->url('linkedin_url');
    }

    /**
     * Profile links for the footer, contact page, and schema.org `sameAs`.
     *
     * @return list<array{label: string, url: string, icon: string, handle: string}>
     */
    public function socialLinks(): array
    {
        return array_values(array_filter([
            $this->githubUrl() ? [
                'label' => 'GitHub',
                'url' => $this->githubUrl(),
                'icon' => 'github',
                'handle' => '@'.basename(rtrim($this->githubUrl(), '/')),
            ] : null,
            $this->linkedinUrl() ? [
                'label' => 'LinkedIn',
                'url' => $this->linkedinUrl(),
                'icon' => 'linkedin',
                'handle' => 'in/'.basename(rtrim($this->linkedinUrl(), '/')),
            ] : null,
        ]));
    }

    public function photoUrl(): ?string
    {
        $photo = config('portfolio.identity.photo');

        if (blank($photo)) {
            return null;
        }

        return str_starts_with($photo, 'http') ? $photo : asset($photo);
    }

    private function url(string $key): ?string
    {
        $url = $this->settings->get($key);

        return filled($url) && filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }
}
