<?php

namespace App\Entity;

use App\Repository\CmpCompteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CmpCompteRepository::class)]
class CmpCompte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cmpComptes')]
    private ?Agence $agence = null;

    #[ORM\ManyToOne(inversedBy: 'cmpComptes')]
    private ?CmpBanque $banque = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numero = null;

    #[ORM\Column(nullable: true)]
    private ?float $solde = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'compte', targetEntity: CmpOperation::class)]
    private Collection $cmpOperations;

    public function __construct()
    {
        $this->cmpOperations = new ArrayCollection();
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

    public function getBanque(): ?CmpBanque
    {
        return $this->banque;
    }

    public function setBanque(?CmpBanque $banque): self
    {
        $this->banque = $banque;

        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getSolde(): ?float
    {
        return $this->solde;
    }

    public function setSolde(?float $solde): self
    {
        $this->solde = $solde;

        return $this;
    }

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(?bool $statut): self
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
            $cmpOperation->setCompte($this);
        }

        return $this;
    }

    public function removeCmpOperation(CmpOperation $cmpOperation): self
    {
        if ($this->cmpOperations->removeElement($cmpOperation)) {
            // set the owning side to null (unless already changed)
            if ($cmpOperation->getCompte() === $this) {
                $cmpOperation->setCompte(null);
            }
        }

        return $this;
    }
}
