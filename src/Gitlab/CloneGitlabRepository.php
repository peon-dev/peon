<?php
declare (strict_types=1);

namespace Acme\Gitlab;

interface CloneGitlabRepository
{
    public function __invoke(string $repositoryName): GitlabApplication;
}
