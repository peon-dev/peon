<?php

declare(strict_types=1);

namespace PHPMate\Subscribers;

use PHPMate\Domain\Task\Event\TaskAdded;
use PHPMate\Packages\MessageBus\Event\EventHandlerInterface;
use PHPMate\Ui\ReadModel\Dashboard\ProvideReadProjectById;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenTaskAdded implements EventHandlerInterface
{
    public function __construct(
        private HubInterface $hub,
        private Environment $twig,
        private ProvideReadProjectById $provideReadProjectById,
    ) {}


    public function __invoke(TaskAdded $event): void
    {
        // Project overview - tasks table append

        $project = $this->provideReadProjectById->provide($event->projectId);

        $this->hub->publish(
            new Update(
                'dashboard',
                $this->twig->render('dashboard.project_stats.stream.html.twig', [
                    'project' => $project,
                ])
            )
        );
    }
}
