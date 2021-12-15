<?php

declare(strict_types=1);

namespace PHPMate\Domain\GitProvider;

use PHPMate\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use PHPMate\Domain\GitProvider\Value\Commit;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;

interface GetLastCommitOfDefaultBranch
{
    /**
     * @throws GitProviderCommunicationFailed
     */
    public function getLastCommitOfDefaultBranch(RemoteGitRepository $gitRepository): Commit;
}
