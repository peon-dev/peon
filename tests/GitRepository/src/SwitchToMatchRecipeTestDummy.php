<?php

declare(strict_types=1);

namespace PeonDogFood;

final class SwitchToMatchRecipeTestDummy
{
    public function whatIsThisFood(string $food = 'cake'): string
    {
        switch($food) {
            case 'vegetable':
                return 'This food is healthy';
            case 'fruit':
                return 'This food is juicy';
            default:
                return 'This food is yummy';
        }
    }
}
