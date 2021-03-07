<?php

declare(strict_types=1);

use Acme\Domain\Composer\Composer;
use Acme\Domain\Git\Git;
use Acme\Domain\Gitlab\GitlabApi;
use Acme\Domain\Rector\Rector;
use Acme\UseCase\RunRectorOnGitlabRepositoryOpenCreateMergeRequestUseCase;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->loadEnv(__DIR__ . '/../.env');

$git = new class implements Git {
    public function clone(string $directory, string $remoteUri): void {}
    public function hasUncommittedChanges(string $directory): bool
    {
        return false;
    }
    public function checkoutNewBranch(string $directory, string $branch): void {}
    public function commitChanges(string $directory, string $commitMessage): void {}
};

$gitlabApi = new class implements GitlabApi {};

$composer = new class implements Composer {
    public function install(string $directory): void {}
};

$rector = new class implements Rector {};

$repositoryUri = $argv[1] ?? throw new InvalidArgumentException('Missing repositoryUri (1st) CLI parameter');
$username = $argv[2] ?? throw new InvalidArgumentException('Missing username (2nd) CLI parameter');
$personalAccessToken = $argv[3] ?? throw new InvalidArgumentException('Missing personalAccessToken (3rd) CLI parameter');

(new RunRectorOnGitlabRepositoryOpenCreateMergeRequestUseCase(
    $git,
    $gitlabApi,
    $composer,
    $rector
))('', '', '');
