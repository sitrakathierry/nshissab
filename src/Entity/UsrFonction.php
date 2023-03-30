<?php

namespace App\Entity;

use App\Repository\UsrFonctionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsrFonctionRepository::class)]
class UsrFonction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\OneToMany(mappedBy: 'fonction', targetEntity: UsrHistoFonction::class)]
    private Collection $usrHistoFonctions;

    public function __construct()
    {
        $this->usrHistoFonctions = new ArrayCollection();
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
     * @return Collection<int, UsrHistoFonction>
     */
    public function getUsrHistoFonctions(): Collection
    {
        return $this->usrHistoFonctions;
    }

    public function addUsrHistoFonction(UsrHistoFonction $usrHistoFonction): self
    {
        if (!$this->usrHistoFonctions->contains($usrHistoFonction)) {
            $this->usrHistoFonctions->add($usrHistoFonction);
            $usrHistoFonction->setFonction($this);
        }

        return $this;
    }

    public function removeUsrHistoFonction(UsrHistoFonction $usrHistoFonction): self
    {
        if ($this->usrHistoFonctions->removeElement($usrHistoFonction)) {
            // set the owning side to null (unless already changed)
            if ($usrHistoFonction->getFonction() === $this) {
                $usrHistoFonction->setFonction(null);
            }
        }

        return $this;
    }
}
