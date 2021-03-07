<?php

declare(strict_types=1);

use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\Git\Git;
use PHPMate\Domain\Gitlab\Gitlab;
use PHPMate\Domain\Rector\Rector;
use PHPMate\UseCase\RunRectorOnGitlabRepositoryOpenCreateMergeRequestUseCase;

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

$gitlabApi = new class implements Gitlab {};

$composer = new class implements Composer {
    public function installInDirectory(string $directory): void {}
};

$rector = new class implements Rector {
    public function runInDirectory(string $directory): void {}
};

$repositoryUri = $argv[1] ?? throw new InvalidArgumentException('Missing repositoryUri (1st) CLI parameter');
$username = $argv[2] ?? throw new InvalidArgumentException('Missing username (2nd) CLI parameter');
$personalAccessToken = $argv[3] ?? throw new InvalidArgumentException('Missing personalAccessToken (3rd) CLI parameter');

(new RunRectorOnGitlabRepositoryOpenCreateMergeRequestUseCase(
    $git,
    $gitlabApi,
    $composer,
    $rector
))('', '', '');
