<?php

namespace App\Entity;

use App\Repository\HistoActionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoActionRepository::class)]
class HistoAction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\OneToMany(mappedBy: 'action', targetEntity: HistoHistorique::class)]
    private Collection $histoHistoriques;

    public function __construct()
    {
        $this->histoHistoriques = new ArrayCollection();
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
     * @return Collection<int, HistoHistorique>
     */
    public function getHistoHistoriques(): Collection
    {
        return $this->histoHistoriques;
    }

    public function addHistoHistorique(HistoHistorique $histoHistorique): self
    {
        if (!$this->histoHistoriques->contains($histoHistorique)) {
            $this->histoHistoriques->add($histoHistorique);
            $histoHistorique->setAction($this);
        }

        return $this;
    }

    public function removeHistoHistorique(HistoHistorique $histoHistorique): self
    {
        if ($this->histoHistoriques->removeElement($histoHistorique)) {
            // set the owning side to null (unless already changed)
            if ($histoHistorique->getAction() === $this) {
                $histoHistorique->setAction(null);
            }
        }

        return $this;
    }
}
