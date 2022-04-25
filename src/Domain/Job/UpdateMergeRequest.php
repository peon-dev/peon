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
        bool $mergeAutomatically,
    ): MergeRequest|null
    {
        $workingDirectory = $localApplication->workingDirectory;
        $mergeRequest = null;

        if ($this->git->hasUncommittedChanges($jobId, $workingDirectory->localPath)) {
            $this->git->commit($jobId, $workingDirectory->localPath, '[Peon] ' . $title);

            $this->git->forcePushWithLease($jobId, $workingDirectory->localPath);

            $mergeRequest = $this->getOpenedMergeRequestOrOpenNewOne($remoteGitRepository, $localApplication, $title);
        }

        // Branch exists, it should have MR no matter what
        // When this can happen? MR manually closed? Should it exclude files?
        if ($mergeRequest === null && $this->git->remoteBranchExists($jobId, $workingDirectory->localPath, $localApplication->jobBranch)) {
            $mergeRequest = $this->getOpenedMergeRequestOrOpenNewOne($remoteGitRepository, $localApplication, $title);
        }

        if ($mergeAutomatically === true && $mergeRequest !== null) {
            // TODO: throwin exception here should not fail the job, maybe? mr exists, that is success, right?
            $this->gitProvider->mergeAutomatically($remoteGitRepository, $mergeRequest);
        }

        return $mergeRequest;
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
        return <<<DESCRIPTION
<b>This MR was created by [Peon](https://github.com/peon-dev/peon).</b><br>
If you have any questions, troubles or feature requests, please [open an issue](https://github.com/peon-dev/peon/issues/new), we will be happy to help!
DESCRIPTION;
    }
}
