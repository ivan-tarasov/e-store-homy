<?php

declare(strict_types=1);

namespace App\Action\Search;

use App\Http\Request;
use App\Http\Response;
use App\Repository\ProductRepository;
use App\Service\ProductCardRenderer;
use App\Support\Lang;
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
            $content = '<p class="text-muted">' . Lang::t('search.none', [
                'q' => $qSafe,
                'link' => '<a href="/category/">' . Lang::t('search.none_link') . '</a>',
            ]) . '</p>';
        } else {
            $grid = '';
            foreach ($results as $product) {
                $grid .= $this->cards->card($product, colClass: 'col-6 col-sm-4 col-md-3');
            }
            $content = sprintf('<div class="row">%s</div>', $grid);
        }

        $n = count($results);
        $resWord = Lang::isEnglish()
            ? Lang::t($n === 1 ? 'word.results.one' : 'word.results.few')
            : $this->pluralResults($n);

        $body = sprintf(
            '<section class="container" style="padding:2em 0;">'
            . '<h1>%s <small class="text-muted" style="font-size:.5em;">%s</small></h1>'
            . '%s'
            . '</section>',
            Lang::t('search.results_title', ['q' => $qSafe]),
            Lang::t('search.results_count', ['count' => $n, 'word' => $resWord]),
            $content,
        );

        return Response::html($this->layout->render(
            $body,
            new PageMeta(Lang::t('search.meta', ['q' => $q])),
            $this->layout->breadcrumb(null, Lang::t('search.breadcrumb')),
        ));
    }

    private function pluralResults(int $n): string
    {
        $mod10 = $n % 10;
        $mod100 = $n % 100;
        if ($mod100 >= 11 && $mod100 <= 14) {
            return Lang::t('word.results.many');
        }
        return match (true) {
            $mod10 === 1 => Lang::t('word.results.one'),
            $mod10 >= 2 && $mod10 <= 4 => Lang::t('word.results.few'),
            default => Lang::t('word.results.many'),
        };
    }
}
