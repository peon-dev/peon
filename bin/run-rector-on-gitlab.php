<?php

declare(strict_types=1);

use PHPMate\Infrastructure\Symfony\DependencyInjection\ContainerFactory;
use PHPMate\UseCase\RunRectorOnGitlabRepositoryUseCase;

require_once __DIR__ . '/../src/Infrastructure/bootstrap.php';

if ($argc !== 4) {
    echo "Missing required arguments. Usage:\n  php script.php <repositoryUri> <username> <personalAccessToken>\n";
    exit(1);
}

$repositoryUri = $argv[1];
$username = $argv[2];
$personalAccessToken = $argv[3];

$container = ContainerFactory::create();

/** @var RunRectorOnGitlabRepositoryUseCase $useCase */
$useCase = $container->get(RunRectorOnGitlabRepositoryUseCase::class);

$useCase->__invoke($repositoryUri, $username, $personalAccessToken);
