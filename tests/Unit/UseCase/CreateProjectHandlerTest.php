<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\GitProvider\CheckWriteAccessToRemoteRepository;
use PHPMate\Domain\GitProvider\Exception\InsufficientAccessToRemoteRepository;
use PHPMate\Domain\GitProvider\Value\GitRepositoryAuthentication;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;
use PHPMate\Domain\Project\Event\ProjectAdded;
use PHPMate\Domain\Task\Event\TaskAdded;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use PHPMate\Packages\MessageBus\Event\EventBus;
use PHPMate\Tests\DataFixtures\DataFixtures;
use PHPMate\UseCase\CreateProject;
use PHPMate\UseCase\CreateProjectHandler;
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
            ->with(new IsInstanceOf(ProjectAdded::class));

        self::assertCount(0, $projectsCollection->all());

        $handler = new CreateProjectHandler($projectsCollection, $checkWriteAccessToRemoteRepository, $eventBusSpy);
        $remoteGitRepository = DataFixtures::createRemoteGitRepository();

        $handler->__invoke(new CreateProject($remoteGitRepository));

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

        $handler->__invoke(new CreateProject($remoteGitRepository));
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
