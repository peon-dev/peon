<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

PHPMate\Dashboard\Bootstrap::boot()
    ->createContainer()
    ->getByType(Nette\Application\Application::class)
    ->run();
