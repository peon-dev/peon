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
        // TODO: maybe check that local path exists?
    }


    public function __destruct()
    {
        // TODO: too risky :(
        // FileSystem::delete($this->localPath);
    }
}
