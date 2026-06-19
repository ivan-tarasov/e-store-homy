<?php

declare(strict_types=1);

namespace App\Action\Pages;

use App\Http\Request;
use App\Http\Response;
use App\Service\Translator;
use App\Support\Session;

/**
 * Switches the active UI language and returns the visitor to the page they
 * came from. Stores the choice in the session and a 1-year cookie so it
 * survives across visits.
 */
final class LangAction
{
    public function __construct(
        private readonly Session $session,
    ) {
    }

    /** @param array<string, string> $vars */
    public function __invoke(Request $request, array $vars): Response
    {
        $code = $vars['code'] ?? 'en';
        if (!in_array($code, Translator::supported(), true)) {
            $code = 'en';
        }

        $this->session->set('lang', $code);
        setcookie('lang', $code, [
            'expires'  => time() + 31536000,
            'path'     => '/',
            'samesite' => 'Lax',
            'httponly' => false,
        ]);

        return Response::redirect($this->backTo($request));
    }

    /** Same-site path from the Referer header, defaulting to the homepage. */
    private function backTo(Request $request): string
    {
        $referer = $request->server['HTTP_REFERER'] ?? '';
        if (!is_string($referer) || $referer === '') {
            return '/';
        }
        $path = parse_url($referer, PHP_URL_PATH);
        if (!is_string($path) || $path === '' || str_starts_with($path, '/lang/')) {
            return '/';
        }
        $query = parse_url($referer, PHP_URL_QUERY);
        return $path . (is_string($query) && $query !== '' ? '?' . $query : '');
    }
}
