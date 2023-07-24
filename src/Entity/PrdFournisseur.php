<?php

namespace App\Entity;

use App\Repository\PrdFournisseurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrdFournisseurRepository::class)]
class PrdFournisseur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'prdFournisseurs')]
    private ?Agence $agence = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomContact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telBureau = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telMobile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\OneToMany(mappedBy: 'fournisseur', targetEntity: PrdHistoFournisseur::class)]
    private Collection $prdHistoFournisseurs;

    #[ORM\OneToMany(mappedBy: 'fournisseur', targetEntity: PrdApproFournisseur::class)]
    private Collection $prdApproFournisseurs;

    #[ORM\OneToMany(mappedBy: 'fournisseur', targetEntity: AchBonCommande::class)]
    private Collection $achBonCommandes;

    public function __construct()
    {
        $this->prdHistoFournisseurs = new ArrayCollection();
        $this->prdApproFournisseurs = new ArrayCollection();
        $this->achBonCommandes = new ArrayCollection();
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getNomContact(): ?string
    {
        return $this->nomContact;
    }

    public function setNomContact(?string $nomContact): self
    {
        $this->nomContact = $nomContact;

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

    public function getTelBureau(): ?string
    {
        return $this->telBureau;
    }

    public function setTelBureau(?string $telBureau): self
    {
        $this->telBureau = $telBureau;

        return $this;
    }

    public function getTelMobile(): ?string
    {
        return $this->telMobile;
    }

    public function setTelMobile(?string $telMobile): self
    {
        $this->telMobile = $telMobile;

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

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection<int, PrdHistoFournisseur>
     */
    public function getPrdHistoFournisseurs(): Collection
    {
        return $this->prdHistoFournisseurs;
    }

    public function addPrdHistoFournisseur(PrdHistoFournisseur $prdHistoFournisseur): self
    {
        if (!$this->prdHistoFournisseurs->contains($prdHistoFournisseur)) {
            $this->prdHistoFournisseurs->add($prdHistoFournisseur);
            $prdHistoFournisseur->setFournisseur($this);
        }

        return $this;
    }

    public function removePrdHistoFournisseur(PrdHistoFournisseur $prdHistoFournisseur): self
    {
        if ($this->prdHistoFournisseurs->removeElement($prdHistoFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($prdHistoFournisseur->getFournisseur() === $this) {
                $prdHistoFournisseur->setFournisseur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PrdApproFournisseur>
     */
    public function getPrdApproFournisseurs(): Collection
    {
        return $this->prdApproFournisseurs;
    }

    public function addPrdApproFournisseur(PrdApproFournisseur $prdApproFournisseur): self
    {
        if (!$this->prdApproFournisseurs->contains($prdApproFournisseur)) {
            $this->prdApproFournisseurs->add($prdApproFournisseur);
            $prdApproFournisseur->setFournisseur($this);
        }

        return $this;
    }

    public function removePrdApproFournisseur(PrdApproFournisseur $prdApproFournisseur): self
    {
        if ($this->prdApproFournisseurs->removeElement($prdApproFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($prdApproFournisseur->getFournisseur() === $this) {
                $prdApproFournisseur->setFournisseur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AchBonCommande>
     */
    public function getAchBonCommandes(): Collection
    {
        return $this->achBonCommandes;
    }

    public function addAchBonCommande(AchBonCommande $achBonCommande): self
    {
        if (!$this->achBonCommandes->contains($achBonCommande)) {
            $this->achBonCommandes->add($achBonCommande);
            $achBonCommande->setFournisseur($this);
        }

        return $this;
    }

    public function removeAchBonCommande(AchBonCommande $achBonCommande): self
    {
        if ($this->achBonCommandes->removeElement($achBonCommande)) {
            // set the owning side to null (unless already changed)
            if ($achBonCommande->getFournisseur() === $this) {
                $achBonCommande->setFournisseur(null);
            }
        }

        return $this;
    }
}
