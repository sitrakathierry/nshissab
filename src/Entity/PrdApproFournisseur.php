<?php

namespace App\Entity;

use App\Repository\PrdApproFournisseurRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrdApproFournisseurRepository::class)]
class PrdApproFournisseur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'prdApproFournisseurs')]
    private ?PrdApprovisionnement $prdAppro = null;

    #[ORM\ManyToOne(inversedBy: 'prdApproFournisseurs')]
    private ?PrdFournisseur $fournisseur = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrdAppro(): ?PrdApprovisionnement
    {
        return $this->prdAppro;
    }

    public function setPrdAppro(?PrdApprovisionnement $prdAppro): self
    {
        $this->prdAppro = $prdAppro;

        return $this;
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
}
