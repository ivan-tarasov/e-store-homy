<?php

declare(strict_types=1);

namespace App\Action\Pages;

use App\Http\Request;
use App\Http\Response;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;
use App\Template\TemplateEngine;

final class AboutAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly TemplateEngine $tpl,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $body = $this->tpl->render('about', 'index');
        return Response::html($this->layout->render(
            $body,
            new PageMeta('О магазине'),
            $this->layout->breadcrumb(null, 'О магазине'),
        ));
    }
}
