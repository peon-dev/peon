<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\GitProvider;

use PHPMate\Domain\GitProvider\CheckWriteAccessToRemoteRepository;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;

final class DummyCheckWriteAccessToRemoteRepository implements CheckWriteAccessToRemoteRepository
{
    public function hasWriteAccess(RemoteGitRepository $gitRepository): bool
    {
        return true;
    }
}
