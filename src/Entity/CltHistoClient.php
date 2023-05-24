<?php

namespace App\Entity;

use App\Repository\CltHistoClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CltHistoClientRepository::class)]
class CltHistoClient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cltHistoClients')]
    private ?Agence $agence = null;

    #[ORM\ManyToOne(inversedBy: 'cltHistoClients')]
    private ?Client $client = null;

    #[ORM\ManyToOne(inversedBy: 'cltHistoClients')]
    private ?CltSociete $societe = null;

    #[ORM\ManyToOne(inversedBy: 'cltHistoClients')]
    private ?CltUrgence $urgence = null;

    #[ORM\ManyToOne(inversedBy: 'cltHistoClients')]
    private ?CltTypes $type = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Facture::class)]
    private Collection $factures;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: SavAnnulation::class)]
    private Collection $savAnnulations;

    public function __construct()
    {
        $this->factures = new ArrayCollection();
        $this->savAnnulations = new ArrayCollection();
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

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getSociete(): ?CltSociete
    {
        return $this->societe;
    }

    public function setSociete(?CltSociete $societe): self
    {
        $this->societe = $societe;

        return $this;
    }

    public function getUrgence(): ?CltUrgence
    {
        return $this->urgence;
    }

    public function setUrgence(?CltUrgence $urgence): self
    {
        $this->urgence = $urgence;

        return $this;
    }

    public function getType(): ?CltTypes
    {
        return $this->type;
    }

    public function setType(?CltTypes $type): self
    {
        $this->type = $type;

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
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

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
     * @return Collection<int, Facture>
     */
    public function getFactures(): Collection
    {
        return $this->factures;
    }

    public function addFacture(Facture $facture): self
    {
        if (!$this->factures->contains($facture)) {
            $this->factures->add($facture);
            $facture->setClient($this);
        }

        return $this;
    }

    public function removeFacture(Facture $facture): self
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getClient() === $this) {
                $facture->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SavAnnulation>
     */
    public function getSavAnnulations(): Collection
    {
        return $this->savAnnulations;
    }

    public function addSavAnnulation(SavAnnulation $savAnnulation): self
    {
        if (!$this->savAnnulations->contains($savAnnulation)) {
            $this->savAnnulations->add($savAnnulation);
            $savAnnulation->setClient($this);
        }

        return $this;
    }

    public function removeSavAnnulation(SavAnnulation $savAnnulation): self
    {
        if ($this->savAnnulations->removeElement($savAnnulation)) {
            // set the owning side to null (unless already changed)
            if ($savAnnulation->getClient() === $this) {
                $savAnnulation->setClient(null);
            }
        }

        return $this;
    }
}
