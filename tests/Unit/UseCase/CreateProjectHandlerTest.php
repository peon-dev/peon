<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Peon\Domain\GitProvider\CheckWriteAccessToRemoteRepository;
use Peon\Domain\GitProvider\Exception\InsufficientAccessToRemoteRepository;
use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\Project\Event\ProjectAdded;
use Peon\Domain\Task\Event\TaskAdded;
use Peon\Domain\User\Value\UserId;
use Peon\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use Peon\Packages\MessageBus\Event\EventBus;
use Peon\Tests\DataFixtures\DataFixtures;
use Peon\UseCase\CreateProject;
use Peon\UseCase\CreateProjectHandler;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

final class CreateProjectHandlerTest extends TestCase
{
    public function testProjectCanBeCreated(): void
    {
        $projectsCollection = new InMemoryProjectsCollection();
        $checkWriteAccessToRemoteRepository = $this->createCheckWriteAccessToRemoteRepository(true);

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ProjectAdded::class));

        self::assertCount(0, $projectsCollection->all());

        $handler = new CreateProjectHandler($projectsCollection, $checkWriteAccessToRemoteRepository, $eventBusSpy);
        $remoteGitRepository = DataFixtures::createRemoteGitRepository();
        $ownerUserId = new UserId(DataFixtures::USER_1_ID);

        $handler->__invoke(new CreateProject($remoteGitRepository, $ownerUserId));

        self::assertCount(1, $projectsCollection->all());
    }


    public function testProjectNeedsGitRepositoryWriteAccess(): void
    {
        $this->expectException(InsufficientAccessToRemoteRepository::class);

        $projectsCollection = new InMemoryProjectsCollection();
        $checkWriteAccessToRemoteRepository = $this->createCheckWriteAccessToRemoteRepository(false);

        $dummyEventBus = $this->createMock(EventBus::class);

        $handler = new CreateProjectHandler($projectsCollection, $checkWriteAccessToRemoteRepository, $dummyEventBus);
        $remoteGitRepository = DataFixtures::createRemoteGitRepository();
        $ownerUserId = new UserId(DataFixtures::USER_1_ID);

        $handler->__invoke(new CreateProject($remoteGitRepository, $ownerUserId));
    }


    private function createCheckWriteAccessToRemoteRepository(bool $shouldHaveAccess): CheckWriteAccessToRemoteRepository
    {
        return new class ($shouldHaveAccess) implements CheckWriteAccessToRemoteRepository {
            public function __construct(private bool $shouldHaveAccess) {}

            public function hasWriteAccess(RemoteGitRepository $gitRepository): bool
            {
                return $this->shouldHaveAccess;
            }
        };
    }
}
