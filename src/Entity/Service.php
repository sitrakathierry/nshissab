<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'service', targetEntity: SrvTarif::class)]
    private Collection $srvTarifs;

    #[ORM\ManyToOne(inversedBy: 'services')]
    private ?Agence $agence = null;

    public function __construct()
    {
        $this->srvTarifs = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeImmutable $createAt): self
    {
        $this->createAt = $createAt;

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

    /**
     * @return Collection<int, SrvTarif>
     */
    public function getSrvTarifs(): Collection
    {
        return $this->srvTarifs;
    }

    public function addSrvTarif(SrvTarif $srvTarif): self
    {
        if (!$this->srvTarifs->contains($srvTarif)) {
            $this->srvTarifs->add($srvTarif);
            $srvTarif->setService($this);
        }

        return $this;
    }

    public function removeSrvTarif(SrvTarif $srvTarif): self
    {
        if ($this->srvTarifs->removeElement($srvTarif)) {
            // set the owning side to null (unless already changed)
            if ($srvTarif->getService() === $this) {
                $srvTarif->setService(null);
            }
        }

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
