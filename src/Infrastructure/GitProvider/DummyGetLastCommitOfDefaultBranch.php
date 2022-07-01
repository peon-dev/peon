<?php

declare(strict_types=1);

namespace Peon\Infrastructure\GitProvider;

use Peon\Domain\GitProvider\Value\Commit;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;

final class DummyGetLastCommitOfDefaultBranch
{
    public function forRemoteGitRepository(RemoteGitRepository $gitRepository): Commit
    {
        return new Commit('12345');
    }
}
