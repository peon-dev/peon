<?php

declare(strict_types=1);

namespace PHPMate\Worker\Domain\Gitlab;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
class GitlabAuthentication
{
    public function __construct(
        public string $username,
        public string $personalAccessToken
    ) {}
}
