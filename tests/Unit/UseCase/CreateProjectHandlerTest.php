<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\Tools\Git\GitRepositoryAuthentication;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use PHPMate\UseCase\CreateProject;
use PHPMate\UseCase\CreateProjectHandler;
use PHPUnit\Framework\TestCase;

final class CreateProjectHandlerTest extends TestCase
{
    public function testProjectCanBeCreated(): void
    {
        $projectsCollection = new InMemoryProjectsCollection();

        self::assertCount(0, $projectsCollection->all());

        $handler = new CreateProjectHandler($projectsCollection);
        $handler->__invoke(
            new CreateProject(
                new RemoteGitRepository(
                    'https://gitlab.com/phpmate-dogfood/rector.git',
                    GitRepositoryAuthentication::fromPersonalAccessToken('PAT')
                )
            )
        );

        self::assertCount(1, $projectsCollection->all());
    }
}