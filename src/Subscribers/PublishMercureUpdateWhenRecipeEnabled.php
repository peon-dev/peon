<?php

declare(strict_types=1);

namespace Peon\Subscribers;

use Peon\Domain\Cookbook\Event\RecipeEnabled;
use Peon\Packages\MessageBus\Event\EventHandlerInterface;
use Peon\Ui\ReadModel\Dashboard\ProvideReadProjectById;
use Peon\Ui\ReadModel\ProjectDetail\ProvideProjectReadRecipes;
use Peon\Ui\ReadModel\ProjectDetail\ProvideReadProjectDetail;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenRecipeEnabled implements EventHandlerInterface
{
    public function __construct(
        private readonly HubInterface              $hub,
        private readonly Environment               $twig,
        private readonly ProvideProjectReadRecipes $provideProjectReadRecipes,
        private readonly ProvideReadProjectDetail  $provideReadProjectDetail,
        private readonly ProvideReadProjectById $provideReadProjectById,
        private readonly LoggerInterface $logger,
    ) {}


    public function __invoke(RecipeEnabled $event): void
    {
        try {
            $projectDetail = $this->provideReadProjectDetail->provide($event->projectId);
            $project = $this->provideReadProjectById->provide($event->projectId);

            $this->hub->publish(
                new Update(
                    'project-' . $event->projectId->id . '-cookbook',
                    $this->twig->render('cookbook.stream.html.twig', [
                        'project' => $projectDetail,
                        'recipeName' => $event->recipeName
                    ])
                )
            );

            $this->hub->publish(
                new Update(
                    'project-' . $event->projectId->id . '-overview',
                    $this->twig->render('recipes.stream.html.twig', [
                        'project' => $projectDetail,
                        'recipes' => $this->provideProjectReadRecipes->provide($event->projectId),
                    ])
                )
            );

            $this->hub->publish(
                new Update(
                    'dashboard',
                    $this->twig->render('dashboard.project_stats.stream.html.twig', [
                        'project' => $project,
                    ])
                )
            );
        } catch (\Throwable $throwable) {
            $this->logger->warning($throwable->getMessage(), [
                'exception' => $throwable,
            ]);
        }
    }
}
