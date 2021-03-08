<?php

declare(strict_types=1);

namespace PHPMate\Domain\Gitlab;

use GuzzleHttp\Psr7\Uri;
use Nette\Utils\Strings;

final class GitlabRepository
{
    private string $repositoryUri;

    private GitlabAuthentication $authentication;


    /**
     * @throws RepositoryUriNotCompatible
     */
    private function __construct(string $repositoryUri, GitlabAuthentication $authentication)
    {
        if (Strings::startsWith($repositoryUri, 'https://') === false) {
            throw new RepositoryUriNotCompatible();
        }

        $this->repositoryUri = $repositoryUri;
        $this->authentication = $authentication;
    }


    /**
     * @throws RepositoryUriNotCompatible
     */
    public static function createWithAuthentication(string $repositoryUri, GitlabAuthentication $authentication): self
    {
        return new self($repositoryUri, $authentication);
    }


    public function getAuthenticatedRepositoryUri(): string
    {
        $username = $this->authentication->getUsername();
        $password = $this->authentication->getPersonalAccessToken();

        $uri = (new Uri($this->repositoryUri))
            ->withUserInfo($username, $password);

        return (string) $uri;
    }
}
