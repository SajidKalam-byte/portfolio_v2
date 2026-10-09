<?php

namespace App\Support\Seo;

/**
 * Safe JSON-LD encoding for inline <script type="application/ld+json">.
 */
class JsonLd
{
    /**
     * @param  list<array<string, mixed>>  $nodes
     */
    public static function graph(array $nodes): string
    {
        return self::encode([
            '@context' => 'https://schema.org',
            '@graph' => array_values(array_map([self::class, 'clean'], $nodes)),
        ]);
    }

    /**
     * JSON_HEX_TAG turns < and > into < / >, so user-controlled
     * strings can never close the surrounding <script> element.
     */
    public static function encode(array $data): string
    {
        return json_encode(
            $data,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR
        );
    }

    /**
     * Drop null / empty values so the output only contains facts we actually have.
     */
    private static function clean(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $cleaned = array_map([self::class, 'clean'], $value);
        $cleaned = array_filter($cleaned, fn ($v) => $v !== null && $v !== '' && $v !== []);

        return array_is_list($value) ? array_values($cleaned) : $cleaned;
    }
}
