<?php

namespace App\Entity;

use App\Repository\LctBailRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LctBailRepository::class)]
class LctBail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lctBails')]
    private ?LctBailleur $bailleur = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dimension = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $lieux = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBailleur(): ?LctBailleur
    {
        return $this->bailleur;
    }

    public function setBailleur(?LctBailleur $bailleur): self
    {
        $this->bailleur = $bailleur;

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

    public function getDimension(): ?string
    {
        return $this->dimension;
    }

    public function setDimension(?string $dimension): self
    {
        $this->dimension = $dimension;

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

    public function getLieux(): ?string
    {
        return $this->lieux;
    }

    public function setLieux(?string $lieux): self
    {
        $this->lieux = $lieux;

        return $this;
    }
}
