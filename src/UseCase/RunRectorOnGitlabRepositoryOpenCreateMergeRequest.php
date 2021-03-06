<?php
declare (strict_types=1);

namespace Acme\UseCase;

use Acme\Domain\Application\Procedures\Composer\InstallComposer;
use Acme\Domain\Application\Procedures\Rector\RunRector;
use Acme\Domain\Gitlab\CloneGitlabRepository;
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
        $application = ($this->checkoutGitlabRepository)($remoteUri, $username, $accessToken);
        $remoteHead = 'master';
        $application->checkoutGitReference($remoteHead);
        $application->installComposer();
        $application->runRector();

        if ($application->hasUncommittedChanges()) {
            $localHead = 'improvements';

            $application->checkoutNewGitBranch($localHead);
            $application->commitAndPushChanges();

            ($this->openMergeRequest)($application->getRepositoryName(), $remoteHead, $localHead);
        }
    }
}
