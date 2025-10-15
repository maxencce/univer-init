<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\AffaireMaster;
use App\Form\AffaireSearchType;
use Symfony\Component\Form\FormFactoryInterface;

class AffaireSearchService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FormFactoryInterface $formFactory
    ) {
    }

    /**
     * Recherche et filtre les affaires selon les critères
     */
    public function searchAffaires(array $criteria = []): array
    {
        // Appliquer les valeurs par défaut
        $criteria = $this->applyDefaultCriteria($criteria);

        // Construire la requête
        $qb = $this->em->createQueryBuilder();
        $qb->select('m', 'v')
           ->from(AffaireMaster::class, 'm')
           ->leftJoin('m.currentVersion', 'v');

        $this->applySearchFilters($qb, $criteria);

        $qb->orderBy('m.code', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Crée le formulaire de recherche
     */
    public function createSearchForm(): \Symfony\Component\Form\FormInterface
    {
        return $this->formFactory->create(AffaireSearchType::class);
    }

    /**
     * Traite le formulaire de recherche et retourne les données
     */
    public function handleSearchRequest(\Symfony\Component\HttpFoundation\Request $request): array
    {
        $searchForm = $this->createSearchForm();
        $searchForm->handleRequest($request);

        $criteria = $searchForm->getData() ?: [];
        $affaires = $this->searchAffaires($criteria);

        return [
            'affaires' => $affaires,
            'searchForm' => $searchForm->createView(),
        ];
    }

    /**
     * Applique les critères par défaut
     */
    private function applyDefaultCriteria(array $criteria): array
    {
        // Par défaut, afficher uniquement les affaires non archivées si aucun filtre d'archivage n'est défini
        if (empty($criteria['archive'])) {
            $criteria['archive'] = '0';
        }

        return $criteria;
    }

    /**
     * Applique les filtres de recherche à la requête
     */
    private function applySearchFilters($qb, array $criteria): void
    {
        if (!empty($criteria['code'])) {
            $qb->andWhere('m.code LIKE :code')
               ->setParameter('code', '%' . trim($criteria['code']) . '%');
        }

        if (!empty($criteria['client'])) {
            $qb->andWhere('m.client = :client')
               ->setParameter('client', $criteria['client']);
        }

        if (!empty($criteria['statut'])) {
            // statut is stored on the current version
            $qb->andWhere('v.statut = :statut')
               ->setParameter('statut', $criteria['statut']);
        }

        if (!empty($criteria['dateDebutMin'])) {
            $qb->andWhere('v.dateDebut >= :dmin')
               ->setParameter('dmin', $criteria['dateDebutMin']);
        }

        if (!empty($criteria['dateDebutMax'])) {
            $qb->andWhere('v.dateDebut <= :dmax')
               ->setParameter('dmax', $criteria['dateDebutMax']);
        }

        if (isset($criteria['archive']) && $criteria['archive'] !== '') {
            $archiveVal = $criteria['archive'] === '1' ? true : false;
            $qb->andWhere('m.archive = :archive')
               ->setParameter('archive', $archiveVal);
        }
    }
}
