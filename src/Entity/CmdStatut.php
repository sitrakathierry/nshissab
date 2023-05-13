<?php

namespace App\Entity;

use App\Repository\CmdStatutRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CmdStatutRepository::class)]
class CmdStatut
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\OneToMany(mappedBy: 'statut', targetEntity: CmdBonCommande::class)]
    private Collection $cmdBonCommandes;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    public function __construct()
    {
        $this->cmdBonCommandes = new ArrayCollection();
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

    /**
     * @return Collection<int, CmdBonCommande>
     */
    public function getCmdBonCommandes(): Collection
    {
        return $this->cmdBonCommandes;
    }

    public function addCmdBonCommande(CmdBonCommande $cmdBonCommande): self
    {
        if (!$this->cmdBonCommandes->contains($cmdBonCommande)) {
            $this->cmdBonCommandes->add($cmdBonCommande);
            $cmdBonCommande->setStatut($this);
        }

        return $this;
    }

    public function removeCmdBonCommande(CmdBonCommande $cmdBonCommande): self
    {
        if ($this->cmdBonCommandes->removeElement($cmdBonCommande)) {
            // set the owning side to null (unless already changed)
            if ($cmdBonCommande->getStatut() === $this) {
                $cmdBonCommande->setStatut(null);
            }
        }

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
}
