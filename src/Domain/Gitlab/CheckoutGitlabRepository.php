<?php
declare (strict_types=1);

namespace Acme\Domain\Gitlab;

use Acme\Domain\Application\Application;

interface CheckoutGitlabRepository
{
    public function __invoke(string $repositoryName): Application;
}
