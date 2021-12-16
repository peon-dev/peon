<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\GitProvider;

use PHPMate\Domain\GitProvider\GetLastCommitOfDefaultBranch;
use PHPMate\Domain\GitProvider\Value\Commit;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;

final class DummyGetLastCommitOfDefaultBranch implements GetLastCommitOfDefaultBranch
{
    public function getLastCommitOfDefaultBranch(RemoteGitRepository $gitRepository): Commit
    {
        return new Commit('12345');
    }
}
