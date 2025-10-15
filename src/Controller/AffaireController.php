<?php

namespace App\Controller;

use App\Entity\AffaireMaster;
use App\Entity\AffaireVersion;


use App\Entity\ContratApplication;
use App\Form\AffaireType;
use App\Form\AffaireEditType;
use App\Service\AffaireDuplicationService;
use App\Service\AffaireBpuService;
use App\Service\AffaireAffectationService;
use App\Service\AffaireVersionService;
use App\Service\AffaireContratService;
use App\Service\AffaireSearchService;
use App\Service\AffaireManagementService;
use App\Service\AffaireAdherentService;
use App\Form\AffaireAdherentType;
use App\Form\AffaireSearchType;
use App\Form\AffaireSiteAdherentType;
use App\Entity\AffaireSiteAdherent;
use App\Enum\BpuEnumType;
use App\Enum\BpuCategorie;
use App\Enum\AffaireStatut;
use Symfony\Component\HttpFoundation\JsonResponse;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface;

#[Route('/affaires')]
class AffaireController extends AbstractController
{
    public function __construct(
        private AffaireBpuService $bpuService,
        private AffaireAffectationService $affectationService,
        private AffaireVersionService $versionService,
        private AffaireContratService $contratService,
        private AffaireDuplicationService $duplicationService,
        private AffaireSearchService $searchService,
        private AffaireManagementService $managementService,
        private AffaireAdherentService $adherentService,
        private LoggerInterface $logger
    ) {
    }

    #[Route('/', name: 'affaire_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        // GESEC users keep the full search experience
        if ($this->isGranted('ROLE_GESEC')) {
            $data = $this->searchService->handleSearchRequest($request);
            return $this->render('affaire/index.html.twig', $data);
        }

        // ADHERENT users: only show affaires they are assigned to
        if ($this->isGranted('ROLE_ADHERENT')) {
            /** @var \App\Entity\Utilisateur $user */
            $user = $this->getUser();

            // Adherent is linked on the user side now (Utilisateur->adherent)
            $adherent = $user->getAdherent();
            if (!$adherent) {
                return $this->render('affaire/index.html.twig', [
                    'affaires' => [],
                ]);
            }

            $qb = $em->createQueryBuilder()
                ->select('m', 'v')
                ->from(\App\Entity\AffaireMaster::class, 'm')
                ->leftJoin('m.currentVersion', 'v')
                ->innerJoin(\App\Entity\AffaireAdherent::class, 'aa', 'WITH', 'aa.affaire = v')
                ->andWhere('aa.adherent = :adherent')
                ->setParameter('adherent', $adherent)
                ->orderBy('m.code', 'ASC');

            $affaires = $qb->getQuery()->getResult();

            return $this->render('affaire/index.html.twig', [
                'affaires' => $affaires,
            ]);
        }

