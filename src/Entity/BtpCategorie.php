<?php

namespace App\Entity;

use App\Repository\BtpCategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BtpCategorieRepository::class)]
class BtpCategorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'btpCategories')]
    private ?BtpEnoncee $enonce = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statut = null;

    #[ORM\ManyToOne(inversedBy: 'btpCategories')]
    private ?BtpMesure $mesure = null;

    #[ORM\OneToMany(mappedBy: 'categorie', targetEntity: FactSupDetailsPbat::class)]
    private Collection $factSupDetailsPbats;

    public function __construct()
    {
        $this->factSupDetailsPbats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEnonce(): ?BtpEnoncee
    {
        return $this->enonce;
    }

    public function setEnonce(?BtpEnoncee $enonce): self
    {
        $this->enonce = $enonce;

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

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(?bool $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getMesure(): ?BtpMesure
    {
        return $this->mesure;
    }

    public function setMesure(?BtpMesure $mesure): self
    {
        $this->mesure = $mesure;

        return $this;
    }

    /**
     * @return Collection<int, FactSupDetailsPbat>
     */
    public function getFactSupDetailsPbats(): Collection
    {
        return $this->factSupDetailsPbats;
    }

    public function addFactSupDetailsPbat(FactSupDetailsPbat $factSupDetailsPbat): self
    {
        if (!$this->factSupDetailsPbats->contains($factSupDetailsPbat)) {
            $this->factSupDetailsPbats->add($factSupDetailsPbat);
            $factSupDetailsPbat->setCategorie($this);
        }

        return $this;
    }

    public function removeFactSupDetailsPbat(FactSupDetailsPbat $factSupDetailsPbat): self
    {
        if ($this->factSupDetailsPbats->removeElement($factSupDetailsPbat)) {
            // set the owning side to null (unless already changed)
            if ($factSupDetailsPbat->getCategorie() === $this) {
                $factSupDetailsPbat->setCategorie(null);
            }
        }

        return $this;
    }
}
