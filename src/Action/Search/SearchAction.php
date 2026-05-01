<?php

declare(strict_types=1);

namespace App\Action\Search;

use App\Http\Request;
use App\Http\Response;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\Slugify;

final class SearchAction
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly CategoryRepository $categories,
        private readonly BrandRepository $brands,
        private readonly Slugify $slugify,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $term = trim($request->input('term', '') ?? '');
        if ($term === '') {
            return Response::json([]);
        }

        $matches = $this->products->search($term, 6);
        if ($matches === []) {
            return Response::json([['id' => '#', 'value' => '', 'label' => 'Ничего не найдено']]);
        }

        $out = [];
        foreach ($matches as $product) {
            $brand = $this->brands->find($product->brandId);
            $category = $this->categories->find($product->categoryId);
            $out[] = [
                'id' => $this->slugify->productPath($product->id, $brand?->slug ?? '', $product->name),
                'value' => $product->name,
                'label' => sprintf(
                    '%s — %s',
                    $category?->name ?? 'Каталог',
                    ($brand?->name ?? '') . ' ' . $product->name,
                ),
            ];
        }
        return Response::json($out);
    }
}
