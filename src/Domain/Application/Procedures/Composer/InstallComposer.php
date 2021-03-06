<?php
declare (strict_types=1);

namespace Acme\Domain\Application\Procedures\Composer;

use Acme\Domain\Application\Application;

class InstallComposer
{
    /**
     * @throws MissingComposerFile
     */
    public function __invoke(Application $application): void
    {
        $composerFilePath = $application->getDirectory() . '/composer.json';

        if (!is_file($composerFilePath)) {
            throw new MissingComposerFile();
        }

        // Do something
    }
}
