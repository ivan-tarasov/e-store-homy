<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\User;
use App\Storage\JsonStore;

final class UserRepository
{
    /** @var array<int, User>|null */
    private ?array $byId = null;

    public function __construct(private readonly JsonStore $store)
    {
    }

    public function find(int $id): ?User
    {
        return $this->loadById()[$id] ?? null;
    }

    public function findByEmail(string $email): ?User
    {
        $email = mb_strtolower(trim($email));
        foreach ($this->loadById() as $user) {
            if (mb_strtolower($user->email) === $email) {
                return $user;
            }
        }
        return null;
    }

    /** @return list<User> */
    public function all(): array
    {
        return array_values($this->loadById());
    }

    /** @return array<int, User> */
    private function loadById(): array
    {
        if ($this->byId !== null) {
            return $this->byId;
        }

        $byId = [];
        foreach ($this->store->read('users') as $row) {
            $user = User::fromArray($row);
            $byId[$user->id] = $user;
        }
        return $this->byId = $byId;
    }
}
