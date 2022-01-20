<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Peon\Domain\PhpApplication\Value\BuildConfiguration;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\Project;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use Peon\UseCase\ConfigureProject;
use Peon\UseCase\ConfigureProjectHandler;
use PHPUnit\Framework\TestCase;

class ConfigureProjectHandlerTest extends TestCase
{
    public function testNonExistingProjectCanNotBeConfigured(): void
    {
        $this->expectException(ProjectNotFound::class);

        $projectsCollection = new InMemoryProjectsCollection();

        $handler = new ConfigureProjectHandler($projectsCollection);
        $handler->__invoke(
            new ConfigureProject(
                new ProjectId(''),
                BuildConfiguration::createDefault(),
            )
        );
    }


    public function testProjectCanBeDeleted(): void
    {
        $configuration = BuildConfiguration::createDefault();

        $project = $this->createMock(Project::class);
        $project->expects(self::once())
            ->method('configureBuild')
            ->with($configuration);

        $projectsCollection = $this->createMock(ProjectsCollection::class);
        $projectsCollection->expects(self::once())
            ->method('get')
            ->willReturn($project);

        $projectsCollection->expects(self::once())
            ->method('save')
            ->with($project);

        $handler = new ConfigureProjectHandler($projectsCollection);
        $handler->__invoke(new ConfigureProject(new ProjectId(''), $configuration));
    }
}
