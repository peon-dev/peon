<?php

declare(strict_types=1);

use Acme\Domain\Application\Application;
use Acme\Domain\Application\Procedures\InstallComposer;
use Acme\Domain\Application\Procedures\RunRector;
use Acme\Domain\Gitlab\GitlabApplication;
use Acme\Domain\Gitlab\OpenGitlabMergeRequest;
use Acme\Infrastructure\Shell\Gitlab\ShellCheckoutGitlabRepository;
use Acme\UseCase\RunRectorOnGitlabRepositoryOpenCreateMergeRequest;

require_once __DIR__ . '/../src/Infrastructure/bootstrap.php';

$checkoutGitlabRepository = new ShellCheckoutGitlabRepository();

$installComposer = new class implements InstallComposer {
    public function __invoke(Application $application): void { }
};

$runRector = new class implements RunRector {
    public function __invoke(Application $application): void { }
};

$openGitlabMergeRequest = new class implements OpenGitlabMergeRequest {
    public function __invoke(GitlabApplication $gitlabApplication): void { }
};

(new RunRectorOnGitlabRepositoryOpenCreateMergeRequest(
    $checkoutGitlabRepository,
    $installComposer,
    $runRector,
    $openGitlabMergeRequest
))('');
