<?php

declare(strict_types=1);

namespace PHPMate\Worker\UseCase;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Worker\Domain\Composer\ComposerEnvironment;
use PHPMate\Worker\Domain\Gitlab\GitlabRepository;
use PHPMate\Worker\Domain\Rector\RectorProcessCommandConfiguration;

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

        $this->composerEnvironment = $composerEnvironment ?? new ComposerEnvironment();
    }
}
