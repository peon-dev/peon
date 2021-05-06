<?php

declare(strict_types=1);

use PHPMate\Worker\App\RunRectorOnGitlabRepositoryLauncher;
use PHPMate\Worker\Domain\Gitlab\GitlabAuthentication;
use PHPMate\Worker\Domain\Gitlab\GitlabRepository;
use PHPMate\Worker\Infrastructure\Symfony\DependencyInjection\ContainerFactory;
use PHPMate\Worker\UseCase\RunRectorOnGitlabRepository;

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
