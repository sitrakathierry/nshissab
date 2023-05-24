<?php

namespace App\Entity;

use App\Repository\SavMotifRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SavMotifRepository::class)]
class SavMotif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\OneToMany(mappedBy: 'motif', targetEntity: SavAnnulation::class)]
    private Collection $savAnnulations;

    #[ORM\ManyToOne(inversedBy: 'savMotifs')]
    private ?Agence $agence = null;

    public function __construct()
    {
        $this->savAnnulations = new ArrayCollection();
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
     * @return Collection<int, SavAnnulation>
     */
    public function getSavAnnulations(): Collection
    {
        return $this->savAnnulations;
    }

    public function addSavAnnulation(SavAnnulation $savAnnulation): self
    {
        if (!$this->savAnnulations->contains($savAnnulation)) {
            $this->savAnnulations->add($savAnnulation);
            $savAnnulation->setMotif($this);
        }

        return $this;
    }

    public function removeSavAnnulation(SavAnnulation $savAnnulation): self
    {
        if ($this->savAnnulations->removeElement($savAnnulation)) {
            // set the owning side to null (unless already changed)
            if ($savAnnulation->getMotif() === $this) {
                $savAnnulation->setMotif(null);
            }
        }

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
