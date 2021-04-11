<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Gitlab\GitlabRepository;
use PHPMate\Domain\Rector\RectorProcessCommandConfiguration;

#[Immutable]
final class RunRectorOnGitlabRepository
{
    /**
     * @param array<RectorProcessCommandConfiguration> $processCommandConfigurations
     */
    public function __construct(
        public GitlabRepository $gitlabRepository,
        public array $processCommandConfigurations = []
    ) {
        if ($processCommandConfigurations === []) {
            $this->processCommandConfigurations = [
                new RectorProcessCommandConfiguration()
            ];
        }
    }
}
