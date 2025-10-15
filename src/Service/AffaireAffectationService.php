<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\AffaireSiteAdherent;
use App\Entity\AffaireVersion;
use App\Entity\AffaireAdherent;
use App\Enum\Activite;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AffaireAffectationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * Récupère les données d'affectation pour une affaire
     */
    public function getAffectationData(AffaireVersion $affaire): array
    {
        $sites = $this->em->getRepository(\App\Entity\Site::class)->findBy(['client' => $affaire->getMaster()->getClient()]);
        $existing = $this->em->getRepository(AffaireSiteAdherent::class)->findBy(['affaire' => $affaire]);

        // Préparer une structure par site
        $siteMap = [];
        foreach ($sites as $site) {
            $siteMap[$site->getSiteId()] = [
                'site' => $site,
                'affectations' => []
            ];
        }

        foreach ($existing as $assign) {
            $sid = $assign->getSite()->getSiteId();
            if (!isset($siteMap[$sid])) continue;
            $siteMap[$sid]['affectations'][] = $assign;
        }

        // Calculer l'exhaustivité des activités par site
        $siteList = array_values($siteMap);
        foreach ($siteList as $k => $sd) {
            $siteList[$k]['activities_complete'] = $this->isSiteActivitiesComplete($sd['affectations']);
        }

        // Trier par exhaustivité (incomplets d'abord)
        usort($siteList, function($a, $b) {
            $av = $a['activities_complete'] ? 1 : 0;
            $bv = $b['activities_complete'] ? 1 : 0;
            return $av <=> $bv;
        });

        return [
            'siteMap' => $siteMap,
            'siteList' => $siteList,
            'sites' => $sites,
            'existing' => $existing
        ];
    }

    /**
     * Récupère les adhérents disponibles pour une affaire
     */
    public function getAvailableAdherents(AffaireVersion $affaire): array
    {
        $adherentRepo = $this->em->getRepository(AffaireAdherent::class);
        $adherentAssigns = $adherentRepo->findBy(['affaire' => $affaire]);
        return array_map(fn($a) => $a->getAdherent(), $adherentAssigns);
    }

    /**
     * Valide et crée une nouvelle affectation
     */
    public function createAffectation(AffaireVersion $affaire, array $data): ?string
    {
        $site = $data['site'] ?? null;
        if (!$site) {
            return 'Site invalide';
        }

        $activiteVal = $data['activite'];
        $siteMap = $this->getAffectationData($affaire)['siteMap'];
        $existingAssigns = $siteMap[$site->getSiteId()]['affectations'] ?? [];

        // Validation des règles métier
        $validationError = $this->validateAffectation($activiteVal, $existingAssigns);
        if ($validationError) {
            return $validationError;
        }

        // Créer l'affectation
        $asa = new AffaireSiteAdherent();
        $asa->setAffaire($affaire);
        $asa->setSite($site);
        $asa->setAdherent($data['adherent']);
        $asa->setActivite(Activite::from($activiteVal));

        $now = new \DateTimeImmutable();
        $asa->setCreatedAt($now);
        $asa->setUpdatedAt($now);

        $this->em->persist($asa);
        $this->em->flush();

        return null; // Succès
    }

    /**
     * Vérifie si les activités d'un site sont complètes
     */
    private function isSiteActivitiesComplete(array $affectations): bool
    {
        $has_multitech = false;
        $has_elec = false;
        $has_cvc = false;

        foreach ($affectations as $a) {
            $val = $a->getActivite()->value;
            if ($val === Activite::MULTITECH->value) $has_multitech = true;
            if ($val === Activite::ELEC->value) $has_elec = true;
            if ($val === Activite::CVC->value) $has_cvc = true;
        }

        return ($has_multitech || ($has_elec && $has_cvc));
    }

    /**
     * Valide les règles métier pour une affectation
     */
    private function validateAffectation(string $activiteVal, array $existingAssigns): ?string
    {
        $existingActivities = array_map(function($a) {
            return $a->getActivite()->value;
        }, $existingAssigns);

        if ($activiteVal === Activite::MULTITECH->value) {
            if (count($existingActivities) > 0) {
                return 'Impossible d\'ajouter MULTITECH : des lignes existent déjà pour ce site.';
            }
        } else {
            // ELEC ou CVC
            if (in_array(Activite::MULTITECH->value, $existingActivities)) {
                return 'Impossible d\'ajouter une activité ELEC/CVC si une ligne MULTITECH existe.';
            }

            if (in_array($activiteVal, $existingActivities)) {
                return 'Une ligne pour cette activité existe déjà pour ce site.';
            }

            if (count($existingActivities) >= 2) {
                return 'Impossible d\'ajouter plus de 2 lignes pour ce site.';
            }
        }

        return null; // Validation OK
    }
}
