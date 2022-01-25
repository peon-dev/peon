<?php

declare(strict_types=1);

namespace Peon\Domain\Tools\Composer;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Peon\Domain\Job\Job;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\ExecuteCommand;
use Peon\Domain\Process\ProcessLogger;
use Peon\Domain\Tools\Composer\Exception\ComposerCommandFailed;
use Peon\Domain\Tools\Composer\Value\ComposerEnvironment;

class Composer
{
    public function __construct(
        private ExecuteCommand $executeCommand,
    ) {}


    /**
     * @throws ProcessFailed
     */
    public function install(Job $job, string $directory): void
    {
        // TODO: remove --ignore-platform-reqs once we have supported environment for the project
        $this->executeCommand->inDirectory($job, $directory,'install --ignore-platform-reqs --no-interaction', $environmentVariables);
    }


    /**
     * @return array<string>|null
     * @throws JsonException
     */
    public function getPsr4Roots(string $directory): array|null
    {
        // TODO: throw exception if file missing
        $json = file_get_contents($directory . '/composer.json');
        assert(is_string($json));

        /**
         * @var array{autoload?: array{psr-4?: string[]}} $composer
         */
        $composer = Json::decode($json, Json::FORCE_ARRAY);

        return $composer['autoload']['psr-4'] ?? null;
    }
}
