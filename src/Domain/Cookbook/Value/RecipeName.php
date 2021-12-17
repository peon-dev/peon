<?php

declare(strict_types=1);

namespace PHPMate\Domain\Cookbook\Value;

enum RecipeName: string
{
    case UNUSED_PRIVATE_METHODS = 'unused-private-methods';
    case TYPED_PROPERTIES = 'typed-properties';
    case SWITCH_TO_MATCH = 'switch-to-match';
}
