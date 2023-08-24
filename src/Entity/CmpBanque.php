<?php

namespace App\Entity;

use App\Repository\CmpBanqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CmpBanqueRepository::class)]
class CmpBanque
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cmpBanques')]
    private ?Agence $agence = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'banque', targetEntity: CmpCompte::class)]
    private Collection $cmpComptes;

    #[ORM\OneToMany(mappedBy: 'banque', targetEntity: CmpOperation::class)]
    private Collection $cmpOperations;

    #[ORM\OneToMany(mappedBy: 'banque', targetEntity: ChkCheque::class)]
    private Collection $chkCheques;

    public function __construct()
    {
        $this->cmpComptes = new ArrayCollection();
        $this->cmpOperations = new ArrayCollection();
        $this->chkCheques = new ArrayCollection();
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

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
     * @return Collection<int, CmpCompte>
     */
    public function getCmpComptes(): Collection
    {
        return $this->cmpComptes;
    }

    public function addCmpCompte(CmpCompte $cmpCompte): self
    {
        if (!$this->cmpComptes->contains($cmpCompte)) {
            $this->cmpComptes->add($cmpCompte);
            $cmpCompte->setBanque($this);
        }

        return $this;
    }

    public function removeCmpCompte(CmpCompte $cmpCompte): self
    {
        if ($this->cmpComptes->removeElement($cmpCompte)) {
            // set the owning side to null (unless already changed)
            if ($cmpCompte->getBanque() === $this) {
                $cmpCompte->setBanque(null);
            }
        }

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
            $cmpOperation->setBanque($this);
        }

        return $this;
    }

    public function removeCmpOperation(CmpOperation $cmpOperation): self
    {
        if ($this->cmpOperations->removeElement($cmpOperation)) {
            // set the owning side to null (unless already changed)
            if ($cmpOperation->getBanque() === $this) {
                $cmpOperation->setBanque(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ChkCheque>
     */
    public function getChkCheques(): Collection
    {
        return $this->chkCheques;
    }

    public function addChkCheque(ChkCheque $chkCheque): self
    {
        if (!$this->chkCheques->contains($chkCheque)) {
            $this->chkCheques->add($chkCheque);
            $chkCheque->setBanque($this);
        }

        return $this;
    }

    public function removeChkCheque(ChkCheque $chkCheque): self
    {
        if ($this->chkCheques->removeElement($chkCheque)) {
            // set the owning side to null (unless already changed)
            if ($chkCheque->getBanque() === $this) {
                $chkCheque->setBanque(null);
            }
        }

        return $this;
    }
}
