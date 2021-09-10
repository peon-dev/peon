<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\GitLab;

use Gitlab\Client;
use Gitlab\Exception\RuntimeException;
use PHPMate\Domain\GitProvider\CheckWriteAccessToRemoteRepository;
use PHPMate\Domain\GitProvider\GitProvider;
use PHPMate\Domain\GitProvider\InsufficientAccessToRemoteRepository;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;

final class GitLab implements GitProvider, CheckWriteAccessToRemoteRepository
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
        $personalAccessToken = $repository->authentication->password;

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


    public function hasWriteAccess(RemoteGitRepository $gitRepository): bool
    {
        $client = $this->createHttpClient($gitRepository);

        try {
            $project = $client->projects()->show($gitRepository->getProject());

            return (bool) $project['can_create_merge_request_in'];
        } catch (RuntimeException) {
            return false;
        }
    }
}
