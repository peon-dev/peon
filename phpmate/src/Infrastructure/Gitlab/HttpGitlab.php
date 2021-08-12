<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Gitlab;

use Gitlab\Client;
use PHPMate\Domain\Gitlab\Gitlab;
use PHPMate\Domain\Gitlab\GitlabRepository;

final class HttpGitlab implements Gitlab
{
    public function openMergeRequest(
        GitlabRepository $gitlabRepository,
        string $targetBranch,
        string $branchWithChanges,
        string $title,
    ): void
    {
        $client = $this->createHttpClient($gitlabRepository);
        $project = $gitlabRepository->getProject();

        $client->mergeRequests()->create(
            $project,
            $branchWithChanges,
            $targetBranch,
            $title,
        );
    }


    public function createHttpClient(GitlabRepository $repository): Client
    {
        $personalAccessToken = $repository->authentication->personalAccessToken;

        $client = new Client();
        $client->setUrl($repository->getGitlabInstanceUrl());
        $client->authenticate($personalAccessToken, Client::AUTH_HTTP_TOKEN);

        return $client;
    }


    public function mergeRequestForBranchExists(GitlabRepository $gitlabRepository, string $branch): bool
    {
        $client = $this->createHttpClient($gitlabRepository);
        $project = $gitlabRepository->getProject();

        $mergeRequests = $client->mergeRequests()->all($project, [
            'state' => 'opened',
            'source_branch' => $branch,
        ]);

        return count($mergeRequests) === 1;
    }
}
