<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\AffaireMaster;
use App\Entity\AffaireVersion;
use App\Entity\AffaireBPU;
use App\Entity\AffaireAdherent;
use App\Entity\AffaireSiteAdherent;
use App\Entity\ContratApplication;

class AffaireDuplicationService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * Duplicate an entire affaire as a new AffaireMaster (new code).
     * Returns the new AffaireMaster.
     */
    public function duplicateAsNewAffaire(AffaireMaster $srcMaster, ?string $newCode = null, ?\App\Entity\Client $newClient = null): AffaireMaster
    {
        $this->em->beginTransaction();
        try {
            $srcVersion = $srcMaster->getCurrentVersion();
            if (!$srcVersion) {
                throw new \RuntimeException('Source affaire has no current version');
            }

            $now = new \DateTimeImmutable();

            // create master
            $newMaster = new AffaireMaster();
            $newMaster->setCode($newCode ?? $this->generateUniqueCode($srcMaster->getCode()));
            $newMaster->setClient($newClient ?? $srcMaster->getClient());
            $newMaster->setCreatedAt($now);
            $newMaster->setUpdatedAt($now);

            // create version
            $newVersion = new AffaireVersion();
            $newVersion->setMaster($newMaster);
            $newVersion->setVersionNumber(1);
            $newVersion->setStatut($srcVersion->getStatut());
            $newVersion->setDescription($srcVersion->getDescription());
            $newVersion->setDateDebut($srcVersion->getDateDebut());
            $newVersion->setDateFin($srcVersion->getDateFin());
            $newVersion->setCreatedAt($now);
            $newVersion->setUpdatedAt($now);

            $newMaster->setCurrentVersion($newVersion);

            $this->em->persist($newMaster);
            $this->em->persist($newVersion);

            // map of old bpu id => new bpu
            $bpuMap = [];

            // duplicate BPUs
            foreach ($srcVersion->getAffaireBPUs() as $bpu) {
                $nb = new AffaireBPU();
                $nb->setAffaire($newVersion);
                $nb->setType($bpu->getType());
                $nb->setCategorie($bpu->getCategorie());
                $nb->setDescription($bpu->getDescription());
                $nb->setPuissance($bpu->getPuissance());
                $nb->setTempsMaintenanceAnnuelle($bpu->getTempsMaintenanceAnnuelle());
                $nb->setNbHeuresVisiteTechnique($bpu->getNbHeuresVisiteTechnique());
                $nb->setNbHeuresVisiteBonFonctionnement($bpu->getNbHeuresVisiteBonFonctionnement());
                $nb->setTotalHeuresEquipement($bpu->getTotalHeuresEquipement());
                $nb->setPrixMo($bpu->getPrixMo());
                $nb->setFraisKilometriqueUnitaire($bpu->getFraisKilometriqueUnitaire());
                $nb->setNbKmMoyen($bpu->getNbKmMoyen());
                $nb->setTotalFraisKilometrique($bpu->getTotalFraisKilometrique());
                $nb->setDiversConsommable($bpu->getDiversConsommable());
                $nb->setPrixEquipement1VisiteTechnique($bpu->getPrixEquipement1VisiteTechnique());
                $nb->setPrixTotalEquipement($bpu->getPrixTotalEquipement());
                $nb->setCreatedAt($now);
                $nb->setUpdatedAt($now);
                $this->em->persist($nb);
                $bpuMap[$bpu->getId()] = $nb;
            }

            // duplicate AffaireAdherent
            foreach ($srcVersion->getAffaireAdherents() as $aa) {
                $na = new AffaireAdherent();
                $na->setAffaire($newVersion);
                $na->setAdherent($aa->getAdherent());
                $na->setCreatedAt($now);
                $na->setUpdatedAt($now);
                $this->em->persist($na);
            }

            // duplicate AffaireSiteAdherent
            foreach ($srcVersion->getAffaireAdherents() as $unused) {
                // no-op here
            }
            foreach ($srcVersion->getAffaireAdherents() as $unused) {
                // keep for clarity; primary duplication above handles adherents
            }
            foreach ($srcVersion->getAffaireAdherents() as $unused) {
                // placeholder
            }

            // duplicate site adherents
            foreach ($srcVersion->getAffaireAdherents() as $unused) {
                // placeholder
            }

            // duplicate AffaireSiteAdherent properly by iterating repository if needed
            $siteRepo = $this->em->getRepository(AffaireSiteAdherent::class);
            $siteAssigns = $siteRepo->findBy(['affaire' => $srcVersion]);
            foreach ($siteAssigns as $sa) {
                $nsa = new AffaireSiteAdherent();
                $nsa->setAffaire($newVersion);
                $nsa->setSite($sa->getSite());
                $nsa->setAdherent($sa->getAdherent());
                $nsa->setActivite($sa->getActivite());
                $nsa->setSousTraitant($sa->getSousTraitant());
                $nsa->setCreatedAt($now);
                $nsa->setUpdatedAt($now);
                $this->em->persist($nsa);
            }

            // duplicate ContratApplication
            $contratRepo = $this->em->getRepository(ContratApplication::class);
            $contrats = $contratRepo->findBy(['affaire' => $srcVersion]);
            foreach ($contrats as $c) {
                $nc = new ContratApplication();
                $nc->setAffaire($newVersion);
                $nc->setSite($c->getSite());
                // map old BPU to new BPU if possible
                $oldBpu = $c->getAffaireBPU();
                $newBpu = $bpuMap[$oldBpu->getId()] ?? null;
                if ($newBpu) $nc->setAffaireBPU($newBpu);
                $nc->setQuantite($c->getQuantite());
                $nc->setPrixMaintenanceSite($c->getPrixMaintenanceSite());
                $nc->setCreatedAt($now);
                $nc->setUpdatedAt($now);
                $this->em->persist($nc);
            }

            $this->em->flush();
            $this->em->commit();

            return $newMaster;
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    /**
     * Create a new version for the same AffaireMaster, duplicating children and switching currentVersion.
     */
    public function createNewVersion(AffaireMaster $master): AffaireVersion
    {
        $this->em->beginTransaction();
        try {
            $srcVersion = $master->getCurrentVersion();
            if (!$srcVersion) {
                throw new \RuntimeException('Master has no current version');
            }
            // compute next version number
            $max = 0;
            foreach ($master->getVersions() as $v) {
                $vn = $v->getVersionNumber();
                if ($vn > $max) $max = $vn;
            }
            $next = $max + 1;

            $now = new \DateTimeImmutable();

            $newVersion = new AffaireVersion();
            $newVersion->setMaster($master);
            $newVersion->setVersionNumber($next);
            $newVersion->setStatut($srcVersion->getStatut());
            $newVersion->setDescription($srcVersion->getDescription());
            $newVersion->setDateDebut($srcVersion->getDateDebut());
            $newVersion->setDateFin($srcVersion->getDateFin());
            $newVersion->setCreatedAt($now);
            $newVersion->setUpdatedAt($now);

            $this->em->persist($newVersion);

            // duplicate BPUs and map
            $bpuMap = [];
            foreach ($srcVersion->getAffaireBPUs() as $bpu) {
                $nb = new AffaireBPU();
                $nb->setAffaire($newVersion);
                $nb->setType($bpu->getType());
                $nb->setCategorie($bpu->getCategorie());
                $nb->setDescription($bpu->getDescription());
                $nb->setPuissance($bpu->getPuissance());
                $nb->setTempsMaintenanceAnnuelle($bpu->getTempsMaintenanceAnnuelle());
                $nb->setNbHeuresVisiteTechnique($bpu->getNbHeuresVisiteTechnique());
                $nb->setNbHeuresVisiteBonFonctionnement($bpu->getNbHeuresVisiteBonFonctionnement());
                $nb->setTotalHeuresEquipement($bpu->getTotalHeuresEquipement());
                $nb->setPrixMo($bpu->getPrixMo());
                $nb->setFraisKilometriqueUnitaire($bpu->getFraisKilometriqueUnitaire());
                $nb->setNbKmMoyen($bpu->getNbKmMoyen());
                $nb->setTotalFraisKilometrique($bpu->getTotalFraisKilometrique());
                $nb->setDiversConsommable($bpu->getDiversConsommable());
                $nb->setPrixEquipement1VisiteTechnique($bpu->getPrixEquipement1VisiteTechnique());
                $nb->setPrixTotalEquipement($bpu->getPrixTotalEquipement());
                $nb->setCreatedAt($now);
                $nb->setUpdatedAt($now);
                $this->em->persist($nb);
                $bpuMap[$bpu->getId()] = $nb;
            }

            // duplicate site adherents
            $siteRepo = $this->em->getRepository(AffaireSiteAdherent::class);
            $siteAssigns = $siteRepo->findBy(['affaire' => $srcVersion]);
            foreach ($siteAssigns as $sa) {
                $nsa = new AffaireSiteAdherent();
                $nsa->setAffaire($newVersion);
                $nsa->setSite($sa->getSite());
                $nsa->setAdherent($sa->getAdherent());
                $nsa->setActivite($sa->getActivite());
                $nsa->setSousTraitant($sa->getSousTraitant());
                $nsa->setCreatedAt($now);
                $nsa->setUpdatedAt($now);
                $this->em->persist($nsa);
            }

            // duplicate adherents
            $adhRepo = $this->em->getRepository(AffaireAdherent::class);
            $adhs = $adhRepo->findBy(['affaire' => $srcVersion]);
            foreach ($adhs as $aa) {
                $na = new AffaireAdherent();
                $na->setAffaire($newVersion);
                $na->setAdherent($aa->getAdherent());
                $na->setCreatedAt($now);
                $na->setUpdatedAt($now);
                $this->em->persist($na);
            }

            // duplicate contrats
            $contratRepo = $this->em->getRepository(ContratApplication::class);
            $contrats = $contratRepo->findBy(['affaire' => $srcVersion]);
            foreach ($contrats as $c) {
                $nc = new ContratApplication();
                $nc->setAffaire($newVersion);
                $nc->setSite($c->getSite());
                $oldBpu = $c->getAffaireBPU();
                $newBpu = $bpuMap[$oldBpu->getId()] ?? null;
                if ($newBpu) $nc->setAffaireBPU($newBpu);
                $nc->setQuantite($c->getQuantite());
                $nc->setPrixMaintenanceSite($c->getPrixMaintenanceSite());
                $nc->setCreatedAt($now);
                $nc->setUpdatedAt($now);
                $this->em->persist($nc);
            }

            // switch current version
            $master->setCurrentVersion($newVersion);

            $this->em->flush();
            $this->em->commit();

            return $newVersion;
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    private function generateUniqueCode(string $base): string
    {
        return $base . '-copy-' . time();
    }
}


