<?php

declare(strict_types=1);

namespace PHPMate\Domain\PhpApplication;

use PHPMate\Domain\Tools\Composer\Composer;
use PHPMate\Domain\Tools\Composer\ComposerCommandFailed;
use PHPMate\Domain\Tools\Composer\ComposerEnvironment;
use PHPMate\Domain\Project\ProjectNotFound;

final class BuildApplication // TODO: better naming
{
    public function __construct(
        private Composer $composer,
    ) {}


    /**
     * @throws ComposerCommandFailed
     */
    public function build(string $applicationDirectory): void
    {
        // TODO: build application using buildpacks instead
        // TODO: env should be dynamic
        $this->composer->install($applicationDirectory, new ComposerEnvironment());
    }
}
