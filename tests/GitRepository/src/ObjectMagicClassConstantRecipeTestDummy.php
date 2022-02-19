<?php

declare(strict_types=1);

namespace PeonDogFood;

final class ObjectMagicClassConstantRecipeTestDummy
{
    public function getClass(): string
    {
        $getClass = get_class($this);

        return $getClass;
    }
}
