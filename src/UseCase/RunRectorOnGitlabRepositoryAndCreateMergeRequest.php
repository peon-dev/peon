<?php
declare (strict_types=1);

namespace Acme\UseCase;

use Acme\Application\Application;

final class RunRectorOnGitlabRepositoryAndCreateMergeRequest
{
    public function __invoke(string $gitlabRepositoryName): void
    {
        $application = $this->prepareApplication($gitlabRepositoryName);

        $this->runRector($application);

        $this->createMergeRequest($gitlabRepositoryName, $application);
    }


    private function prepareApplication(string $gitlabRepositoryName): Application
    {
        // get source code, PHP app -> composer install, what else?
        return new Application($gitlabRepositoryName);
    }


    private function runRector(Application $application): void
    {
    }


    private function createMergeRequest(string $repository, Application $application): void
    {
    }
}
