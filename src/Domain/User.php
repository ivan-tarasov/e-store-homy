<?php

declare(strict_types=1);

namespace App\Domain;

final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly ?string $phone = null,
        public readonly bool $isAdmin = false,
    ) {
    }

    public function displayName(): string
    {
        if ($this->firstName !== null && $this->lastName !== null) {
            return $this->firstName . ' ' . $this->lastName;
        }
        return $this->email;
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            email: (string) $row['email'],
            passwordHash: (string) $row['password_hash'],
            firstName: $row['first_name'] ?? null,
            lastName: $row['last_name'] ?? null,
            phone: $row['phone'] ?? null,
            isAdmin: (bool) ($row['is_admin'] ?? false),
        );
    }
}
