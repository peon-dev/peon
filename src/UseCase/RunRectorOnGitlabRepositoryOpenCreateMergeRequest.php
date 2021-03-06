<?php
declare (strict_types=1);

namespace Acme\UseCase;

use Acme\Domain\Application\Application;
use Acme\Domain\Application\Procedures\Composer\InstallComposer;
use Acme\Domain\Application\Procedures\Rector\RunRector;
use Acme\Domain\Gitlab\CloneGitlabRepository;
use Acme\Domain\Gitlab\GitlabRepositoryCredentials;
use Acme\Domain\Gitlab\OpenGitlabMergeRequest;

final class RunRectorOnGitlabRepositoryOpenCreateMergeRequest
{
    private CloneGitlabRepository $checkoutGitlabRepository;

    private InstallComposer $installComposer;

    private RunRector $runRector;

    private OpenGitlabMergeRequest $openMergeRequest;


    public function __construct(
        CloneGitlabRepository $checkoutGitlabRepository,
        InstallComposer $installComposer,
        RunRector $runRector,
        OpenGitlabMergeRequest $openMergeRequest
    )
    {
        $this->checkoutGitlabRepository = $checkoutGitlabRepository;
        $this->installComposer = $installComposer;
        $this->runRector = $runRector;
        $this->openMergeRequest = $openMergeRequest;
    }


    public function __invoke(string $remoteUri, string $username, string $accessToken): void
    {
        try {
            $gitRepository = ($this->cloneGitlabRepository)($remoteUri, $username, $accessToken);
        } catch (CloneDestinationDirectoryNotEmpty $exception) {
            $gitRepository = GitRepository::createFromDirectory($exception->getDirectory());
            $gitRepository->fetchRemoteChanges();
            $gitRepository->checkoutGitReference('master', $checkoutGitReferenceExecutor);
            $gitRepository->checkoutGitReference('master', $checkoutGitReferenceExecutor);
        }

        $application = Application::createFromDirectory();

        $application->installComposer($this->installComposer);
        $application->runRector($this->runRector);

        if ($gitRepository->hasUncommittedChanges()) {
            $gitRepository->checkoutNewGitBranch('improvements');
            $gitRepository->commitAndPushChanges();
            $gitRepository->openMergeRequest();
        }
    }
}
