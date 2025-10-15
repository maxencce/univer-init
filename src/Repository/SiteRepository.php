<?php

namespace App\Repository;

use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
    }

    /**
     * Recherche des sites selon les critères fournis
     *
     * @param array $criteria
     * @return Site[]
     */
    public function findBySearchCriteria(array $criteria): array
    {
        $qb = $this->createQueryBuilder('s');

        if (!empty($criteria['codeGesec'])) {
            $qb->andWhere('s.codeGesec LIKE :codeGesec')
               ->setParameter('codeGesec', '%' . $criteria['codeGesec'] . '%');
        }

        if (!empty($criteria['client'])) {
            $qb->andWhere('s.client = :client')
               ->setParameter('client', $criteria['client']);
        }

        if (!empty($criteria['statut'])) {
            $qb->andWhere('s.statut = :statut')
               ->setParameter('statut', $criteria['statut']);
        }

        if (!empty($criteria['codePostal'])) {
            $qb->andWhere('s.codePostal LIKE :codePostal')
               ->setParameter('codePostal', '%' . $criteria['codePostal'] . '%');
        }

        if (!empty($criteria['contactEmail'])) {
            $qb->andWhere('s.contactEmail LIKE :contactEmail')
               ->setParameter('contactEmail', '%' . $criteria['contactEmail'] . '%');
        }

        $qb->leftJoin('s.client', 'c')
           ->addSelect('c')
           ->orderBy('s.codeGesec', 'ASC');

        return $qb->getQuery()->getResult();
    }
}


