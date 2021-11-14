<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class EnableRecipeForProjectHandler  implements MessageHandlerInterface
{
    public function __invoke(EnableRecipeForProject $command): void
    {
    }
}
