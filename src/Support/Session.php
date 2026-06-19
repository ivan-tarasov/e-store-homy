<?php

declare(strict_types=1);

namespace App\Support;

final class Session
{
    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => true,
        ]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function userId(): ?int
    {
        $id = $this->get('user_id');
        return is_int($id) ? $id : null;
    }

    public function login(int $userId): void
    {
        $this->set('user_id', $userId);
        session_regenerate_id(true);
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 3600,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly'],
            );
        }
        session_destroy();
    }

    public function flash(string $key): mixed
    {
        $value = $_SESSION['__flash'][$key] ?? null;
        unset($_SESSION['__flash'][$key]);
        return $value;
    }

    public function setFlash(string $key, mixed $value): void
    {
        $_SESSION['__flash'][$key] = $value;
    }
}
