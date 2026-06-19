<?php

declare(strict_types=1);

namespace App\Action\Errors;

use App\Http\Request;
use App\Http\Response;
use App\Support\Lang;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;

final class NotFoundAction
{
    public function __construct(private readonly LayoutRenderer $layout)
    {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $path = '<code>' . htmlspecialchars($request->path, ENT_QUOTES, 'UTF-8') . '</code>';
        $body = '<section class="container" style="padding: 4em 0;">'
            . '<h1>' . Lang::t('error.404_title') . '</h1>'
            . '<p>' . Lang::t('error.404_text', ['path' => $path]) . '</p>'
            . '<p><a class="le-button" href="/">' . Lang::t('error.to_home') . '</a></p>'
            . '</section>';

        return Response::html(
            $this->layout->render($body, new PageMeta(Lang::t('error.404_title'))),
            404,
        );
    }
}
