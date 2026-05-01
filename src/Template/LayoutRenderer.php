<?php

declare(strict_types=1);

namespace App\Template;

use App\Domain\Category;
use App\Repository\CategoryRepository;
use App\Service\AuthService;
use App\Service\CartService;
use App\Service\PriceFormatter;
use App\Service\Slugify;

/**
 * Builds the HTML chrome (head, top nav, header, main menu, breadcrumb, footer)
 * around a page body produced by an Action.
 *
 * This is the only place that knows about the templates/index/* family of
 * partials — Actions just supply their own page body.
 */
final class LayoutRenderer
{
    public function __construct(
        private readonly TemplateEngine $tpl,
        private readonly CategoryRepository $categories,
        private readonly AuthService $auth,
        private readonly CartService $cart,
        private readonly PriceFormatter $price,
        private readonly Slugify $slugify,
        private readonly string $shopName,
        private readonly string $shopPhone,
        private readonly string $shopEmail,
        private readonly string $shopAddress,
    ) {
    }

    public function render(string $body, PageMeta $meta, ?string $breadcrumb = null): string
    {
        $startTime = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);

        $head = $this->tpl->render('index', 'header', [
            'title' => $meta->title,
            'description' => $meta->description,
            'keywords' => $meta->keywords,
        ]);

        $topLinks = $this->tpl->render('index', 'header/navigation/links', [
            'auth_menu' => $this->renderAuthMenu(),
        ]);

        $middle = $this->tpl->render('index', 'header/middle', [
            'logo_alt' => $this->shopName,
            'top_cart' => $this->renderTopCart(),
            'homy_phone' => $this->shopPhone,
            'homy_email' => $this->shopEmail,
            'search_txt' => 'Поиск по каталогу...',
        ]);

        $menu = $this->tpl->render('index', 'header/menu', [
            'list' => $this->renderMainMenu(),
        ]);

        $renderTime = number_format(microtime(true) - $startTime, 4, '.', '');

        $footer = $this->tpl->render('index', 'footer', [
            'recomended-body' => '',
            'on-sale-body' => '',
            'top-rated-body' => '',
            'subscribe' => 'Подписаться на нашу рассылку',
            'gogogo' => 'Вперёд!',
            'addr_descr' => 'Демо-версия магазина. Это портфолио-проект.',
            'homy_address' => $this->shopAddress,
            'homy_phone' => $this->shopPhone,
            'social_btns' => 'Социальные сети',
            'prod_catalog' => 'Каталог товаров',
            'price_update' => 'демо-данные',
            'iconset' => 'round',
            'icon_size' => '32',
            'effect' => 'hvr-pulse-shrink',
            'quick_menu' => $this->renderQuickMenu(),
            'admin_inf' => '',
            'cp_year' => '2014–' . date('Y'),
            'oferta' => 'Демо-версия. Заказы не оформляются по-настоящему.',
        ]);

        return $head . $topLinks . $middle . $menu . ($breadcrumb ?? '') . $body . $footer;
    }

    public function breadcrumb(?int $categoryId, ?string $extra = null): string
    {
        $items = '<li class="breadcrumb-item"><a href="/"><i class="fa fa-home"></i></a></li>';
        $items .= '<li class="breadcrumb-item"><a href="/category/">Каталог</a></li>';

        if ($categoryId !== null) {
            foreach ($this->categories->path($categoryId) as $cat) {
                $items .= sprintf(
                    '<li class="breadcrumb-item"><a href="/category/%s/">%s</a></li>',
                    rawurlencode($cat->slug),
                    htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'),
                );
            }
        }

        if ($extra !== null) {
            $items .= sprintf(
                '<li class="breadcrumb-item current"><a href="#">%s</a></li>',
                htmlspecialchars($extra, ENT_QUOTES, 'UTF-8'),
            );
        }

        return $this->tpl->render('index', 'header/breadcrumb', ['bread_items' => $items]);
    }

    private function renderAuthMenu(): string
    {
        if (!$this->auth->isLoggedIn()) {
            return $this->tpl->render('index', 'header/navigation/auth-false');
        }

        $user = $this->auth->currentUser();
        $adminLink = $this->auth->isAdmin()
            ? '<li><a href="/admin/" title="Админ-панель"><i class="fa fa-cog"></i> Админ</a></li>'
            : '';
        return $this->tpl->render('index', 'header/navigation/auth-true', [
            'username' => $user?->displayName() ?? 'Личный кабинет',
            'admin' => $adminLink,
        ]);
    }

    private function renderTopCart(): string
    {
        $lines = $this->cart->lineItems();
        if ($lines === []) {
            return $this->tpl->render('index', 'shoppingcart/body', [
                'total_items' => '0',
                'total_value' => 'пуста',
                'top_cart_items' => '<li class="text-center"><div class="h4 lead">Ваша корзина пуста</div></li>',
            ]);
        }

        $itemsHtml = '';
        foreach ($lines as $line) {
            $product = $line['product'];
            $itemsHtml .= $this->tpl->render('index', 'shoppingcart/item', [
                'item_id' => (string) $product->id,
                'item_qty' => (string) $line['qty'],
                'item_cost' => $this->price->format($product->price),
                'item_brand' => '',
                'item_name' => htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'),
                'item_img' => $product->mainPhoto() ?? '/img/cart/trolley.png',
            ]);
        }

        return $this->tpl->render('index', 'shoppingcart/body', [
            'total_items' => (string) $this->cart->totalItems(),
            'total_value' => $this->price->format($this->cart->totalAmount()),
            'top_cart_items' => $itemsHtml,
        ]);
    }

    private function renderMainMenu(): string
    {
        $html = '';
        foreach ($this->categories->rootCategories() as $root) {
            $children = $this->categories->children($root->id);
            $childHtml = $this->renderRootChildren($children);
            $html .= sprintf(
                '<li class="dropdown yamm-fw"><a href="/category/%s/" class="dropdown-toggle" data-hover="dropdown">%s</a><ul class="dropdown-menu"><li><div class="yamm-content"><div class="row">%s</div></div></li></ul></li>',
                rawurlencode($root->slug),
                htmlspecialchars($root->name, ENT_QUOTES, 'UTF-8'),
                $childHtml,
            );
        }
        return $html;
    }

    /** @param list<Category> $children */
    private function renderRootChildren(array $children): string
    {
        if ($children === []) {
            return '';
        }
        $cols = '';
        foreach ($children as $child) {
            $description = $child->description !== null
                ? sprintf('<p class="text-muted">%s</p>', htmlspecialchars($child->description, ENT_QUOTES, 'UTF-8'))
                : '';
            $icon = $child->icon !== null
                ? sprintf('<span class="glyph-icon flaticon-%s fa-lg"></span>', htmlspecialchars($child->icon, ENT_QUOTES, 'UTF-8'))
                : '';
            $cols .= sprintf(
                '<div class="col-md-4"><a href="/category/%s/"><h2>%s%s</h2></a>%s</div>',
                rawurlencode($child->slug),
                $icon,
                htmlspecialchars($child->name, ENT_QUOTES, 'UTF-8'),
                $description,
            );
        }
        return $cols;
    }

    private function renderQuickMenu(): string
    {
        $html = '';
        foreach ($this->categories->rootCategories() as $cat) {
            $html .= sprintf(
                '<li><a href="/category/%s/">%s</a></li>',
                rawurlencode($cat->slug),
                htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'),
            );
        }
        return $html;
    }
}
