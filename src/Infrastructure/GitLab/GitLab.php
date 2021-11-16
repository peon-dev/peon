<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\GitLab;

use Gitlab\Client;
use Gitlab\Exception\RuntimeException;
use PHPMate\Domain\GitProvider\CheckWriteAccessToRemoteRepository;
use PHPMate\Domain\GitProvider\GitProvider;
use PHPMate\Domain\GitProvider\GitProviderCommunicationFailed;
use PHPMate\Domain\GitProvider\InsufficientAccessToRemoteRepository;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class GitLab implements GitProvider, CheckWriteAccessToRemoteRepository
{
    /**
     * @throws GitProviderCommunicationFailed
     */
    public function openMergeRequest(
        RemoteGitRepository $gitRepository,
        string $targetBranch,
        string $branchWithChanges,
        string $title,
    ): void
    {
        try {
            $client = $this->createHttpClient($gitRepository);
            $project = $gitRepository->getProject();

            $client->mergeRequests()->create(
                $project,
                $branchWithChanges,
                $targetBranch,
                $title,
            );
        } catch (\Throwable $throwable) {
            throw new GitProviderCommunicationFailed($throwable->getMessage(), previous: $throwable);
        }
    }


    public function createHttpClient(RemoteGitRepository $repository): Client
    {
        $personalAccessToken = $repository->authentication->password;

        $client = new Client();
        $client->setUrl($repository->getInstanceUrl());
        $client->authenticate($personalAccessToken, Client::AUTH_HTTP_TOKEN);

        return $client;
    }


    /**
     * @throws GitProviderCommunicationFailed
     */
    public function hasMergeRequestForBranch(RemoteGitRepository $gitRepository, string $branch): bool
    {
        try {
            $client = $this->createHttpClient($gitRepository);
            $project = $gitRepository->getProject();

            /** @var array<mixed> $mergeRequests */
            $mergeRequests = $client->mergeRequests()->all($project, [
                'state' => 'opened',
                'source_branch' => $branch,
            ]);

            return count($mergeRequests) === 1;
        } catch (\Throwable $throwable) {
            throw new GitProviderCommunicationFailed($throwable->getMessage(), previous: $throwable);
        }
    }


    /**
     * @throws GitProviderCommunicationFailed
     */
    public function hasWriteAccess(RemoteGitRepository $gitRepository): bool
    {
        $client = $this->createHttpClient($gitRepository);

        try {
            /** @var array{can_create_merge_request_in?: int} $project */
            $project = $client->projects()->show($gitRepository->getProject());

            return (bool) ($project['can_create_merge_request_in'] ?? false);
        } catch (\Throwable $throwable) {
            throw new GitProviderCommunicationFailed($throwable->getMessage(), previous: $throwable);
        }
    }
}
