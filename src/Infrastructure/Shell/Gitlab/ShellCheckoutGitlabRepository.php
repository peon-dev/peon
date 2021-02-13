<?php
declare (strict_types=1);

namespace Acme\Infrastructure\Shell\Gitlab;

use Acme\Domain\Gitlab\CheckoutGitlabRepository;
use Acme\Domain\Gitlab\GitlabApplication;
use Nette\Utils\FileSystem;

final class ShellCheckoutGitlabRepository implements CheckoutGitlabRepository
{
    private string $repositoriesCommonDirectory;


    public function __construct(string $repositoriesCommonDirectory)
    {
        $this->repositoriesCommonDirectory = $repositoriesCommonDirectory;
    }


    public function __invoke(string $repositoryName): GitlabApplication
    {
        $repositoryDirectory = $this->repositoriesCommonDirectory . '/' . $repositoryName;
        FileSystem::createDir($repositoryDirectory);

        return new GitlabApplication();
    }
}
