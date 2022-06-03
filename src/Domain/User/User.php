<?php

declare(strict_types=1);

namespace Peon\Domain\User;

use Peon\Domain\User\Value\UserId;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(
        public readonly UserId $userId,
        public readonly string $username,
        public string $hashedPassword,
    ) {
    }


    public function getRoles(): array
    {
        // This is here just to satisfy the interface 🤦
        return ['ROLE_USER'];
    }


    public function eraseCredentials(): void
    {
        // This is here just to satisfy the interface 🤦
    }


    public function getUserIdentifier(): string
    {
        // This is here just to satisfy the interface 🤦
        return $this->userId->id;
    }


    public function getPassword(): ?string
    {
        // This is here just to satisfy the interface 🤦
        return $this->hashedPassword;
    }
}
