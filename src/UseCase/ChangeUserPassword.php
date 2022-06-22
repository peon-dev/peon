<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\User\Value\UserId;

final class ChangeUserPassword
{
    public function __construct(
        public readonly UserId $userId,
        public readonly string $plainTextNewPassword,
    ) {
    }
}
