<?php

declare(strict_types=1);

namespace Peon\Domain\User;

use Peon\Domain\User\Value\UserId;

interface UsersCollection
{
    public function nextIdentity(): UserId;

    public function save(User $user): void;
}
