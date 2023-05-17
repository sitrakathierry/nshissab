<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?Agence $agence = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codeProduit = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $qrCode = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $images = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $unite = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?PrdPreferences $preference = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?int $stock = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: PrdVariationPrix::class)]
    private Collection $prdVariationPrixes;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: PrdHistoFournisseur::class)]
    private Collection $prdHistoFournisseurs;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?ParamTvaType $tvaType = null;

    public function __construct()
    {
        $this->prdVariationPrixes = new ArrayCollection();
        $this->prdHistoFournisseurs = new ArrayCollection();
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

    public function getCodeProduit(): ?string
    {
        return $this->codeProduit;
    }

    public function setCodeProduit(?string $codeProduit): self
    {
        $this->codeProduit = $codeProduit;

        return $this;
    }

    public function getQrCode(): ?string
    {
        return $this->qrCode;
    }

    public function setQrCode(?string $qrCode): self
    {
        $this->qrCode = $qrCode;

        return $this;
    }

    public function getImages(): ?string
    {
        return $this->images;
    }

    public function setImages(?string $images): self
    {
        $this->images = $images;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUnite(): ?string
    {
        return $this->unite;
    }

    public function setUnite(?string $unite): self
    {
        $this->unite = $unite;

        return $this;
    }

    public function getPreference(): ?PrdPreferences
    {
        return $this->preference;
    }

    public function setPreference(?PrdPreferences $preference): self
    {
        $this->preference = $preference;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): self
    {
        $this->stock = $stock;

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
     * @return Collection<int, PrdVariationPrix>
     */
    public function getPrdVariationPrixes(): Collection
    {
        return $this->prdVariationPrixes;
    }

    public function addPrdVariationPrix(PrdVariationPrix $prdVariationPrix): self
    {
        if (!$this->prdVariationPrixes->contains($prdVariationPrix)) {
            $this->prdVariationPrixes->add($prdVariationPrix);
            $prdVariationPrix->setProduit($this);
        }

        return $this;
    }

    public function removePrdVariationPrix(PrdVariationPrix $prdVariationPrix): self
    {
        if ($this->prdVariationPrixes->removeElement($prdVariationPrix)) {
            // set the owning side to null (unless already changed)
            if ($prdVariationPrix->getProduit() === $this) {
                $prdVariationPrix->setProduit(null);
            }
        }

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
            $prdHistoFournisseur->setProduit($this);
        }

        return $this;
    }

    public function removePrdHistoFournisseur(PrdHistoFournisseur $prdHistoFournisseur): self
    {
        if ($this->prdHistoFournisseurs->removeElement($prdHistoFournisseur)) {
            // set the owning side to null (unless already changed)
            if ($prdHistoFournisseur->getProduit() === $this) {
                $prdHistoFournisseur->setProduit(null);
            }
        }

        return $this;
    }

    public function getTvaType(): ?ParamTvaType
    {
        return $this->tvaType;
    }

    public function setTvaType(?ParamTvaType $tvaType): self
    {
        $this->tvaType = $tvaType;

        return $this;
    }
}
