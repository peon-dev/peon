<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\Domain\Tools\Git;

use PHPMate\Domain\Tools\Git\GitRepositoryAuthentication;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;
use PHPMate\Domain\Tools\Git\InvalidRemoteUri;
use PHPUnit\Framework\TestCase;

class RemoteGitRepositoryTest extends TestCase
{
    public function testGetAuthenticatedRepositoryUri(): void
    {
        $repository = self::createRemoteGitRepository('https://gitlab.com/janmikes/repository.git');

        self::assertSame('https://janmikes:PAT@gitlab.com/janmikes/repository.git', (string) $repository->getAuthenticatedUri());
    }


    public function testGetAuthenticatedRepositoryUriUProtocol(): void
    {
        $this->expectException(InvalidRemoteUri::class);

        self::createRemoteGitRepository('git@gitlab.com:janmikes/repository.git');
    }


    public function testGetAuthenticatedRepositoryMustEndWithGitSuffix(): void
    {
        $this->expectException(InvalidRemoteUri::class);

        self::createRemoteGitRepository('https://gitlab.com/janmikes/repository');
    }


    public function testGetProject(): void
    {
        $repository = self::createRemoteGitRepository('https://gitlab.com/janmikes/repository.git');

        self::assertSame('janmikes/repository', $repository->getProject());
    }


    /**
     * @dataProvider provideTestGetInstanceUrlData
     */
    public function testGetInstanceUrl(string $expected, string $repositoryUri): void
    {
        $repository = self::createRemoteGitRepository($repositoryUri);

        self::assertSame($expected, $repository->getInstanceUrl());
    }


    /**
     * @return \Generator<string[]>
     */
    public function provideTestGetInstanceUrlData(): \Generator
    {
        yield ['https://gitlab.com', 'https://gitlab.com/janmikes/repository.git'];
        yield ['https://gitlab.server.com', 'https://gitlab.server.com/janmikes/repository.git'];
    }


    private static function createRemoteGitRepository(string $repositoryUri): RemoteGitRepository
    {
        $authentication = new GitRepositoryAuthentication('phpmate', 'phpmate');

        return new RemoteGitRepository($repositoryUri, $authentication);
    }
}
