<?php

declare(strict_types=1);

namespace PHPMate\Domain\Gitlab;

use GuzzleHttp\Psr7\Uri;
use JetBrains\PhpStorm\Immutable;
use Nette\Utils\Strings;

#[Immutable]
final class GitlabRepository
{
    /**
     * @throws InvalidGitlabRepositoryUri
     */
    public function __construct(
        private string $repositoryUri,
        public GitlabAuthentication $authentication
    ) {
        if (Strings::startsWith($repositoryUri, 'https://') === false) {
            throw new InvalidGitlabRepositoryUri();
        }
    }


    public function getAuthenticatedRepositoryUri(): string
    {
        $username = $this->authentication->username;
        $password = $this->authentication->personalAccessToken;

        $uri = (new Uri($this->repositoryUri))
            ->withUserInfo($username, $password);

        return (string) $uri;
    }
}
