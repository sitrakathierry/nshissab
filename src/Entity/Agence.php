<?php

namespace App\Entity;

use App\Repository\AgenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgenceRepository::class)]
class Agence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $region = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $code = null;

    #[ORM\Column]
    private ?int $capacite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'agences')]
    private ?AgcDevise $devise = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(nullable: true)]
    private ?int $codeAgence = null;

    #[ORM\Column]
    private ?bool $statut = null;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: User::class)]
    private Collection $users;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: AgcHistoTicket::class)]
    private Collection $agcHistoTickets;

    #[ORM\OneToMany(mappedBy: 'agence', targetEntity: MenuAgence::class)]
    private Collection $menuAgences;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->agcHistoTickets = new ArrayCollection();
        $this->menuAgences = new ArrayCollection();
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

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCapacite(): ?int
    {
        return $this->capacite;
    }

    public function setCapacite(int $capacite): self
    {
        $this->capacite = $capacite;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getDevise(): ?AgcDevise
    {
        return $this->devise;
    }

    public function setDevise(?AgcDevise $devise): self
    {
        $this->devise = $devise;

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

    public function getCodeAgence(): ?int
    {
        return $this->codeAgence;
    }

    public function setCodeAgence(?int $codeAgence): self
    {
        $this->codeAgence = $codeAgence;

        return $this;
    }

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(bool $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setAgence($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getAgence() === $this) {
                $user->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AgcHistoTicket>
     */
    public function getAgcHistoTickets(): Collection
    {
        return $this->agcHistoTickets;
    }

    public function addAgcHistoTicket(AgcHistoTicket $agcHistoTicket): self
    {
        if (!$this->agcHistoTickets->contains($agcHistoTicket)) {
            $this->agcHistoTickets->add($agcHistoTicket);
            $agcHistoTicket->setAgence($this);
        }

        return $this;
    }

    public function removeAgcHistoTicket(AgcHistoTicket $agcHistoTicket): self
    {
        if ($this->agcHistoTickets->removeElement($agcHistoTicket)) {
            // set the owning side to null (unless already changed)
            if ($agcHistoTicket->getAgence() === $this) {
                $agcHistoTicket->setAgence(null);
            }
        }

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
            $menuAgence->setAgence($this);
        }

        return $this;
    }

    public function removeMenuAgence(MenuAgence $menuAgence): self
    {
        if ($this->menuAgences->removeElement($menuAgence)) {
            // set the owning side to null (unless already changed)
            if ($menuAgence->getAgence() === $this) {
                $menuAgence->setAgence(null);
            }
        }

        return $this;
    }
}
