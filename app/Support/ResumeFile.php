<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

/**
 * The downloadable resume PDF, resolved safely inside the public directory.
 */
class ResumeFile
{
    public function __construct(private Settings $settings) {}

    public function relativePath(): ?string
    {
        $path = trim((string) $this->settings->get('resume_path'), '/\\');

        return $path !== '' ? $path : null;
    }

    /**
     * Absolute path, or null when missing, not a PDF, or outside the public directory.
     */
    public function absolutePath(): ?string
    {
        $relative = $this->relativePath();

        if ($relative === null || strtolower(pathinfo($relative, PATHINFO_EXTENSION)) !== 'pdf') {
            return null;
        }

        $public = realpath(public_path());
        $file = realpath(public_path($relative));

        if ($public === false || $file === false || ! is_file($file)) {
            return null;
        }

        return str_starts_with($file, $public.DIRECTORY_SEPARATOR) ? $file : null;
    }

    public function exists(): bool
    {
        return $this->absolutePath() !== null;
    }

    public function externalUrl(): ?string
    {
        $url = $this->settings->get('resume_external_url');

        return filled($url) && filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    public function available(): bool
    {
        return $this->exists() || $this->externalUrl() !== null;
    }

    /**
     * Where the "Download resume" button should point, or null if nothing is available.
     */
    public function downloadUrl(): ?string
    {
        if ($this->exists()) {
            return route('resume.download');
        }

        return $this->externalUrl();
    }

    public function sizeLabel(): ?string
    {
        $path = $this->absolutePath();

        return $path ? Number::fileSize(filesize($path), precision: 0) : null;
    }

    public function updatedAt(): ?Carbon
    {
        $path = $this->absolutePath();

        return $path ? Carbon::createFromTimestamp(filemtime($path)) : null;
    }

    public function downloadName(): string
    {
        return str_replace(' ', '-', config('portfolio.identity.name')).'-Resume.pdf';
    }
}
