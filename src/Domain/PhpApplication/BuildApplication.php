<?php

declare(strict_types=1);

namespace Peon\Domain\PhpApplication;

use Peon\Domain\Tools\Composer\Composer;
use Peon\Domain\Tools\Composer\Exception\ComposerCommandFailed;
use Peon\Domain\Tools\Composer\Value\ComposerEnvironment;

class BuildApplication // TODO: better naming
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
