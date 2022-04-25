<?php

declare(strict_types=1);

namespace Peon\Domain\Application\Value;

use Nette\Utils\FileSystem;

final class WorkingDirectory
{
    public function __construct(
        public readonly string $localPath,
        public readonly string $hostPath,
    ) {
    }


    public function __destruct()
    {
        FileSystem::delete($this->localPath);
    }
}
