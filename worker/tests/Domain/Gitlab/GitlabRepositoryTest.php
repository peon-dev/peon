<?php
declare(strict_types=1);

namespace PHPMate\Tests\Domain\Gitlab;

use PHPMate\Domain\Gitlab\GitlabAuthentication;
use PHPMate\Domain\Gitlab\GitlabRepository;
use PHPMate\Domain\Gitlab\InvalidGitlabRepositoryUri;
use PHPUnit\Framework\TestCase;

class GitlabRepositoryTest extends TestCase
{
    public function testGetAuthenticatedRepositoryUri(): void
    {
        $repository = self::createGitlabRepository('https://gitlab.com/janmikes/repository.git');

        self::assertSame('https://janmikes:PAT@gitlab.com/janmikes/repository.git', (string) $repository->getAuthenticatedRepositoryUri());
    }


    public function testGetAuthenticatedRepositoryUriUProtocol(): void
    {
        $this->expectException(InvalidGitlabRepositoryUri::class);

        self::createGitlabRepository('git@gitlab.com:janmikes/repository.git');
    }


    public function testGetProject(): void
    {
        $repository = self::createGitlabRepository('https://gitlab.com/janmikes/repository.git');

        self::assertSame('janmikes/repository', $repository->getProject());
    }


    /**
     * @dataProvider provideTestGetGitlabInstanceUrlData
     */
    public function testGetGitlabInstanceUrl(string $expected, string $repositoryUri): void
    {
        $repository = self::createGitlabRepository($repositoryUri);

        self::assertSame($expected, $repository->getGitlabInstanceUrl());
    }


    /**
     * @return \Generator<string[]>
     */
    public function provideTestGetGitlabInstanceUrlData(): \Generator
    {
        yield ['https://gitlab.com', 'https://gitlab.com/janmikes/repository.git'];
        yield ['https://gitlab.server.com', 'https://gitlab.server.com/janmikes/repository.git'];
    }


    private static function createGitlabRepository(string $repositoryUri): GitlabRepository
    {
        $authentication = new GitlabAuthentication('janmikes', 'PAT');

        return new GitlabRepository($repositoryUri, $authentication);
    }
}
