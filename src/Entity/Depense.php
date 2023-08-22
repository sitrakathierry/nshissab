<?php

namespace App\Entity;

use App\Repository\DepenseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepenseRepository::class)]
class Depense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'depenses')]
    private ?Agence $agence = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $element = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nomConcerne = null;

    #[ORM\ManyToOne(inversedBy: 'depenses')]
    private ?DepService $service = null;

    #[ORM\ManyToOne(inversedBy: 'depenses')]
    private ?DepMotif $motif = null;

    #[ORM\ManyToOne(inversedBy: 'depenses')]
    private ?DepModePaiement $modePaiement = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateMode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeroMode = null;

    #[ORM\Column(nullable: true)]
    private ?float $montantDep = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numFacture = null;

    #[ORM\Column(nullable: true)]
    private ?int $moisFacture = null;

    #[ORM\Column(nullable: true)]
    private ?int $anneeFacture = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateDeclaration = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statutGen = null;

    #[ORM\ManyToOne(inversedBy: 'depenses')]
    private ?DepStatut $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'depense', targetEntity: DepDetails::class)]
    private Collection $depDetails;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $editeurMode = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public function __construct()
    {
        $this->depDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgence(): ?Agence
    {
        return $this->agence;
    }

    public function setAgence(?Agence $agence): self
    {
        $this->agence = $agence;

        return $this;
    }

    public function getElement(): ?string
    {
        return $this->element;
    }

    public function setElement(?string $element): self
    {
        $this->element = $element;

        return $this;
    }

    public function getNomConcerne(): ?string
    {
        return $this->nomConcerne;
    }

    public function setNomConcerne(?string $nomConcerne): self
    {
        $this->nomConcerne = $nomConcerne;

        return $this;
    }

    public function getService(): ?DepService
    {
        return $this->service;
    }

    public function setService(?DepService $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function getMotif(): ?DepMotif
    {
        return $this->motif;
    }

    public function setMotif(?DepMotif $motif): self
    {
        $this->motif = $motif;

        return $this;
    }

    public function getModePaiement(): ?DepModePaiement
    {
        return $this->modePaiement;
    }

    public function setModePaiement(?DepModePaiement $modePaiement): self
    {
        $this->modePaiement = $modePaiement;

        return $this;
    }

    public function getDateMode(): ?\DateTimeInterface
    {
        return $this->dateMode;
    }

    public function setDateMode(?\DateTimeInterface $dateMode): self
    {
        $this->dateMode = $dateMode;

        return $this;
    }

    public function getNumeroMode(): ?string
    {
        return $this->numeroMode;
    }

    public function setNumeroMode(?string $numeroMode): self
    {
        $this->numeroMode = $numeroMode;

        return $this;
    }

    public function getMontantDep(): ?float
    {
        return $this->montantDep;
    }

    public function setMontantDep(?float $montantDep): self
    {
        $this->montantDep = $montantDep;

        return $this;
    }

    public function getNumFacture(): ?string
    {
        return $this->numFacture;
    }

    public function setNumFacture(?string $numFacture): self
    {
        $this->numFacture = $numFacture;

        return $this;
    }

    public function getMoisFacture(): ?int
    {
        return $this->moisFacture;
    }

    public function setMoisFacture(?int $moisFacture): self
    {
        $this->moisFacture = $moisFacture;

        return $this;
    }

    public function getAnneeFacture(): ?int
    {
        return $this->anneeFacture;
    }

    public function setAnneeFacture(?int $anneeFacture): self
    {
        $this->anneeFacture = $anneeFacture;

        return $this;
    }

    public function getDateDeclaration(): ?\DateTimeInterface
    {
        return $this->dateDeclaration;
    }

    public function setDateDeclaration(?\DateTimeInterface $dateDeclaration): self
    {
        $this->dateDeclaration = $dateDeclaration;

        return $this;
    }

    public function isStatutGen(): ?bool
    {
        return $this->statutGen;
    }

    public function setStatutGen(?bool $statutGen): self
    {
        $this->statutGen = $statutGen;

        return $this;
    }

    public function getStatut(): ?DepStatut
    {
        return $this->statut;
    }

    public function setStatut(?DepStatut $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, DepDetails>
     */
    public function getDepDetails(): Collection
    {
        return $this->depDetails;
    }

    public function addDepDetail(DepDetails $depDetail): self
    {
        if (!$this->depDetails->contains($depDetail)) {
            $this->depDetails->add($depDetail);
            $depDetail->setDepense($this);
        }

        return $this;
    }

    public function removeDepDetail(DepDetails $depDetail): self
    {
        if ($this->depDetails->removeElement($depDetail)) {
            // set the owning side to null (unless already changed)
            if ($depDetail->getDepense() === $this) {
                $depDetail->setDepense(null);
            }
        }

        return $this;
    }

    public function getEditeurMode(): ?string
    {
        return $this->editeurMode;
    }

    public function setEditeurMode(?string $editeurMode): self
    {
        $this->editeurMode = $editeurMode;

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
}
