<?php

declare(strict_types=1);

namespace Peon\Domain\Tools\Composer;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Peon\Domain\Application\Value\TemporaryApplication;
use Peon\Domain\Container\DetectContainerImage;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\ExecuteCommand;

class Composer
{
    public function __construct(
        private ExecuteCommand $executeCommand,
        private DetectContainerImage $detectContainerImage,
    ) {}


    /**
     * @throws ProcessFailed
     */
    public function install(TemporaryApplication $application): void
    {
        $this->executeCommand->inContainer(
            $application->jobId,
            $this->detectContainerImage->forLanguage($application->language),
            $application->gitRepository->workingDirectory->hostPath,
            'ls -la && composer install --no-interaction --ignore-platform-reqs',
            2 * 60,
        );
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
