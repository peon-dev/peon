<?php

declare(strict_types=1);

namespace Peon\Domain\PhpApplication\Value;

final class BuildConfiguration
{
    public const DEFAULT_SKIP_COMPOSER_INSTALL_VALUE = false;


    public function __construct(
        public readonly bool $skipComposerInstall,
    ) {}


    public static function createDefault(): self
    {
        return new self(self::DEFAULT_SKIP_COMPOSER_INSTALL_VALUE);
    }
}
