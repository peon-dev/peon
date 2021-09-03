<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\ProjectNotFound;
use PHPMate\Domain\Tools\Git\GitRepositoryAuthentication;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use PHPMate\UseCase\DeleteProject;
use PHPMate\UseCase\DeleteProjectUseCase;
use PHPUnit\Framework\TestCase;

final class DeleteProjectUseCaseTest extends TestCase
{
    public function testProjectCanBeDeleted(): void
    {
        $repository = new RemoteGitRepository(
            'https://gitlab.com/phpmate/phpmate.git',
            new GitRepositoryAuthentication('phpmate', 'phpmate')
        );

        $projectsCollection = new InMemoryProjectsCollection();
        $projectId = new ProjectId('1');
        $projectsCollection->save(
            new Project($projectId, $repository)
        );

        self::assertCount(1, $projectsCollection->all());

        $handler = new DeleteProjectUseCase($projectsCollection);
        $handler->__invoke(
            new DeleteProject($projectId)
        );

        self::assertCount(0, $projectsCollection->all());
    }


    public function testNonExistingProjectCanNotBeDeleted(): void
    {
        $this->expectException(ProjectNotFound::class);

        $projectsCollection = new InMemoryProjectsCollection();

        $handler = new DeleteProjectUseCase($projectsCollection);
        $handler->__invoke(
            new DeleteProject(new ProjectId(''))
        );
    }
}