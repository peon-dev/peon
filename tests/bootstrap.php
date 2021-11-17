<?php

declare(strict_types=1);

use PHPMate\Infrastructure\Symfony\PHPMateKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$_ENV['APP_ENV'] = 'test';
(new Dotenv())->loadEnv(__DIR__ . '/../.env');

// Skip database bootstrapping if running unit test(s)
if (isRunningUnitTestOnly() === false) {
    bootstrapDatabase();
}


// TODO: cache database, to save some time in local development
function bootstrapDatabase(): void
{
    $kernel = new PHPMateKernel('test', true);
    $kernel->boot();

    $application = new Application($kernel);
    $application->setAutoExit(false);

    $application->run(new ArrayInput([
        'command' => 'doctrine:database:drop',
        '--if-exists' => '1',
        '--force' => '1',
    ]));

    $application->run(new ArrayInput([
        'command' => 'doctrine:database:create',
    ]));

    // Faster than running migrations
    $application->run(new ArrayInput([
        'command' => 'doctrine:schema:create',
    ]));

    $kernel->shutdown();
}


function isRunningUnitTestOnly(): bool
{
    foreach($_SERVER['argv'] as $argument) {
        if ((string) str_contains($argument, '/Unit')) {
            return true;
        }
    }

    return false;
}
