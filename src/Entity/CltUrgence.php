<?php

namespace App\Entity;

use App\Repository\CltUrgenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CltUrgenceRepository::class)]
class CltUrgence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lien_parente = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observation = null;

    #[ORM\OneToMany(mappedBy: 'urgence', targetEntity: CltHistoClient::class)]
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getLienParente(): ?string
    {
        return $this->lien_parente;
    }

    public function setLienParente(?string $lien_parente): self
    {
        $this->lien_parente = $lien_parente;

        return $this;
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function setObservation(?string $observation): self
    {
        $this->observation = $observation;

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
            $cltHistoClient->setUrgence($this);
        }

        return $this;
    }

    public function removeCltHistoClient(CltHistoClient $cltHistoClient): self
    {
        if ($this->cltHistoClients->removeElement($cltHistoClient)) {
            // set the owning side to null (unless already changed)
            if ($cltHistoClient->getUrgence() === $this) {
                $cltHistoClient->setUrgence(null);
            }
        }

        return $this;
    }
}
