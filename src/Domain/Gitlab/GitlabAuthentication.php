<?php

declare(strict_types=1);

namespace PHPMate\Domain\Gitlab;

class GitlabAuthentication
{
    public function __construct(
        private string $username,
        private string $personalAccessToken
    ) {}


    public function getUsername(): string
    {
        return $this->username;
    }


    public function getPersonalAccessToken(): string
    {
        return $this->personalAccessToken;
    }
}
