<?php

namespace App\Entity;

use App\Repository\PrdSoldeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrdSoldeRepository::class)]
class PrdSolde
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'prdSoldes')]
    private ?PrdMargeType $type = null;

    #[ORM\ManyToOne(inversedBy: 'prdSoldes')]
    private ?PrdVariationPrix $variationPrix = null;

    #[ORM\Column(nullable: true)]
    private ?float $solde = null;

    #[ORM\Column(nullable: true)]
    private ?float $calculee = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateLimite = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?PrdMargeType
    {
        return $this->type;
    }

    public function setType(?PrdMargeType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getVariationPrix(): ?PrdVariationPrix
    {
        return $this->variationPrix;
    }

    public function setVariationPrix(?PrdVariationPrix $variationPrix): self
    {
        $this->variationPrix = $variationPrix;

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

    public function getCalculee(): ?float
    {
        return $this->calculee;
    }

    public function setCalculee(?float $calculee): self
    {
        $this->calculee = $calculee;

        return $this;
    }

    public function getDateLimite(): ?\DateTimeInterface
    {
        return $this->dateLimite;
    }

    public function setDateLimite(?\DateTimeInterface $dateLimite): self
    {
        $this->dateLimite = $dateLimite;

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
}
