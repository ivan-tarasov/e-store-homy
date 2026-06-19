<?php

declare(strict_types=1);

namespace App\Support;

use App\Service\Translator;

/**
 * Thin static accessor over the request's Translator.
 *
 * The app has ~30 string call-sites across templates, the layout renderer and
 * single-action controllers. Threading a Translator through every constructor
 * would be a lot of plumbing for a per-request singleton, so bootstrap sets the
 * instance once via init() and everything reads it through Lang::t().
 */
final class Lang
{
    private static ?Translator $translator = null;

    public static function init(Translator $translator): void
    {
        self::$translator = $translator;
    }

    /** @param array<string, scalar> $params */
    public static function t(string $key, array $params = []): string
    {
        return self::$translator?->t($key, $params) ?? $key;
    }

    public static function locale(): string
    {
        return self::$translator?->locale() ?? 'en';
    }

    public static function isEnglish(): bool
    {
        return self::locale() === 'en';
    }
}
