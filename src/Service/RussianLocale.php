<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Tiny Russian locale helper for plurals and date formatting.
 *
 * The previous codebase pulled in a 1.2k-line third-party library for this;
 * the demo only needs two functions. Days and months are inflected the way
 * they read in regular Russian text ("3 января 2026 года").
 */
final class RussianLocale
{
    private const MONTHS = [
        1 => 'января',  'февраля', 'марта',  'апреля',
             'мая',     'июня',    'июля',   'августа',
             'сентября','октября', 'ноября', 'декабря',
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
        $month = self::MONTHS[(int) date('n', $timestamp)];
        $year = date('Y', $timestamp);
        $time = date('H:i', $timestamp);
        return sprintf('%d %s %s года в %s', $day, $month, $year, $time);
    }

    public function formatDate(int $timestamp): string
    {
        $day = (int) date('j', $timestamp);
        $month = self::MONTHS[(int) date('n', $timestamp)];
        $year = date('Y', $timestamp);
        return sprintf('%d %s %s года', $day, $month, $year);
    }
}
