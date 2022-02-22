<?php

declare(strict_types=1);

namespace Peon\Domain\Project\Value;

final class RecipeJobConfiguration
{
    public const DEFAULT_MERGE_AUTOMATICALLY_VALUE = true;


    public function __construct(
        public bool $mergeAutomatically,
    ) {
    }


    public static function createDefault(): self
    {
        return new self(
            self::DEFAULT_MERGE_AUTOMATICALLY_VALUE,
        );
    }
}
