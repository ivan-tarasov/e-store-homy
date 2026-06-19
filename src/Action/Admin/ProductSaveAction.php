<?php

declare(strict_types=1);

namespace App\Action\Admin;

use App\Domain\Product;
use App\Http\Request;
use App\Http\Response;
use App\Repository\ProductRepository;
use App\Service\AuthService;
use App\Support\Session;

final class ProductSaveAction extends AbstractAdminAction
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly ProductRepository $products,
        private readonly Session $session,
    ) {
    }

    protected function auth(): AuthService { return $this->auth; }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $id = $request->int('id', 0);
        $name = trim($request->input('name', '') ?? '');
        $categoryId = $request->int('category_id', 0);
        $brandId = $request->int('brand_id', 0);
        $price = $request->int('price', 0);
        $stock = $request->int('stock', 0);
        $inStock = ($request->input('in_stock') === '1');
        $description = trim($request->input('description', '') ?? '');

        if ($name === '' || $categoryId === 0 || $brandId === 0) {
            $this->session->setFlash('admin_error', 'Заполните обязательные поля.');
            $back = $id > 0 ? "/admin/products/{$id}/edit" : '/admin/products/new';
            return Response::redirect($back);
        }

        $existing = $id > 0 ? $this->products->find($id) : null;

        $product = new Product(
            id: $id > 0 ? $id : $this->products->nextId(),
            categoryId: $categoryId,
            brandId: $brandId,
            name: $name,
            description: $description,
            price: $price,
            stock: $stock,
            inStock: $inStock,
            rating: $existing?->rating ?? 0.0,
            photos: $existing?->photos ?? [],
            properties: $existing?->properties ?? [],
        );

        $this->products->save($product);

        return Response::redirect('/admin/products/');
    }
}
