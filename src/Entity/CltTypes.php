<?php

namespace App\Entity;

use App\Repository\CltTypesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CltTypesRepository::class)]
class CltTypes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\OneToMany(mappedBy: 'type', targetEntity: CltHistoClient::class)]
    private Collection $cltHistoClients;

    public function __construct()
    {
        $this->cltHistoClients = new ArrayCollection();
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
     * @return Collection<int, CltHistoClient>
     */
    public function getCltHistoClients(): Collection
    {
        return $this->cltHistoClients;
    }

    public function addCltHistoClient(CltHistoClient $cltHistoClient): self
    {
        if (!$this->cltHistoClients->contains($cltHistoClient)) {
            $this->cltHistoClients->add($cltHistoClient);
            $cltHistoClient->setType($this);
        }

        return $this;
    }

    public function removeCltHistoClient(CltHistoClient $cltHistoClient): self
    {
        if ($this->cltHistoClients->removeElement($cltHistoClient)) {
            // set the owning side to null (unless already changed)
            if ($cltHistoClient->getType() === $this) {
                $cltHistoClient->setType(null);
            }
        }

        return $this;
    }
}
