<?php

declare(strict_types=1);

namespace PHPMate\Worker\Domain\Docker;

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
    public function build(string $directory): string;
}
