<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Runtime settings: database overrides layered over config('portfolio.defaults').
 *
 * Reads never throw — if the database is unavailable (first deploy, error
 * pages during an outage) the config defaults are used.
 */
class Settings
{
    private const CACHE_KEY = 'portfolio:settings';

    /** @var array<string, mixed>|null */
    private ?array $resolved = null;

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function bool(string $key): bool
    {
        return filter_var($this->get($key), FILTER_VALIDATE_BOOLEAN);
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->resolved ??= array_merge(config('portfolio.defaults', []), $this->stored());
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function put(array $values): void
    {
        $allowed = array_keys(config('portfolio.defaults', []));

        foreach ($values as $key => $value) {
            if (! in_array($key, $allowed, true)) {
                continue;
            }

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : $value],
            );
        }

        Cache::forget(self::CACHE_KEY);
        ContentCache::flush();
        $this->resolved = null;
    }

    /** @return array<string, mixed> */
    private function stored(): array
    {
        try {
            $rows = Cache::rememberForever(self::CACHE_KEY, fn () => Setting::query()->pluck('value', 'key')->all());
        } catch (Throwable) {
            return [];
        }

        $defaults = config('portfolio.defaults', []);

        // Cast stored strings back to the type of the default (booleans are stored as "1"/"0").
        foreach ($rows as $key => $value) {
            if (array_key_exists($key, $defaults) && is_bool($defaults[$key])) {
                $rows[$key] = $value === '1';
            }
        }

        return $rows;
    }
}
