<?php
declare (strict_types=1);

namespace Acme\Domain\Application;

final class Application
{
    public function __construct(
        private string $directory
    ) {

    }


    public function getDirectory(): string
    {
        return $this->directory;
    }
}
