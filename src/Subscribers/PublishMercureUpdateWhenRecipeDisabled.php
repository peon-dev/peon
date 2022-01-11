<?php

declare(strict_types=1);

namespace PHPMate\Subscribers;

use PHPMate\Domain\Cookbook\Event\RecipeDisabled;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Packages\MessageBus\Event\EventHandlerInterface;
use PHPMate\Ui\ReadModel\Dashboard\ProvideReadProjectById;
use PHPMate\Ui\ReadModel\ProjectDetail\ProvideProjectReadRecipes;
use PHPMate\Ui\ReadModel\ProjectDetail\ProvideReadProjectDetail;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenRecipeDisabled implements EventHandlerInterface
{
    public function __construct(
        private HubInterface $hub,
        private Environment $twig,
        private ProvideProjectReadRecipes $provideProjectReadRecipes,
        private ProvideReadProjectDetail $provideReadProjectDetail,
        private ProvideReadProjectById $provideReadProjectById,
    ) {}


    public function __invoke(RecipeDisabled $event): void
    {
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
    }
}
