<?php

declare(strict_types=1);

namespace PHPMate\Domain\Docker;

use PHPMate\Domain\FileSystem\WorkingDirectory;

/**
 * @TODO implementation
 *
 * Ideas:
 *   - Prebuilt images with different multiple versions and as many php extensions as possible
 *   - Specific image provided by customer
 *   - Buildpacks
 *   - Dockerfile + provided instructions how to build
 */
interface DockerImageBuilder
{
    /**
     * TODO
     */
    public function build(WorkingDirectory $workingDirectory): string;
}
