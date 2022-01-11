<?php

declare(strict_types=1);

namespace Peon\Domain\GitProvider\Value;

class MergeRequest
{
    public function __construct(
        public readonly string|null $url,
    ){}
}
