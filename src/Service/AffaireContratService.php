<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\AffaireVersion;
use App\Entity\ContratApplication;
use App\Entity\AffaireBPU;
use App\Entity\AffaireSiteAdherent;
use Symfony\Component\HttpFoundation\JsonResponse;

class AffaireContratService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * Récupère les données de contrat pour une version et un site
     */
    public function getContratData(int $versionId, ?int $siteId, ?\App\Entity\Adherent $adherent = null): array
    {
        $version = $this->em->getRepository(AffaireVersion::class)->find($versionId);
        if (!$version) {
            return ['error' => 'Version not found'];
        }

        // Determine BPUs for this request. If an adherent is provided and a siteId is set,
        // filter BPUs according to the activite assigned for that adherent on that site.
        $bpusEntities = $version->getAffaireBPUs()->toArray();
        if ($adherent !== null && $siteId !== null) {
            // Find the AffaireSiteAdherent for this version, site and adherent
            $asa = $this->em->getRepository(AffaireSiteAdherent::class)->findOneBy([
                'affaire' => $version,
                'site' => $siteId,
                'adherent' => $adherent,
            ]);
            if (!$asa) {
                return ['error' => 'Site not assigned to adherent'];
            }

            $activite = $asa->getActivite();
            // Filter BPUs by type according to activite
            $bpusEntities = array_filter($bpusEntities, function($b) use ($activite) {
                if ($activite->value === \App\Enum\Activite::MULTITECH->value) {
                    return true;
                }
                if ($activite->value === \App\Enum\Activite::CVC->value) {
                    return $b->getType()->value === \App\Enum\BpuEnumType::CVC->value;
                }
                if ($activite->value === \App\Enum\Activite::ELEC->value) {
                    return $b->getType()->value === \App\Enum\BpuEnumType::ELEC->value;
                }
                return false;
            });
        }

        $bpus = $this->mapBpusForSaisieFromEntities($bpusEntities);
        $contratMap = [];

        if ($siteId) {
            $contrats = $this->em->getRepository(ContratApplication::class)->findBy(['affaire' => $version, 'site' => $siteId]);
            foreach ($contrats as $c) {
                $contratMap[$c->getAffaireBPU()->getId()] = [
                    'quantite' => $c->getQuantite(),
                    'prixMaintenanceSite' => $c->getPrixMaintenanceSite(),
                    'nbVisitesTechnique' => $c->getNbVisitesTechnique(),
                    'nbVisitesBonFonctionnement' => $c->getNbVisitesBonFonctionnement(),
                    'prixVisiteTechnique' => $c->getPrixVisiteTechnique(),
                ];
            }
        }

        return ['bpus' => $bpus, 'contrats' => $contratMap];
    }

    /**
     * Sauvegarde les données de contrat depuis la saisie tableur
     */
    public function saveContratData(int $versionId, array $data, ?\App\Entity\Adherent $adherent = null): array
    {
        $version = $this->em->getRepository(AffaireVersion::class)->find($versionId);
        if (!$version) {
            return ['success' => false, 'message' => 'Version not found'];
        }

        $site = $this->em->getRepository(\App\Entity\Site::class)->find($data['siteId']);
        if (!$site) {
            return ['success' => false, 'message' => 'Site not found'];
        }

        // Determine BPUs list respecting adherent/activity if provided
        $bpusEntities = $version->getAffaireBPUs()->toArray();
        if ($adherent !== null) {
            $asa = $this->em->getRepository(AffaireSiteAdherent::class)->findOneBy([
                'affaire' => $version,
                'site' => $site,
                'adherent' => $adherent,
            ]);
            if (!$asa) {
                return ['success' => false, 'message' => 'Site not assigned to adherent'];
            }
            $activite = $asa->getActivite();
            $bpusEntities = array_filter($bpusEntities, function($b) use ($activite) {
                if ($activite->value === \App\Enum\Activite::MULTITECH->value) {
                    return true;
                }
                if ($activite->value === \App\Enum\Activite::CVC->value) {
                    return $b->getType()->value === \App\Enum\BpuEnumType::CVC->value;
                }
                if ($activite->value === \App\Enum\Activite::ELEC->value) {
                    return $b->getType()->value === \App\Enum\BpuEnumType::ELEC->value;
                }
                return false;
            });
        }

        $bpus = $bpusEntities;
        $rows = $data['rows'];

        foreach ($rows as $i => $row) {
            $bpu = $this->findBpuForRow($bpus, $i, $data['bpuId'] ?? null);
            if (!$bpu) {
                continue;
            }

            $this->upsertContratApplication($version, $site, $bpu, $row);
        }

        $this->em->flush();
        return ['success' => true];
    }

    /**
     * Mappe les BPUs pour l'interface de saisie
     */
    private function mapBpusForSaisie(AffaireVersion $version): array
    {
        $result = [];
        foreach ($version->getAffaireBPUs() as $b) {
            $result[] = [
                'id' => $b->getId(),
                'lot' => $b->getCategorie()?->value ?? '',
                'description' => $b->getDescription(),
                'puissance' => $b->getPuissance(),
                'prixEquipement1VisiteTechnique' => $b->getPrixEquipement1VisiteTechnique() ?? $b->getPrixMo() ?? 0,
            ];
        }
        return $result;
    }

    /**
     * Mappe un tableau d'entités AffaireBPU vers le format attendu par la saisie
     */
    private function mapBpusForSaisieFromEntities(array $bpusEntities): array
    {
        $result = [];
        foreach ($bpusEntities as $b) {
            $result[] = [
                'id' => $b->getId(),
                'lot' => $b->getCategorie()?->value ?? '',
                'description' => $b->getDescription(),
                'puissance' => $b->getPuissance(),
                'prixEquipement1VisiteTechnique' => $b->getPrixEquipement1VisiteTechnique() ?? $b->getPrixMo() ?? 0,
            ];
        }
        return $result;
    }

    /**
     * Trouve le BPU approprié pour une ligne
     */
    private function findBpuForRow(array $bpus, int $index, ?int $bpuId): ?AffaireBPU
    {
        if ($bpuId !== null) {
            return $this->em->getRepository(AffaireBPU::class)->find($bpuId);
        }

        return $bpus[$index] ?? null;
    }

    /**
     * Crée ou met à jour une application de contrat
     */
    private function upsertContratApplication(AffaireVersion $version, $site, AffaireBPU $bpu, array $row): void
    {
        $repo = $this->em->getRepository(ContratApplication::class);
        $existing = $repo->findOneBy(['affaire' => $version, 'site' => $site, 'affaireBPU' => $bpu]);

        $quantite = !empty($row[3]) ? (int)$row[3] : 1;
        $nbVisitesTech = !empty($row[4]) ? (int)$row[4] : null;
        $nbVisitesBon = !empty($row[5]) ? (int)$row[5] : null;
        $prixVisiteTech = !empty($row[6]) ? (float)$row[6] : null;
        $prixMaintenance = !empty($row[7]) ? (float)$row[7] : null;

        if ($existing) {
            $existing->setQuantite($quantite);
            $existing->setNbVisitesTechnique($nbVisitesTech);
            $existing->setNbVisitesBonFonctionnement($nbVisitesBon);
            $existing->setPrixVisiteTechnique($prixVisiteTech);
            $existing->setPrixMaintenanceSite($prixMaintenance);
            $existing->setUpdatedAt(new \DateTimeImmutable());
        } else {
            $ca = new ContratApplication();
            $ca->setAffaire($version);
            $ca->setSite($site);
            $ca->setAffaireBPU($bpu);
            $ca->setQuantite($quantite);
            $ca->setNbVisitesTechnique($nbVisitesTech);
            $ca->setNbVisitesBonFonctionnement($nbVisitesBon);
            $ca->setPrixVisiteTechnique($prixVisiteTech);
            $ca->setPrixMaintenanceSite($prixMaintenance);
            $now = new \DateTimeImmutable();
            $ca->setCreatedAt($now);
            $ca->setUpdatedAt($now);
            $this->em->persist($ca);
        }
    }

    /**
     * Récupère les données pour la saisie tableur
     */
    public function getSaisieTableurData(AffaireVersion $affaireVersion): array
    {
        // Récupérer les sites du client de cette affaire
        $sites = $this->em->getRepository(\App\Entity\Site::class)->findBy(['client' => $affaireVersion->getMaster()->getClient()]);

        // Récupérer les affectations de sites pour cette version d'affaire
        $siteAdherents = $this->em->getRepository(AffaireSiteAdherent::class)->findBy(['affaire' => $affaireVersion]);

        // Filtrer les sites qui ont des affectations
        $assignedSites = [];
        foreach ($siteAdherents as $siteAdherent) {
            $site = $siteAdherent->getSite();
            if (!in_array($site, $assignedSites, true)) {
                $assignedSites[] = $site;
            }
        }

        // Transformer les sites pour le template
        $sitesData = array_map(function($site) {
            return [
                'siteId' => $site->getSiteId(),
                'code' => $site->getCode(),
                'adresse' => $site->getAdresse(),
            ];
        }, $assignedSites);

        // Utiliser la méthode existante pour mapper les BPUs
        $bpusData = $this->mapBpusForSaisie($affaireVersion);

        return [
            'affaireVersion' => $affaireVersion,
            'bpus' => $bpusData,
            'sites' => $sitesData,
        ];
    }
}
