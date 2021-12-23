<?php

declare(strict_types=1);

namespace PHPMate\Domain\Job;

use PHPMate\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use PHPMate\Domain\GitProvider\GitProvider;
use PHPMate\Domain\GitProvider\Value\MergeRequest;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;
use PHPMate\Domain\PhpApplication\Value\LocalApplication;
use PHPMate\Domain\Tools\Git\Exception\GitCommandFailed;
use PHPMate\Domain\Tools\Git\Git;

class UpdateMergeRequest
{
    public function __construct(
        private GitProvider $gitProvider,
        private Git $git,
    )
    {
    }


    /**
     * @throws GitProviderCommunicationFailed
     * @throws GitCommandFailed
     */
    public function update(
        LocalApplication    $localApplication,
        RemoteGitRepository $remoteGitRepository,
        string              $title,
    ): MergeRequest|null
    {
        $workingDirectory = $localApplication->workingDirectory;

        if ($this->git->hasUncommittedChanges($workingDirectory)) {
            $this->git->commit($workingDirectory, '[PHP Mate] ' . $title);
            $this->git->forcePushWithLease($workingDirectory);

            return $this->getOpenedMergeRequestOrOpenNewOne($remoteGitRepository, $localApplication, $title);
        }

        // Branch exists, it should have MR no matter what
        // When this can happen? MR manually closed? Should it exclude files?
        if ($this->git->remoteBranchExists($workingDirectory, $localApplication->jobBranch)) {
            return $this->getOpenedMergeRequestOrOpenNewOne($remoteGitRepository, $localApplication, $title);
        }

        return null;
    }


    /**
     * @throws GitProviderCommunicationFailed
     */
    private function getOpenedMergeRequestOrOpenNewOne(
        RemoteGitRepository $remoteGitRepository,
        LocalApplication $localApplication,
        string $title
    ): MergeRequest
    {
        $mergeRequest = $this->gitProvider->getMergeRequestForBranch($remoteGitRepository, $localApplication->jobBranch);

        if ($mergeRequest === null) {
            return $this->gitProvider->openMergeRequest(
                $remoteGitRepository,
                $localApplication->mainBranch,
                $localApplication->jobBranch,
                '[PHP Mate] ' . $title
            );
        }

        return $mergeRequest;
    }
}
