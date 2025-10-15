<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/clients')]
#[IsGranted('ROLE_GESEC')]
class ClientController extends AbstractController
{
    #[Route('/', name: 'client_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $clients = $em->getRepository(Client::class)->findAll();
        return $this->render('client/index.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/new', name: 'client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $client->setCreatedAt(new \DateTimeImmutable());
            $client->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($client);
            $em->flush();
            $this->addFlash('success', 'Client créé avec succès.');
            return $this->redirectToRoute('client_index');
        }
        return $this->render('client/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}/edit', name: 'client_edit', methods: ['GET', 'POST'])]
    public function edit(Client $client, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $client->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
            $this->addFlash('success', 'Client modifié avec succès.');
            return $this->redirectToRoute('client_index');
        }
        return $this->render('client/edit.html.twig', [
            'form' => $form->createView(),
            'client' => $client,
        ]);
    }
    
    #[Route('/{id}/delete', name: 'client_delete', methods: ['POST'])]
    public function delete(Client $client, Request $request, EntityManagerInterface $em): Response
    {
        // Vérifier le token CSRF pour la protection contre les attaques CSRF
        if (!$this->isCsrfTokenValid('delete-client'.$client->getClientId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('client_index');
        }
        
        try {
            // Vérifier si le client a des sites associés
            $sites = $em->getRepository(\App\Entity\Site::class)->findBy(['client' => $client]);
            if (count($sites) > 0) {
                $this->addFlash('error', 'Ce client ne peut pas être supprimé car il a des sites associés.');
                return $this->redirectToRoute('client_index');
            }
            
            // Vérifier si le client a des affaires associées
            $affaires = $em->getRepository(\App\Entity\AffaireMaster::class)->findBy(['client' => $client]);
            if (count($affaires) > 0) {
                $this->addFlash('error', 'Ce client ne peut pas être supprimé car il a des affaires associées.');
                return $this->redirectToRoute('client_index');
            }
            
            // Supprimer le client
            $em->remove($client);
            $em->flush();
            $this->addFlash('success', 'Client supprimé avec succès.');
            return $this->redirectToRoute('client_index');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression du client: ' . $e->getMessage());
            return $this->redirectToRoute('client_index');
        }
    }
}