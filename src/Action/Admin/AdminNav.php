<?php

declare(strict_types=1);

namespace App\Action\Admin;

/**
 * Tiny helper that emits the in-page admin sidebar/sub-nav. Lives next to
 * the admin actions so we don't need a full second LayoutRenderer for the
 * dashboard — we just decorate the storefront chrome with a left rail.
 */
final class AdminNav
{
    /** @return list<array{label: string, href: string, key: string}> */
    public static function items(): array
    {
        return [
            ['label' => 'Сводка',  'href' => '/admin/',          'key' => 'dashboard'],
            ['label' => 'Заказы',  'href' => '/admin/orders/',   'key' => 'orders'],
            ['label' => 'Товары',  'href' => '/admin/products/', 'key' => 'products'],
            ['label' => 'Пользователи', 'href' => '/admin/users/', 'key' => 'users'],
        ];
    }

    public static function render(string $activeKey): string
    {
        $links = '';
        foreach (self::items() as $item) {
            $cls = $item['key'] === $activeKey ? 'admin-nav-link is-active' : 'admin-nav-link';
            $links .= sprintf(
                '<li><a class="%s" href="%s">%s</a></li>',
                $cls,
                htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'),
            );
        }
        return '<nav class="admin-nav"><ul class="list-unstyled">' . $links . '</ul></nav>';
    }
}
