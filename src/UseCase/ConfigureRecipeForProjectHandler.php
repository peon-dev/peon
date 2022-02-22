<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Packages\MessageBus\Command\CommandHandlerInterface;

final class ConfigureRecipeForProjectHandler implements CommandHandlerInterface
{
    public function __invoke(ConfigureRecipeForProject $command): void
    {
        // Change configuration :-)
        // Save
    }
}
