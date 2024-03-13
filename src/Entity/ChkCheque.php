<?php

namespace App\Entity;

use App\Repository\ChkChequeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChkChequeRepository::class)]
class ChkCheque
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'chkCheques')]
    private ?CmpBanque $banque = null;

    #[ORM\ManyToOne(inversedBy: 'chkCheques')]
    private ?ChkType $type = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nomChequier = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numCheque = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCheque = null;

    #[ORM\Column(nullable: true)]
    private ?float $montant = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateDeclaration = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'chkCheques')]
    private ?ChkStatut $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'chkCheques')]
    private ?Agence $agence = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statutGen = null;

    #[ORM\OneToMany(mappedBy: 'cheque', targetEntity: CmpOperation::class)]
    private Collection $cmpOperations;

    public function __construct()
    {
        $this->cmpOperations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBanque(): ?CmpBanque
    {
        return $this->banque;
    }

    public function setBanque(?CmpBanque $banque): self
    {
        $this->banque = $banque;

        return $this;
    }

    public function getType(): ?ChkType
    {
        return $this->type;
    }

    public function setType(?ChkType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNomChequier(): ?string
    {
        return $this->nomChequier;
    }

    public function setNomChequier(?string $nomChequier): self
    {
        $this->nomChequier = $nomChequier;

        return $this;
    }

    public function getNumCheque(): ?string
    {
        return $this->numCheque;
    }

    public function setNumCheque(?string $numCheque): self
    {
        $this->numCheque = $numCheque;

        return $this;
    }

    public function getDateCheque(): ?\DateTimeInterface
    {
        return $this->dateCheque;
    }

    public function setDateCheque(?\DateTimeInterface $dateCheque): self
    {
        $this->dateCheque = $dateCheque;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(?float $montant): self
    {
        $this->montant = $montant;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStatut(): ?ChkStatut
    {
        return $this->statut;
    }

    public function setStatut(?ChkStatut $statut): self
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

    public function getAgence(): ?Agence
    {
        return $this->agence;
    }

    public function setAgence(?Agence $agence): self
    {
        $this->agence = $agence;

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

    /**
     * @return Collection<int, CmpOperation>
     */
    public function getCmpOperations(): Collection
    {
        return $this->cmpOperations;
    }

    public function addCmpOperation(CmpOperation $cmpOperation): self
    {
        if (!$this->cmpOperations->contains($cmpOperation)) {
            $this->cmpOperations->add($cmpOperation);
            $cmpOperation->setCheque($this);
        }

        return $this;
    }

    public function removeCmpOperation(CmpOperation $cmpOperation): self
    {
        if ($this->cmpOperations->removeElement($cmpOperation)) {
            // set the owning side to null (unless already changed)
            if ($cmpOperation->getCheque() === $this) {
                $cmpOperation->setCheque(null);
            }
        }

        return $this;
    }
}
