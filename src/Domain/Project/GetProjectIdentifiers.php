<?php

declare(strict_types=1);

namespace Peon\Domain\Project;

use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\User\Value\UserId;

interface GetProjectIdentifiers
{
    /**
     * @return array<ProjectId>
     */
    public function ownedByUser(UserId $userId): array;
}
