<?php

namespace App\Entity;

use App\Repository\SavAvoirUseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SavAvoirUseRepository::class)]
class SavAvoirUse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'savAvoirUses')]
    private ?Facture $facture = null;

    #[ORM\ManyToOne(inversedBy: 'savAvoirUses')]
    private ?CltHistoClient $histoClient = null;

    #[ORM\Column(nullable: true)]
    private ?float $montant = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isNew = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): static
    {
        $this->facture = $facture;

        return $this;
    }

    public function getHistoClient(): ?CltHistoClient
    {
        return $this->histoClient;
    }

    public function setHistoClient(?CltHistoClient $histoClient): static
    {
        $this->histoClient = $histoClient;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(?float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function isIsNew(): ?bool
    {
        return $this->isNew;
    }

    public function setIsNew(?bool $isNew): static
    {
        $this->isNew = $isNew;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
