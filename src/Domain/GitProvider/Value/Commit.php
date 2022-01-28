<?php

declare(strict_types=1);

namespace Peon\Domain\GitProvider\Value;

final class Commit
{
    public function __construct(
        public readonly string $hash,
    ) {}
}
