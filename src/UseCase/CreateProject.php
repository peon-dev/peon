<?php

declare(strict_types=1);

namespace Peon\UseCase;

use JetBrains\PhpStorm\Immutable;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;

#[Immutable]
final class CreateProject
{
    public function __construct(
        public RemoteGitRepository $remoteGitRepository
    ) {}
}
