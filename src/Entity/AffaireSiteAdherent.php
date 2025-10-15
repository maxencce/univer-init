<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Enum\Activite;

#[ORM\Entity]
#[ORM\Table(name: 'affaire_site_adherent')]
class AffaireSiteAdherent
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
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    #[ORM\JoinColumn(name: 'adherent_id', referencedColumnName: 'adherent_id', nullable: false)]
    private Adherent $adherent;

    #[ORM\Column(name: 'activite', type: 'string', enumType: Activite::class, options: ['default' => 'MULTITECH'])]
    private Activite $activite = Activite::MULTITECH;

    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    #[ORM\JoinColumn(name: 'sous_traitant_id', referencedColumnName: 'adherent_id', nullable: true)]
    private ?Adherent $sousTraitant = null;

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
    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }
    public function setAdherent(Adherent $adherent): self
    {
        $this->adherent = $adherent;
        return $this;
    }
    public function getActivite(): Activite
    {
        return $this->activite;
    }
    public function setActivite(Activite $activite): self
    {
        $this->activite = $activite;
        return $this;
    }
    public function getSousTraitant(): ?Adherent
    {
        return $this->sousTraitant;
    }
    public function setSousTraitant(?Adherent $sousTraitant): self
    {
        $this->sousTraitant = $sousTraitant;
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