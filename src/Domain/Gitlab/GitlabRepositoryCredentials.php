<?php

declare(strict_types=1);

namespace Acme\Domain\Gitlab;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class GitlabRepositoryCredentials
{
    public function __construct(
        public string $remoteUri,
        public string $username,
        public string $accessToken
    ){ }
}
