<?php
declare (strict_types=1);

namespace Acme\Domain\Gitlab;

interface CloneGitlabRepository
{
    public function __invoke(string $remoteUri, string $username, string $accessToken): GitlabRepository;
}
