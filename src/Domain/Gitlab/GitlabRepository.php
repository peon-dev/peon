<?php

declare(strict_types=1);

namespace Acme\Domain\Gitlab;

interface GitlabRepository
{
    public static function fromPersonalAccessToken(string $repositoryName, string $personalAccessToken);

    public function openMergeRequest(string $branch);
}
