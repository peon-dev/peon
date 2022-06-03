<?php

declare(strict_types=1);

namespace Peon\UseCase;

final class RegisterUser
{
    public function __construct(
        public readonly string $username,
        public readonly string $plainTextPassword,
    ) {
    }
}
