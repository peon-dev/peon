<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\ProjectNotFound;
use PHPMate\Domain\Tools\Git\GitRepositoryAuthentication;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use PHPMate\Tests\DataFixtures\DataFixtures;
use PHPMate\UseCase\DeleteProject;
use PHPMate\UseCase\DeleteProjectHandler;
use PHPUnit\Framework\TestCase;

final class DeleteProjectHandlerTest extends TestCase
{
    public function testProjectCanBeDeleted(): void
    {
        $remoteGitRepository = DataFixtures::createRemoteGitRepository();
        $projectsCollection = new InMemoryProjectsCollection();
        $projectId = new ProjectId('1');

        $projectsCollection->save(
            new Project($projectId, $remoteGitRepository)
        );

        self::assertCount(1, $projectsCollection->all());

        $handler = new DeleteProjectHandler($projectsCollection);

        $handler->__invoke(new DeleteProject($projectId));

        self::assertCount(0, $projectsCollection->all());
    }


    public function testNonExistingProjectCanNotBeDeleted(): void
    {
        $this->expectException(ProjectNotFound::class);

        $projectsCollection = new InMemoryProjectsCollection();

        $handler = new DeleteProjectHandler($projectsCollection);
        $handler->__invoke(
            new DeleteProject(new ProjectId(''))
        );
    }
}
