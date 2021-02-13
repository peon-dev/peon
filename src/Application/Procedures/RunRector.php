<?php
declare (strict_types=1);

namespace Acme\Application\Procedures;

use Acme\Application\Application;

interface RunRector
{
    public function __invoke(Application $application): void;
}
