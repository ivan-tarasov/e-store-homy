<?php

declare(strict_types=1);

namespace App\Action\Pages;

use App\Http\Request;
use App\Http\Response;
use App\Support\Lang;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;
use App\Template\TemplateEngine;

final class TermsAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly TemplateEngine $tpl,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $body = '<section class="container" style="padding:2em 0;">'
            . '<h1>' . Lang::t('terms.title') . '</h1>'
            . '<p>' . Lang::t('terms.intro') . '</p>'
            . '<h3>' . Lang::t('terms.pay_head') . '</h3>'
            . '<ul><li>' . Lang::t('terms.pay_cash') . '</li><li>' . Lang::t('terms.pay_card') . '</li><li>' . Lang::t('terms.pay_wire') . '</li></ul>'
            . '<h3>' . Lang::t('terms.ship_head') . '</h3>'
            . '<p>' . Lang::t('terms.ship_text') . '</p>'
            . '</section>';

        return Response::html($this->layout->render(
            $body,
            new PageMeta(Lang::t('terms.title')),
            $this->layout->breadcrumb(null, Lang::t('terms.title')),
        ));
    }
}
