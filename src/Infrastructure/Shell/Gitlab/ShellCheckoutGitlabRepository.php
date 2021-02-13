<?php
declare (strict_types=1);

namespace Acme\Infrastructure\Shell\Gitlab;

use Acme\Domain\Gitlab\CheckoutGitlabRepository;
use Acme\Domain\Gitlab\GitlabApplication;

final class ShellCheckoutGitlabRepository implements CheckoutGitlabRepository
{
    public function __invoke(string $repositoryName): GitlabApplication
    {
        shell_exec('');

        return new GitlabApplication();
    }
}
