<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Enum\UserRole;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use App\Entity\Adherent;

#[ORM\Entity]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'user_id', type: 'integer')]
    private ?int $userId = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 255)]
    private string $nom;

    #[ORM\Column(name: 'email', type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(name: 'mot_de_passe', type: 'string', length: 255)]
    private string $motDePasse;

    #[ORM\Column(name: 'role', type: 'string', enumType: UserRole::class)]
    private UserRole $role;

    #[ORM\Column(name: 'enabled', type: 'boolean', options: ['default' => true])]
    private bool $enabled = true;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Adherent::class, inversedBy: 'utilisateurs')]
    #[ORM\JoinColumn(name: 'adherent_id', referencedColumnName: 'adherent_id', nullable: true)]
    private ?Adherent $adherent = null;

    public function __construct()
    {
    }

    public function getUserId(): ?int
    {
        return $this->userId;
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
    public function getEmail(): string
    {
        return $this->email;
    }
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }
    public function getMotDePasse(): string
    {
        return $this->motDePasse;
    }
    public function setMotDePasse(string $motDePasse): self
    {
        $this->motDePasse = $motDePasse;
        return $this;
    }
    public function getRole(): UserRole
    {
        return $this->role;
    }
    public function setRole(UserRole $role): self
    {
        $this->role = $role;
        return $this;
    }
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
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
    // UserInterface & PasswordAuthenticatedUserInterface
    public function getPassword(): string
    {
        return $this->motDePasse;
    }
    public function getRoles(): array
    {
        return ["ROLE_" . $this->role->name];
    }
    public function getSalt(): ?string
    {
        return null;
    }
    public function getUserIdentifier(): string
    {
        return $this->email;
    }
    public function eraseCredentials(): void
    {
        // Rien à faire
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): self
    {
        $this->adherent = $adherent;
        return $this;
    }
} 