<?php

declare(strict_types=1);

namespace Peon\Domain\PhpApplication;

use Peon\Domain\Job\Job;
use Peon\Domain\PhpApplication\Value\BuildConfiguration;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Tools\Composer\Composer;

class BuildApplication // TODO: better naming
{
    public function __construct(
        private Composer $composer,
    ) {}


    /**
     * @throws ProcessFailed
     */
    public function build(Job $job, string $applicationDirectory, BuildConfiguration $configuration): void
    {
        // TODO: build application using buildpacks instead
        if ($configuration->skipComposerInstall === false) {
            $this->composer->install($job, $applicationDirectory);
        }
    }
}
