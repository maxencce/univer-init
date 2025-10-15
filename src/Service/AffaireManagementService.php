<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\AffaireMaster;
use App\Entity\AffaireVersion;
use App\Form\AffaireType;
use App\Form\AffaireEditType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AffaireManagementService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * Crée une nouvelle affaire avec sa première version
     */
    public function createAffaire(AffaireMaster $affaireMaster): AffaireMaster
    {
        // If the submitted form already populated a currentVersion on the AffaireMaster
        // (AffaireType maps fields to currentVersion.*), reuse it so description/date are kept.
        $formVersion = $affaireMaster->getCurrentVersion();

        if ($formVersion instanceof AffaireVersion) {
            $version = $formVersion;
            // ensure a version number exists
            if (! $version->getVersionNumber()) {
                $version->setVersionNumber(1);
            }
        } else {
            // Fallback: create an initial version
            $version = new AffaireVersion();
            $version->setVersionNumber(1);
            $affaireMaster->setCurrentVersion($version);
        }

        // Ensure relation from version -> master is set
        $version->setMaster($affaireMaster);

        // Set timestamps on master and version
        $now = new \DateTimeImmutable();
        $affaireMaster->setCreatedAt($now);
        $affaireMaster->setUpdatedAt($now);
        $version->setCreatedAt($now);
        $version->setUpdatedAt($now);

        // Persist both entities
        $this->em->persist($affaireMaster);
        $this->em->persist($version);
        $this->em->flush();

        return $affaireMaster;
    }

    /**
     * Met à jour une affaire existante
     */
    public function updateAffaire(AffaireVersion $affaire): void
    {
        $master = $affaire->getMaster();

        // Assurer que le master a une currentVersion pour le mapping du formulaire
        if ($master->getCurrentVersion() === null) {
            $master->setCurrentVersion($affaire);
        }

        $affaire->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    /**
     * Prépare les données pour la duplication d'une affaire
     */
    public function prepareDuplicationData(AffaireMaster $sourceAffaire): array
    {
        $newMaster = new AffaireMaster();
        $initialVersion = new AffaireVersion();
        $initialVersion->setVersionNumber(1);
        $newMaster->setCurrentVersion($initialVersion);

        // Pré-remplir avec les données existantes
        $newMaster->setCode($sourceAffaire->getCode() . '-copy');
        $newMaster->setClient($sourceAffaire->getClient());

        if ($sourceAffaire->getCurrentVersion()) {
            $srcVersion = $sourceAffaire->getCurrentVersion();
            $initialVersion->setDescription($srcVersion->getDescription());
            $initialVersion->setStatut($srcVersion->getStatut());
            $initialVersion->setDateDebut($srcVersion->getDateDebut());
            $initialVersion->setDateFin($srcVersion->getDateFin());
        }

        return [
            'newMaster' => $newMaster,
            'initialVersion' => $initialVersion,
        ];
    }

    /**
     * Traite la duplication d'une affaire
     */
    public function processDuplication(AffaireMaster $sourceAffaire, AffaireMaster $affaireMaster): AffaireMaster
    {
        $newCode = trim((string)($affaireMaster->getCode() ?? '')) ?: $sourceAffaire->getCode() . '-copy';
        $newClient = $affaireMaster->getClient() ?? $sourceAffaire->getClient();

        return $this->duplicateAsNewAffaire($sourceAffaire, $newCode, $newClient);
    }

    /**
     * Remplit les données de l'affaire depuis l'entité
     */
    private function populateAffaireFromEntity(AffaireMaster $affaire): void
    {
        $now = new \DateTimeImmutable();

        // Les données sont déjà dans l'entité via le formulaire
        // On définit juste les timestamps
        $affaire->setCreatedAt($now);
        $affaire->setUpdatedAt($now);
    }

    /**
     * Remplit les données de la version depuis l'entité AffaireMaster
     */
    private function populateVersionFromEntity(AffaireVersion $version, AffaireMaster $affaireMaster): void
    {
        $now = new \DateTimeImmutable();

        // Récupérer la currentVersion de l'entité AffaireMaster
        $currentVersion = $affaireMaster->getCurrentVersion();

        if ($currentVersion) {
            // Copier les données de la currentVersion vers la nouvelle version
            if ($currentVersion->getDescription()) {
                $version->setDescription($currentVersion->getDescription());
            }
            if ($currentVersion->getStatut()) {
                $version->setStatut($currentVersion->getStatut());
            }
            if ($currentVersion->getDateDebut()) {
                $version->setDateDebut($currentVersion->getDateDebut());
            }
            if ($currentVersion->getDateFin()) {
                $version->setDateFin($currentVersion->getDateFin());
            }
        }

        $version->setCreatedAt($now);
        $version->setUpdatedAt($now);
    }

    /**
     * Duplique une affaire comme nouvelle affaire (méthode helper)
     */
    private function duplicateAsNewAffaire(AffaireMaster $srcMaster, ?string $newCode = null, ?\App\Entity\Client $newClient = null): AffaireMaster
    {
        $dupService = new AffaireDuplicationService($this->em);
        return $dupService->duplicateAsNewAffaire($srcMaster, $newCode, $newClient);
    }
}
