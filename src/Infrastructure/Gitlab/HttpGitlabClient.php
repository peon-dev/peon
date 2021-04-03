<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Gitlab;

use PHPMate\Domain\Gitlab\Gitlab;
use PHPMate\Domain\Gitlab\GitlabAuthentication;
use PHPMate\Domain\Gitlab\GitlabRepository;

final class HttpGitlabClient implements Gitlab
{
    public function openMergeRequest(GitlabRepository $gitlabRepository, string $branchWithChanges): void
    {
        $this->createHttpClient($gitlabRepository->authentication);
    }

    private function createHttpClient(GitlabAuthentication $authentication): void
    {
    }
}
