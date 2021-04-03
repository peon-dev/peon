<?php

declare(strict_types=1);

namespace PHPMate\Domain\Gitlab;

use GuzzleHttp\Psr7\Uri;
use JetBrains\PhpStorm\Immutable;
use Nette\Utils\Strings;
use Psr\Http\Message\UriInterface;

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


    public function getAuthenticatedRepositoryUri(): UriInterface
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


    public function getUrl(): string
    {
        $uri = (new Uri($this->repositoryUri));
        return $uri->getScheme() . '://' . $uri->getHost();
    }
}
