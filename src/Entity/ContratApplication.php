<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\AffaireBPU;

#[ORM\Entity]
#[ORM\Table(name: 'contrat_application')]
class ContratApplication
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: AffaireVersion::class)]
    #[ORM\JoinColumn(name: 'affaire_version_id', referencedColumnName: 'id', nullable: false)]
    private AffaireVersion $affaire;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Site::class)]
    #[ORM\JoinColumn(name: 'site_id', referencedColumnName: 'site_id', nullable: false)]
    private Site $site;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: AffaireBPU::class)]
    #[ORM\JoinColumn(name: 'affaire_bpu_id', referencedColumnName: 'affaire_bpu_id', nullable: false)]
    private AffaireBPU $affaireBPU;

    #[ORM\Column(name: 'quantite', type: 'integer')]
    private int $quantite;

    #[ORM\Column(name: 'prix_maintenance_site', type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $prixMaintenanceSite = null;

    #[ORM\Column(name: 'nb_visites_technique', type: 'integer', nullable: true)]
    private ?int $nbVisitesTechnique = null;

    #[ORM\Column(name: 'nb_visites_bon_fonctionnement', type: 'integer', nullable: true)]
    private ?int $nbVisitesBonFonctionnement = null;

    #[ORM\Column(name: 'prix_visite_technique', type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $prixVisiteTechnique = null;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function getAffaire(): AffaireVersion
    {
        return $this->affaire;
    }
    public function setAffaire(AffaireVersion $affaire): self
    {
        $this->affaire = $affaire;
        return $this;
    }
    public function getSite(): Site
    {
        return $this->site;
    }
    public function setSite(Site $site): self
    {
        $this->site = $site;
        return $this;
    }
    public function getAffaireBPU(): AffaireBPU
    {
        return $this->affaireBPU;
    }
    public function setAffaireBPU(AffaireBPU $affaireBPU): self
    {
        $this->affaireBPU = $affaireBPU;
        return $this;
    }
    public function getQuantite(): int
    {
        return $this->quantite;
    }
    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }
    public function getPrixMaintenanceSite(): ?float
    {
        return $this->prixMaintenanceSite;
    }
    public function setPrixMaintenanceSite(?float $prixMaintenanceSite): self
    {
        $this->prixMaintenanceSite = $prixMaintenanceSite;
        return $this;
    }

    public function getNbVisitesTechnique(): ?int
    {
        return $this->nbVisitesTechnique;
    }

    public function setNbVisitesTechnique(?int $nbVisitesTechnique): self
    {
        $this->nbVisitesTechnique = $nbVisitesTechnique;
        return $this;
    }

    public function getNbVisitesBonFonctionnement(): ?int
    {
        return $this->nbVisitesBonFonctionnement;
    }

    public function setNbVisitesBonFonctionnement(?int $nbVisitesBonFonctionnement): self
    {
        $this->nbVisitesBonFonctionnement = $nbVisitesBonFonctionnement;
        return $this;
    }

    public function getPrixVisiteTechnique(): ?float
    {
        return $this->prixVisiteTechnique;
    }

    public function setPrixVisiteTechnique(?float $prixVisiteTechnique): self
    {
        $this->prixVisiteTechnique = $prixVisiteTechnique;
        return $this;
    }
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
} 