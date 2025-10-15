<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManagerInterface;

#[ORM\Entity]
#[ORM\Table(name: 'affaire_adherent')]
class AffaireAdherent
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: AffaireVersion::class)]
    #[ORM\JoinColumn(name: 'affaire_version_id', referencedColumnName: 'id', nullable: true)]
    private ?AffaireVersion $affaire = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    #[ORM\JoinColumn(name: 'adherent_id', referencedColumnName: 'adherent_id', nullable: false)]
    private Adherent $adherent;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;


    public function getAffaire(): ?AffaireVersion
    {
        return $this->affaire;
    }
    public function setAffaire(?AffaireVersion $affaire): self
    {
        $this->affaire = $affaire;
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
