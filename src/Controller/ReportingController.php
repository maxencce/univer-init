<?php

namespace App\Controller;

use App\Entity\Adherent;
use App\Entity\Site;
use App\Service\ReportingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reporting')]
class ReportingController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ReportingService $reportingService
    ) {
    }
    
    #[Route('/', name: 'reporting_index', methods: ['GET'])]
    public function index(): Response
    {
        // Page d'accueil du reporting
        return $this->render('reporting/index.html.twig');
    }

    #[Route('/adherent/{id}', name: 'reporting_adherent', methods: ['GET'])]
    public function adherentReporting(Adherent $adherent, Request $request): Response
    {
        // Si l'utilisateur est un adhérent, il ne peut voir que son propre reporting
        if ($this->isGranted('ROLE_ADHERENT') && !$this->isGranted('ROLE_GESEC')) {
            $currentUser = $this->getUser();
            if ($currentUser instanceof Utilisateur) {
                $currentAdherent = $currentUser->getAdherent();
            }
            
            if ($currentAdherent->getAdherentId() !== $adherent->getAdherentId()) {
                throw $this->createAccessDeniedException('Vous ne pouvez consulter que votre propre reporting.');
            }
        }
        
        // Récupérer les données de reporting pour l'adhérent
        $reportingData = $this->reportingService->getAdherentReportingData($adherent);
        
        return $this->render('reporting/adherent.html.twig', [
            'adherent' => $adherent,
            'reportingData' => $reportingData,
        ]);
    }

    #[Route('/site/{id}', name: 'reporting_site', methods: ['GET'])]
    public function siteReporting(Site $site, Request $request): Response
    {
        // Si l'utilisateur est un adhérent, vérifier qu'il est affecté à ce site
        if ($this->isGranted('ROLE_ADHERENT') && !$this->isGranted('ROLE_GESEC')) {
            $currentUser = $this->getUser();
            if ($currentUser instanceof Utilisateur) {
                $currentAdherent = $currentUser->getAdherent();
            }
            
            // Vérifier si l'adhérent est affecté à ce site
            $isAssigned = $this->em->getRepository(\App\Entity\AffaireSiteAdherent::class)
                ->findOneBy(['site' => $site, 'adherent' => $currentAdherent]);
            
            if (!$isAssigned) {
                throw $this->createAccessDeniedException('Vous n\'êtes pas affecté à ce site.');
            }
        }
        
        // Récupérer les données de reporting pour le site
        $reportingData = $this->reportingService->getSiteReportingData($site);
        
        return $this->render('reporting/site.html.twig', [
            'site' => $site,
            'reportingData' => $reportingData,
        ]);
    }
    
    #[Route('/adherents', name: 'reporting_adherents_list', methods: ['GET'])]
    public function adherentsList(): Response
    {
        // Si l'utilisateur est un adhérent, rediriger vers son propre reporting
        if ($this->isGranted('ROLE_ADHERENT') && !$this->isGranted('ROLE_GESEC')) {
            $currentUser = $this->getUser();
            if ($currentUser instanceof Utilisateur) {
                $currentAdherent = $currentUser->getAdherent();
            }
            
            if ($currentAdherent) {
                return $this->redirectToRoute('reporting_adherent', ['id' => $currentAdherent->getAdherentId()]);
            }
        }
        
        // Pour les utilisateurs GESEC, afficher la liste des adhérents
        $adherents = $this->em->getRepository(Adherent::class)->findAll();
        
        return $this->render('reporting/adherents_list.html.twig', [
            'adherents' => $adherents,
        ]);
    }

    #[Route('/sites', name: 'reporting_sites_list', methods: ['GET'])]
    public function sitesList(Request $request): Response
    {
        // Si l'utilisateur est un adhérent, montrer uniquement ses sites affectés
        if ($this->isGranted('ROLE_ADHERENT') && !$this->isGranted('ROLE_GESEC')) {
            $currentUser = $this->getUser();
            if ($currentUser instanceof Utilisateur) {
                $currentAdherent = $currentUser->getAdherent();
            }
            
            if ($currentAdherent) {
                $sites = $this->reportingService->getAdherentAssignedSites($currentAdherent);
            } else {
                $sites = [];
            }
        } else {
            // Pour les utilisateurs GESEC, possibilité de filtrer par client
            $clientId = $request->query->get('client');
            if ($clientId) {
                $client = $this->em->getRepository(\App\Entity\Client::class)->find($clientId);
                $sites = $client ? $this->em->getRepository(Site::class)->findBy(['client' => $client]) : [];
            } else {
                $sites = $this->em->getRepository(Site::class)->findAll();
            }
        }
        
        $clients = $this->em->getRepository(\App\Entity\Client::class)->findAll();
        
        return $this->render('reporting/sites_list.html.twig', [
            'sites' => $sites,
            'clients' => $clients
        ]);
    }
    
    #[Route('/affaires', name: 'reporting_affaires_list', methods: ['GET'])]
    public function affairesList(Request $request): Response
    {
        // Si l'utilisateur est un adhérent, montrer uniquement ses affaires
        if ($this->isGranted('ROLE_ADHERENT') && !$this->isGranted('ROLE_GESEC')) {
            $currentUser = $this->getUser();
            if ($currentUser instanceof Utilisateur) {
                $currentAdherent = $currentUser->getAdherent();
            }
            
            if ($currentAdherent) {
                $affaires = $this->reportingService->getAdherentAffaires($currentAdherent);
            } else {
                $affaires = [];
            }
        } else {
            // Pour les utilisateurs GESEC, possibilité de filtrer par client
            $clientId = $request->query->get('client');
            if ($clientId) {
                $client = $this->em->getRepository(\App\Entity\Client::class)->find($clientId);
                $affaires = $client ? $this->reportingService->getClientAffaires($client) : [];
            } else {
                $affaires = $this->reportingService->getAllAffairesForReporting();
            }
        }
        
        $clients = $this->em->getRepository(\App\Entity\Client::class)->findAll();
        
        return $this->render('reporting/affaires_list.html.twig', [
            'affaires' => $affaires,
            'clients' => $clients
        ]);
    }
    
    #[Route('/affaire/{id}', name: 'reporting_affaire', methods: ['GET'])]
    public function affaireReporting(\App\Entity\AffaireVersion $affaire): Response
    {
        // Vérifier les permissions si l'utilisateur est un adhérent
        if ($this->isGranted('ROLE_ADHERENT') && !$this->isGranted('ROLE_GESEC')) {
            $currentUser = $this->getUser();
            if ($currentUser instanceof Utilisateur) {
                $currentAdherent = $currentUser->getAdherent();
            }
            
            // Vérifier si l'adhérent est affecté à cette affaire
            $isAssigned = $this->em->getRepository(\App\Entity\AffaireAdherent::class)
                ->findOneBy(['affaire' => $affaire, 'adherent' => $currentAdherent]);
            
            if (!$isAssigned) {
                throw $this->createAccessDeniedException('Vous n\'êtes pas affecté à cette affaire.');
            }
        }
        
        // Récupérer les données de reporting pour l'affaire
        $reportingData = $this->reportingService->getAffaireReportingData($affaire);
        
        return $this->render('reporting/affaire.html.twig', [
            'affaire' => $affaire,
            'reportingData' => $reportingData
        ]);
    }
}
