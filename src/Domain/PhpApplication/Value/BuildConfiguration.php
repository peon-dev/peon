<?php

declare(strict_types=1);

namespace Peon\Domain\PhpApplication\Value;

final class BuildConfiguration
{
    public function __construct(
        public readonly bool $skipComposerInstall
    ) {}
}
