<?php

declare(strict_types=1);

namespace PHPMate\Domain\Tools\Git;

use GuzzleHttp\Psr7\Uri;
use Nette\Utils\Strings;
use Psr\Http\Message\UriInterface;

final class RemoteGitRepository
{
    /**
     * @throws InvalidRemoteUri
     */
    public function __construct(
        private string $repositoryUri,
        public GitRepositoryAuthentication $authentication
    ) {
        if (Strings::startsWith($repositoryUri, 'https://') === false) {
            throw new InvalidRemoteUri();
        }
    }


    public function getAuthenticatedUri(): UriInterface
    {
        $username = $this->authentication->username;
        $password = $this->authentication->personalAccessToken;

        $uri = (new Uri($this->repositoryUri))
            ->withUserInfo($username, $password);

        return $uri;
    }


    public function getProject(): string
    {
        $uri = (new Uri($this->repositoryUri));
        $path = $uri->getPath();

        return str_replace('.git', '', trim($path, '/'));
    }


    public function getInstanceUrl(): string
    {
        $uri = (new Uri($this->repositoryUri));

        return $uri->getScheme() . '://' . $uri->getHost();
    }
}
