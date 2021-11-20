<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\Domain\GitProvider\Value;

use PHPMate\Domain\GitProvider\Value\GitRepositoryAuthentication;
use PHPUnit\Framework\TestCase;

class GitRepositoryAuthenticationTest extends TestCase
{
    public function testFromPersonalAccessToken(): void
    {
        $authentication = GitRepositoryAuthentication::fromPersonalAccessToken('PAT');

        self::assertSame(GitRepositoryAuthentication::GITLAB_PAT_USERNAME, $authentication->username);
        self::assertSame('PAT', $authentication->password);
    }
}
