<?php

declare(strict_types=1);

namespace Peon\Domain\Tools\Composer\Value;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class ComposerEnvironment
{
    public const AUTH = 'COMPOSER_AUTH';


    public function __construct(
        public ?string $auth = null
    ) {}
}
