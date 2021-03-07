<?php
declare (strict_types=1);

namespace Acme\Tests\UseCase;

use Acme\Domain\Application\Application;
use Acme\Domain\Application\Procedures\Composer\InstallComposer;
use Acme\Domain\Application\Procedures\Rector\RunRector;
use Acme\Domain\Gitlab\CloneGitlabRepository;
use Acme\Domain\Gitlab\OpenGitlabMergeRequest;
use Acme\UseCase\RunRectorOnGitlabRepositoryOpenCreateMergeRequestUseCase;
use PHPUnit\Framework\TestCase;

class RunRectorOnGitlabRepositoryAndOpenMergeRequestTest extends TestCase
{

    public function test__invoke(): void
    {
        $this->expectNotToPerformAssertions();

        $cloneGitlabRepository = $this->getCloneGitlabRepository();
        $installComposer = $this->getInstallComposer();
        $runRector = $this->getRunRector();
        $OpenGitlabMergeRequest = $this->getOpenGitlabMergeRequest();

        $useCase = new RunRectorOnGitlabRepositoryOpenCreateMergeRequestUseCase(
            $cloneGitlabRepository,
            $installComposer,
            $runRector,
            $OpenGitlabMergeRequest
        );

        $useCase->__invoke('acme/foo');

        // $this->assertRepositoryHasBeenCloned();
        // $this->assertRectorHasBeenRun();
        // $this->assertComposerHasBeenInstalled();
        // $this->assertMergeRequestHasBeenOpened();
    }




    private function getCloneGitlabRepository(): CloneGitlabRepository
    {
        return new class () implements CloneGitlabRepository {
            public function __invoke(string $repositoryName): Application
            {
                return new Application('');
            }
        };
    }


    private function getInstallComposer(): InstallComposer
    {
        return new class implements InstallComposer {
            public function __invoke(Application $application): void { }
        };
    }


    private function getRunRector(): RunRector
    {
        return new class implements RunRector {
            public function __invoke(Application $application): void { }
        };
    }


    private function getOpenGitlabMergeRequest(): OpenGitlabMergeRequest
    {
        return new class implements OpenGitlabMergeRequest {
            public function __invoke(Application $application): void { }
        };
    }
}
