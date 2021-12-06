<?php

declare(strict_types=1);

namespace PHPMate\Domain\GitProvider\Value;

final class MergeRequest
{
    public function __construct(
        public readonly string $url,
    ){}
}
