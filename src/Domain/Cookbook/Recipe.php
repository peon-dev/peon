<?php

declare(strict_types=1);

namespace PHPMate\Domain\Cookbook;

use Cron\CronExpression;
use JetBrains\PhpStorm\Immutable;
use Lorisleiva\CronTranslator\CronTranslator;
use PHPMate\Domain\Cookbook\Value\RecipeName;

#[Immutable]
final class Recipe
{
    public string $schedule = '0 */2 * * *';

    /**
     * @param array<string> $commands
     */
    public function __construct(
        public RecipeName $name,
        public string $title,
        public ?string $exampleCodeDiff,
        public ?float $minPhpVersionRequirement,
        public array $commands,
    ) {}


    public function getHumanReadableCron(): string
    {
        return CronTranslator::translate($this->schedule);
    }


    /**
     * @throws \Exception
     */
    public function getNextRunTime(): \DateTimeImmutable
    {
        $cronExpression = new CronExpression($this->schedule);
        $nextRun = $cronExpression->getNextRunDate();

        return \DateTimeImmutable::createFromMutable($nextRun);
    }

    // TODO: we will need required tools
    // TODO: we will need commands
}
