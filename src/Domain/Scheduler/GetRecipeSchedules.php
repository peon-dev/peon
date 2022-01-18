<?php

declare(strict_types=1);

namespace Peon\Domain\Scheduler;

interface GetRecipeSchedules
{
    /**
     * @return array<RecipeJobSchedule>
     */
    public function all(): array;
}
