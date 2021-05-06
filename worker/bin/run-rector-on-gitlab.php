<?php

declare(strict_types=1);

use PHPMate\App\RunRectorOnGitlabRepositoryLauncher;
use PHPMate\Domain\Gitlab\GitlabAuthentication;
use PHPMate\Domain\Gitlab\GitlabRepository;
use PHPMate\Infrastructure\Symfony\DependencyInjection\ContainerFactory;
use PHPMate\UseCase\RunRectorOnGitlabRepository;

require_once __DIR__ . '/../src/Infrastructure/bootstrap.php';

if ($argc !== 4) {
    echo "Missing required arguments. Usage:\n  php script.php <repositoryUri> <username> <personalAccessToken>\n";
    exit(1);
}

$repositoryUri = $argv[1];
$username = $argv[2];
$personalAccessToken = $argv[3];

$container = ContainerFactory::create();


$authentication = new GitlabAuthentication($username, $personalAccessToken);
$gitlabRepository = new GitlabRepository($repositoryUri, $authentication);

$command = new RunRectorOnGitlabRepository($gitlabRepository);

/** @var RunRectorOnGitlabRepositoryLauncher $launcher */
$launcher = $container->get(RunRectorOnGitlabRepositoryLauncher::class);
$launcher->launch($command);
