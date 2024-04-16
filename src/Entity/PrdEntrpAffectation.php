<?php

namespace App\Entity;

use App\Repository\PrdEntrpAffectationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrdEntrpAffectationRepository::class)]
class PrdEntrpAffectation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'prdEntrpAffectations')]
    private ?Agence $agence = null;

    #[ORM\ManyToOne(inversedBy: 'prdEntrpAffectations')]
    private ?User $agent = null;

    #[ORM\ManyToOne(inversedBy: 'prdEntrpAffectations')]
    private ?PrdEntrepot $entrepot = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

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

    public function getAgent(): ?User
    {
        return $this->agent;
    }

    public function setAgent(?User $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function getEntrepot(): ?PrdEntrepot
    {
        return $this->entrepot;
    }

    public function setEntrepot(?PrdEntrepot $entrepot): self
    {
        $this->entrepot = $entrepot;

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
}
