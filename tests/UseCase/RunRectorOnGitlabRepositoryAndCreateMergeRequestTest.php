<?php
declare (strict_types=1);

namespace Acme\Tests\UseCase;

use Acme\UseCase\RunRectorOnGitlabRepositoryAndCreateMergeRequest;
use PHPUnit\Framework\TestCase;

class RunRectorOnGitlabRepositoryAndCreateMergeRequestTest extends TestCase
{

    public function test__invoke(): void
    {
        $this->expectNotToPerformAssertions();

        $useCase = new RunRectorOnGitlabRepositoryAndCreateMergeRequest();

        $useCase->__invoke('');
    }
}
