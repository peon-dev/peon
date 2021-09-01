<?php

declare(strict_types=1);

namespace PHPMate\Domain\Tools\Git;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
class GitRepositoryAuthentication
{
    private const GITLAB_PAT_USERNAME = 'gitlab-ci-token';


    public function __construct(
        public string $username,
        public string $password
    ) {}


    public static function fromPersonalAccessToken(string $personalAccessToken): self
    {
        return new self(self::GITLAB_PAT_USERNAME, $personalAccessToken);
    }
}
