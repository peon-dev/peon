<?php

declare(strict_types=1);

namespace PHPMate\Worker\Domain\Composer;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class ComposerEnvironment
{
    public const AUTH = 'COMPOSER_AUTH';


    public function __construct(
        public ?string $auth = null
    ) {}
}
