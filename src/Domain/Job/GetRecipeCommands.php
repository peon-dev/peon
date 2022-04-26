<?php

declare(strict_types=1);

namespace Peon\Domain\Job;

use Peon\Domain\Application\Value\TemporaryApplication;
use Peon\Domain\Project\Value\EnabledRecipe;

interface GetRecipeCommands
{
    /**
     * @return array<string>
     */
    public function forApplication(EnabledRecipe $enabledRecipe, TemporaryApplication $application): array;
}
