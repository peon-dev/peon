<?php

declare(strict_types=1);

namespace Peon\Domain\PhpApplication;

use Peon\Domain\Application\Value\TemporaryApplication;
use Peon\Domain\PhpApplication\Value\PhpApplicationBuildConfiguration;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Tools\Composer\Composer;

class BuildPhpApplication
{
    public function __construct(
        private Composer $composer,
    ) {}


    /**
     * @throws ProcessFailed
     */
    public function build(TemporaryApplication $application, PhpApplicationBuildConfiguration $configuration): void
    {
        // TODO: build application using buildpacks instead
        if ($configuration->skipComposerInstall === false) {
            $this->composer->install($application);
        }
    }
}
