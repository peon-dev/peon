<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Ui\Form\CreateProjectFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CreateProjectController extends AbstractController
{
    #[Route(path: '/create-project', name: 'create_project')]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(CreateProjectFormType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processForm($form);
        }

        return $this->render('create_project.html.twig', [
            'create_project_form' => $form->createView(),
        ]);
    }


    private function processForm(FormInterface $form): RedirectResponse
    {
        $data = $form->getData();

        return $this->redirectToRoute('jobs_list');
    }
}
