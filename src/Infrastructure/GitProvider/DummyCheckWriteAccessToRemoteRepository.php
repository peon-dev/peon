<?php

declare(strict_types=1);

namespace Peon\Infrastructure\GitProvider;

use Peon\Domain\GitProvider\Value\RemoteGitRepository;

final class DummyCheckWriteAccessToRemoteRepository
{
    public function hasWriteAccess(RemoteGitRepository $gitRepository): bool
    {
        return true;
    }
}
