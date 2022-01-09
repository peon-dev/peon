<?php

declare(strict_types=1);

namespace PHPMate\Subscribers;

use PHPMate\Domain\Cookbook\Event\RecipeEnabled;
use PHPMate\Packages\MessageBus\Event\EventHandlerInterface;
use PHPMate\Ui\ReadModel\ProjectDetail\ProvideProjectReadRecipes;
use PHPMate\Ui\ReadModel\ProjectDetail\ProvideReadProjectDetail;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenRecipeEnabled implements EventHandlerInterface
{
    public function __construct(
        private HubInterface              $hub,
        private Environment               $twig,
        private ProvideProjectReadRecipes $provideProjectReadRecipes,
        private ProvideReadProjectDetail  $provideReadProjectDetail,
    ) {}


    public function __invoke(RecipeEnabled $event): void
    {
        $project = $this->provideReadProjectDetail->provide($event->projectId);

        // TODO: Dashboard - project stats

        $this->hub->publish(
            new Update(
                'project-' . $event->projectId->id . '-cookbook',
                $this->twig->render('cookbook.stream.html.twig', [
                    'project' => $project,
                    'recipeName' => $event->recipeName
                ])
            )
        );

        $this->hub->publish(
            new Update(
                'project-' . $event->projectId->id . '-overview',
                $this->twig->render('recipes.stream.html.twig', [
                    'project' => $project,
                    'recipes' => $this->provideProjectReadRecipes->provide($event->projectId),
                ])
            )
        );
    }
}
