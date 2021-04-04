<?php

declare(strict_types=1);

use Tracy\Debugger;

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->loadEnv(__DIR__ . '/../../.env');

Debugger::enable(logDirectory: __DIR__ . '/../../var/log');
