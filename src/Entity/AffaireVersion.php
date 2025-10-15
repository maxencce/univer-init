<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Enum\AffaireStatut;

#[ORM\Entity]
#[ORM\Table(name: 'affaire_version')]
class AffaireVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AffaireMaster::class, inversedBy: 'versions')]
    #[ORM\JoinColumn(name: 'master_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private AffaireMaster $master;

    #[ORM\Column(name: 'version_number', type: 'integer')]
    private int $versionNumber;

    #[ORM\Column(name: 'statut', type: 'string', enumType: AffaireStatut::class, options: ['default' => 'OFFRE'])]
    private AffaireStatut $statut = AffaireStatut::OFFRE;

    #[ORM\Column(name: 'date_debut', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(name: 'date_fin', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(name: 'archive', type: 'boolean')]
    private bool $archive = false;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'affaire', targetEntity: AffaireBPU::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $affaireBPUs;

    #[ORM\OneToMany(mappedBy: 'affaire', targetEntity: AffaireAdherent::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $affaireAdherents;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->affaireBPUs = new ArrayCollection();
        $this->affaireAdherents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAffaireId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $date): self
    {
        $this->dateDebut = $date;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $date): self
    {
        $this->dateFin = $date;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $dt): self
    {
        $this->createdAt = $dt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $dt): self
    {
        $this->updatedAt = $dt;
        return $this;
    }

    public function getStatut(): AffaireStatut
    {
        return $this->statut;
    }

    public function setStatut(AffaireStatut $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getMaster(): AffaireMaster
    {
        return $this->master;
    }

    public function setMaster(AffaireMaster $master): self
    {
        $this->master = $master;
        return $this;
    }

    public function getVersionNumber(): int
    {
        return $this->versionNumber;
    }

    public function setVersionNumber(int $versionNumber): self
    {
        $this->versionNumber = $versionNumber;
        return $this;
    }

    public function getAffaireBPUs(): Collection
    {
        return $this->affaireBPUs;
    }

    public function getAffaireAdherents(): Collection
    {
        return $this->affaireAdherents;
    }
}


