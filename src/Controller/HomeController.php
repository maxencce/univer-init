<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/', name: 'root_redirect', methods: ['GET'])]
    public function root(): Response
    {
        return $this->redirectToRoute('app_home');
    }
}
