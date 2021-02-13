<?php
declare (strict_types=1);

namespace Acme\UseCase;

use Acme\Application\Application;

final class RunRectorOnGitlabRepositoryAndCreateMergeRequest
{
    public function __invoke(string $repository): void
    {
        $application = $this->prepareApplication($repository);

        $this->runRector($application);

        $this->createMergeRequest($repository, $application);
    }


    private function prepareApplication(): Application
    {
        // get source code, PHP app -> composer install, what else?
    }


    private function runRector(Application $application): void
    {
    }


}
