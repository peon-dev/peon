<?php

declare(strict_types=1);

namespace Peon\Tests\End2End;

use Peon\Domain\GitProvider\Exception\InsufficientAccessToRemoteRepository;
use Peon\Domain\GitProvider\GitProvider;
use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\User\Value\UserId;
use Peon\Infrastructure\GitProvider\GitLab;
use Peon\UseCase\CreateProject;
use Peon\UseCase\CreateProjectHandler;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class GitLabCreateProjectTest extends KernelTestCase
{
    private const USER_ID = Uuid::NIL;


    private RemoteGitRepository $remoteGitRepository;
    private CreateProjectHandler $useCase;
    private ProjectsCollection $projectsCollection;


    protected function setUp(): void
    {
        // Populate values in `.env.test.local`
        $repositoryUri = $_SERVER['TEST_GITLAB_REPOSITORY'];
        $username = $_SERVER['TEST_GITLAB_USERNAME'];
        $personalAccessToken = $_SERVER['TEST_GITLAB_PERSONAL_ACCESS_TOKEN'];

        $container = self::getContainer();

        // Force to use Gitlab provider over default DummyProvider for tests
        $gitLab = $container->get(GitLab::class);
        if ($container->initialized(GitProvider::class) === false) {
            $container->set(GitProvider::class, $gitLab);
        }

        $this->useCase = $container->get(CreateProjectHandler::class);
        $this->projectsCollection = $container->get(ProjectsCollection::class);

        $authentication = new GitRepositoryAuthentication($username, $personalAccessToken);
        $this->remoteGitRepository = new RemoteGitRepository($repositoryUri, $authentication);
    }


    public function testUserHasWritePermission(): void
    {
        $projectsBeforeUseCaseCount = count($this->projectsCollection->all());

        $this->useCase->__invoke(
            new CreateProject(
                $this->remoteGitRepository,
                new UserId(self::USER_ID)
            ),
        );

        // Obviously, 1 more than before :-)
        $this->assertCount($projectsBeforeUseCaseCount + 1, $this->projectsCollection->all());
    }


    public function testUserHasNotWritePermission(): void
    {
        // This must be some repository we do not have write access!
        // Let's pick something :-)
        $authentication = new GitRepositoryAuthentication(
            $_SERVER['TEST_GITLAB_USERNAME'],
            $_SERVER['TEST_GITLAB_PERSONAL_ACCESS_TOKEN'],
        );
        $remoteGitRepository = new RemoteGitRepository('https://gitlab.com/gitlab-org/gitlab-runner.git', $authentication);

        $this->expectException(InsufficientAccessToRemoteRepository::class);

        $this->useCase->__invoke(
            new CreateProject(
                $remoteGitRepository,
                new UserId(self::USER_ID)
            ),
        );
    }
}
