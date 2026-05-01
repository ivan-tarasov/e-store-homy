<?php

declare(strict_types=1);

namespace App\Action\Errors;

use App\Http\Request;
use App\Http\Response;
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
        $body = '<section class="container" style="padding: 4em 0;">'
            . '<h1>404 — Страница не найдена</h1>'
            . '<p>Запрошенный адрес <code>' . htmlspecialchars($request->path, ENT_QUOTES, 'UTF-8') . '</code> не существует.</p>'
            . '<p><a class="le-button" href="/">На главную</a></p>'
            . '</section>';

        return Response::html(
            $this->layout->render($body, new PageMeta('404 — Страница не найдена')),
            404,
        );
    }
}
