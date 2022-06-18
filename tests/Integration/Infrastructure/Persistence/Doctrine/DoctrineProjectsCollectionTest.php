<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Infrastructure\Persistence\Doctrine;

use Peon\Domain\Project\Project;
use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\User\Value\UserId;
use Peon\Infrastructure\Persistence\Doctrine\DoctrineProjectsCollection;
use Peon\Tests\DataFixtures\DataFixtures;
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
        /*
         * We need to set baseline - number of rows already in database, before interacting with it
         * Because we do not have empty database - it is populated with fixtures data
         */
        $baselineCount = count($this->doctrineProjectsCollection->all());

        $projectId = $this->doctrineProjectsCollection->nextIdentity();
        $remoteGitRepository = DataFixtures::createRemoteGitRepository();

        $project = new Project(
            $projectId,
            $remoteGitRepository,
            new UserId(DataFixtures::USER_1_ID),
        );

        $this->doctrineProjectsCollection->save($project);
        self::assertCount($baselineCount + 1, $this->doctrineProjectsCollection->all());

        // Nothing to assert, just make sure record is in database and exception is not thrown
        $this->doctrineProjectsCollection->get($projectId);

        $this->doctrineProjectsCollection->remove($projectId);
        self::assertCount($baselineCount, $this->doctrineProjectsCollection->all());
    }
}
