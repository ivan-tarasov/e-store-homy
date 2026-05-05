<?php

declare(strict_types=1);

namespace App\Action\Search;

use App\Http\Request;
use App\Http\Response;
use App\Repository\ProductRepository;
use App\Service\ProductCardRenderer;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;

final class SearchResultsAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly ProductRepository $products,
        private readonly ProductCardRenderer $cards,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $q = trim($request->input('q', '') ?? '');

        if ($q === '') {
            return Response::redirect('/');
        }

        $results = $this->products->search($q, 24);
        $qSafe = htmlspecialchars($q, ENT_QUOTES, 'UTF-8');

        if ($results === []) {
            $content = sprintf(
                '<p class="text-muted">По запросу «%s» ничего не найдено. '
                . 'Попробуйте другое слово или <a href="/category/">перейдите в каталог</a>.</p>',
                $qSafe,
            );
        } else {
            $grid = '';
            foreach ($results as $product) {
                $grid .= $this->cards->card($product, colClass: 'col-6 col-sm-4 col-md-3');
            }
            $content = sprintf('<div class="row">%s</div>', $grid);
        }

        $body = sprintf(
            '<section class="container" style="padding:2em 0;">'
            . '<h1>Поиск: «%s» <small class="text-muted" style="font-size:.5em;">%d результат(ов)</small></h1>'
            . '%s'
            . '</section>',
            $qSafe,
            count($results),
            $content,
        );

        return Response::html($this->layout->render(
            $body,
            new PageMeta('Поиск: ' . $q),
            $this->layout->breadcrumb(null, 'Поиск'),
        ));
    }
}
