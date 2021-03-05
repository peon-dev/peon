<?php
declare (strict_types=1);

namespace Acme\Domain\Gitlab;

use Acme\Domain\Application\Application;

interface OpenGitlabMergeRequest
{
    public function __invoke(Application $application): void;
}
