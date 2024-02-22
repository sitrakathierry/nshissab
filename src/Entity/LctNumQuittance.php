<?php

namespace App\Entity;

use App\Repository\LctNumQuittanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LctNumQuittanceRepository::class)]
class LctNumQuittance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $numero = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'numQuittance', targetEntity: LctRepartition::class)]
    private Collection $lctRepartitions;

    #[ORM\ManyToOne(inversedBy: 'lctNumQuittances')]
    private ?Agence $agence = null;

    public function __construct()
    {
        $this->lctRepartitions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

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
     * @return Collection<int, LctRepartition>
     */
    public function getLctRepartitions(): Collection
    {
        return $this->lctRepartitions;
    }

    public function addLctRepartition(LctRepartition $lctRepartition): self
    {
        if (!$this->lctRepartitions->contains($lctRepartition)) {
            $this->lctRepartitions->add($lctRepartition);
            $lctRepartition->setNumQuittance($this);
        }

        return $this;
    }

    public function removeLctRepartition(LctRepartition $lctRepartition): self
    {
        if ($this->lctRepartitions->removeElement($lctRepartition)) {
            // set the owning side to null (unless already changed)
            if ($lctRepartition->getNumQuittance() === $this) {
                $lctRepartition->setNumQuittance(null);
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
