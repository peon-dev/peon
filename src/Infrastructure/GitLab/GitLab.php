<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\GitLab;

use Gitlab\Client;
use Gitlab\Exception\RuntimeException;
use PHPMate\Domain\GitProvider\CheckWriteAccessToRemoteRepository;
use PHPMate\Domain\GitProvider\GitProvider;
use PHPMate\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use PHPMate\Domain\GitProvider\Exception\InsufficientAccessToRemoteRepository;
use PHPMate\Domain\GitProvider\Value\MergeRequest;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;
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
    ): MergeRequest
    {
        try {
            $client = $this->createHttpClient($gitRepository);
            $project = $gitRepository->getProject();

            /** @var array{web_url: string} $mergeRequest */
            $mergeRequest = $client->mergeRequests()->create(
                $project,
                $branchWithChanges,
                $targetBranch,
                $title,
            );

            return new MergeRequest($mergeRequest['web_url']);
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
    public function getMergeRequestForBranch(RemoteGitRepository $gitRepository, string $branch): MergeRequest|null
    {
        try {
            $client = $this->createHttpClient($gitRepository);
            $project = $gitRepository->getProject();

            /** @var array<mixed> $mergeRequests */
            $mergeRequests = $client->mergeRequests()->all($project, [
                'state' => 'opened',
                'source_branch' => $branch,
            ]);

            if (count($mergeRequests) === 0) {
                return null;
            }

            if (count($mergeRequests) > 1) {
                throw new GitProviderCommunicationFailed('Should not exist more than 1 merge request for branch');
            }

            /** @var array{web_url: string} $mergeRequest */
            $mergeRequest = array_shift($mergeRequests);

            return new MergeRequest($mergeRequest['web_url']);
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
