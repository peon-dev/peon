<?php

declare(strict_types=1);

namespace Peon\Domain\GitProvider\Value;

class GitRepositoryAuthentication
{
    public const GITHUB_PAT_USERNAME = 'oauth';
    public const GITLAB_PAT_USERNAME = 'gitlab-ci-token';


    public function __construct(
        public readonly string $username,
        public readonly string $password
    ) {}


    public static function fromGitLabPersonalAccessToken(string $personalAccessToken): self
    {
        return new self(self::GITLAB_PAT_USERNAME, $personalAccessToken);
    }


    public static function fromGitHubPersonalAccessToken(string $personalAccessToken): self
    {
        return new self(self::GITHUB_PAT_USERNAME, $personalAccessToken);
    }
}
