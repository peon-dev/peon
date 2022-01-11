<?php

declare(strict_types=1);

namespace PHPMate\Subscribers;

use PHPMate\Domain\Job\Event\JobScheduled;
use PHPMate\Packages\MessageBus\Event\EventHandlerInterface;
use PHPMate\Ui\ReadModel\Dashboard\ProvideReadProjectById;
use PHPMate\Ui\ReadModel\Job\ProvideReadJobById;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;
use Twig\Error\Error;

final class PublishMercureUpdateWhenJobScheduled implements EventHandlerInterface
{
    public function __construct(
        private Environment $twig,
        private HubInterface $hub,
        private ProvideReadJobById $provideReadJobById,
        private ProvideReadProjectById $provideReadProjectById,
    ) {}


    /**
     * @throws Error
     */
    public function __invoke(JobScheduled $event): void
    {
        $job = $this->provideReadJobById->provide($event->jobId);
        $project = $this->provideReadProjectById->provide($event->projectId);

        $this->hub->publish(
            new Update(
                'project-' . $event->projectId->id . '-overview',
                $this->twig->render('project_overview.stream.html.twig', [
                    'job' => $job,
                ])
            )
        );

        $this->hub->publish(
            new Update(
                'dashboard',
                $this->twig->render('dashboard.stream.html.twig', [
                    'job' => $job,
                    'project' => $project,
                ])
            )
        );
    }
}
