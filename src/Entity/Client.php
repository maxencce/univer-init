<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Enum\ClientType;

#[ORM\Entity]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'client_id', type: 'integer')]
    private ?int $clientId = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 255, unique: true, nullable: false)]
    private string $nom;

    #[ORM\Column(name: 'type', type: 'string', enumType: ClientType::class)]
    private ClientType $type;

    #[ORM\Column(name: 'adresse', type: 'text')]
    private string $adresse;

    #[ORM\Column(name: 'siren', type: 'string', length: 255)]
    private string $siren;

    #[ORM\Column(name: 'contact_nom', type: 'string', length: 255)]
    private string $contactNom;

    #[ORM\Column(name: 'contact_email', type: 'string', length: 255)]
    private string $contactEmail;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function getClientId(): ?int
    {
        return $this->clientId;
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
    public function getType(): ClientType
    {
        return $this->type;
    }
    public function setType(ClientType $type): self
    {
        $this->type = $type;
        return $this;
    }
    public function getAdresse(): string
    {
        return $this->adresse;
    }
    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }
    public function getSiren(): string
    {
        return $this->siren;
    }
    public function setSiren(string $siren): self
    {
        $this->siren = $siren;
        return $this;
    }
    public function getContactNom(): string
    {
        return $this->contactNom;
    }
    public function setContactNom(string $contactNom): self
    {
        $this->contactNom = $contactNom;
        return $this;
    }
    public function getContactEmail(): string
    {
        return $this->contactEmail;
    }
    public function setContactEmail(string $contactEmail): self
    {
        $this->contactEmail = $contactEmail;
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