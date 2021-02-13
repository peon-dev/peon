<?php
declare (strict_types=1);

namespace Acme\Infrastructure\Shell\Application\Procedures;

use Acme\Domain\Application\Application;
use Acme\Domain\Application\Procedures\InstallComposer;
use Acme\Domain\Application\Procedures\MissingComposerFile;

final class ShellInstallComposer implements InstallComposer
{
    /**
     * @throws MissingComposerFile
     */
    public function __invoke(Application $application): void
    {
        $this->checkComposerFileExists($application->getDirectory());

        $command = sprintf(
            'cd %s && /usr/local/bin/composer install',
            $application->getDirectory()
        );

        echo shell_exec($command);
    }


    /**
     * @throws MissingComposerFile
     */
    private function checkComposerFileExists(string $repositoryDirectory): void
    {
        $composerFilePath = $repositoryDirectory . '/composer.json';

        if (!is_file($composerFilePath)) {
            throw new MissingComposerFile();
        }
    }
}
