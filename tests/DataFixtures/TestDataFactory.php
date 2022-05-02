<?php

declare(strict_types=1);

namespace Peon\Tests\DataFixtures;

use Peon\Domain\Application\Value\ApplicationGitRepositoryClone;
use Peon\Domain\Application\Value\ApplicationLanguage;
use Peon\Domain\Application\Value\TemporaryApplication;
use Peon\Domain\Application\Value\WorkingDirectory;
use Peon\Domain\Job\Value\JobId;
use Ramsey\Uuid\Uuid;

final class TestDataFactory
{
    public static function createTemporaryApplication(): TemporaryApplication
    {
        return new TemporaryApplication(
            new JobId(Uuid::NIL),
            new ApplicationLanguage('PHP', '8.1'),
            new ApplicationGitRepositoryClone(
                new WorkingDirectory(
                    'local',
                    'host',
                ),
                'main',
                'job',
            ),
        );
    }
}
