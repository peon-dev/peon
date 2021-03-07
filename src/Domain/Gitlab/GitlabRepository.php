<?php

declare(strict_types=1);

namespace PHPMate\Domain\Gitlab;

final class GitlabRepository
{
    public static function fromPersonalAccessToken(string $repositoryName, string $personalAccessToken): GitlabRepository
    {
        return new self();
    }

    public function openMergeRequest(string $branch, GitlabApi $gitlabApi): void
    {
        // TODO: not implemented
    }
}
