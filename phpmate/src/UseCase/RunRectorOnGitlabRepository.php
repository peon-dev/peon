<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Tools\Composer\ComposerEnvironment;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;
use PHPMate\Domain\Tools\Rector\RectorProcessCommandConfiguration;

#[Immutable]
final class RunRectorOnGitlabRepository
{
    public ComposerEnvironment $composerEnvironment;


    /**
     * @param array<RectorProcessCommandConfiguration> $processCommandConfigurations
     */
    public function __construct(
        public RemoteGitRepository $gitlabRepository,
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
