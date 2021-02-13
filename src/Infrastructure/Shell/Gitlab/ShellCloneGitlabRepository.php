<?php
declare (strict_types=1);

namespace Acme\Infrastructure\Shell\Gitlab;

use Acme\Domain\Gitlab\CloneGitlabRepository;
use Acme\Domain\Gitlab\GitlabApplication;

final class ShellCloneGitlabRepository implements CloneGitlabRepository
{
    public function __invoke(string $repositoryName): GitlabApplication
    {
        shell_exec('');

        return new GitlabApplication();
    }
}
