<?php

namespace App\Entity;

use App\Repository\LctPaiementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LctPaiementRepository::class)]
class LctPaiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lctPaiements')]
    private ?LctContrat $contrat = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(nullable: true)]
    private ?float $montant = null;

    #[ORM\OneToMany(mappedBy: 'paiement', targetEntity: LctRepartition::class)]
    private Collection $lctRepartitions;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numReleve = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'lctPaiements')]
    private ?Agence $agence = null;

    public function __construct()
    {
        $this->lctRepartitions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContrat(): ?LctContrat
    {
        return $this->contrat;
    }

    public function setContrat(?LctContrat $contrat): self
    {
        $this->contrat = $contrat;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    /**
     * @return Collection<int, LctRepartition>
     */
    public function getLctRepartitions(): Collection
    {
        return $this->lctRepartitions;
    }

    public function addLctRepartition(LctRepartition $lctRepartition): self
    {
        if (!$this->lctRepartitions->contains($lctRepartition)) {
            $this->lctRepartitions->add($lctRepartition);
            $lctRepartition->setPaiement($this);
        }

        return $this;
    }

    public function removeLctRepartition(LctRepartition $lctRepartition): self
    {
        if ($this->lctRepartitions->removeElement($lctRepartition)) {
            // set the owning side to null (unless already changed)
            if ($lctRepartition->getPaiement() === $this) {
                $lctRepartition->setPaiement(null);
            }
        }

        return $this;
    }

    public function getNumReleve(): ?string
    {
        return $this->numReleve;
    }

    public function setNumReleve(?string $numReleve): self
    {
        $this->numReleve = $numReleve;

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

    public function getAgence(): ?Agence
    {
        return $this->agence;
    }

    public function setAgence(?Agence $agence): self
    {
        $this->agence = $agence;

        return $this;
    }
}
