<?php

declare(strict_types=1);

namespace PHPMate\Domain\Gitlab;

final class GitlabRepository
{
    private string $repositoryUri;

    private GitlabAuthentication $authentication;


    private function __construct(string $repositoryUri)
    {
        $this->repositoryUri = $repositoryUri;
    }


    public static function createWithAuthentication(string $repositoryUri, GitlabAuthentication $authentication): self
    {
        $repository = new self($repositoryUri);
        $repository->authentication = $authentication;

        return $repository;
    }


    public function getAuthenticatedRepositoryUri(): string
    {
        return $this->repositoryUri;
    }
}
