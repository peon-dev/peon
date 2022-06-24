<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class WorkersController extends AbstractController
{
    #[Route(path: '/workers', name: 'workers')]
    public function __invoke(): Response
    {
        return $this->render('workers.html.twig');
    }
}
