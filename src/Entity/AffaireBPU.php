<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Enum\BpuEnumType;
use App\Enum\BpuCategorie;

#[ORM\Entity]
#[ORM\Table(name: 'affaire_bpu')]
#[ORM\Index(columns: ['affaire_version_id', 'categorie', 'type'])]
class AffaireBPU
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(name: 'affaire_bpu_id', type: 'integer')]
	private ?int $id = null;

	#[ORM\ManyToOne(targetEntity: AffaireVersion::class)]
	#[ORM\JoinColumn(name: 'affaire_version_id', referencedColumnName: 'id', nullable: false)]
	private AffaireVersion $affaire;

	#[ORM\Column(name: 'type', type: 'string', enumType: BpuEnumType::class)]
	private BpuEnumType $type;

	#[ORM\Column(name: 'categorie', type: 'string', enumType: BpuCategorie::class)]
	private BpuCategorie $categorie;

	#[ORM\Column(name: 'description', type: 'text', nullable: true)]
	private ?string $description = null;

	#[ORM\Column(name: 'puissance', type: 'string', length: 50, nullable: true)]
	private ?string $puissance = null;

	#[ORM\Column(name: 'temps_maintenance_annuelle', type: 'float', nullable: true)]
	private ?float $tempsMaintenanceAnnuelle = null;

	#[ORM\Column(name: 'nb_heures_visite_technique', type: 'float', nullable: true)]
	private ?float $nbHeuresVisiteTechnique = null;

	#[ORM\Column(name: 'nb_heures_visite_bon_fonctionnement', type: 'text', nullable: true)]
	private ?string $nbHeuresVisiteBonFonctionnement = null;

	#[ORM\Column(name: 'total_heures_equipement', type: 'text', nullable: true)]
	private ?string $totalHeuresEquipement = null;

	#[ORM\Column(name: 'prix_mo', type: 'float', nullable: true)]
	private ?float $prixMo = null;

	#[ORM\Column(name: 'frais_kilometrique_unitaire', type: 'float', nullable: true)]
	private ?float $fraisKilometriqueUnitaire = null;

	#[ORM\Column(name: 'nb_km_moyen', type: 'integer', nullable: true)]
	private ?int $nbKmMoyen = null;

	#[ORM\Column(name: 'total_frais_kilometrique', type: 'text', nullable: true)]
	private ?string $totalFraisKilometrique = null;

	#[ORM\Column(name: 'divers_consommable', type: 'float', nullable: true)]
	private ?float $diversConsommable = null;

	#[ORM\Column(name: 'prix_equipement_1_visite_technique', type: 'text', nullable: true)]
	private ?string $prixEquipement1VisiteTechnique = null;

	#[ORM\Column(name: 'prix_total_equipement', type: 'text', nullable: true)]
	private ?string $prixTotalEquipement = null;

	#[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
	private ?\DateTimeInterface $createdAt = null;

	#[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
	private ?\DateTimeInterface $updatedAt = null;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getAffaire(): AffaireVersion
	{
		return $this->affaire;
	}

	public function setAffaire(AffaireVersion $affaire): self
	{
		$this->affaire = $affaire;
		return $this;
	}

	public function getType(): BpuEnumType
	{
		return $this->type;
	}

	public function setType(BpuEnumType $type): self
	{
		$this->type = $type;
		return $this;
	}

	public function getCategorie(): BpuCategorie
	{
		return $this->categorie;
	}

	public function setCategorie(BpuCategorie $categorie): self
	{
		$this->categorie = $categorie;
		return $this;
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

	public function getPuissance(): ?string
	{
		return $this->puissance;
	}

	public function setPuissance(?string $puissance): self
	{
		$this->puissance = $puissance;
		return $this;
	}

	public function getTempsMaintenanceAnnuelle(): ?float
	{
		return $this->tempsMaintenanceAnnuelle;
	}

	public function setTempsMaintenanceAnnuelle(?float $tempsMaintenanceAnnuelle): self
	{
		$this->tempsMaintenanceAnnuelle = $tempsMaintenanceAnnuelle;
		return $this;
	}

	public function getNbHeuresVisiteTechnique(): ?float
	{
		return $this->nbHeuresVisiteTechnique;
	}

	public function setNbHeuresVisiteTechnique(?float $nbHeuresVisiteTechnique): self
	{
		$this->nbHeuresVisiteTechnique = $nbHeuresVisiteTechnique;
		return $this;
	}

	public function getNbHeuresVisiteBonFonctionnement(): ?float
	{
		return $this->nbHeuresVisiteBonFonctionnement;
	}

	public function setNbHeuresVisiteBonFonctionnement(?float $nbHeuresVisiteBonFonctionnement): self
	{
		$this->nbHeuresVisiteBonFonctionnement = $nbHeuresVisiteBonFonctionnement;
		return $this;
	}

	public function getTotalHeuresEquipement(): ?float
	{
		return $this->totalHeuresEquipement;
	}

	public function setTotalHeuresEquipement(?float $totalHeuresEquipement): self
	{
		$this->totalHeuresEquipement = $totalHeuresEquipement;
		return $this;
	}

	public function getPrixMo(): ?float
	{
		return $this->prixMo;
	}

	public function setPrixMo(?float $prixMo): self
	{
		$this->prixMo = $prixMo;
		return $this;
	}

	public function getFraisKilometriqueUnitaire(): ?float
	{
		return $this->fraisKilometriqueUnitaire;
	}

	public function setFraisKilometriqueUnitaire(?float $fraisKilometriqueUnitaire): self
	{
		$this->fraisKilometriqueUnitaire = $fraisKilometriqueUnitaire;
		return $this;
	}

	public function getNbKmMoyen(): ?int
	{
		return $this->nbKmMoyen;
	}

	public function setNbKmMoyen(?int $nbKmMoyen): self
	{
		$this->nbKmMoyen = $nbKmMoyen;
		return $this;
	}

	public function getTotalFraisKilometrique(): ?float
	{
		return $this->totalFraisKilometrique;
	}

	public function setTotalFraisKilometrique(?float $totalFraisKilometrique): self
	{
		$this->totalFraisKilometrique = $totalFraisKilometrique;
		return $this;
	}

	public function getDiversConsommable(): ?float
	{
		return $this->diversConsommable;
	}

	public function setDiversConsommable(?float $diversConsommable): self
	{
		$this->diversConsommable = $diversConsommable;
		return $this;
	}

	public function getPrixEquipement1VisiteTechnique(): ?float
	{
		return $this->prixEquipement1VisiteTechnique;
	}

	public function setPrixEquipement1VisiteTechnique(?float $prixEquipement1VisiteTechnique): self
	{
		$this->prixEquipement1VisiteTechnique = $prixEquipement1VisiteTechnique;
		return $this;
	}

	public function getPrixTotalEquipement(): ?float
	{
		return $this->prixTotalEquipement;
	}

	public function setPrixTotalEquipement(?float $prixTotalEquipement): self
	{
		$this->prixTotalEquipement = $prixTotalEquipement;
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