<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\User\Value\UserId;

final class CreateProject
{
    public function __construct(
        public readonly RemoteGitRepository $remoteGitRepository,
        public readonly UserId $ownerUserId,
    ) {}
}
