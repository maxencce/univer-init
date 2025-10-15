<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\AffaireMaster;
use App\Entity\AffaireVersion;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AffaireVersionService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * Active une version spécifique pour une affaire
     */
    public function activateVersion(AffaireMaster $affaireMaster, int $versionId, string $token): ?string
    {
        if (!$this->isCsrfTokenValid('activate'.$affaireMaster->getId(), $token)) {
            return 'Token invalide';
        }

        $version = $this->em->getRepository(AffaireVersion::class)->find($versionId);
        if (!$version || $version->getMaster()->getId() !== $affaireMaster->getId()) {
            return 'Version non trouvée pour cette affaire';
        }

        $affaireMaster->setCurrentVersion($version);
        $affaireMaster->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        return null; // Succès
    }

    /**
     * Crée une nouvelle version pour une affaire
     */
    public function createNewVersion(AffaireMaster $affaire, string $token): ?AffaireVersion
    {
        if (!$this->isCsrfTokenValid('newversion'.$affaire->getId(), $token)) {
            return null; // Token invalide
        }

        $dupService = new AffaireDuplicationService($this->em);
        return $dupService->createNewVersion($affaire);
    }

    /**
     * Archive une affaire
     */
    public function archiveAffaire(AffaireMaster $affaireMaster, string $token): bool
    {
        if (!$this->isCsrfTokenValid('archive'.$affaireMaster->getId(), $token)) {
            return false;
        }

        $affaireMaster->setArchive(true);
        $affaireMaster->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        return true;
    }

    /**
     * Supprime une affaire
     */
    public function deleteAffaire(AffaireMaster $affaireMaster, string $token): bool
    {
        if (!$this->isCsrfTokenValid('delete'.$affaireMaster->getId(), $token)) {
            return false;
        }

        $this->em->remove($affaireMaster);
        $this->em->flush();

        return true;
    }

    /**
     * Simule la validation CSRF (en production, utiliser le vrai validateur)
     */
    private function isCsrfTokenValid(string $id, string $token): bool
    {
        // En production, utiliser le vrai validateur CSRF de Symfony
        // Pour l'instant, on simule la validation
        return !empty($token);
    }
}
