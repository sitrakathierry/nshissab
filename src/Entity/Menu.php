<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $route = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icone = null;

    #[ORM\Column(nullable: true)]
    private ?int $rang = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\OneToMany(mappedBy: 'menu', targetEntity: MenuAgence::class)]
    private Collection $menuAgences;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'menus')]
    private ?self $menuParent = null;

    #[ORM\OneToMany(mappedBy: 'menuParent', targetEntity: self::class)]
    private Collection $menus;

    public function __construct()
    {
        $this->menuAgences = new ArrayCollection();
        $this->menus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(?string $route): self
    {
        $this->route = $route;

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

    public function getIcone(): ?string
    {
        return $this->icone;
    }

    public function setIcone(?string $icone): self
    {
        $this->icone = $icone;

        return $this;
    }

    public function getRang(): ?int
    {
        return $this->rang;
    }

    public function setRang(?int $rang): self
    {
        $this->rang = $rang;

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
     * @return Collection<int, MenuAgence>
     */
    public function getMenuAgences(): Collection
    {
        return $this->menuAgences;
    }

    public function addMenuAgence(MenuAgence $menuAgence): self
    {
        if (!$this->menuAgences->contains($menuAgence)) {
            $this->menuAgences->add($menuAgence);
            $menuAgence->setMenu($this);
        }

        return $this;
    }

    public function removeMenuAgence(MenuAgence $menuAgence): self
    {
        if ($this->menuAgences->removeElement($menuAgence)) {
            // set the owning side to null (unless already changed)
            if ($menuAgence->getMenu() === $this) {
                $menuAgence->setMenu(null);
            }
        }

        return $this;
    }

    public function getMenuParent(): ?self
    {
        return $this->menuParent;
    }

    public function setMenuParent(?self $menuParent): self
    {
        $this->menuParent = $menuParent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(self $menu): self
    {
        if (!$this->menus->contains($menu)) {
            $this->menus->add($menu);
            $menu->setMenuParent($this);
        }

        return $this;
    }

    public function removeMenu(self $menu): self
    {
        if ($this->menus->removeElement($menu)) {
            // set the owning side to null (unless already changed)
            if ($menu->getMenuParent() === $this) {
                $menu->setMenuParent(null);
            }
        }

        return $this;
    }
}
