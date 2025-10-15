<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SiteType;
use App\Enum\SiteStatut;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/test')]
#[IsGranted('ROLE_GESEC')]
class TestController extends AbstractController
{
    #[Route('/', name: 'site_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }

}