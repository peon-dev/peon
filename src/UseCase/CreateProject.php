<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;

#[Immutable]
final class CreateProject
{
    public function __construct(
        public RemoteGitRepository $remoteGitRepository
    ) {}
}
