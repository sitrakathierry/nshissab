<?php

namespace App\Entity;

use App\Repository\PrdHistoFournisseurRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrdHistoFournisseurRepository::class)]
class PrdHistoFournisseur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'prdHistoFournisseurs')]
    private ?PrdFournisseur $fournisseur = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'prdHistoFournisseurs')]
    private ?PrdApprovisionnement $approvisionnement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFournisseur(): ?PrdFournisseur
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?PrdFournisseur $fournisseur): self
    {
        $this->fournisseur = $fournisseur;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getApprovisionnement(): ?PrdApprovisionnement
    {
        return $this->approvisionnement;
    }

    public function setApprovisionnement(?PrdApprovisionnement $approvisionnement): self
    {
        $this->approvisionnement = $approvisionnement;

        return $this;
    }
}
