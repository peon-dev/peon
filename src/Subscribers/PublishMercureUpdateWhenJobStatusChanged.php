<?php

declare(strict_types=1);

namespace PHPMate\Subscribers;

use PHPMate\Domain\Job\Event\JobStatusChanged;
use PHPMate\Packages\MessageBus\Event\EventHandlerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenJobStatusChanged implements EventHandlerInterface
{
    public function __construct(
        private HubInterface $hub,
        private Environment $twig,
    ) {}


    public function __invoke(JobStatusChanged $event): void
    {
        // Dashboard - recent jobs
        // Project overview - recent jobs
        // Project overview - last job (task or recipe)

    }
}
