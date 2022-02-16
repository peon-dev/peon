<?php

declare(strict_types=1);

namespace Peon\Domain\Cookbook\Value;

enum RecipeName: string
{
    case UNUSED_PRIVATE_METHODS = 'unused-private-methods';
    case TYPED_PROPERTIES = 'typed-properties';
    case SWITCH_TO_MATCH = 'switch-to-match';
    case OBJECT_MAGIC_CLASS_CONSTANT = 'object-magic-class-constant';
}
