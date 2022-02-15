<?php

declare(strict_types=1);

namespace Peon\Tests;

use Nette\Utils\FileSystem;
use Nette\Utils\Random;
use Nyholm\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use Symfony\Component\Process\Process;

final class TestingRemoteGitRepository
{
    public const MAIN_BRANCH = 'main';

    public readonly UriInterface $uri;
    private readonly string $directory;


    public function __construct()
    {
        $targetDirectory = __DIR__ . '/../var/git_repositories/Clone_' . Random::generate();

        $this->directory = $targetDirectory;
        $this->uri = new Uri($this->directory);
    }


    public static function init(): self
    {
        $repository = new self();

        register_shutdown_function(static function() use ($repository) {
            FileSystem::delete($repository->directory);
        });

        FileSystem::copy(__DIR__ . '/GitRepository', $repository->directory);

        Process::fromShellCommandline(sprintf('git init --initial-branch "%s"', self::MAIN_BRANCH), $repository->directory)->mustRun();
        Process::fromShellCommandline('git commit --all --allow-empty --message "Init"', $repository->directory)->mustRun();

        return $repository;
    }


    public function makeBranchBehindMain(string $branch): void
    {
        Process::fromShellCommandline(sprintf('git checkout %s', self::MAIN_BRANCH), $this->directory)->mustRun();

        $fileName = $this->directory . '/' . 'random_file_' . Random::generate();
        FileSystem::write($fileName, 'Hi, im testing Peon!');

        Process::fromShellCommandline('git add .', $this->directory)->mustRun();
        Process::fromShellCommandline('git commit --all --message "Random file"', $this->directory)->mustRun();

        Process::fromShellCommandline(sprintf('git branch --force %s HEAD~1', $branch), $this->directory)->mustRun();
    }


    public function makeBranchConflictAgainstMain(string $branch): void
    {
        Process::fromShellCommandline(sprintf('git checkout %s', self::MAIN_BRANCH), $this->directory)->mustRun();

        $fileName = $this->directory . '/' . 'random_file_' . Random::generate();
        FileSystem::write($fileName, 'Hi, im testing Peon!');

        Process::fromShellCommandline('git add .', $this->directory)->mustRun();
        Process::fromShellCommandline('git commit --all --message "Random file"', $this->directory)->mustRun();

        Process::fromShellCommandline(sprintf('git branch --force %s HEAD~1', $branch), $this->directory)->mustRun();
        Process::fromShellCommandline(sprintf('git checkout %s', $branch), $this->directory)->mustRun();

        FileSystem::write($fileName, 'Hi, im testing Peon!');

        Process::fromShellCommandline('git add .', $this->directory)->mustRun();
        Process::fromShellCommandline('git commit --all --message "Random file the other change"', $this->directory)->mustRun();

        Process::fromShellCommandline(sprintf('git checkout %s', self::MAIN_BRANCH), $this->directory)->mustRun();
    }
}
