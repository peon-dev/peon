<?php
declare (strict_types=1);

namespace Acme\Domain\Gitlab;

interface CheckoutGitlabRepository
{
    public function __invoke(string $repositoryName): GitlabApplication;
}
