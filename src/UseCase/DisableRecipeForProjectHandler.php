<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class DisableRecipeForProjectHandler implements MessageHandlerInterface
{
    public function __invoke(DisableRecipeForProject $command): void
    {
    }
}
