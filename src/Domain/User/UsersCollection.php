<?php

declare(strict_types=1);

namespace Peon\Domain\User;

use Peon\Domain\User\Exception\UserNotFound;
use Peon\Domain\User\Value\UserId;

interface UsersCollection
{
    public function nextIdentity(): UserId;

    /**
     * @throws UserNotFound
     */
    public function get(UserId $userId): User;

    public function save(User $user): void;
}
