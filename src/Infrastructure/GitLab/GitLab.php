<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\GitLab;

use Gitlab\Client;
use PHPMate\Domain\GitProvider\GitProvider;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;

final class GitLab implements GitProvider
{
    public function openMergeRequest(
        RemoteGitRepository $gitRepository,
        string $targetBranch,
        string $branchWithChanges,
        string $title,
    ): void
    {
        $client = $this->createHttpClient($gitRepository);
        $project = $gitRepository->getProject();

        $client->mergeRequests()->create(
            $project,
            $branchWithChanges,
            $targetBranch,
            $title,
        );
    }


    public function createHttpClient(RemoteGitRepository $repository): Client
    {
        $personalAccessToken = $repository->authentication->personalAccessToken;

        $client = new Client();
        $client->setUrl($repository->getInstanceUrl());
        $client->authenticate($personalAccessToken, Client::AUTH_HTTP_TOKEN);

        return $client;
    }


    public function hasMergeRequestForBranch(RemoteGitRepository $gitRepository, string $branch): bool
    {
        $client = $this->createHttpClient($gitRepository);
        $project = $gitRepository->getProject();

        $mergeRequests = $client->mergeRequests()->all($project, [
            'state' => 'opened',
            'source_branch' => $branch,
        ]);

        return count($mergeRequests) === 1;
    }
}
