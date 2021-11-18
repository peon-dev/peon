<?php

declare(strict_types=1);

use PHPMate\Infrastructure\Symfony\PHPMateKernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new PHPMateKernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
