<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\User;
use App\Repository\UserRepository;
use App\Support\Session;

final class AuthService
{
    public function __construct(
        private readonly Session $session,
        private readonly UserRepository $users,
    ) {
    }

    public function currentUser(): ?User
    {
        $id = $this->session->userId();
        return $id !== null ? $this->users->find($id) : null;
    }

    public function isLoggedIn(): bool
    {
        return $this->session->userId() !== null;
    }

    public function isAdmin(): bool
    {
        return $this->currentUser()?->isAdmin === true;
    }

    public function attempt(string $email, string $password): ?User
    {
        $user = $this->users->findByEmail($email);
        if ($user === null) {
            return null;
        }
        if (!password_verify($password, $user->passwordHash)) {
            return null;
        }
        $this->session->login($user->id);
        return $user;
    }

    public function logout(): void
    {
        $this->session->logout();
    }
}
