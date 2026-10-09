<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Admin uploads are written straight into the public directory
 * (public/uploads or public_html/uploads). This avoids `storage:link`,
 * which many shared hosts can't create.
 */
class PublicUploads
{
    /**
     * @return string Path relative to the public directory, e.g. "uploads/projects/emr-3fa9c1.webp".
     */
    public function store(UploadedFile $file, string $folder, string $basename): string
    {
        $directory = trim(config('portfolio.uploads.directory', 'uploads'), '/').'/'.trim($folder, '/');
        $absoluteDirectory = public_path($directory);

        if (! is_dir($absoluteDirectory)) {
            mkdir($absoluteDirectory, 0755, true);
        }

        // Extension comes from the detected MIME type, never the client-supplied name.
        $extension = $file->guessExtension() ?: 'bin';
        $name = Str::slug($basename).'-'.Str::lower(Str::random(6)).'.'.$extension;

        $file->move($absoluteDirectory, $name);

        return $directory.'/'.$name;
    }

    /**
     * Store at a fixed name (used for the resume so its URL stays stable).
     */
    public function storeAs(UploadedFile $file, string $relativePath): string
    {
        $relativePath = trim($relativePath, '/');
        $absoluteDirectory = public_path(dirname($relativePath));

        if (! is_dir($absoluteDirectory)) {
            mkdir($absoluteDirectory, 0755, true);
        }

        $file->move($absoluteDirectory, basename($relativePath));

        return $relativePath;
    }

    /**
     * Delete a file only if it lives inside the uploads directory.
     */
    public function delete(?string $relativePath): void
    {
        if (blank($relativePath)) {
            return;
        }

        $uploads = realpath(public_path(config('portfolio.uploads.directory', 'uploads')));
        $file = realpath(public_path($relativePath));

        if ($uploads && $file && str_starts_with($file, $uploads.DIRECTORY_SEPARATOR) && is_file($file)) {
            @unlink($file);
        }
    }

    /**
     * @return array{0: ?int, 1: ?int} width and height, when detectable.
     */
    public function dimensions(string $relativePath): array
    {
        $size = @getimagesize(public_path($relativePath));

        return $size ? [$size[0], $size[1]] : [null, null];
    }
}
