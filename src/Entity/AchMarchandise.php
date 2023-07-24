<?php

namespace App\Entity;

use App\Repository\AchMarchandiseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AchMarchandiseRepository::class)]
class AchMarchandise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $designation = null;

    #[ORM\Column(nullable: true)]
    private ?float $prix = null;

    #[ORM\ManyToOne(inversedBy: 'achMarchandises')]
    private ?Agence $agence = null;

    #[ORM\Column(nullable: true)]
    private ?bool $statutGen = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'marchandise', targetEntity: AchDetails::class)]
    private Collection $achDetails;

    public function __construct()
    {
        $this->achDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): self
    {
        $this->prix = $prix;

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

    public function isStatutGen(): ?bool
    {
        return $this->statutGen;
    }

    public function setStatutGen(?bool $statutGen): self
    {
        $this->statutGen = $statutGen;

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
     * @return Collection<int, AchDetails>
     */
    public function getAchDetails(): Collection
    {
        return $this->achDetails;
    }

    public function addAchDetail(AchDetails $achDetail): self
    {
        if (!$this->achDetails->contains($achDetail)) {
            $this->achDetails->add($achDetail);
            $achDetail->setMarchandise($this);
        }

        return $this;
    }

    public function removeAchDetail(AchDetails $achDetail): self
    {
        if ($this->achDetails->removeElement($achDetail)) {
            // set the owning side to null (unless already changed)
            if ($achDetail->getMarchandise() === $this) {
                $achDetail->setMarchandise(null);
            }
        }

        return $this;
    }
}
