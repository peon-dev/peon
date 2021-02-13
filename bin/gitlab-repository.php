<?php

declare(strict_types=1);

use Acme\Domain\Application\Application;
use Acme\Domain\Application\Procedures\RunRector;
use Acme\Domain\Gitlab\GitlabApplication;
use Acme\Domain\Gitlab\OpenGitlabMergeRequest;
use Acme\Infrastructure\Shell\Application\Procedures\ShellInstallComposer;
use Acme\Infrastructure\Shell\Gitlab\ShellCheckoutGitlabRepository;
use Acme\UseCase\RunRectorOnGitlabRepositoryOpenCreateMergeRequest;

require_once __DIR__ . '/../src/Infrastructure/bootstrap.php';

$checkoutGitlabRepository = new ShellCheckoutGitlabRepository(
    __DIR__ . '/../../rectorbot-repositories/gitlab'
);

$installComposer = new ShellInstallComposer();

$runRector = new class implements RunRector {
    public function __invoke(Application $application): void { }
};

$openGitlabMergeRequest = new class implements OpenGitlabMergeRequest {
    public function __invoke(GitlabApplication $gitlabApplication): void { }
};

$repositoryName = $argv[1] ?? throw new InvalidArgumentException('Missing repository name CLI parameter');

(new RunRectorOnGitlabRepositoryOpenCreateMergeRequest(
    $checkoutGitlabRepository,
    $installComposer,
    $runRector,
    $openGitlabMergeRequest
))($repositoryName);
