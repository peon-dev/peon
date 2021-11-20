<?php

declare(strict_types=1);

namespace PHPMate\Domain\PhpApplication;

use PHPMate\Domain\Tools\Composer\Composer;
use PHPMate\Domain\Tools\Composer\Exception\ComposerCommandFailed;
use PHPMate\Domain\Tools\Composer\Value\ComposerEnvironment;

final class BuildApplication // TODO: better naming
{
    public function __construct(
        private Composer $composer,
    ) {}


    /**
     * @throws \PHPMate\Domain\Tools\Composer\Exception\ComposerCommandFailed
     */
    public function build(string $applicationDirectory): void
    {
        // TODO: build application using buildpacks instead
        // TODO: env should be dynamic
        $this->composer->install($applicationDirectory, new ComposerEnvironment());
    }
}
