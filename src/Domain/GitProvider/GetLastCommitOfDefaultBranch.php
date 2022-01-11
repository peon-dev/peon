<?php

declare(strict_types=1);

namespace Peon\Domain\GitProvider;

use Peon\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use Peon\Domain\GitProvider\Value\Commit;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;

interface GetLastCommitOfDefaultBranch
{
    /**
     * @throws GitProviderCommunicationFailed
     */
    public function getLastCommitOfDefaultBranch(RemoteGitRepository $gitRepository): Commit;
}
