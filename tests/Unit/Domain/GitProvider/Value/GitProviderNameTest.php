<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\GitProvider\Value;

use Peon\Domain\GitProvider\Exception\UnknownGitProvider;
use Peon\Domain\GitProvider\Value\GitProviderName;
use PHPUnit\Framework\TestCase;

final class GitProviderNameTest extends TestCase
{
    public static function provideData(): \Generator
    {
        yield ['https://bitbucket.com/org/repo.git', null];
        yield ['https://user:pass@bitbucket.com/org/repo.git', null];
        yield ['https://gitlab.com/org/repo.git', GitProviderName::GitLab];
        yield ['https://user:pass@gitlab.com/org/repo.git', GitProviderName::GitLab];
        yield ['https://gitlab.subdomain.com/org/repo.git', GitProviderName::GitLab];
        yield ['https://user:pass@gitlab.subdomain.com/org/repo.git', GitProviderName::GitLab];
        yield ['https://github.com/org/repo.git', GitProviderName::GitHub];
        yield ['https://user:pass@github.com/org/repo.git', GitProviderName::GitHub];
    }


    #[\PHPUnit\Framework\Attributes\DataProvider('provideData')]
    public function test(string $repositoryUri, null|GitProviderName $expectedProvider): void
    {
        if ($expectedProvider === null) {
            $this->expectException(UnknownGitProvider::class);
        }

        $provider = GitProviderName::determineFromRepositoryUri($repositoryUri);

        self::assertEquals($expectedProvider, $provider);
    }
}
