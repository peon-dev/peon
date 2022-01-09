<?php

declare(strict_types=1);

namespace PHPMate\Subscribers;

use PHPMate\Domain\Task\Event\TaskDeleted;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenTaskDeleted
{
    public function __construct(
        private HubInterface $hub,
        private Environment $twig,
    ) {}


    public function __invoke(TaskDeleted $event): void
    {
        // Project overview - task row
        // Dashboard - project stats

        $update = new Update();

        $this->hub->publish($update);
    }
}
