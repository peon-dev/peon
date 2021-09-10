<?php

declare(strict_types=1);

namespace PHPMate\Domain\GitProvider;

use PHPMate\Domain\Tools\Git\RemoteGitRepository;

interface CheckWriteAccessToRemoteRepository
{
    /**
     * @throws InsufficientAccessToRemoteRepository
     */
    public function check(RemoteGitRepository $remoteGitRepository): void;
}
