<?php

namespace App\Entity;

use App\Repository\LctBailleurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LctBailleurRepository::class)]
class LctBailleur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lctBailleurs')]
    private ?Agence $agence = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\OneToMany(mappedBy: 'bailleur', targetEntity: LctBail::class)]
    private Collection $lctBails;

    public function __construct()
    {
        $this->lctBails = new ArrayCollection();
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

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

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(?bool $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, LctBail>
     */
    public function getLctBails(): Collection
    {
        return $this->lctBails;
    }

    public function addLctBail(LctBail $lctBail): self
    {
        if (!$this->lctBails->contains($lctBail)) {
            $this->lctBails->add($lctBail);
            $lctBail->setBailleur($this);
        }

        return $this;
    }

    public function removeLctBail(LctBail $lctBail): self
    {
        if ($this->lctBails->removeElement($lctBail)) {
            // set the owning side to null (unless already changed)
            if ($lctBail->getBailleur() === $this) {
                $lctBail->setBailleur(null);
            }
        }

        return $this;
    }
}
