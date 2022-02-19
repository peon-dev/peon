<?php

declare(strict_types=1);

namespace Peon\Domain\Job;

use Peon\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use Peon\Domain\GitProvider\GitProvider;
use Peon\Domain\GitProvider\Value\MergeRequest;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\PhpApplication\Value\TemporaryApplication;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Tools\Git\Git;

class UpdateMergeRequest
{
    public function __construct(
        private GitProvider $gitProvider,
        private Git $git,
    ) {}


    /**
     * @throws GitProviderCommunicationFailed
     * @throws ProcessFailed
     */
    public function update(
        JobId                  $jobId,
        TemporaryApplication $localApplication,
        RemoteGitRepository  $remoteGitRepository,
        string               $title,
    ): MergeRequest|null
    {
        $workingDirectory = $localApplication->workingDirectory;

        if ($this->git->hasUncommittedChanges($jobId, $workingDirectory)) {
            $this->git->commit($jobId, $workingDirectory, '[Peon] ' . $title);
            $this->git->forcePushWithLease($jobId, $workingDirectory);

            return $this->getOpenedMergeRequestOrOpenNewOne($remoteGitRepository, $localApplication, $title);
        }

        // Branch exists, it should have MR no matter what
        // When this can happen? MR manually closed? Should it exclude files?
        if ($this->git->remoteBranchExists($jobId, $workingDirectory, $localApplication->jobBranch)) {
            return $this->getOpenedMergeRequestOrOpenNewOne($remoteGitRepository, $localApplication, $title);
        }

        return null;
    }


    /**
     * @throws GitProviderCommunicationFailed
     */
    private function getOpenedMergeRequestOrOpenNewOne(
        RemoteGitRepository  $remoteGitRepository,
        TemporaryApplication $localApplication,
        string               $title
    ): MergeRequest
    {
        $mergeRequest = $this->gitProvider->getMergeRequestForBranch($remoteGitRepository, $localApplication->jobBranch);

        if ($mergeRequest === null) {
            return $this->gitProvider->openMergeRequest(
                $remoteGitRepository,
                $localApplication->mainBranch,
                $localApplication->jobBranch,
                '[Peon] ' . $title,
                $this->getDefaultMergeRequestDescription(),
            );
        }

        return $mergeRequest;
    }


    private function getDefaultMergeRequestDescription(): string
    {
        return 'This MR was created by [Peon](https://github.com/peon-dev/peon).<br>If you have any questions or problems, please [open an issue](https://github.com/peon-dev/peon/issues/new), we will be happy to help!';
    }
}
