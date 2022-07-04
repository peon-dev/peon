<?php

declare(strict_types=1);

namespace Peon\Domain\GitProvider\Value;

use Nette\Utils\Strings;
use Peon\Domain\GitProvider\Exception\UnknownGitProvider;

enum GitProviderName: string
{
    case GitLab = 'gitlab';
    case GitHub = 'github';


    /**
     * @throws UnknownGitProvider
     */
    public static function determineFromRepositoryUri(string $repositoryUri): self
    {
        if (Strings::contains($repositoryUri, 'gitlab.')) {
            return self::GitLab;
        }

        if (Strings::contains($repositoryUri, 'github.')) {
            return self::GitHub;
        }

        throw new UnknownGitProvider();
    }
}
