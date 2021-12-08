<?php

declare(strict_types=1);

namespace PHPMate\Domain\Tools\Rector\Value;

final class RectorProcessCommandConfiguration
{
    /**
     * @param null|array<string> $paths
     */
    public function __construct(
        public readonly string|null $autoloadFile = null,
        public readonly string|null $workingDirectory = null,
        public readonly string|null $config = null,
        public readonly array|null $paths = null,
    ) {}
}
