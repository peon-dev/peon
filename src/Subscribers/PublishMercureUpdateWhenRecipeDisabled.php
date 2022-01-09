<?php

declare(strict_types=1);

namespace PHPMate\Subscribers;

use PHPMate\Domain\Cookbook\Event\RecipeDisabled;
use PHPMate\Packages\MessageBus\Event\EventHandlerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenRecipeDisabled implements EventHandlerInterface
{
    public function __construct(
        private HubInterface $hub,
        private Environment $twig,
    ) {}


    public function __invoke(RecipeDisabled $event): void
    {
        // Dashboard - project stats

        $this->hub->publish(
            new Update(
                'project-' . $event->projectId->id . '-overview',
                $this->twig->render('disable_recipe.stream.html.twig', [
                    'recipeName' => $event->recipeName,
                ])
            )
        );
    }
}
