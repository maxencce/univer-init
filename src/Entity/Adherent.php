<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Adherent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'adherent_id', type: 'integer')]
    private ?int $adherentId = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 255)]
    private string $nom;

    /**
     * One Adherent can have multiple Utilisateur accounts.
     */
    #[ORM\OneToMany(mappedBy: 'adherent', targetEntity: Utilisateur::class)]
    private \Doctrine\Common\Collections\Collection $utilisateurs;

    public function __construct()
    {
        $this->utilisateurs = new \Doctrine\Common\Collections\ArrayCollection();
    }

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function getAdherentId(): ?int
    {
        return $this->adherentId;
    }
    public function getNom(): string
    {
        return $this->nom;
    }
    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }
    /**
     * Retourne la collection des comptes utilisateurs liés à cet adhérent.
     */
    public function getUtilisateurs(): \Doctrine\Common\Collections\Collection
    {
        return $this->utilisateurs;
    }
    public function addUtilisateur(Utilisateur $utilisateur): self
    {
        if (! $this->utilisateurs->contains($utilisateur)) {
            $this->utilisateurs->add($utilisateur);
            $utilisateur->setAdherent($this);
        }
        return $this;
    }
    public function removeUtilisateur(Utilisateur $utilisateur): self
    {
        if ($this->utilisateurs->removeElement($utilisateur)) {
            if ($utilisateur->getAdherent() === $this) {
                $utilisateur->setAdherent(null);
            }
        }
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