<?php

declare(strict_types=1);

namespace Peon\Tests\End2End;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\GitProvider\GitProvider;
use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\Project\Project;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\User\Value\UserId;
use Peon\Infrastructure\GitProvider\GitLab;
use Peon\Tests\DataFixtures\DataFixtures;
use Peon\UseCase\EnableRecipeWithBaselineForProject;
use Peon\UseCase\EnableRecipeWithBaselineForProjectHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class GitLabEnableRecipeWithBaselineTest extends KernelTestCase
{
    private const PROJECT_ID = '00000000-0000-0000-0000-000000000000';

    private RemoteGitRepository $remoteGitRepository;
    private EnableRecipeWithBaselineForProjectHandler $useCase;
    private ProjectsCollection $projectsCollection;


    protected function setUp(): void
    {
        // Populate values in `.env.test.local`
        $repositoryUri = $_SERVER['TEST_GITLAB_REPOSITORY'];
        $username = $_SERVER['TEST_GITLAB_USERNAME'];
        $personalAccessToken = $_SERVER['TEST_GITLAB_PERSONAL_ACCESS_TOKEN'];

        $container = self::getContainer();

        // Force to use GitHub provider over default DummyProvider for tests
        $gitLab = $container->get(GitLab::class);
        if ($container->initialized(GitProvider::class) === false) {
            $container->set(GitProvider::class, $gitLab);
        }

        $this->useCase = $container->get(EnableRecipeWithBaselineForProjectHandler::class);
        $this->projectsCollection = $container->get(ProjectsCollection::class);

        $authentication = new GitRepositoryAuthentication($username, $personalAccessToken);
        $this->remoteGitRepository = new RemoteGitRepository($repositoryUri, $authentication);

        $this->prepareData();
    }


    public function testRecipeWillBeEnabledWithLatestCommitHash(): void
    {
        $project = $this->projectsCollection->get(new ProjectId(self::PROJECT_ID));
        self::assertCount(0, $project->enabledRecipes);

        $this->useCase->__invoke(
            new EnableRecipeWithBaselineForProject(
                RecipeName::UNUSED_PRIVATE_METHODS,
                new ProjectId(self::PROJECT_ID),
            ),
        );

        $project = $this->projectsCollection->get(new ProjectId(self::PROJECT_ID));
        self::assertCount(1, $project->enabledRecipes);
        self::assertNotNull($project->getEnabledRecipe(RecipeName::UNUSED_PRIVATE_METHODS)->baselineHash);
    }


    private function prepareData(): void
    {
        $projectId = new ProjectId(self::PROJECT_ID);
        $ownerUserId = new UserId(DataFixtures::USER_1_ID);
        $project = new Project(
            $projectId,
            $this->remoteGitRepository,
            $ownerUserId,
        );

        $this->projectsCollection->save($project);
    }
}
