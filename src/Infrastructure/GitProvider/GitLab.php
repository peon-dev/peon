<?php

declare(strict_types=1);

namespace Peon\Infrastructure\GitProvider;

use Gitlab\Client;
use Peon\Domain\GitProvider\GitProvider;
use Peon\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use Peon\Domain\GitProvider\Value\Commit;
use Peon\Domain\GitProvider\Value\MergeRequest;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;

final class GitLab implements GitProvider
{
    /**
     * @throws GitProviderCommunicationFailed
     */
    public function openMergeRequest(
        RemoteGitRepository $gitRepository,
        string $targetBranch,
        string $branchWithChanges,
        string $title,
        string $description,
    ): MergeRequest
    {
        try {
            $client = $this->createHttpClient($gitRepository);
            $project = $gitRepository->getProject();

            /** @var array{web_url: string, iid: int|string} $mergeRequest */
            $mergeRequest = $client->mergeRequests()->create(
                $project,
                $branchWithChanges,
                $targetBranch,
                $title,
                [
                    'description' => $description,
                    'labels' => 'Peon',
                    'remove_source_branch' => true,
                ]
            );

            return new MergeRequest((string) $mergeRequest['iid'], $mergeRequest['web_url']);
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

            /** @var array{web_url: string, iid: int|string} $mergeRequest */
            $mergeRequest = array_shift($mergeRequests);

            return new MergeRequest((string) $mergeRequest['iid'], $mergeRequest['web_url']);
        } catch (\Throwable $throwable) {
            throw new GitProviderCommunicationFailed($throwable->getMessage(), previous: $throwable);
        }
    }


    /**
     * @throws GitProviderCommunicationFailed
     */
    public function hasWriteAccessToRepository(RemoteGitRepository $gitRepository): bool
    {
        $client = $this->createHttpClient($gitRepository);

        try {
            /** @var array{permissions: array{project_access: null|array{access_level: int}, group_access: null|array{access_level: int}}} $project */
            $project = $client->projects()->show($gitRepository->getProject());

            // https://docs.gitlab.com/ee/api/access_requests.html
            $availablePermissions = 0;

            if (isset($project['permissions']['project_access']['access_level'])) {
                $availablePermissions = max((int) $project['permissions']['project_access']['access_level'], $availablePermissions);
            }

            if (isset($project['permissions']['group_access']['access_level'])) {
                $availablePermissions = max((int) $project['permissions']['group_access']['access_level'], $availablePermissions);
            }

            return $availablePermissions > 20;
        } catch (\Throwable $throwable) {
            throw new GitProviderCommunicationFailed($throwable->getMessage(), previous: $throwable);
        }
    }


    /**
     * @throws GitProviderCommunicationFailed
     *
     */
    public function getLastCommitOfDefaultBranch(RemoteGitRepository $gitRepository): Commit
    {
        $client = $this->createHttpClient($gitRepository);

        try {
            /** @var array{default_branch: string} $project */
            $project = $client->projects()->show($gitRepository->getProject());

            /** @var array{commit: array{short_id: string}} $branch */
            $branch = $client->repositories()->branch($gitRepository->getProject(), $project['default_branch']);

            return new Commit(
              $branch['commit']['short_id']
            );
        } catch (\Throwable $throwable) {
            throw new GitProviderCommunicationFailed($throwable->getMessage(), previous: $throwable);
        }
    }


    /**
     * @throws GitProviderCommunicationFailed
     */
    public function mergeAutomatically(RemoteGitRepository $gitRepository, MergeRequest $mergeRequest): void
    {
        $client = $this->createHttpClient($gitRepository);

        try {
            // TODO: change to polling, right now this is stupid sleep, because gitlab os slow and peon is too fast :/
            sleep(5);

            $client->mergeRequests()->merge($gitRepository->getProject(), (int) $mergeRequest->id, [
                'merge_when_pipeline_succeeds' => true,
            ]);
        } catch (\Throwable $throwable) {
            throw new GitProviderCommunicationFailed($throwable->getMessage(), previous: $throwable);
        }
    }


    public function isAutoMergeSupported(): bool
    {
        return true;
    }
}
