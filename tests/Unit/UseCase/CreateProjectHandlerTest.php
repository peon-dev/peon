<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\GitProvider\CheckWriteAccessToRemoteRepository;
use PHPMate\Domain\GitProvider\InsufficientAccessToRemoteRepository;
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
        $checkWriteAccessToRemoteRepository = $this->createCheckWriteAccessToRemoteRepository(true);

        self::assertCount(0, $projectsCollection->all());

        $handler = new CreateProjectHandler($projectsCollection, $checkWriteAccessToRemoteRepository);
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


    public function testProjectNeedsGitRepositoryWriteAccess(): void
    {
        $this->expectException(InsufficientAccessToRemoteRepository::class);

        $projectsCollection = new InMemoryProjectsCollection();
        $checkWriteAccessToRemoteRepository = $this->createCheckWriteAccessToRemoteRepository(false);

        $handler = new CreateProjectHandler($projectsCollection, $checkWriteAccessToRemoteRepository);
        $handler->__invoke(
            new CreateProject(
                new RemoteGitRepository(
                    'https://gitlab.com/phpmate-dogfood/rector.git',
                    GitRepositoryAuthentication::fromPersonalAccessToken('PAT')
                )
            )
        );
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
