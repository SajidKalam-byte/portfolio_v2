<?php

namespace App\Support;

use Closure;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Versioned cache for public content.
 *
 * Every key is prefixed with a version number; bumping the version (on any
 * content change) makes all previous entries unreachable at once. This works
 * with the file and database cache stores, which don't support tags.
 */
class ContentCache
{
    private const VERSION_KEY = 'portfolio:content-version';

    /**
     * Cache a scalar/array value. Objects are not allowed: Laravel only
     * unserializes allow-listed classes from the cache (cache.serializable_classes).
     */
    public static function remember(string $key, Closure $callback): mixed
    {
        return Cache::remember(self::key($key), config('portfolio.cache_ttl', 3600), $callback);
    }

    /**
     * Cache a model query as raw attribute arrays and rehydrate on read, so no
     * PHP objects are ever unserialized from the cache store.
     *
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $model
     * @param  Closure(): iterable<TModel>  $query
     * @return EloquentCollection<int, TModel>
     */
    public static function rememberModels(string $key, string $model, Closure $query): EloquentCollection
    {
        $rows = self::remember($key, fn () => collect($query())
            ->map(fn (Model $record) => $record->getAttributes())
            ->values()
            ->all());

        return $model::hydrate($rows);
    }

    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }

    private static function key(string $key): string
    {
        return 'portfolio:'.self::version().':'.$key;
    }

    private static function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }
}
