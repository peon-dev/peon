<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\GitProvider\Value;

use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use PHPUnit\Framework\TestCase;

class GitRepositoryAuthenticationTest extends TestCase
{
    public function testFromPersonalAccessToken(): void
    {
        $authentication = GitRepositoryAuthentication::fromGitLabPersonalAccessToken('PAT');

        self::assertSame(GitRepositoryAuthentication::GITLAB_PAT_USERNAME, $authentication->username);
        self::assertSame('PAT', $authentication->password);
    }
}
