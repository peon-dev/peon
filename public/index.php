<?php

declare(strict_types=1);

use Peon\Infrastructure\Symfony\PeonKernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new PeonKernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
