<?php

namespace App\Entity;

use App\Repository\AchStatutRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AchStatutRepository::class)]
class AchStatut
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\OneToMany(mappedBy: 'statut', targetEntity: AchBonCommande::class)]
    private Collection $achBonCommandes;

    #[ORM\OneToMany(mappedBy: 'statut', targetEntity: AchDetails::class)]
    private Collection $achDetails;

    public function __construct()
    {
        $this->achBonCommandes = new ArrayCollection();
        $this->achDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @return Collection<int, AchBonCommande>
     */
    public function getAchBonCommandes(): Collection
    {
        return $this->achBonCommandes;
    }

    public function addAchBonCommande(AchBonCommande $achBonCommande): self
    {
        if (!$this->achBonCommandes->contains($achBonCommande)) {
            $this->achBonCommandes->add($achBonCommande);
            $achBonCommande->setStatut($this);
        }

        return $this;
    }

    public function removeAchBonCommande(AchBonCommande $achBonCommande): self
    {
        if ($this->achBonCommandes->removeElement($achBonCommande)) {
            // set the owning side to null (unless already changed)
            if ($achBonCommande->getStatut() === $this) {
                $achBonCommande->setStatut(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AchDetails>
     */
    public function getAchDetails(): Collection
    {
        return $this->achDetails;
    }

    public function addAchDetail(AchDetails $achDetail): self
    {
        if (!$this->achDetails->contains($achDetail)) {
            $this->achDetails->add($achDetail);
            $achDetail->setStatut($this);
        }

        return $this;
    }

    public function removeAchDetail(AchDetails $achDetail): self
    {
        if ($this->achDetails->removeElement($achDetail)) {
            // set the owning side to null (unless already changed)
            if ($achDetail->getStatut() === $this) {
                $achDetail->setStatut(null);
            }
        }

        return $this;
    }
}
