<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'affaire_master')]
class AffaireMaster
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(name: 'id', type: 'integer')]
	private ?int $id = null;

	#[ORM\Column(name: 'code', type: 'string', length: 255, unique: true)]
	private string $code;

	#[ORM\ManyToOne(targetEntity: Client::class)]
	#[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'client_id', nullable: true)]
	private ?Client $client = null;

	#[ORM\OneToOne(targetEntity: AffaireVersion::class)]
	#[ORM\JoinColumn(name: 'current_version_id', referencedColumnName: 'id', nullable: true)]
	private ?AffaireVersion $currentVersion = null;

	#[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
	private ?\DateTimeInterface $createdAt = null;

	#[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
	private ?\DateTimeInterface $updatedAt = null;

	#[ORM\Column(name: 'archive', type: 'boolean')]
	private bool $archive = false;

	#[ORM\OneToMany(mappedBy: 'master', targetEntity: AffaireVersion::class, cascade: ['persist', 'remove'])]
	private Collection $versions;

	public function __construct()
	{
		$now = new \DateTimeImmutable();
		$this->createdAt = $now;
		$this->updatedAt = $now;
		$this->versions = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getCode(): string
	{
		return $this->code;
	}

	public function setCode(string $code): self
	{
		$this->code = $code;
		return $this;
	}

	public function getClient(): ?Client
	{
		return $this->client;
	}

	public function setClient(?Client $client): self
	{
		$this->client = $client;
		return $this;
	}

	public function getCurrentVersion(): ?AffaireVersion
	{
		return $this->currentVersion;
	}

	public function setCurrentVersion(?AffaireVersion $version): self
	{
		$this->currentVersion = $version;
		return $this;
	}

	public function getVersions(): Collection
	{
		return $this->versions;
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

	public function isArchive(): bool
	{
		return $this->archive;
	}

	public function setArchive(bool $v): self
	{
		$this->archive = $v;
		return $this;
	}

}


