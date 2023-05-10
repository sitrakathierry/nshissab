<?php

namespace App\Entity;

use App\Repository\CltTypeSocieteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CltTypeSocieteRepository::class)]
class CltTypeSociete
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $designation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'typeSociete', targetEntity: CltSociete::class)]
    private Collection $cltSocietes;

    public function __construct()
    {
        $this->cltSocietes = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, CltSociete>
     */
    public function getCltSocietes(): Collection
    {
        return $this->cltSocietes;
    }

    public function addCltSociete(CltSociete $cltSociete): self
    {
        if (!$this->cltSocietes->contains($cltSociete)) {
            $this->cltSocietes->add($cltSociete);
            $cltSociete->setTypeSociete($this);
        }

        return $this;
    }

    public function removeCltSociete(CltSociete $cltSociete): self
    {
        if ($this->cltSocietes->removeElement($cltSociete)) {
            // set the owning side to null (unless already changed)
            if ($cltSociete->getTypeSociete() === $this) {
                $cltSociete->setTypeSociete(null);
            }
        }

        return $this;
    }
}
