<?php
declare (strict_types=1);

namespace Acme\Domain\Gitlab;

use Acme\Domain\Application\Application;

final class GitlabApplication implements Application
{
    public function __construct(
        private string $directory
    ) {}


    public function getDirectory(): string
    {
        return $this->directory;
    }
}
