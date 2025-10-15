<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\AffaireBPU;
use App\Entity\AffaireVersion;
use App\Enum\BpuEnumType;
use App\Enum\BpuCategorie;
use Symfony\Component\HttpFoundation\JsonResponse;

class AffaireBpuService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * Parse un flottant depuis une valeur potentiellement localisée (FR/EN)
     */
    private function parseLocalizedFloat(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }
        if (is_float($value) || is_int($value)) {
            return (float)$value;
        }
        $s = trim((string)$value);
        if ($s === '') {
            return null;
        }
        // supprimer espaces et insécables
        $s = preg_replace('/[\h\v\x{00A0}]+/u', '', $s);
        $hasComma = str_contains($s, ',');
        $hasDot = str_contains($s, '.');
        if ($hasComma && $hasDot) {
            // On détermine le séparateur décimal par la dernière occurrence
            if (strrpos($s, ',') > strrpos($s, '.')) {
                // format européen: 1.234,56
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            } else {
                // format anglo: 1,234.56
                $s = str_replace(',', '', $s);
            }
        } elseif ($hasComma) {
            // 12,34 -> 12.34
            $s = str_replace(',', '.', $s);
        }
        if (!is_numeric($s)) {
            return null;
        }
        return (float)$s;
    }


    /**
     * Récupère et mappe les BPUs d'une version d'affaire pour l'API
     */
    public function getBpusForApi(AffaireVersion $affaire): array
    {
        $repo = $this->em->getRepository(AffaireBPU::class);
        $bpus = $repo->findBy(['affaire' => $affaire]);

        return array_map(function(AffaireBPU $bpu) {
            return [
                'id' => $bpu->getId(),
                'type' => $bpu->getType()->value,
                'categorie' => $bpu->getCategorie()->value,
                'description' => $bpu->getDescription(),
                'puissance' => $bpu->getPuissance(),
                'tempsMaintenanceAnnuelle' => $bpu->getTempsMaintenanceAnnuelle(),
                'nbHeuresVisiteTechnique' => $bpu->getNbHeuresVisiteTechnique(),
                'nbHeuresVisiteBonFonctionnement' => $bpu->getNbHeuresVisiteBonFonctionnement(),
                'totalHeuresEquipement' => $bpu->getTotalHeuresEquipement(),
                'prixMo' => $bpu->getPrixMo(),
                'fraisKilometriqueUnitaire' => $bpu->getFraisKilometriqueUnitaire(),
                'nbKmMoyen' => $bpu->getNbKmMoyen(),
                'totalFraisKilometrique' => $bpu->getTotalFraisKilometrique(),
                'diversConsommable' => $bpu->getDiversConsommable(),
                'prixEquipement1VisiteTechnique' => $bpu->getPrixEquipement1VisiteTechnique(),
                'prixTotalEquipement' => $bpu->getPrixTotalEquipement(),
            ];
        }, $bpus);
    }

    /**
     * Sauvegarde en masse des BPUs depuis les données API
     */
    public function saveBpusFromApi(AffaireVersion $affaire, array $rows): void
    {
        // Supprimer les BPU existants pour cette version
        $repo = $this->em->getRepository(AffaireBPU::class);
        $existing = $repo->findBy(['affaire' => $affaire]);
        foreach ($existing as $ex) {
            $this->em->remove($ex);
        }
        $this->em->flush();

        // Recréer à partir du payload
        foreach ($rows as $row) {
            $bpu = new AffaireBPU();
            $bpu->setAffaire($affaire);
            $this->populateBpuFromRow($bpu, $row);
            $this->em->persist($bpu);
        }
        $this->em->flush();
    }

    /**
     * Crée un nouveau BPU avec un type par défaut
     */
    public function createBpu(AffaireVersion $affaire, array $data = []): AffaireBPU
    {
        $bpu = new AffaireBPU();
        $bpu->setAffaire($affaire);
        $bpu->setType(BpuEnumType::CVC); // Définition par défaut

        if (!empty($data)) {
            $this->populateBpuFromRow($bpu, $data);
        }

        $now = new \DateTimeImmutable();
        $bpu->setCreatedAt($now);
        $bpu->setUpdatedAt($now);

        return $bpu;
    }

    /**
     * Mappe les entités BPU vers un format tableau pour les templates
     */
    public function mapBpusToArray(iterable $bpus): array
    {
        $result = [];
        foreach ($bpus as $b) {
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
     * Récupère tous les BPUs d'une affaire pour affichage
     */
    public function getExistingBpus(AffaireVersion $affaire): array
    {
        return $this->em->getRepository(AffaireBPU::class)->findBy(['affaire' => $affaire]);
    }

    /**
     * Remplit un BPU avec les données d'une ligne
     */
    private function populateBpuFromRow(AffaireBPU $bpu, array $row): void
    {
        $bpu->setDescription($row['description'] ?? null);
        $bpu->setPuissance($row['puissance'] ?? null);

        // Gérer les enums en sécurisant la valeur
        try {
            $bpu->setType(BpuEnumType::from($row['type'] ?? BpuEnumType::ELEC->value));
        } catch (\Throwable $e) {
            $bpu->setType(BpuEnumType::ELEC);
        }

        try {
            $bpu->setCategorie(BpuCategorie::from($row['categorie'] ?? BpuCategorie::PLOMBERIE_SANITAIRES->value));
        } catch (\Throwable $e) {
            $values = BpuCategorie::cases();
            $bpu->setCategorie($values[0]);
        }

        $this->setOptionalFields($bpu, $row);

        $now = new \DateTimeImmutable();
        $bpu->setCreatedAt($now);
        $bpu->setUpdatedAt($now);
    }

    /**
     * Définit les champs optionnels du BPU
     */
    private function setOptionalFields(AffaireBPU $bpu, array $row): void
    {
        $fields = [
            'tempsMaintenanceAnnuelle',
            'nbHeuresVisiteTechnique',
            'nbHeuresVisiteBonFonctionnement',
            'totalHeuresEquipement',
            'prixMo',
            'fraisKilometriqueUnitaire',
            'nbKmMoyen',
            'totalFraisKilometrique',
            'diversConsommable',
            'prixEquipement1VisiteTechnique',
            'prixTotalEquipement'
        ];

        $intFields = ['nbKmMoyen'];
        $floatFields = [
            'tempsMaintenanceAnnuelle',
            'nbHeuresVisiteTechnique',
            'nbHeuresVisiteBonFonctionnement',
            'totalHeuresEquipement',
            'prixMo',
            'fraisKilometriqueUnitaire',
            'totalFraisKilometrique',
            'diversConsommable',
            'prixEquipement1VisiteTechnique',
            'prixTotalEquipement'
        ];

        foreach ($fields as $field) {
            if (isset($row[$field])) {
                $setter = 'set' . ucfirst($field);
                $value = $row[$field];

                // Conversions de type spécifiques
                if (in_array($field, $intFields, true)) {
                    $value = ($value === null || $value === '') ? null : (int)$value;
                } elseif (in_array($field, $floatFields, true)) {
                    $value = $this->parseLocalizedFloat($value);
                }

                $bpu->$setter($value);
            }
        }
    }
}