<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Peon\Domain\Cookbook\Event\RecipeDisabled;
use Peon\Domain\Project\Event\ProjectDeleted;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Project\Project;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use Peon\Packages\MessageBus\Event\EventBus;
use Peon\Tests\DataFixtures\DataFixtures;
use Peon\UseCase\DeleteProject;
use Peon\UseCase\DeleteProjectHandler;
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
