<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Peon\Domain\GitProvider\Exception\InsufficientAccessToRemoteRepository;
use Peon\Domain\GitProvider\GitProvider;
use Peon\Domain\Project\Event\ProjectAdded;
use Peon\Domain\User\Value\UserId;
use Peon\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use Peon\Packages\MessageBus\Event\EventBus;
use Peon\Tests\DataFixtures\DataFixtures;
use Peon\UseCase\CreateProject;
use Peon\UseCase\CreateProjectHandler;
use PHPUnit\Framework\TestCase;

final class CreateProjectHandlerTest extends TestCase
{
    public function testProjectCanBeCreated(): void
    {
        $projectsCollection = new InMemoryProjectsCollection();
        $gitProvider = $this->createMock(GitProvider::class);
        $gitProvider->expects(self::once())
            ->method('hasWriteAccessToRepository')
            ->willReturn(true);

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ProjectAdded::class));

        self::assertCount(0, $projectsCollection->all());

        $handler = new CreateProjectHandler($projectsCollection, $gitProvider, $eventBusSpy);
        $remoteGitRepository = DataFixtures::createRemoteGitRepository();
        $ownerUserId = new UserId(DataFixtures::USER_1_ID);

        $handler->__invoke(new CreateProject($remoteGitRepository, $ownerUserId));

        self::assertCount(1, $projectsCollection->all());
    }


    public function testProjectNeedsGitRepositoryWriteAccess(): void
    {
        $this->expectException(InsufficientAccessToRemoteRepository::class);

        $projectsCollection = new InMemoryProjectsCollection();
        $gitProvider = $this->createMock(GitProvider::class);
        $gitProvider->expects(self::once())
            ->method('hasWriteAccessToRepository')
            ->willReturn(false);

        $dummyEventBus = $this->createMock(EventBus::class);

        $handler = new CreateProjectHandler($projectsCollection, $gitProvider, $dummyEventBus);
        $remoteGitRepository = DataFixtures::createRemoteGitRepository();
        $ownerUserId = new UserId(DataFixtures::USER_1_ID);

        $handler->__invoke(new CreateProject($remoteGitRepository, $ownerUserId));
    }
}
