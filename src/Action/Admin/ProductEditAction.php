<?php

declare(strict_types=1);

namespace App\Action\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\AuthService;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;

final class ProductEditAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly AuthService $auth,
        private readonly ProductRepository $products,
        private readonly CategoryRepository $categories,
        private readonly BrandRepository $brands,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        if (!$this->auth->isAdmin()) {
            return Response::redirect('/login/');
        }

        $id = isset($vars['id']) ? (int) $vars['id'] : null;
        $product = $id !== null ? $this->products->find($id) : null;

        if ($id !== null && $product === null) {
            return Response::redirect('/admin/products/');
        }

        $categoryOptions = '';
        foreach ($this->categories->all() as $cat) {
            $selected = $product !== null && $product->categoryId === $cat->id ? ' selected' : '';
            $categoryOptions .= sprintf(
                '<option value="%d"%s>%s</option>',
                $cat->id,
                $selected,
                htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'),
            );
        }

        $brandOptions = '';
        foreach ($this->brands->all() as $brand) {
            $selected = $product !== null && $product->brandId === $brand->id ? ' selected' : '';
            $brandOptions .= sprintf(
                '<option value="%d"%s>%s</option>',
                $brand->id,
                $selected,
                htmlspecialchars($brand->name, ENT_QUOTES, 'UTF-8'),
            );
        }

        $title = $product !== null ? 'Редактировать товар' : 'Новый товар';

        $body = sprintf(
            '<section class="container admin-page" style="padding:2em 0;">'
            . '<div class="row"><div class="col-md-3">%s</div><div class="col-md-9">'
            . '<h1>%s</h1>'
            . '<form method="post" action="/admin/products/save">'
            . '<input type="hidden" name="id" value="%d" />'
            . '<div class="mb-3"><label>Название</label>'
            . '<input class="form-control" name="name" value="%s" required /></div>'
            . '<div class="mb-3"><label>Категория</label>'
            . '<select class="form-control" name="category_id" required><option value="">— выберите —</option>%s</select></div>'
            . '<div class="mb-3"><label>Бренд</label>'
            . '<select class="form-control" name="brand_id" required><option value="">— выберите —</option>%s</select></div>'
            . '<div class="row mb-3"><div class="col">'
            . '<label>Цена (руб.)</label>'
            . '<input class="form-control" name="price" type="number" min="0" value="%d" required />'
            . '</div><div class="col">'
            . '<label>Запас (шт.)</label>'
            . '<input class="form-control" name="stock" type="number" min="0" value="%d" />'
            . '</div></div>'
            . '<div class="mb-3 form-check">'
            . '<input class="form-check-input" type="checkbox" name="in_stock" id="in_stock" value="1"%s />'
            . '<label class="form-check-label" for="in_stock">В наличии</label></div>'
            . '<div class="mb-3"><label>Описание</label>'
            . '<textarea class="form-control" name="description" rows="4">%s</textarea></div>'
            . '<button class="le-button" type="submit">Сохранить</button>'
            . ' <a href="/admin/products/" class="btn btn-link">Отмена</a>'
            . '</form>'
            . '</div></div></section>',
            AdminNav::render('products'),
            htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            $product?->id ?? 0,
            htmlspecialchars($product?->name ?? '', ENT_QUOTES, 'UTF-8'),
            $categoryOptions,
            $brandOptions,
            $product?->price ?? 0,
            $product?->stock ?? 0,
            ($product?->inStock ?? true) ? ' checked' : '',
            htmlspecialchars($product?->description ?? '', ENT_QUOTES, 'UTF-8'),
        );

        return Response::html($this->layout->render($body, new PageMeta($title . ' — админ-панель')));
    }
}
