<?php
declare(strict_types=1);

namespace PHPMate\Tests\Integration\Infrastructure\Persistence\Doctrine;

use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Tools\Git\GitRepositoryAuthentication;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;
use PHPMate\Infrastructure\Persistence\Doctrine\DoctrineProjectsCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineProjectsCollectionTest extends KernelTestCase
{
    private DoctrineProjectsCollection $doctrineProjectsCollection;


    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->doctrineProjectsCollection = $container->get(DoctrineProjectsCollection::class);
    }


    public function testPersistenceWorks(): void
    {
        self::assertCount(0, $this->doctrineProjectsCollection->all());

        $projectId = $this->doctrineProjectsCollection->nextIdentity();

        // TODO: consider using some kind of factory
        $remoteGitRepository = new RemoteGitRepository(
            'https://gitlab.com/phpmate/phpmate.git',
            GitRepositoryAuthentication::fromPersonalAccessToken('PAT')
        );

        // TODO: consider using some kind of factory
        $project = new Project(
            $projectId,
            $remoteGitRepository
        );

        $this->doctrineProjectsCollection->save($project);

        self::assertCount(1, $this->doctrineProjectsCollection->all());

        $this->doctrineProjectsCollection->get($projectId);
        $this->doctrineProjectsCollection->remove($projectId);

        self::assertCount(0, $this->doctrineProjectsCollection->all());
    }
}
