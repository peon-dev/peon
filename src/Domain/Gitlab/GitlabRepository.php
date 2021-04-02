<?php

declare(strict_types=1);

namespace PHPMate\Domain\Gitlab;

use GuzzleHttp\Psr7\Uri;
use Nette\Utils\Strings;

final class GitlabRepository
{
    /**
     * @throws InvalidGitlabRepositoryUri
     */
    public function __construct(
        private string $repositoryUri,
        private GitlabAuthentication $authentication
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
