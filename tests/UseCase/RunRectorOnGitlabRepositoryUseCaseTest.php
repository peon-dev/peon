<?php
declare(strict_types=1);

namespace PHPMate\Tests\UseCase;

use PHPMate\Infrastructure\Symfony\DependencyInjection\ContainerFactory;
use PHPMate\UseCase\RunRectorOnGitlabRepositoryUseCase;
use PHPUnit\Framework\TestCase;

class RunRectorOnGitlabRepositoryUseCaseTest extends TestCase
{
    public function test(): void
    {
        $container = ContainerFactory::createContainer();
        $useCase = $container->get(RunRectorOnGitlabRepositoryUseCase::class);

        self::assertInstanceOf(RunRectorOnGitlabRepositoryUseCase::class, $useCase);
    }
}
