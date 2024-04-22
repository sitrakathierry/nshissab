<?php

namespace App\Entity;

use App\Repository\FactSupDetailsPbatRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactSupDetailsPbatRepository::class)]
class FactSupDetailsPbat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'factSupDetailsPbats')]
    private ?BtpEnoncee $enonce = null;

    #[ORM\ManyToOne(inversedBy: 'factSupDetailsPbats')]
    private ?BtpCategorie $categorie = null;

    #[ORM\ManyToOne(inversedBy: 'factSupDetailsPbats')]
    private ?FactDetails $detail = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $infoSup = null;

    #[ORM\ManyToOne(inversedBy: 'factSupDetailsPbats')]
    private ?BtpSurface $surface = null;

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

    public function getCategorie(): ?BtpCategorie
    {
        return $this->categorie;
    }

    public function setCategorie(?BtpCategorie $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getDetail(): ?FactDetails
    {
        return $this->detail;
    }

    public function setDetail(?FactDetails $detail): self
    {
        $this->detail = $detail;

        return $this;
    }

    public function getInfoSup(): ?string
    {
        return $this->infoSup;
    }

    public function setInfoSup(?string $infoSup): self
    {
        $this->infoSup = $infoSup;

        return $this;
    }

    public function getSurface(): ?BtpSurface
    {
        return $this->surface;
    }

    public function setSurface(?BtpSurface $surface): self
    {
        $this->surface = $surface;

        return $this;
    }
}
