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

#[Route('/sites')]
#[IsGranted('ROLE_GESEC')]
class SiteController extends AbstractController
{
    #[Route('/', name: 'site_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $searchForm = $this->createForm(\App\Form\SiteSearchType::class);
        $searchForm->handleRequest($request);

        $criteria = $searchForm->getData() ?: [];

        $siteRepository = $em->getRepository(Site::class);
        if (method_exists($siteRepository, 'findBySearchCriteria')) {
            $sites = $siteRepository->findBySearchCriteria($criteria);
        } else {
            $sites = $siteRepository->findAll();
        }

        return $this->render('site/index.html.twig', [
            'sites' => $sites,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/new', name: 'site_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $site = new Site();
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $site->setCreatedAt(new \DateTimeImmutable());
            $site->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($site);
            $em->flush();
            $this->addFlash('success', 'Site créé avec succès.');
            return $this->redirectToRoute('site_index');
        }
        return $this->render('site/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}/edit', name: 'site_edit', methods: ['GET', 'POST'])]
    public function edit(Site $site, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SiteType::class, $site, ['is_edit' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $site->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
            $this->addFlash('success', 'Site modifié avec succès.');
            return $this->redirectToRoute('site_index');
        }
        return $this->render('site/edit.html.twig', [
            'form' => $form->createView(),
            'site' => $site,
        ]);
    }
    
    #[Route('/{id}/toggle-statut', name: 'site_toggle_statut', methods: ['POST'])]
    public function toggleStatut(Site $site, EntityManagerInterface $em, Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('toggle-statut'.$site->getSiteId(), $request->request->get('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide.'], 403);
        }
        
        // Basculer le statut entre ACTIF et INACTIF
        if ($site->getStatut() === SiteStatut::ACTIF) {
            $site->setStatut(SiteStatut::INACTIF);
            $message = 'Site désactivé avec succès.';
            $newStatus = 'INACTIF';
        } else {
            $site->setStatut(SiteStatut::ACTIF);
            $message = 'Site activé avec succès.';
            $newStatus = 'ACTIF';
        }
        
        $site->setUpdatedAt(new \DateTimeImmutable());
        $em->flush();
        
        return new JsonResponse([
            'success' => true, 
            'message' => $message,
            'newStatus' => $newStatus,
            'statusClass' => $newStatus === 'ACTIF' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'
        ]);
    }
}
