<?php
declare (strict_types=1);

namespace Acme\Infrastructure\Shell\Application\Procedures;

use Acme\Domain\Application\Application;
use Acme\Domain\Application\Procedures\Composer\InstallComposerExecutor;

final class ShellInstallComposerExecutor implements InstallComposerExecutor
{
    public function __invoke(Application $application): void
    {
        $command = sprintf(
            'cd %s && /usr/local/bin/composer install',
            $application->getDirectory()
        );

        echo shell_exec($command);
    }
}
