<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Gitlab\GitlabRepository;

#[Immutable]
final class RunRectorOnGitlabRepository
{
    public function __construct(
        public GitlabRepository $gitlabRepository
    ) {}
}
