<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\Security;

use Peon\Domain\Project\GetProjectIdentifiers;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Security\CheckUserAccess;
use Peon\Domain\Security\Exception\ForbiddenUserAccessToProject;
use Peon\Domain\User\Value\UserId;
use PHPUnit\Framework\TestCase;

final class CheckUserAccessTest extends TestCase
{
    public function testUserHasAccessIfProjectIsOwnedByUser(): void
    {
        $userId = new UserId('');
        $projectId1 = new ProjectId('');
        $projectId2 = new ProjectId('');

        $getProjectIdentifiersMock = $this->createMock(GetProjectIdentifiers::class);
        $getProjectIdentifiersMock->expects(self::once())
            ->method('ownedByUser')
            ->with($userId)
            ->willReturn([$projectId1, $projectId2]);

        $checkUserAccess = new CheckUserAccess($getProjectIdentifiersMock);

        $checkUserAccess->toProject($userId, $projectId1);
        $checkUserAccess->toProject($userId, $projectId2);
    }


    public function testUserHasNoAccess(): void
    {
        $userId = new UserId('');

        $getProjectIdentifiersMock = $this->createMock(GetProjectIdentifiers::class);
        $getProjectIdentifiersMock->expects(self::once())
            ->method('ownedByUser')
            ->with($userId)
            ->willReturn([]);

        $checkUserAccess = new CheckUserAccess($getProjectIdentifiersMock);

        $this->expectException(ForbiddenUserAccessToProject::class);

        $checkUserAccess->toProject($userId, new ProjectId(''));
    }
}
