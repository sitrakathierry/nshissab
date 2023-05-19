<?php

namespace App\Entity;

use App\Repository\LvrDetailsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LvrDetailsRepository::class)]
class LvrDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lvrDetails')]
    private ?LvrLivraison $livraison = null;

    #[ORM\ManyToOne(inversedBy: 'lvrDetails')]
    private ?FactDetails $factureDetail = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\ManyToOne(inversedBy: 'lvrDetails')]
    private ?Agence $agence = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLivraison(): ?LvrLivraison
    {
        return $this->livraison;
    }

    public function setLivraison(?LvrLivraison $livraison): self
    {
        $this->livraison = $livraison;

        return $this;
    }

    public function getFactureDetail(): ?FactDetails
    {
        return $this->factureDetail;
    }

    public function setFactureDetail(?FactDetails $factureDetail): self
    {
        $this->factureDetail = $factureDetail;

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
