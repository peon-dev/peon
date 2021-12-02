<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class RunRecipeHandler implements MessageHandlerInterface
{
    public function __construct()
    {
    }


    public function __invoke(RunRecipe $command): void
    {

    }
}
