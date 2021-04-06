<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Gitlab;

use Gitlab\Client;
use PHPMate\Domain\Gitlab\Gitlab;
use PHPMate\Domain\Gitlab\GitlabRepository;

final class HttpGitlabClient implements Gitlab
{
    public function openMergeRequest(
        GitlabRepository $gitlabRepository,
        string $targetBranch,
        string $branchWithChanges,
        string $title,
    ): void
    {
        $client = $this->createClient($gitlabRepository);
        $project = $gitlabRepository->getProject();

        $client->mergeRequests()->create(
            $project,
            $branchWithChanges,
            $targetBranch,
            $title,
        );
    }


    private function createClient(GitlabRepository $repository): Client
    {
        $personalAccessToken = $repository->authentication->personalAccessToken;

        $client = new Client();
        $client->setUrl($repository->getGitlabInstanceUrl());
        $client->authenticate($personalAccessToken, Client::AUTH_HTTP_TOKEN);

        return $client;
    }
}
