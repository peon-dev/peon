<?php
declare (strict_types=1);

namespace Acme\Infrastructure\Shell\Gitlab;

use Acme\Domain\Application\Application;
use Acme\Domain\Gitlab\CheckoutGitlabRepository;

final class ShellCheckoutGitlabRepository implements CheckoutGitlabRepository
{
    private string $repositoriesCommonDirectory;


    public function __construct(string $repositoriesCommonDirectory)
    {
        $this->repositoriesCommonDirectory = $repositoriesCommonDirectory;
    }


    public function __invoke(string $repositoryName): Application
    {
        $repositoryDirectory = $this->repositoriesCommonDirectory . '/' . $repositoryName;

        // TODO: clone private repositories
        // TODO: clone self-hosted gitlab repositories (non gitlab.com domain)

        $repositoryUrl = 'https://gitlab.com/'. $repositoryName . '.git';

        if ($this->isRepositoryAlreadyCloned($repositoryDirectory)) {
            $this->updateRepository($repositoryDirectory);
        } else {
            $this->cloneRepository($repositoryDirectory, $repositoryUrl);
        }

        return new Application($repositoryDirectory);
    }


    private function cloneRepository(string $repositoryDirectory, string $repositoryUrl): void
    {
        $command = sprintf(
            'git clone %s %s',
            $repositoryUrl,
            $repositoryDirectory,
        );

        echo shell_exec($command);
    }


    private function isRepositoryAlreadyCloned(string $repositoryDirectory): bool
    {
        return is_dir($repositoryDirectory);
    }


    private function updateRepository(string $repositoryDirectory): void
    {
        $command = sprintf(
            'cd %s && git fetch && git pull --rebase',
            $repositoryDirectory
        );

        echo shell_exec($command);
    }
}
