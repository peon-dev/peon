<?php

declare(strict_types=1);

namespace Peon\Infrastructure\GitProvider;

use Peon\Domain\GitProvider\Exception\AutoMergeNotSupported;
use Peon\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use Peon\Domain\GitProvider\Exception\UnknownGitProvider;
use Peon\Domain\GitProvider\GitProvider;
use Peon\Domain\GitProvider\Value\Commit;
use Peon\Domain\GitProvider\Value\GitProviderName;
use Peon\Domain\GitProvider\Value\MergeRequest;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;

final class ProxyGitProvider implements GitProvider
{
    public function __construct(
        private readonly GitLab $gitLab,
        private readonly GitHub $gitHub,
    ) {}


    /**
     * @throws UnknownGitProvider
     * @throws GitProviderCommunicationFailed
     */
    public function openMergeRequest(RemoteGitRepository $gitRepository, string $targetBranch, string $branchWithChanges, string $title, string $description,): MergeRequest
    {
        return $this->determineProvider($gitRepository)
            ->openMergeRequest(...func_get_args());
    }


    /**
     * @throws UnknownGitProvider
     * @throws GitProviderCommunicationFailed
     */
    public function getMergeRequestForBranch(RemoteGitRepository $gitRepository, string $branch): MergeRequest|null
    {
        return $this->determineProvider($gitRepository)
            ->getMergeRequestForBranch(...func_get_args());
    }


    /**
     * @throws UnknownGitProvider
     * @throws GitProviderCommunicationFailed
     */
    public function isAutoMergeSupported(RemoteGitRepository $gitRepository): bool
    {
        return $this->determineProvider($gitRepository)
            ->isAutoMergeSupported(...func_get_args());
    }


    /**
     * @throws UnknownGitProvider
     * @throws GitProviderCommunicationFailed
     * @throws AutoMergeNotSupported
     */
    public function mergeAutomatically(RemoteGitRepository $gitRepository, MergeRequest $mergeRequest): void
    {
        $this->determineProvider($gitRepository)
            ->mergeAutomatically(...func_get_args());
    }


    /**
     * @throws UnknownGitProvider
     * @throws GitProviderCommunicationFailed
     */
    public function getLastCommitOfDefaultBranch(RemoteGitRepository $gitRepository): Commit
    {
        return $this->determineProvider($gitRepository)
            ->getLastCommitOfDefaultBranch(...func_get_args());
    }


    /**
     * @throws UnknownGitProvider
     * @throws GitProviderCommunicationFailed
     */
    public function hasWriteAccessToRepository(RemoteGitRepository $gitRepository): bool
    {
        return $this->determineProvider($gitRepository)
            ->hasWriteAccessToRepository(...func_get_args());
    }


    /**
     * @throws UnknownGitProvider
     */
    private function determineProvider(RemoteGitRepository $gitRepository): GitProvider
    {
        $providerName = GitProviderName::determineFromRepositoryUri($gitRepository->repositoryUri);

        return match ($providerName) {
            GitProviderName::GitHub => $this->gitHub,
            GitProviderName::GitLab => $this->gitLab,
        };
    }
}
