<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\Cookbook\Event\RecipeDisabled;
use PHPMate\Domain\Project\Event\ProjectDeleted;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\GitProvider\Value\GitRepositoryAuthentication;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use PHPMate\Packages\MessageBus\Event\EventBus;
use PHPMate\Tests\DataFixtures\DataFixtures;
use PHPMate\UseCase\DeleteProject;
use PHPMate\UseCase\DeleteProjectHandler;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

final class DeleteProjectHandlerTest extends TestCase
{
    public function testProjectCanBeDeleted(): void
    {
        $remoteGitRepository = DataFixtures::createRemoteGitRepository();
        $projectsCollection = new InMemoryProjectsCollection();
        $projectId = new ProjectId('1');


        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::once())
            ->method('dispatch')
            ->with(new IsInstanceOf(ProjectDeleted::class));

        $projectsCollection->save(
            new Project($projectId, $remoteGitRepository)
        );

        self::assertCount(1, $projectsCollection->all());

        $handler = new DeleteProjectHandler($projectsCollection, $eventBusSpy);

        $handler->__invoke(new DeleteProject($projectId));

        self::assertCount(0, $projectsCollection->all());
    }


    public function testNonExistingProjectCanNotBeDeleted(): void
    {
        $this->expectException(ProjectNotFound::class);

        $dummyEventBus = $this->createMock(EventBus::class);
        $projectsCollection = new InMemoryProjectsCollection();

        $handler = new DeleteProjectHandler($projectsCollection, $dummyEventBus);
        $handler->__invoke(
            new DeleteProject(new ProjectId(''))
        );
    }
}
