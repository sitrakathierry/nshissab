<?php

namespace App\Entity;

use App\Repository\MenuAgenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuAgenceRepository::class)]
class MenuAgence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'menuAgences')]
    private ?Agence $agence = null;

    #[ORM\ManyToOne(inversedBy: 'menuAgences')]
    private ?Menu $menu = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\OneToMany(mappedBy: 'menuAgence', targetEntity: MenuUser::class)]
    private Collection $menuUsers;

    public function __construct()
    {
        $this->menuUsers = new ArrayCollection();
    }

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

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): self
    {
        $this->menu = $menu;

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
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection<int, MenuUser>
     */
    public function getMenuUsers(): Collection
    {
        return $this->menuUsers;
    }

    public function addMenuUser(MenuUser $menuUser): self
    {
        if (!$this->menuUsers->contains($menuUser)) {
            $this->menuUsers->add($menuUser);
            $menuUser->setMenuAgence($this);
        }

        return $this;
    }

    public function removeMenuUser(MenuUser $menuUser): self
    {
        if ($this->menuUsers->removeElement($menuUser)) {
            // set the owning side to null (unless already changed)
            if ($menuUser->getMenuAgence() === $this) {
                $menuUser->setMenuAgence(null);
            }
        }

        return $this;
    }
}
