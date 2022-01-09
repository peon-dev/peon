<?php

declare(strict_types=1);

namespace PHPMate\Subscribers;

use PHPMate\Domain\Cookbook\Event\RecipeEnabled;
use PHPMate\Packages\MessageBus\Event\EventHandlerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenRecipeEnabled implements EventHandlerInterface
{
    public function __construct(
        private HubInterface $hub,
        private Environment $twig,
    ) {}


    public function __invoke(RecipeEnabled $event): void
    {
        // Dashboard - project stats
        // Project overview - recipes table

        // $update = new Update();

        // $this->hub->publish($update);
    }
}
