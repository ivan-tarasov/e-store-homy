<?php

declare(strict_types=1);

use App\App;
use App\Http\Request;

$rootDir = dirname(__DIR__);

require $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

$app = App::bootstrap($rootDir);
$app->handle(Request::fromGlobals())->send();
