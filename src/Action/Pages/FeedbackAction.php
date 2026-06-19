<?php

declare(strict_types=1);

namespace App\Action\Pages;

use App\Http\Request;
use App\Http\Response;
use App\Support\Lang;
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
            $this->session->setFlash('feedback_sent', Lang::t('feedback.sent'));
            return Response::redirect('/feedback/');
        }

        $flash = $this->session->flash('feedback_sent');
        $alert = $flash !== null
            ? sprintf('<div class="alert alert-success">%s</div>', htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8'))
            : '';

        $title = Lang::t('feedback.title');
        $note = Lang::t('feedback.note');
        $nameLabel = Lang::t('feedback.name');
        $messageLabel = Lang::t('feedback.message');
        $submit = Lang::t('feedback.submit');

        $body = <<<HTML
<section class="container" style="padding:2em 0; max-width:640px;">
   <h1>{$title}</h1>
   {$alert}
   <p class="text-muted">{$note}</p>
   <form method="post" action="/feedback/">
      <div class="mb-3"><label>{$nameLabel}</label><input class="form-control" name="name" required /></div>
      <div class="mb-3"><label>E-mail</label><input class="form-control" type="email" name="email" required /></div>
      <div class="mb-3"><label>{$messageLabel}</label><textarea class="form-control" name="message" required></textarea></div>
      <button class="le-button" type="submit">{$submit}</button>
   </form>
</section>
HTML;

        return Response::html($this->layout->render(
            $body,
            new PageMeta(Lang::t('feedback.title')),
            $this->layout->breadcrumb(null, Lang::t('feedback.title')),
        ));
    }
}
