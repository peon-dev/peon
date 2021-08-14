<?php

declare(strict_types=1);

namespace PHPMate\Domain\Tools\Git;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
class GitRepositoryAuthentication
{
    public function __construct(
        public string $username,
        public string $personalAccessToken
    ) {}
}
