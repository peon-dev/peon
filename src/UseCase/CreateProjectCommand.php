<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;

#[Immutable]
final class CreateProjectCommand
{
    public function __construct(
        public RemoteGitRepository $remoteGitRepository
    ) {}
}
