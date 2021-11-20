<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\GitProvider\CheckWriteAccessToRemoteRepository;
use PHPMate\Domain\GitProvider\Exception\InsufficientAccessToRemoteRepository;
use PHPMate\Domain\Tools\Git\Value\GitRepositoryAuthentication;
use PHPMate\Domain\Tools\Git\Value\RemoteGitRepository;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use PHPMate\Tests\DataFixtures\DataFixtures;
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
        $remoteGitRepository = DataFixtures::createRemoteGitRepository();

        $handler->__invoke(new CreateProject($remoteGitRepository));

        self::assertCount(1, $projectsCollection->all());
    }


    public function testProjectNeedsGitRepositoryWriteAccess(): void
    {
        $this->expectException(InsufficientAccessToRemoteRepository::class);

        $projectsCollection = new InMemoryProjectsCollection();
        $checkWriteAccessToRemoteRepository = $this->createCheckWriteAccessToRemoteRepository(false);

        $handler = new CreateProjectHandler($projectsCollection, $checkWriteAccessToRemoteRepository);
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
