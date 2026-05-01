<?php

declare(strict_types=1);

namespace App\Template;

use RuntimeException;

/**
 * Tiny `{key}` substitution engine.
 *
 * Templates live in `templates/<folder>/<name>.tpl`. Any occurrence of
 * `{key}` is replaced with the corresponding entry in $vars. Missing keys
 * are silently emptied — that matches the legacy contract every template
 * was written against.
 */
final class TemplateEngine
{
    public function __construct(private readonly string $rootDir)
    {
    }

    /** @param array<string, scalar|null> $vars */
    public function render(string $folder, string $name, array $vars = []): string
    {
        $path = sprintf('%s/%s/%s.tpl', $this->rootDir, $folder, $name);
        if (!is_file($path)) {
            throw new RuntimeException(sprintf('Template not found: %s', $path));
        }

        $template = file_get_contents($path);
        if ($template === false) {
            throw new RuntimeException(sprintf('Cannot read template: %s', $path));
        }

        return $this->substitute($template, $vars);
    }

    /** @param array<string, scalar|null> $vars */
    public function renderString(string $template, array $vars = []): string
    {
        return $this->substitute($template, $vars);
    }

    /** @param array<string, scalar|null> $vars */
    private function substitute(string $template, array $vars): string
    {
        if ($vars === []) {
            return preg_replace('/\{[a-zA-Z0-9_-]+\}/', '', $template) ?? $template;
        }

        $search = [];
        $replace = [];
        foreach ($vars as $key => $value) {
            $search[] = '{' . $key . '}';
            $replace[] = $value === null ? '' : (string) $value;
        }
        $output = str_replace($search, $replace, $template);
        // strip remaining placeholders so unset keys don't leak into HTML
        return preg_replace('/\{[a-zA-Z0-9_-]+\}/', '', $output) ?? $output;
    }
}
