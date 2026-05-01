<?php

declare(strict_types=1);

namespace App\Action\Pages;

use App\Http\Request;
use App\Http\Response;
use App\Support\Session;
use App\Template\LayoutRenderer;
use App\Template\PageMeta;
use App\Template\TemplateEngine;

final class FeedbackAction
{
    public function __construct(
        private readonly LayoutRenderer $layout,
        private readonly TemplateEngine $tpl,
        private readonly Session $session,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        if ($request->method === 'POST') {
            $this->session->setFlash('feedback_sent', 'Спасибо! Это демо-форма — сообщение нигде не сохраняется.');
            return Response::redirect('/feedback/');
        }

        $flash = $this->session->flash('feedback_sent');
        $alert = $flash !== null
            ? sprintf('<div class="alert alert-success">%s</div>', htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8'))
            : '';

        $body = <<<HTML
<section class="container" style="padding:2em 0; max-width:640px;">
   <h1>Обратная связь</h1>
   {$alert}
   <p class="text-muted">Демо-форма. Поля проверяются на стороне браузера, отправка ничего не делает.</p>
   <form method="post" action="/feedback/">
      <div class="form-group"><label>Имя</label><input class="form-control" name="name" required /></div>
      <div class="form-group"><label>E-mail</label><input class="form-control" type="email" name="email" required /></div>
      <div class="form-group"><label>Сообщение</label><textarea class="form-control" name="message" required></textarea></div>
      <button class="le-button" type="submit">Отправить</button>
   </form>
</section>
HTML;

        return Response::html($this->layout->render(
            $body,
            new PageMeta('Обратная связь'),
            $this->layout->breadcrumb(null, 'Обратная связь'),
        ));
    }
}
