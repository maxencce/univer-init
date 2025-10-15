<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Enum\SiteStatut;
use App\Repository\SiteRepository;
use App\Enum\Activite;

#[ORM\Entity(repositoryClass: SiteRepository::class)]
class Site
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'site_id', type: 'integer')]
    private ?int $siteId = null;

    #[ORM\Column(name: 'code_gesec', type: 'string', length: 255, unique: true)]
    private string $codeGesec;

    #[ORM\Column(name: 'code_site', type: 'string', length: 255)]
    private string $codeSite;

    #[ORM\Column(name: 'nom', type: 'text', nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(name: 'adresse', type: 'text', nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(name: 'code_postal', type: 'string', length: 20, nullable: true)]
    private ?string $codePostal = null;

    #[ORM\Column(name: 'ville', type: 'string', length: 255, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(name: 'region_client', type: 'string', length: 255, nullable: true)]
    private ?string $regionClient = null;

    #[ORM\Column(name: 'contact_nom', type: 'string', length: 255, nullable: true)]
    private ?string $contactNom = null;

    #[ORM\Column(name: 'contact_email', type: 'string', length: 255, nullable: true)]
    private ?string $contactEmail = null;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'client_id', nullable: false)]
    private Client $client;

    #[ORM\Column(name: 'statut', type: 'string', enumType: SiteStatut::class, nullable: true)]
    private ?SiteStatut $statut = null;

    #[ORM\Column(name: 'activite', type: 'string', enumType: Activite::class, nullable: true)]
    private ?Activite $activite = null;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function getSiteId(): ?int
    {
        return $this->siteId;
    }
    public function getCodeGesec(): string
    {
        return $this->codeGesec;
    }
    public function setCodeGesec(string $codeGesec): self
    {
        $this->codeGesec = $codeGesec;
        return $this;
    }
    public function getCodeSite(): string
    {
        return $this->codeSite;
    }
    public function setCodeSite(string $codeSite): self
    {
        $this->codeSite = $codeSite;
        return $this;
    }
    public function getNom(): ?string
    {
        return $this->nom;
    }
    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }
    public function getAdresse(): ?string
    {
        return $this->adresse;
    }
    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }
    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }
    public function setCodePostal(?string $codePostal): self
    {
        $this->codePostal = $codePostal;
        return $this;
    }
    public function getVille(): ?string
    {
        return $this->ville;
    }
    public function setVille(?string $ville): self
    {
        $this->ville = $ville;
        return $this;
    }
    public function getRegionClient(): ?string
    {
        return $this->regionClient;
    }
    public function setRegionClient(?string $regionClient): self
    {
        $this->regionClient = $regionClient;
        return $this;
    }
    public function getContactNom(): ?string
    {
        return $this->contactNom;
    }
    public function setContactNom(?string $contactNom): self
    {
        $this->contactNom = $contactNom;
        return $this;
    }
    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }
    public function setContactEmail(?string $contactEmail): self
    {
        $this->contactEmail = $contactEmail;
        return $this;
    }
    public function getClient(): Client
    {
        return $this->client;
    }
    public function setClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }
    public function getStatut(): ?SiteStatut
    {
        return $this->statut;
    }
    public function setStatut(?SiteStatut $statut): self
    {
        $this->statut = $statut;
        return $this;
    }
    public function getActivite(): ?Activite
    {
        return $this->activite;
    }
    public function setActivite(?Activite $activite): self
    {
        $this->activite = $activite;
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