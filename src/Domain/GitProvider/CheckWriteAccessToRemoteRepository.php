<?php

declare(strict_types=1);

namespace PHPMate\Domain\GitProvider;

use PHPMate\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;

interface CheckWriteAccessToRemoteRepository
{
    /**
     * @throws GitProviderCommunicationFailed
     */
    public function hasWriteAccess(RemoteGitRepository $gitRepository): bool;
}
