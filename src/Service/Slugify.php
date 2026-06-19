<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Russian → ASCII slug builder, used for product permalinks like
 * /product/123-apple-iphone-15.
 *
 * The transliteration table is kept in this class because the previous
 * implementation lived in a 121-line helper that did the same thing.
 */
final class Slugify
{
    /** @var array<string, string> */
    private const MAP = [
        'А' => 'A',  'Б' => 'B',  'В' => 'V',  'Г' => 'G',  'Д' => 'D',
        'Е' => 'E',  'Ё' => 'Yo', 'Ж' => 'Zh', 'З' => 'Z',  'И' => 'I',
        'Й' => 'J',  'К' => 'K',  'Л' => 'L',  'М' => 'M',  'Н' => 'N',
        'О' => 'O',  'П' => 'P',  'Р' => 'R',  'С' => 'S',  'Т' => 'T',
        'У' => 'U',  'Ф' => 'F',  'Х' => 'X',  'Ц' => 'Cz', 'Ч' => 'Ch',
        'Ш' => 'Sh', 'Щ' => 'Shh','Ъ' => '',   'Ы' => 'Y',  'Ь' => '',
        'Э' => 'E',  'Ю' => 'Yu', 'Я' => 'Ya',
        'а' => 'a',  'б' => 'b',  'в' => 'v',  'г' => 'g',  'д' => 'd',
        'е' => 'e',  'ё' => 'yo', 'ж' => 'zh', 'з' => 'z',  'и' => 'i',
        'й' => 'j',  'к' => 'k',  'л' => 'l',  'м' => 'm',  'н' => 'n',
        'о' => 'o',  'п' => 'p',  'р' => 'r',  'с' => 's',  'т' => 't',
        'у' => 'u',  'ф' => 'f',  'х' => 'x',  'ц' => 'cz', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'shh','ъ' => '',   'ы' => 'y',  'ь' => '',
        'э' => 'e',  'ю' => 'yu', 'я' => 'ya',
    ];

    public function slugify(string $input): string
    {
        $input = strtr($input, self::MAP);
        $input = mb_strtolower($input);
        $input = preg_replace('/[^a-z0-9]+/u', '-', $input) ?? $input;
        return trim($input, '-');
    }

    public function productPath(int $id, string $brandSlug, string $name): string
    {
        return sprintf('/product/%d-%s/', $id, $this->slugify($brandSlug . ' ' . $name));
    }
}
