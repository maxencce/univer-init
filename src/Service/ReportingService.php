<?php

namespace App\Service;

use App\Entity\Adherent;
use App\Entity\AffaireBPU;
use App\Entity\AffaireSiteAdherent;
use App\Entity\AffaireVersion;
use App\Entity\ContratApplication;
use App\Entity\Site;
use App\Enum\AffaireStatut;
use App\Enum\Activite;
use Doctrine\ORM\EntityManagerInterface;

class ReportingService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * Récupère les données de reporting pour un adhérent
     */
    public function getAdherentReportingData(Adherent $adherent): array
    {
        // Récupérer toutes les affectations de sites pour cet adhérent
        $affectations = $this->em->getRepository(AffaireSiteAdherent::class)
            ->createQueryBuilder('asa')
            ->innerJoin('asa.affaire', 'av')
            ->innerJoin('av.master', 'm')
            ->innerJoin('asa.site', 's')
            ->andWhere('asa.adherent = :adherent')
            ->andWhere('av.statut = :statut')
            ->andWhere('av.archive = :archive')
            ->setParameter('adherent', $adherent)
            ->setParameter('statut', AffaireStatut::CONTRAT)
            ->setParameter('archive', false)
            ->getQuery()
            ->getResult();

        // Organiser les données par affaire et par site
        $reportingData = [
            'affaires' => [],
            'totaux' => [
                'montantGlobal' => 0,
                'siteCount' => 0,
                'affaireCount' => 0,
                'parActivite' => [
                    Activite::MULTITECH->value => 0,
                    Activite::ELEC->value => 0, 
                    Activite::CVC->value => 0
                ]
            ]
        ];

        $uniqueSites = [];
        $uniqueAffaires = [];

        foreach ($affectations as $affectation) {
            $affaireVersion = $affectation->getAffaire();
            $site = $affectation->getSite();
            $activite = $affectation->getActivite()->value;

            $affaireId = $affaireVersion->getMaster()->getId();
            $siteId = $site->getSiteId();

            // Initialiser l'affaire si elle n'existe pas encore dans notre structure
            if (!isset($reportingData['affaires'][$affaireId])) {
                $reportingData['affaires'][$affaireId] = [
                    'affaire' => $affaireVersion->getMaster(),
                    'version' => $affaireVersion,
                    'client' => $affaireVersion->getMaster()->getClient(),
                    'sites' => [],
                    'montantTotal' => 0
                ];
            }

            // Initialiser le site s'il n'existe pas encore pour cette affaire
            if (!isset($reportingData['affaires'][$affaireId]['sites'][$siteId])) {
                $reportingData['affaires'][$affaireId]['sites'][$siteId] = [
                    'site' => $site,
                    'activites' => [],
                    'montantTotal' => 0,
                    'montantParActivite' => [
                        Activite::MULTITECH->value => 0,
                        Activite::ELEC->value => 0,
                        Activite::CVC->value => 0
                    ],
                    'bpusCount' => 0
                ];
            }

            // Ajouter l'activité pour ce site
            $reportingData['affaires'][$affaireId]['sites'][$siteId]['activites'][$activite] = $activite;

            // Récupérer les applications de contrat pour ce site et cette affaire
            $contratApplications = $this->em->getRepository(ContratApplication::class)->findBy([
                'affaire' => $affaireVersion,
                'site' => $site
            ]);

            $montantSiteActivite = 0;
            $bpusCount = 0;

            foreach ($contratApplications as $contratApp) {
                $bpu = $contratApp->getAffaireBPU();
                
                // Si l'activité est MULTITECH, on prend tout
                // Si l'activité est ELEC ou CVC, on filtre par le type de BPU correspondant
                $bpuType = $bpu->getType()->value;
                
                if ($activite === Activite::MULTITECH->value || 
                    ($activite === Activite::ELEC->value && $bpuType === 'ELEC') ||
                    ($activite === Activite::CVC->value && $bpuType === 'CVC')) {
                    
                    $prixMaintenance = $contratApp->getPrixMaintenanceSite() ?? 0;
                    $quantite = $contratApp->getQuantite() ?? 0;
                    
                    // Calculer le montant pour ce BPU
                    $montant = $prixMaintenance * $quantite;
                    
                    $montantSiteActivite += $montant;
                    $bpusCount++;
                }
            }
            
            // Ajouter les montants calculés
            $reportingData['affaires'][$affaireId]['sites'][$siteId]['montantParActivite'][$activite] += $montantSiteActivite;
            $reportingData['affaires'][$affaireId]['sites'][$siteId]['montantTotal'] += $montantSiteActivite;
            $reportingData['affaires'][$affaireId]['sites'][$siteId]['bpusCount'] += $bpusCount;
            
            $reportingData['affaires'][$affaireId]['montantTotal'] += $montantSiteActivite;
            
            $reportingData['totaux']['montantGlobal'] += $montantSiteActivite;
            $reportingData['totaux']['parActivite'][$activite] += $montantSiteActivite;
            
            // Compter les sites et affaires uniques
            $uniqueSites[$siteId] = true;
            $uniqueAffaires[$affaireId] = true;
        }
        
        $reportingData['totaux']['siteCount'] = count($uniqueSites);
        $reportingData['totaux']['affaireCount'] = count($uniqueAffaires);
        
        // Transformer les tableaux associatifs en tableaux indexés pour Twig
        foreach ($reportingData['affaires'] as &$affaire) {
            $affaire['sites'] = array_values($affaire['sites']);
        }
        $reportingData['affaires'] = array_values($reportingData['affaires']);

        return $reportingData;
    }

    /**
     * Récupère les données de reporting pour un site
     */
    public function getSiteReportingData(Site $site): array
    {
        // Récupérer toutes les affectations pour ce site (affaires actives uniquement)
        $affectations = $this->em->getRepository(AffaireSiteAdherent::class)
            ->createQueryBuilder('asa')
            ->innerJoin('asa.affaire', 'av')
            ->innerJoin('av.master', 'm')
            ->andWhere('asa.site = :site')
            ->andWhere('av.statut = :statut')
            ->andWhere('av.archive = :archive')
            ->setParameter('site', $site)
            ->setParameter('statut', AffaireStatut::CONTRAT)
            ->setParameter('archive', false)
            ->getQuery()
            ->getResult();

        // Structure des données pour le reporting de site
        $reportingData = [
            'affaires' => [],
            'totaux' => [
                'montantGlobal' => 0,
                'adherentCount' => 0,
                'affaireCount' => 0,
                'parCategorie' => [],
                'parActivite' => [
                    Activite::MULTITECH->value => 0,
                    Activite::ELEC->value => 0, 
                    Activite::CVC->value => 0
                ]
            ]
        ];

        $uniqueAdherents = [];
        $uniqueAffaires = [];
        $categories = [];

        foreach ($affectations as $affectation) {
            $affaireVersion = $affectation->getAffaire();
            $adherent = $affectation->getAdherent();
            $activite = $affectation->getActivite()->value;

            $affaireId = $affaireVersion->getMaster()->getId();
            $adherentId = $adherent->getAdherentId();

            // Initialiser l'affaire si elle n'existe pas encore
            if (!isset($reportingData['affaires'][$affaireId])) {
                $reportingData['affaires'][$affaireId] = [
                    'affaire' => $affaireVersion->getMaster(),
                    'version' => $affaireVersion,
                    'adherents' => [],
                    'bpus' => [],
                    'montantTotal' => 0,
                    'montantParCategorie' => []
                ];
                
                // Récupérer les applications de contrat pour cette affaire et ce site
                $contratApplications = $this->em->getRepository(ContratApplication::class)->findBy([
                    'affaire' => $affaireVersion,
                    'site' => $site
                ]);
                
                // Traiter tous les BPUs de cette affaire pour ce site
                foreach ($contratApplications as $contratApp) {
                    $bpu = $contratApp->getAffaireBPU();
                    $categorie = $bpu->getCategorie()->value;
                    $bpuType = $bpu->getType()->value;
                    
                    $prixMaintenance = $contratApp->getPrixMaintenanceSite() ?? 0;
                    $quantite = $contratApp->getQuantite() ?? 0;
                    
                    // Montant pour ce BPU
                    $montant = $prixMaintenance * $quantite;
                    
                    // Ajouter le BPU à la liste
                    $reportingData['affaires'][$affaireId]['bpus'][] = [
                        'bpu' => $bpu,
                        'contratApplication' => $contratApp,
                        'categorie' => $categorie,
                        'type' => $bpuType,
                        'quantite' => $quantite,
                        'prixUnitaire' => $prixMaintenance,
                        'montant' => $montant
                    ];
                    
                    // Mise à jour des totaux par catégorie
                    if (!isset($reportingData['affaires'][$affaireId]['montantParCategorie'][$categorie])) {
                        $reportingData['affaires'][$affaireId]['montantParCategorie'][$categorie] = 0;
                    }
                    $reportingData['affaires'][$affaireId]['montantParCategorie'][$categorie] += $montant;
                    
                    if (!isset($reportingData['totaux']['parCategorie'][$categorie])) {
                        $reportingData['totaux']['parCategorie'][$categorie] = 0;
                        $categories[$categorie] = true;
                    }
                    $reportingData['totaux']['parCategorie'][$categorie] += $montant;
                    
                    // Mise à jour des montants totaux
                    $reportingData['affaires'][$affaireId]['montantTotal'] += $montant;
                    $reportingData['totaux']['montantGlobal'] += $montant;
                    
                    // Mise à jour des totaux par activité basé sur le type de BPU
                    if ($bpuType === 'ELEC') {
                        $reportingData['totaux']['parActivite'][Activite::ELEC->value] += $montant;
                    } else if ($bpuType === 'CVC') {
                        $reportingData['totaux']['parActivite'][Activite::CVC->value] += $montant;
                    } else {
                        // Cas par défaut ou autre type
                        $reportingData['totaux']['parActivite'][Activite::MULTITECH->value] += $montant;
                    }
                }
            }
            
            // Ajouter l'adhérent avec son activité
            $reportingData['affaires'][$affaireId]['adherents'][] = [
                'adherent' => $adherent,
                'activite' => $activite
            ];
            
            // Compter les adhérents et affaires uniques
            $uniqueAdherents[$adherentId] = true;
            $uniqueAffaires[$affaireId] = true;
        }
        
        $reportingData['totaux']['adherentCount'] = count($uniqueAdherents);
        $reportingData['totaux']['affaireCount'] = count($uniqueAffaires);
        $reportingData['totaux']['categories'] = array_keys($categories);
        
        // Transformer les tableaux associatifs en tableaux indexés pour Twig
        foreach ($reportingData['affaires'] as &$affaire) {
            $affaire['montantParCategorie'] = array_map(function($cat, $montant) {
                return ['categorie' => $cat, 'montant' => $montant];
            }, array_keys($affaire['montantParCategorie']), array_values($affaire['montantParCategorie']));
        }
        $reportingData['affaires'] = array_values($reportingData['affaires']);
        
        $reportingData['totaux']['parCategorie'] = array_map(function($cat, $montant) {
            return ['categorie' => $cat, 'montant' => $montant];
        }, array_keys($reportingData['totaux']['parCategorie']), array_values($reportingData['totaux']['parCategorie']));

        return $reportingData;
    }

    /**
     * Récupère tous les sites assignés à un adhérent
     */
    public function getAdherentAssignedSites(Adherent $adherent): array
    {
        // Récupérer tous les sites auxquels cet adhérent est affecté
        $affectations = $this->em->getRepository(AffaireSiteAdherent::class)
            ->createQueryBuilder('asa')
            ->innerJoin('asa.site', 's')
            ->innerJoin('asa.affaire', 'av')
            ->andWhere('asa.adherent = :adherent')
            ->andWhere('av.archive = :archive')
            ->setParameter('adherent', $adherent)
            ->setParameter('archive', false)
            ->getQuery()
            ->getResult();

        // Extraire les sites uniques
        $sites = [];
        $siteIds = [];

        foreach ($affectations as $affectation) {
            $site = $affectation->getSite();
            $siteId = $site->getSiteId();
            
            if (!in_array($siteId, $siteIds)) {
                $sites[] = $site;
                $siteIds[] = $siteId;
            }
        }

        return $sites;
    }
    
    /**
     * Récupère toutes les affaires auxquelles un adhérent est affecté
     */
    public function getAdherentAffaires(Adherent $adherent): array
    {
        // Récupérer les affaires actives et non archivées
        $affaireAffectations = $this->em->getRepository(\App\Entity\AffaireAdherent::class)
            ->createQueryBuilder('aa')
            ->innerJoin('aa.affaire', 'av')
            ->innerJoin('av.master', 'am')
            ->andWhere('aa.adherent = :adherent')
            ->andWhere('av.archive = :archive')
            ->andWhere('av.statut = :statut')
            ->setParameter('adherent', $adherent)
            ->setParameter('archive', false)
            ->setParameter('statut', \App\Enum\AffaireStatut::CONTRAT)
            ->getQuery()
            ->getResult();
            
        // Extraire les affaires uniques (AffaireVersion)
        return array_map(function($aa) {
            return $aa->getAffaire();
        }, $affaireAffectations);
    }
    
    /**
     * Récupère toutes les affaires d'un client
     */
    public function getClientAffaires(\App\Entity\Client $client): array
    {
        return $this->em->getRepository(\App\Entity\AffaireVersion::class)
            ->createQueryBuilder('av')
            ->innerJoin('av.master', 'am')
            ->andWhere('am.client = :client')
            ->andWhere('av.archive = :archive')
            ->andWhere('av.statut = :statut')
            ->setParameter('client', $client)
            ->setParameter('archive', false)
            ->setParameter('statut', \App\Enum\AffaireStatut::CONTRAT)
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Récupère toutes les affaires actives pour le reporting
     */
    public function getAllAffairesForReporting(): array
    {
        return $this->em->getRepository(\App\Entity\AffaireVersion::class)
            ->createQueryBuilder('av')
            ->innerJoin('av.master', 'am')
            ->andWhere('av.archive = :archive')
            ->andWhere('av.statut = :statut')
            ->setParameter('archive', false)
            ->setParameter('statut', \App\Enum\AffaireStatut::CONTRAT)
            ->orderBy('am.code', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Récupère les données de reporting pour une affaire
     */
    public function getAffaireReportingData(AffaireVersion $affaire): array
    {
        // Structure des données pour le reporting d'affaire
        $reportingData = [
            'adherents' => [],
            'sites' => [],
            'bpus' => [],
            'totaux' => [
                'montantGlobal' => 0,
                'sitesCount' => 0,
                'adherentsCount' => 0,
                'parCategorie' => [],
                'parActivite' => [
                    Activite::MULTITECH->value => 0,
                    Activite::ELEC->value => 0, 
                    Activite::CVC->value => 0
                ]
            ]
        ];
        
        // Récupérer les adhérents affectés à cette affaire
        $affaireAdherents = $this->em->getRepository(\App\Entity\AffaireAdherent::class)
            ->findBy(['affaire' => $affaire]);
        
        $uniqueAdherents = [];
        foreach ($affaireAdherents as $aa) {
            $adherent = $aa->getAdherent();
            $adherentId = $adherent->getAdherentId();
            
            if (!isset($uniqueAdherents[$adherentId])) {
                $reportingData['adherents'][] = [
                    'adherent' => $adherent,
                    'montantTotal' => 0
                ];
                $uniqueAdherents[$adherentId] = count($reportingData['adherents']) - 1;
            }
        }
        
        // Récupérer les sites affectés à cette affaire
        $sitesAffectations = $this->em->getRepository(AffaireSiteAdherent::class)
            ->findBy(['affaire' => $affaire]);
        
        $uniqueSites = [];
        foreach ($sitesAffectations as $sa) {
            $site = $sa->getSite();
            $siteId = $site->getSiteId();
            $adherent = $sa->getAdherent();
            $adherentId = $adherent->getAdherentId();
            $activite = $sa->getActivite()->value;
            
            // Ajouter le site s'il n'existe pas encore
            if (!isset($uniqueSites[$siteId])) {
                $reportingData['sites'][] = [
                    'site' => $site,
                    'adherents' => [],
                    'montantTotal' => 0,
                    'montantParActivite' => [
                        Activite::MULTITECH->value => 0,
                        Activite::ELEC->value => 0,
                        Activite::CVC->value => 0
                    ]
                ];
                $uniqueSites[$siteId] = count($reportingData['sites']) - 1;
            }
            
            // Ajouter l'adhérent et son activité pour ce site
            $reportingData['sites'][$uniqueSites[$siteId]]['adherents'][] = [
                'adherent' => $adherent,
                'activite' => $activite
            ];
        }
        
        // Récupérer tous les BPUs de cette affaire
        $bpus = $this->em->getRepository(AffaireBPU::class)->findBy(['affaire' => $affaire]);
        $categories = [];
        
        foreach ($bpus as $bpu) {
            $categorie = $bpu->getCategorie()->value;
            $type = $bpu->getType()->value;
            
            $reportingData['bpus'][] = [
                'bpu' => $bpu,
                'categorie' => $categorie,
                'type' => $type,
                'montantTotal' => 0,
                'sitesCount' => 0
            ];
            
            // Pour la synthèse des catégories
            if (!isset($categories[$categorie])) {
                $categories[$categorie] = true;
                $reportingData['totaux']['parCategorie'][$categorie] = 0;
            }
        }
        
        // Récupérer et calculer les montants pour les contrats applications
        $contrats = $this->em->getRepository(ContratApplication::class)->findBy(['affaire' => $affaire]);
        
        foreach ($contrats as $contrat) {
            $site = $contrat->getSite();
            $siteId = $site->getSiteId();
            $bpu = $contrat->getAffaireBPU();
            $bpuId = $bpu->getId();
            $categorie = $bpu->getCategorie()->value;
            $type = $bpu->getType()->value;
            
            $prixMaintenance = $contrat->getPrixMaintenanceSite() ?? 0;
            $quantite = $contrat->getQuantite() ?? 0;
            $montant = $prixMaintenance * $quantite;
            
            // Mise à jour des totaux
            $reportingData['totaux']['montantGlobal'] += $montant;
            $reportingData['totaux']['parCategorie'][$categorie] += $montant;
            
            // Par type de BPU
            if ($type === 'ELEC') {
                $reportingData['totaux']['parActivite'][Activite::ELEC->value] += $montant;
            } else if ($type === 'CVC') {
                $reportingData['totaux']['parActivite'][Activite::CVC->value] += $montant;
            } else {
                $reportingData['totaux']['parActivite'][Activite::MULTITECH->value] += $montant;
            }
            
            // Mise à jour du site
            if (isset($uniqueSites[$siteId])) {
                $reportingData['sites'][$uniqueSites[$siteId]]['montantTotal'] += $montant;
                if ($type === 'ELEC') {
                    $reportingData['sites'][$uniqueSites[$siteId]]['montantParActivite'][Activite::ELEC->value] += $montant;
                } else if ($type === 'CVC') {
                    $reportingData['sites'][$uniqueSites[$siteId]]['montantParActivite'][Activite::CVC->value] += $montant;
                } else {
                    $reportingData['sites'][$uniqueSites[$siteId]]['montantParActivite'][Activite::MULTITECH->value] += $montant;
                }
            }
            
            // Mise à jour du BPU
            foreach ($reportingData['bpus'] as &$bpuData) {
                if ($bpuData['bpu']->getId() === $bpuId) {
                    $bpuData['montantTotal'] += $montant;
                    $bpuData['sitesCount']++;
                    break;
                }
            }
            
            // Mise à jour de l'adhérent en fonction du site et de l'activité
            if (isset($uniqueSites[$siteId])) {
                $siteIndex = $uniqueSites[$siteId];
                foreach ($reportingData['sites'][$siteIndex]['adherents'] as $siteAdherent) {
                    $adherentId = $siteAdherent['adherent']->getAdherentId();
                    $activite = $siteAdherent['activite'];
                    
                    // Si l'activité correspond au type de BPU ou si c'est MULTITECH
                    if ($activite === Activite::MULTITECH->value || 
                        ($activite === Activite::ELEC->value && $type === 'ELEC') ||
                        ($activite === Activite::CVC->value && $type === 'CVC')) {
                        
                        if (isset($uniqueAdherents[$adherentId])) {
                            $adherentIndex = $uniqueAdherents[$adherentId];
                            $reportingData['adherents'][$adherentIndex]['montantTotal'] += $montant;
                        }
                    }
                }
            }
        }
        
        // Convertir les tableaux associatifs en tableaux indexés pour Twig
        $reportingData['totaux']['parCategorie'] = array_map(function($cat, $montant) {
            return ['categorie' => $cat, 'montant' => $montant];
        }, array_keys($reportingData['totaux']['parCategorie']), array_values($reportingData['totaux']['parCategorie']));
        
        $reportingData['totaux']['adherentsCount'] = count($reportingData['adherents']);
        $reportingData['totaux']['sitesCount'] = count($reportingData['sites']);
        
        return $reportingData;
    }
}
