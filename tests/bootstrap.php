<?php

declare(strict_types=1);

use Nette\Utils\FileSystem;
use Peon\Infrastructure\Symfony\PeonKernel;
use Peon\Tests\TestingDatabaseCaching;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$_ENV['APP_ENV'] = 'test';
(new Dotenv())->loadEnv(__DIR__ . '/../.env');

$cacheFilePath = __DIR__ . '/.database.cache';
$currentDatabaseHash = TestingDatabaseCaching::calculateDirectoriesHash(
    __DIR__ . '/../src/Infrastructure/Persistence/Doctrine/Migrations',
    __DIR__ . '/DataFixtures',
);

// Skip database bootstrapping if running unit test(s)
if (
    isRunningUnitTestOnly() === false
    && TestingDatabaseCaching::isCacheUpToDate($cacheFilePath, $currentDatabaseHash) === false
) {
    bootstrapDatabase();
    FileSystem::write($cacheFilePath, $currentDatabaseHash);
}


function bootstrapDatabase(): void
{
    $kernel = new PeonKernel('test', true);
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

    $result = $application->run(new ArrayInput([
        'command' => 'doctrine:fixtures:load',
        '--append' => 1,
        '--no-interaction' => 1,
    ]));

    if ($result !== 0) {
        throw new LogicException('Command doctrine:fixtures:load failed');
    }

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
