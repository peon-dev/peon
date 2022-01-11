<?php

declare(strict_types=1);

namespace Peon\Domain\GitProvider;

use Peon\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;

interface CheckWriteAccessToRemoteRepository
{
    /**
     * @throws GitProviderCommunicationFailed
     */
    public function hasWriteAccess(RemoteGitRepository $gitRepository): bool;
}
