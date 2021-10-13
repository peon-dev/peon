<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class TasksListController extends AbstractController
{
    #[Route(path: '/tasks', name: 'tasks_list')]
    public function __invoke(): Response
    {
        return $this->render('tasks_list.html.twig');
    }
}
