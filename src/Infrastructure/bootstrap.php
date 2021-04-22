<?php

declare(strict_types=1);

use Tracy\Debugger;

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->loadEnv(__DIR__ . '/../../.env');

$logDirectory = __DIR__ . '/../../var/log';
$tracyMode = Debugger::PRODUCTION;

if ($_ENV['APP_DEBUG'] === 'true') {
    $tracyMode = Debugger::DEVELOPMENT;
}

Debugger::enable($tracyMode, $logDirectory);
