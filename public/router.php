<?php

declare(strict_types=1);

/**
 * Router for the PHP built-in dev server (`php -S localhost:8080 -t public public/router.php`).
 *
 * Static files served from public/ are returned by the server itself; everything
 * else falls through to the front controller.
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
