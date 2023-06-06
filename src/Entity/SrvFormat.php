<?php

namespace App\Entity;

use App\Repository\SrvFormatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SrvFormatRepository::class)]
class SrvFormat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\OneToMany(mappedBy: 'format', targetEntity: SrvTarif::class)]
    private Collection $srvTarifs;

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

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

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
            $srvTarif->setFormat($this);
        }

        return $this;
    }

    public function removeSrvTarif(SrvTarif $srvTarif): self
    {
        if ($this->srvTarifs->removeElement($srvTarif)) {
            // set the owning side to null (unless already changed)
            if ($srvTarif->getFormat() === $this) {
                $srvTarif->setFormat(null);
            }
        }

        return $this;
    }
}
