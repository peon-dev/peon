<?php

declare(strict_types=1);

namespace Peon\Domain\User;

use Peon\Domain\User\Value\UserId;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private null|string $hashedPassword = null;


    public function __construct(
        public readonly UserId $userId,
        public readonly string $username,
    ) {
    }


    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }


    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }


    public function getUserIdentifier(): string
    {
        return $this->userId->id;
    }


    public function changePassword(string $hashedPassword): void
    {
        $this->hashedPassword = $hashedPassword;
    }


    public function getPassword(): ?string
    {
        return $this->hashedPassword;
    }
}
