<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\GitProvider\Value;

use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\GitProvider\Exception\InvalidRemoteUri;
use PHPUnit\Framework\TestCase;

class RemoteGitRepositoryTest extends TestCase
{
    public function testGetAuthenticatedRepositoryUri(): void
    {
        $repository = self::createRemoteGitRepository('https://gitlab.com/peon/repository.git');

        self::assertSame('https://peon:peon@gitlab.com/peon/repository.git', (string) $repository->getAuthenticatedUri());
    }


    public function testGetAuthenticatedRepositoryUriUProtocol(): void
    {
        $this->expectException(InvalidRemoteUri::class);

        self::createRemoteGitRepository('git@gitlab.com:peon/repository.git');
    }


    public function testGetAuthenticatedRepositoryMayEndWithoutGitSuffix(): void
    {
        $repository = self::createRemoteGitRepository('https://gitlab.com/peon/repository');

        self::assertSame('https://gitlab.com/peon/repository.git', $repository->repositoryUri);
    }


    public function testGetProject(): void
    {
        $repository = self::createRemoteGitRepository('https://gitlab.com/peon/repository.git');

        self::assertSame('peon/repository', $repository->getProject());
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
        yield ['https://gitlab.com', 'https://gitlab.com/peon/repository.git'];
        yield ['https://gitlab.server.com', 'https://gitlab.server.com/peon/repository.git'];
    }


    private static function createRemoteGitRepository(string $repositoryUri): RemoteGitRepository
    {
        $authentication = new GitRepositoryAuthentication('peon', 'peon');

        return new RemoteGitRepository($repositoryUri, $authentication);
    }
}
