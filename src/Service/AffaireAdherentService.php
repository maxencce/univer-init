<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\AffaireAdherent;
use App\Entity\AffaireVersion;
use App\Entity\Adherent;
use App\Form\AffaireAdherentType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;

class AffaireAdherentService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FormFactoryInterface $formFactory,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Ajoute un adhérent (entité) à une affaire.
     */
    public function addAdherentToAffaire(AffaireVersion $affaire, AffaireAdherent $affaireAdherent): AffaireAdherent
    {
        $affaireAdherent->setAffaire($affaire);

        $now = new \DateTimeImmutable();
        $affaireAdherent->setCreatedAt($now);
        $affaireAdherent->setUpdatedAt($now);

        $this->em->persist($affaireAdherent);
        $this->em->flush();

        return $affaireAdherent;
    }

    /**
     * Récupère tous les adhérents d'une affaire
     */
    public function getExistingAdherents(AffaireVersion $affaire): array
    {
        return $this->em->getRepository(AffaireAdherent::class)->findBy(['affaire' => $affaire]);
    }

    /**
     * Crée le formulaire d'ajout d'adhérent
     */
    public function createAdherentForm(AffaireVersion $affaire): \Symfony\Component\Form\FormInterface
    {
        $affaireAdherent = new AffaireAdherent();
        $affaireAdherent->setAffaire($affaire);

        return $this->formFactory->create(AffaireAdherentType::class, $affaireAdherent);
    }

    /**
     * Traite l'ajout d'un adhérent depuis une requête
     */
    public function handleAddAdherentRequest(AffaireVersion $affaire, Request $request): array
    {
        $form = $this->createAdherentForm($affaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addAdherentToAffaire($affaire, $form->getData());
            return ['success' => true];
        }

        return [
            'success' => false,
            'form' => $form->createView(),
            'existingAdherents' => $this->getExistingAdherents($affaire)
        ];
    }

    /**
     * Supprime un adhérent d'une affaire en utilisant les clés composites
     * La vérification du token CSRF est gérée par le contrôleur
     */
    public function removeAdherentFromAffaire(int $affaireVersionId, int $adherentId): bool
    {
        $this->logger->info('Suppression d\'un adhérent d\'une affaire', [
            'affaireVersionId' => $affaireVersionId,
            'adherentId' => $adherentId
        ]);

        // Approche directe par SQL pour éviter les problèmes avec les entités Doctrine et les clés composites
        try {
            $conn = $this->em->getConnection();
            
            // Supprimer directement par SQL
            $deleteSql = 'DELETE FROM affaire_adherent WHERE affaire_version_id = :affaireId AND adherent_id = :adherentId';
            $deleteStmt = $conn->prepare($deleteSql);
            $affected = $deleteStmt->executeStatement([
                'affaireId' => $affaireVersionId,
                'adherentId' => $adherentId
            ]);
            
            $this->logger->info('Suppression effectuée', [
                'affectedRows' => $affected
            ]);
            
            // Vider le cache Doctrine pour éviter les problèmes futurs
            $this->em->clear(AffaireAdherent::class);
            
            return $affected > 0;
            
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la suppression SQL', [
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }
}
