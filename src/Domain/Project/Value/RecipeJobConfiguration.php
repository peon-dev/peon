<?php

declare(strict_types=1);

namespace Peon\Domain\Project\Value;

final class RecipeJobConfiguration
{
    public const DEFAULT_MERGE_AUTOMATICALLY_VALUE = false;
    public const DEFAULT_AFTER_SCRIPT_VALUE = '';




    public function __construct(
        public bool $mergeAutomatically,
        public string $afterScript = self::DEFAULT_AFTER_SCRIPT_VALUE,
    ) {
    }


    public static function createDefault(): self
    {
        return new self(
            self::DEFAULT_MERGE_AUTOMATICALLY_VALUE,
            self::DEFAULT_AFTER_SCRIPT_VALUE,
        );
    }
}
