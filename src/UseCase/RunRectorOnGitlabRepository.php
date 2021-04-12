<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Composer\ComposerEnvironment;
use PHPMate\Domain\Gitlab\GitlabRepository;
use PHPMate\Domain\Rector\RectorProcessCommandConfiguration;

#[Immutable]
final class RunRectorOnGitlabRepository
{
    public ComposerEnvironment $composerEnvironment;


    /**
     * @param array<RectorProcessCommandConfiguration> $processCommandConfigurations
     */
    public function __construct(
        public GitlabRepository $gitlabRepository,
        public array $processCommandConfigurations = [],
        ?ComposerEnvironment $composerEnvironment = null
    ) {
        if ($processCommandConfigurations === []) {
            $this->processCommandConfigurations = [
                new RectorProcessCommandConfiguration()
            ];
        }

        if ($composerEnvironment === null) {
            $this->composerEnvironment = new ComposerEnvironment();
        }
    }
}
