<?php

declare(strict_types=1);

use PHPMate\Infrastructure\Symfony\PHPMateKernel;
use Tracy\Debugger;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    if ((bool) $context['APP_DEBUG'] === true) {
        $logDirectory = __DIR__ . '/../var/log';
        $tracyMode = Debugger::DEVELOPMENT;
        Debugger::enable($tracyMode, $logDirectory);
    }

    return new PHPMateKernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
