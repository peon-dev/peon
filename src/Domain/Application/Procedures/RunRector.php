<?php
declare (strict_types=1);

namespace Acme\Domain\Application\Procedures;

use Acme\Domain\Application\Application;

interface RunRector
{
    public function __invoke(Application $application): void;
}
