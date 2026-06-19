<?php

declare(strict_types=1);

namespace App\Service;

use App\Support\Lang;

/**
 * Tiny locale helper for plurals and date formatting.
 *
 * The previous codebase pulled in a 1.2k-line third-party library for this;
 * the demo only needs a few functions. Russian months are inflected the way
 * they read in regular text ("3 января 2026 года"); English uses a plain
 * "3 January 2026" form. Date output follows the active UI language.
 */
final class RussianLocale
{
    private const MONTHS = [
        1 => 'января',  'февраля', 'марта',  'апреля',
             'мая',     'июня',    'июля',   'августа',
             'сентября','октября', 'ноября', 'декабря',
    ];

    private const MONTHS_EN = [
        1 => 'January',  'February', 'March',    'April',
             'May',      'June',     'July',     'August',
             'September','October',  'November', 'December',
    ];

    /**
     * Choose a plural form based on Russian rules:
     *   1, 21, 31...    -> $forms[0]
     *   2-4, 22-24...   -> $forms[1]
     *   0, 5-20, 25-30, -> $forms[2]
     *
     * @param array{0: string, 1: string, 2: string} $forms
     */
    public function plural(int $n, array $forms): string
    {
        $n = abs($n);
        $mod10 = $n % 10;
        $mod100 = $n % 100;

        if ($mod100 >= 11 && $mod100 <= 14) {
            return $forms[2];
        }
        return match (true) {
            $mod10 === 1 => $forms[0],
            $mod10 >= 2 && $mod10 <= 4 => $forms[1],
            default => $forms[2],
        };
    }

    public function formatDateTime(int $timestamp): string
    {
        $day = (int) date('j', $timestamp);
        $year = date('Y', $timestamp);
        $time = date('H:i', $timestamp);
        $monthNum = (int) date('n', $timestamp);

        if (Lang::isEnglish()) {
            return sprintf('%d %s %s, %s', $day, self::MONTHS_EN[$monthNum], $year, $time);
        }
        return sprintf('%d %s %s года в %s', $day, self::MONTHS[$monthNum], $year, $time);
    }

    public function formatDate(int $timestamp): string
    {
        $day = (int) date('j', $timestamp);
        $year = date('Y', $timestamp);
        $monthNum = (int) date('n', $timestamp);

        if (Lang::isEnglish()) {
            return sprintf('%d %s %s', $day, self::MONTHS_EN[$monthNum], $year);
        }
        return sprintf('%d %s %s года', $day, self::MONTHS[$monthNum], $year);
    }
}