        // Fallback: require GESEC
        throw $this->createAccessDeniedException();
    }

    #[Route('/{id}/api/bpus', name: 'affaire_api_bpus_get', methods: ['GET'])]
    #[IsGranted('ROLE_GESEC')]
    public function apiGetBpus(AffaireVersion $affaire): JsonResponse
    {
        $payload = $this->bpuService->getBpusForApi($affaire);
        return new JsonResponse($payload);
    }

    #[Route('/{id}/api/bpus', name: 'affaire_api_bpus_save', methods: ['POST'])]
    #[IsGranted('ROLE_GESEC')]
    public function apiSaveBpus(AffaireVersion $affaire, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Payload invalide'], 400);
        }

        // Support payload { _token: ..., rows: [...] } or direct rows array
        if (isset($data['_token']) && isset($data['rows']) && is_array($data['rows'])) {
            $rows = $data['rows'];
        } else {
            $rows = $data;
        }

        $this->bpuService->saveBpusFromApi($affaire, $rows);

        return new JsonResponse(['status' => 'ok']);
    }

    #[Route('/new', name: 'affaire_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_GESEC')]
    public function new(Request $request): Response
    {
        // Create a new master + version (ensure currentVersion exists for form property_path)
        $affaireMaster = new AffaireMaster();
        $initialVersion = new AffaireVersion();
        $initialVersion->setVersionNumber(1);
        $affaireMaster->setCurrentVersion($initialVersion);
        $form = $this->createForm(AffaireType::class, $affaireMaster);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->managementService->createAffaire($form->getData());
            $this->addFlash('success', 'Affaire créée avec succès.');
            return $this->redirectToRoute('affaire_index');
        }
        return $this->render('affaire/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'affaire_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_GESEC')]
    public function edit(AffaireVersion $affaire, Request $request): Response
    {
        // Editing a specific version
        $master = $affaire->getMaster();
        // ensure master has a currentVersion for form mapping
        if ($master->getCurrentVersion() === null) {
            $master->setCurrentVersion($affaire);
        }
        $form = $this->createForm(AffaireEditType::class, $master);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->managementService->updateAffaire($affaire);
            $this->addFlash('success', 'Affaire modifiée avec succès.');
            return $this->redirectToRoute('affaire_index');
        }
        return $this->render('affaire/edit.html.twig', [
            'form' => $form->createView(),
            'affaire' => $affaire,
        ]);
    }

    #[Route('/{id}/affectation', name: 'affaire_affectation', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_GESEC')]
    public function affectation(AffaireVersion $affaire, Request $request): Response
    {
        $affectationData = $this->affectationService->getAffectationData($affaire);
        $siteMap = $affectationData['siteMap'];
        $siteList = $affectationData['siteList'];
        $sites = $affectationData['sites'];

        // Formulaire pour ajouter une nouvelle affectation
        // Pass only sites for this client and adherents already attached to the affaire
        $adherents = $this->affectationService->getAvailableAdherents($affaire);

        $addForm = $this->createForm(AffaireSiteAdherentType::class, null, [
            'sites' => $sites,
            'adherents' => $adherents
        ]);
        $addForm->handleRequest($request);

        if ($addForm->isSubmitted() && $addForm->isValid()) {
            $data = $addForm->getData();

            $error = $this->affectationService->createAffectation($affaire, $data);
            if ($error) {
                $this->addFlash('error', $error);
                return $this->redirectToRoute('affaire_affectation', ['id' => $affaire->getAffaireId()]);
            }

            $this->addFlash('success', 'Affectation ajoutée.');
            return $this->redirectToRoute('affaire_affectation', ['id' => $affaire->getId()]);
        }

        return $this->render('affaire/affectation.html.twig', [
            'affaire' => $affaire,
            'siteMap' => $siteMap,
            'siteList' => $siteList,
            'addForm' => $addForm->createView(),
        ]);
    }

    #[Route('/{id}/affectation/remove', name: 'affaire_affectation_remove', methods: ['POST'])]
    #[IsGranted('ROLE_GESEC')]
    public function removeAffectation(Request $request, EntityManagerInterface $em): Response
    {
        $affaireId = $request->request->get('affaire_id');
        $siteId = $request->request->get('site_id');
        $adherentId = $request->request->get('adherent_id');

        if (!$affaireId || !$siteId || !$adherentId) {
            $this->addFlash('error', 'Paramètres manquants pour suppression.');
            return $this->redirectToRoute('affaire_index');
        }

        $affaireVersion = $em->getRepository(\App\Entity\AffaireVersion::class)->find($affaireId);
        $site = $em->getRepository(\App\Entity\Site::class)->find($siteId);
        $adherent = $em->getRepository(\App\Entity\Adherent::class)->find($adherentId);

        if (!$affaireVersion || !$site || !$adherent) {
            $this->addFlash('error', 'Impossible de trouver l\'affectation.');
            return $this->redirectToRoute('affaire_affectation', ['id' => $affaireId]);
        }

        $asa = $em->getRepository(AffaireSiteAdherent::class)->findOneBy([
            'affaire' => $affaireVersion,
            'site' => $site,
            'adherent' => $adherent
        ]);

        if (!$asa) {
            $this->addFlash('error', 'Affectation non trouvée.');
            return $this->redirectToRoute('affaire_affectation', ['id' => $affaireId]);
        }

        $em->remove($asa);
        $em->flush();

        $this->addFlash('success', 'Adhérent retiré de ce site.');
        return $this->redirectToRoute('affaire_affectation', ['id' => $affaireId]);
    }

    #[Route('/{id}/duplicate', name: 'affaire_duplicate', methods: ['GET','POST'])]
    #[IsGranted('ROLE_GESEC')]
    public function duplicate(AffaireMaster $affaire, Request $request): Response
    {
        $duplicationData = $this->managementService->prepareDuplicationData($affaire);
        $newMaster = $duplicationData['newMaster'];

        $form = $this->createForm(AffaireType::class, $newMaster);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newAffaire = $this->managementService->processDuplication($affaire, $form->getData());
            $this->addFlash('success', 'Affaire dupliquée avec succès');
            return $this->redirectToRoute('affaire_index');
        }

        return $this->render('affaire/duplicate.html.twig', [
            'form' => $form->createView(),
            'affaire' => $affaire
        ]);
    }

    #[Route('/{id}/activate-version', name: 'affaire_activate_version', methods: ['POST'])]
    #[IsGranted('ROLE_GESEC')]
    public function activateVersion(AffaireMaster $affaireMaster, Request $request): Response
    {
        $error = $this->versionService->activateVersion(
            $affaireMaster,
            $request->request->get('version_id'),
            $request->request->get('_token')
        );

        if ($error) {
            $this->addFlash('error', $error);
            return $this->redirectToRoute('affaire_index');
        }

        $this->addFlash('success', 'Version activée');
        return $this->redirectToRoute('affaire_edit', ['id' => $affaireMaster->getCurrentVersion()->getId()]);
    }

    #[Route('/{id}/new-version', name: 'affaire_new_version', methods: ['POST'])]
    #[IsGranted('ROLE_GESEC')]
    public function newVersion(AffaireMaster $affaire, Request $request): Response
    {
        $newVersion = $this->versionService->createNewVersion(
            $affaire,
            $request->request->get('_token')
        );

        if (!$newVersion) {
            $this->addFlash('error', 'Token invalide');
            return $this->redirectToRoute('affaire_index');
        }

        return $this->redirectToRoute('affaire_edit', ['id' => $newVersion->getId()]);
    }

    #[Route('/{id}/archive', name: 'affaire_archive', methods: ['POST'])]
    #[IsGranted('ROLE_GESEC')]
    public function archive(AffaireMaster $affaireMaster, Request $request): Response
    {
        if ($this->versionService->archiveAffaire($affaireMaster, $request->request->get('_token'))) {
            $this->addFlash('success', 'Affaire archivée avec succès.');
        } else {
            $this->addFlash('error', 'Erreur lors de l\'archivage de l\'affaire.');
        }
        return $this->redirectToRoute('affaire_index');
    }

    #[Route('/{id}/delete', name: 'affaire_delete', methods: ['POST'])]
    #[IsGranted('ROLE_GESEC')]
    public function delete(AffaireMaster $affaireMaster, Request $request): Response
    {
        if ($this->versionService->deleteAffaire($affaireMaster, $request->request->get('_token'))) {
            $this->addFlash('success', 'Affaire supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Erreur lors de la suppression de l\'affaire.');
        }
        return $this->redirectToRoute('affaire_index');
    }

    #[Route('/{id}/add-bpu', name: 'affaire_add_bpu', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_GESEC')]
    public function addBpu(AffaireVersion $affaire, Request $request, EntityManagerInterface $em): Response
    {
        // Récupérer tous les BPU déjà associés à l'affaire
        $existingBpus = $this->bpuService->getExistingBpus($affaire);

        // Exposer les valeurs d'enum pour les listes déroulantes côté client
        $bpuTypes = array_map(fn($c) => $c->value, BpuEnumType::cases());
        $bpuCategories = array_map(fn($c) => $c->value, BpuCategorie::cases());

        return $this->render('affaire/add_bpu.html.twig', [
            'affaire' => $affaire,
            'existingBpus' => $existingBpus,
            'bpuTypes' => $bpuTypes,
            'bpuCategories' => $bpuCategories,
        ]);
    }
    
    #[Route('/{id}/add-adherent', name: 'affaire_add_adherent', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_GESEC')]
    public function addAdherent(AffaireVersion $affaire, Request $request): Response
    {
        $form = $this->adherentService->createAdherentForm($affaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->adherentService->addAdherentToAffaire($affaire, $form->getData());
            $this->addFlash('success', 'Adhérent ajouté avec succès.');
            return $this->redirectToRoute('affaire_add_adherent', ['id' => $affaire->getId()]);
        }

        // Récupérer tous les adhérents déjà associés à l'affaire
        $existingAdherents = $this->adherentService->getExistingAdherents($affaire);

        return $this->render('affaire/add_adherent.html.twig', [
            'form' => $form->createView(),
            'affaire' => $affaire,
            'existingAdherents' => $existingAdherents
        ]);
    }
    
    #[Route('/{id}/remove-adherent', name: 'affaire_remove_adherent', methods: ['POST'])]
    #[IsGranted('ROLE_GESEC')]
    public function removeAdherent(AffaireVersion $affaire, Request $request): Response
    {
        $adherentId = $request->request->get('adherent_id');
        $token = $request->request->get('_token');
        
        // Utiliser le même format que dans les autres actions (comme archive)
        $tokenId = 'delete-adherent' . $adherentId;
        
        $this->logger->info('removeAdherent', [
            'adherentId' => $adherentId,
            'tokenId' => $tokenId
        ]);
        
        if ($this->isCsrfTokenValid($tokenId, $token)) {
            if ($this->adherentService->removeAdherentFromAffaire($affaire->getId(), $adherentId)) {
                $this->addFlash('success', 'Adhérent retiré avec succès.');
            } else {
                $this->addFlash('error', 'Impossible de retirer cet adhérent.');
            }
        } else {
            $this->logger->error('Token CSRF invalide', [
                'tokenId' => $tokenId,
                'token' => $token
            ]);
            $this->addFlash('error', 'Erreur de sécurité lors de la suppression.');
        }
        
        return $this->redirectToRoute('affaire_add_adherent', ['id' => $affaire->getId()]);
    }

    #[Route('/{id}/saisie-tableur', name: 'affaire_saisie_tableur', methods: ['GET'])]
    public function saisieTableur(AffaireVersion $affaireVersion, EntityManagerInterface $em): Response
    {
        // Only allow saisie if the affaire version is in CONTRAT statut
        if ($affaireVersion->getStatut() !== AffaireStatut::CONTRAT) {
            $this->addFlash('warning', 'Saisie désactivée : l\'affaire n\'est pas en statut CONTRAT.');
            return $this->redirectToRoute('affaire_show', ['id' => $affaireVersion->getMaster()->getId()]);
        }

        // If user is GESEC, full access
        if ($this->isGranted('ROLE_GESEC')) {
            $data = $this->contratService->getSaisieTableurData($affaireVersion);
            return $this->render('affaire/saisie_tableur.html.twig', $data);
        }

        // ADHERENT: ensure they are assigned to at least one site and filter sites & bpus
        if ($this->isGranted('ROLE_ADHERENT')) {
            /** @var \App\Entity\Utilisateur $user */
            $user = $this->getUser();
            $adherent = $user->getAdherent();
            if (!$adherent) {
                throw $this->createAccessDeniedException();
            }

            // Get full saisie data then filter by assigned sites for this adherent
            $data = $this->contratService->getSaisieTableurData($affaireVersion);

            // Filter sites to those assigned to this adherent
            $assignedSiteIds = $em->getRepository(AffaireSiteAdherent::class)->createQueryBuilder('asa')
                ->select('s.siteId')
                ->innerJoin('asa.site', 's')
                ->andWhere('asa.affaire = :version')
                ->andWhere('asa.adherent = :adherent')
                ->setParameter('version', $affaireVersion)
                ->setParameter('adherent', $adherent)
                ->getQuery()
                ->getArrayResult();

            $ids = array_map(fn($r) => $r['siteId'], $assignedSiteIds);
            $data['sites'] = array_values(array_filter($data['sites'], fn($s) => in_array($s['siteId'], $ids)));

            // bpus filtering per site is handled by the contrat service when requesting /saisie/data
            // For initial page load, we should pass only BPUs matching MULTITECH if needed — keep full list, frontend will request per-site

            return $this->render('affaire/saisie_tableur.html.twig', $data);
        }

        throw $this->createAccessDeniedException();
    }

    #[Route('/{versionId}/saisie/data', name: 'affaire_saisie_data', methods: ['GET'])]
    public function saisieData(int $versionId, Request $request, EntityManagerInterface $em): Response
    {
        // Allow GESEC users and ADHERENT users (ADHERENT will be limited to their affaires in services)
        if (!$this->isGranted('ROLE_GESEC') && !$this->isGranted('ROLE_ADHERENT')) {
            throw $this->createAccessDeniedException();
        }
        $siteId = $request->query->get('site');

        // GESEC: full data
        if ($this->isGranted('ROLE_GESEC')) {
            $data = $this->contratService->getContratData($versionId, $siteId);
            if (isset($data['error'])) {
                return new JsonResponse($data, 404);
            }
            return new JsonResponse($data);
        }

        // ADHERENT: must be assigned to this site; pass adherent to service for filtering
        if ($this->isGranted('ROLE_ADHERENT')) {
            /** @var \App\Entity\Utilisateur $user */



            $user = $this->getUser();
            $adherent = $user->getAdherent();
            if (!$adherent) {
                return new JsonResponse(['error' => 'Adherent not found'], 403);
            }

            // Check assignment
            $asa = $em->getRepository(AffaireSiteAdherent::class)->findOneBy([
                'affaire' => $em->getRepository(\App\Entity\AffaireVersion::class)->find($versionId),
                'site' => $siteId,
                'adherent' => $adherent,
            ]);
            if (!$asa) {
                return new JsonResponse(['error' => 'Not assigned to site'], 403);
            }

            $data = $this->contratService->getContratData($versionId, $siteId, $adherent);
            if (isset($data['error'])) {
                return new JsonResponse($data, 404);
            }
            return new JsonResponse($data);
        }

        return new JsonResponse(['error' => 'Forbidden'], 403);
    }

    #[Route('/{versionId}/saisie/save', name: 'affaire_saisie_save', methods: ['POST'])]
    public function saisieSave(int $versionId, Request $request, EntityManagerInterface $em): Response
    {
        // Allow GESEC users and ADHERENT users to save saisie data
        if (!$this->isGranted('ROLE_GESEC') && !$this->isGranted('ROLE_ADHERENT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access denied'], 403);
        }
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid payload'], 400);
        }

        // Support payload { _token: ..., rows: [...], siteId: ..., bpuId: ... } for auto-save
        if (isset($data['_token']) && isset($data['rows']) && isset($data['siteId']) && isset($data['bpuId'])) {
            $rows = $data['rows'];
            $siteId = $data['siteId'];
            $bpuId = $data['bpuId']; // For single row auto-save
        } elseif (isset($data['_token']) && isset($data['rows']) && isset($data['siteId'])) {
            $rows = $data['rows'];
            $siteId = $data['siteId'];
            $bpuId = null; // Multiple rows save
        } elseif (isset($data['rows']) && isset($data['siteId'])) {
            $rows = $data['rows'];
            $siteId = $data['siteId'];
            $bpuId = null;
        } else {
            return new JsonResponse(['success' => false, 'message' => 'Invalid payload format'], 400);
        }

        // Validation des données d'entrée
        if (!isset($siteId) || !isset($rows)) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid payload format'], 400);
        }

        // If user is ADHERENT provide adherent to service to enforce filtering
        $adherent = null;
        if ($this->isGranted('ROLE_ADHERENT') && !$this->isGranted('ROLE_GESEC')) {
            /** @var \App\Entity\Utilisateur $user */
            $user = $this->getUser();
            $adherent = $user->getAdherent();
            if (!$adherent) {
                return new JsonResponse(['success' => false, 'message' => 'Adherent not found'], 403);
            }
        }

        // Sauvegarde des données via le service
        $result = $this->contratService->saveContratData($versionId, [
            'rows' => $rows,
            'siteId' => $siteId,
            'bpuId' => $bpuId
        ], $adherent);

        if (!$result['success']) {
            $statusCode = $result['message'] === 'Version not found' || $result['message'] === 'Site not found' ? 404 : 400;
            return new JsonResponse($result, $statusCode);
        }

        return new JsonResponse($result);
    }

    #[Route('/{id}', name: 'affaire_show', methods: ['GET'])]
    public function show(AffaireMaster $affaire, EntityManagerInterface $em): Response
    {
        // Prepare summary data for the dashboard
        $currentVersion = $affaire->getCurrentVersion();

        $bpuCount = 0;
        $adherentsCount = 0;
        $sitesCount = 0;

        if ($currentVersion) {
            $bpuCount = count($currentVersion->getAffaireBPUs());
            $adherentsCount = count($currentVersion->getAffaireAdherents());
            // AffaireSiteAdherent is a separate entity; use repository count
            $sitesCount = $em->getRepository(\App\Entity\AffaireSiteAdherent::class)->count(['affaire' => $currentVersion]);
        }

        $stats = [
            'bpuCount' => $bpuCount,
            'sitesCount' => $sitesCount,
            'adherentsCount' => $adherentsCount,
        ];

        return $this->render('affaire/show.html.twig', [
            'affaire' => $affaire,
            'currentVersion' => $currentVersion,
            'stats' => $stats,
        ]);
    }
}