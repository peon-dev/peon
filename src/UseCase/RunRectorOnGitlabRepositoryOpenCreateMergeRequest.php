<?php
declare (strict_types=1);

namespace Acme\UseCase;

use Acme\Domain\Application\Procedures\Composer\InstallComposer;
use Acme\Domain\Application\Procedures\Rector\RunRector;
use Acme\Domain\Gitlab\CheckoutGitlabRepository;
use Acme\Domain\Gitlab\OpenGitlabMergeRequest;

final class RunRectorOnGitlabRepositoryOpenCreateMergeRequest
{
    private CheckoutGitlabRepository $checkoutGitlabRepository;

    private InstallComposer $installComposer;

    private RunRector $runRector;

    private OpenGitlabMergeRequest $openMergeRequest;


    public function __construct(
        CheckoutGitlabRepository $checkoutGitlabRepository,
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


    public function __invoke(string $gitlabRepositoryName): void
    {
        $application = ($this->checkoutGitlabRepository)($gitlabRepositoryName);

        ($this->installComposer)($application);
        ($this->runRector)($application);

        // if changes
            // git checkout new branch + commit + push
            ($this->openMergeRequest)($application);
        // endif
    }
}
