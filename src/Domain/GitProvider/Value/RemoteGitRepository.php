<?php

declare(strict_types=1);

namespace Peon\Domain\GitProvider\Value;

use GuzzleHttp\Psr7\Uri;
use Nette\Utils\Strings;
use Peon\Domain\GitProvider\Exception\InvalidRemoteUri;
use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Psr\Http\Message\UriInterface;

class RemoteGitRepository
{
    private ?UriInterface $uri = null;


    /**
     * @throws InvalidRemoteUri
     */
    public function __construct(
        public string $repositoryUri,
        public GitRepositoryAuthentication $authentication
    ) {
        try {
            $this->uri = new Uri($this->repositoryUri);
        } catch (\InvalidArgumentException $invalidArgumentException) {
            throw new InvalidRemoteUri($invalidArgumentException->getMessage());
        }

        if (Strings::startsWith($repositoryUri, 'https://') === false) {
            throw new InvalidRemoteUri('Only https:// protocol is supported - URI should start with https://');
        }

        if (Strings::contains($repositoryUri, '/-/')) {
            $this->repositoryUri = (string) Strings::before($this->repositoryUri, '/-/');
        }

        if (Strings::endsWith($repositoryUri, '.git') === false) {
            $this->repositoryUri .= '.git';
        }
    }


    public function getAuthenticatedUri(): UriInterface
    {
        $username = $this->authentication->username;
        $password = $this->authentication->password;

        return $this->getUri()->withUserInfo($username, $password);
    }


    public function getProject(): string
    {
        return str_replace('.git', '', trim($this->getUri()->getPath(), '/'));
    }


    public function getProjectUsername(): string
    {
        return explode('/', $this->getProject())[0];
    }


    public function getProjectRepository(): string
    {
        return explode('/', $this->getProject())[1];
    }


    public function getInstanceUrl(): string
    {
        return $this->getUri()->getScheme() . '://' . $this->getUri()->getHost();
    }


    private function getUri(): UriInterface
    {
        if ($this->uri === null) {
            $this->uri = new Uri($this->repositoryUri);
        }

        return $this->uri;
    }
}
