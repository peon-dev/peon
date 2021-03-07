<?php

declare(strict_types=1);

use Acme\Domain\Application\Application;
use Acme\Domain\Application\Procedures\Rector\RunRector;
use Acme\Domain\Gitlab\GitlabApplication;
use Acme\Domain\Gitlab\OpenGitlabMergeRequest;
use Acme\Infrastructure\Shell\Application\Procedures\ShellInstallComposer;
use Acme\Infrastructure\Shell\Gitlab\ShellCloneGitlabRepository;
use Acme\UseCase\RunRectorOnGitlabRepositoryOpenCreateMergeRequestUseCase;

require_once __DIR__ . '/../src/Infrastructure/bootstrap.php';

$checkoutGitlabRepository = new ShellCloneGitlabRepository(
    __DIR__ . '/../../rectorbot-repositories/gitlab'
);

$installComposer = new ShellInstallComposer();

$runRector = new class implements RunRector {
    public function __invoke(Application $application): void { }
};

$openGitlabMergeRequest = new class implements OpenGitlabMergeRequest {
    public function __invoke(Application $application): void { }
};

$repositoryName = $argv[1] ?? throw new InvalidArgumentException('Missing repository name CLI parameter');

(new RunRectorOnGitlabRepositoryOpenCreateMergeRequestUseCase(
    $checkoutGitlabRepository,
    $installComposer,
    $runRector,
    $openGitlabMergeRequest
))($repositoryName);
