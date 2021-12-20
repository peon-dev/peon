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
        $branchWithChanges = $localApplication->jobBranch;

        $mergeRequest = $this->gitProvider->getMergeRequestForBranch($remoteGitRepository, $branchWithChanges);

        // Let's see if job changed something
        if ($this->git->hasUncommittedChanges($workingDirectory)) {
            $this->git->commit($workingDirectory, '[PHP Mate] ' . $title);
            $this->git->forcePush($workingDirectory);

            // Great, we have changed code and MR was not opened yet
            if ($mergeRequest === null) {
                $mergeRequest = $this->gitProvider->openMergeRequest(
                    $remoteGitRepository,
                    $localApplication->mainBranch,
                    $branchWithChanges,
                    '[PHP Mate] ' . $title
                );
            }
        } elseif ($this->git->remoteBranchExists($workingDirectory, $localApplication->jobBranch)) {
            if ($mergeRequest === null) {
                $mergeRequest = $this->gitProvider->openMergeRequest(
                    $remoteGitRepository,
                    $localApplication->mainBranch,
                    $localApplication->jobBranch,
                    '[PHP Mate] ' . $title
                );
            }
        }

        return $mergeRequest;
    }
}
