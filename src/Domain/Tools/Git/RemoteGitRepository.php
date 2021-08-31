<?php

declare(strict_types=1);

namespace PHPMate\Domain\Tools\Git;

use GuzzleHttp\Psr7\Uri;
use Nette\Utils\Strings;
use Psr\Http\Message\UriInterface;

final class RemoteGitRepository
{
    private UriInterface $uri;


    /**
     * @throws InvalidRemoteUri
     */
    public function __construct(
        private string $repositoryUri,
        public GitRepositoryAuthentication $authentication
    ) {
        try {
            $this->uri = new Uri($this->repositoryUri);
        } catch (\InvalidArgumentException $invalidArgumentException) {
            throw new InvalidRemoteUri($invalidArgumentException->getMessage());
        }

        if (Strings::startsWith($repositoryUri, 'https://') === false) {
            throw new InvalidRemoteUri('URI should start with https://');
        }

        if (Strings::endsWith($repositoryUri, '.git') === false) {
            throw new InvalidRemoteUri('URI should end with .git');
        }
    }


    public function getAuthenticatedUri(): UriInterface
    {
        $username = $this->authentication->username;
        $password = $this->authentication->personalAccessToken;

        return $this->uri->withUserInfo($username, $password);
    }


    public function getProject(): string
    {
        return str_replace('.git', '', trim($this->uri->getPath(), '/'));
    }


    public function getInstanceUrl(): string
    {
        return $this->uri->getScheme() . '://' . $this->uri->getHost();
    }
}
