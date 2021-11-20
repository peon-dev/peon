<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;

#[Immutable]
final class CreateProject
{
    public function __construct(
        public RemoteGitRepository $remoteGitRepository
    ) {}
}
