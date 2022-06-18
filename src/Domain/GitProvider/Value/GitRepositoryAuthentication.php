<?php

declare(strict_types=1);

namespace Peon\Domain\GitProvider\Value;

use JetBrains\PhpStorm\Immutable;

class GitRepositoryAuthentication
{
    public const GITLAB_PAT_USERNAME = 'gitlab-ci-token';


    public function __construct(
        public readonly string $username,
        public readonly string $password
    ) {}


    public static function fromPersonalAccessToken(string $personalAccessToken): self
    {
        return new self(self::GITLAB_PAT_USERNAME, $personalAccessToken);
    }
}
