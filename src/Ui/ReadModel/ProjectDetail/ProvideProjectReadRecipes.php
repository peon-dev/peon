<?php

declare(strict_types=1);

namespace PHPMate\Ui\ReadModel\ProjectDetail;

use Doctrine\DBAL\Connection;
use PHPMate\Domain\Project\Value\ProjectId;
use Symplify\EasyHydrator\ArrayToValueObjectHydrator;

final class ProvideProjectReadRecipes
{
    public function __construct(
        private Connection $connection,
        private ArrayToValueObjectHydrator $hydrator,
    ) {}


    /**
     * @return array<ReadRecipe>
     */
    public function provide(ProjectId $projectId): array
    {
        return [];
    }
}
