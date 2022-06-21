<?php

declare(strict_types=1);

namespace Peon\Domain\Security;

use Peon\Domain\Project\GetProjectIdentifiers;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Security\Exception\ForbiddenUserAccessToProject;
use Peon\Domain\User\Value\UserId;
use SplObjectStorage;

final class CheckUserAccess
{
    /**
     * @var SplObjectStorage<UserId, array<ProjectId>>
     */
    private SplObjectStorage $projectsOfUsers;


    public function __construct(
        private readonly GetProjectIdentifiers $getProjectIdentifiers,
    ) {
        $this->projectsOfUsers = new SplObjectStorage();
    }


    /**
     * @throws ForbiddenUserAccessToProject
     */
    public function toProject(UserId $userId, ProjectId $projectId): void
    {
        foreach ($this->getProjectIdentifiersUserHasAccessTo($userId) as $accessibleProjectId) {
            if ($projectId->isSameAs($accessibleProjectId)) {
                return;
            }
        }

        throw new ForbiddenUserAccessToProject();
    }


    /**
     * @return array<ProjectId>
     */
    private function getProjectIdentifiersUserHasAccessTo(UserId $userId): array
    {
        if (!isset($this->projectsOfUsers[$userId])) {
            $this->projectsOfUsers[$userId] = $this->getProjectIdentifiers->ownedByUser($userId);
        }

        return $this->projectsOfUsers[$userId];
    }
}
