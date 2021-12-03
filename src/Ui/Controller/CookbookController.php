<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Cookbook\RecipesCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CookbookController extends AbstractController
{
    public function __construct(
        private RecipesCollection $recipesCollection
    ) {}


    #[Route(path: '/cookbook', name: 'cookbook')]
    public function __invoke(): Response
    {
        return $this->render('cookbook.html.twig', [
            'recipes' => $this->recipesCollection->all(),
        ]);
    }
}
