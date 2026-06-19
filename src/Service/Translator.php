<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Loads UI message catalogs from lang/{locale}.php (key => string maps) and
 * resolves keys via t(). Missing keys fall back to the fallback locale, then
 * to the key itself, so a forgotten string is visible but never fatal.
 */
final class Translator
{
    /** @var array<string, string> */
    private array $messages = [];

    public function __construct(
        private readonly string $locale,
        string $langDir,
        string $fallback = 'ru',
    ) {
        $this->messages = $this->loadFile($langDir, $locale);
        if ($locale !== $fallback) {
            // union: keep locale strings, backfill anything missing from fallback
            $this->messages += $this->loadFile($langDir, $fallback);
        }
    }

    /** @return list<string> */
    public static function supported(): array
    {
        return ['en', 'ru'];
    }

    public function locale(): string
    {
        return $this->locale;
    }

    /** @param array<string, scalar> $params */
    public function t(string $key, array $params = []): string
    {
        $str = $this->messages[$key] ?? $key;
        foreach ($params as $name => $value) {
            $str = str_replace('{' . $name . '}', (string) $value, $str);
        }
        return $str;
    }

    /** @return array<string, string> */
    private function loadFile(string $langDir, string $locale): array
    {
        $path = $langDir . '/' . $locale . '.php';
        if (!is_file($path)) {
            return [];
        }
        $data = require $path;
        return is_array($data) ? $data : [];
    }
}
