<?php

namespace App\Entity;

use App\Repository\CharacterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CharacterRepository::class)]
#[ORM\Table(name: '`character`')]
class Character
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private ?string $name = null;

    #[ORM\Column(length: 12)]
    private ?string $genre = null;

    #[ORM\Column(length: 50)]
    private ?string $skinColor = null;

    #[ORM\Column(length: 50)]
    private ?string $eyesColor = null;

    #[ORM\Column(length: 50)]
    private ?string $hairColor = null;

    #[ORM\Column(length: 50)]
    private ?string $face = null;

    #[ORM\Column(length: 50)]
    private ?string $hair = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getSkinColor(): ?string
    {
        return $this->skinColor;
    }

    public function setSkinColor(string $skinColor): static
    {
        $this->skinColor = $skinColor;

        return $this;
    }

    public function getEyesColor(): ?string
    {
        return $this->eyesColor;
    }

    public function setEyesColor(string $eyesColor): static
    {
        $this->eyesColor = $eyesColor;

        return $this;
    }

    public function getHairColor(): ?string
    {
        return $this->hairColor;
    }

    public function setHairColor(string $hairColor): static
    {
        $this->hairColor = $hairColor;

        return $this;
    }

    public function getFace(): ?string
    {
        return $this->face;
    }

    public function setFace(string $face): static
    {
        $this->face = $face;

        return $this;
    }

    public function getHair(): ?string
    {
        return $this->hair;
    }

    public function setHair(string $hair): static
    {
        $this->hair = $hair;

        return $this;
    }
}
