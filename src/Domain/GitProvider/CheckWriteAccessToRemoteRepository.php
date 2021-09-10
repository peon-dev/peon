<?php

declare(strict_types=1);

namespace PHPMate\Domain\GitProvider;

use PHPMate\Domain\Tools\Git\RemoteGitRepository;

interface CheckWriteAccessToRemoteRepository
{
    /**
     * @throws GitProviderCommunicationFailed
     */
    public function hasWriteAccess(RemoteGitRepository $gitRepository): bool;
}
