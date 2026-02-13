<?php

namespace App\Entity;

use App\Repository\CharacterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column]
    private ?bool $isShared = false;

    /**
     * @var Collection<int, Equipment>
     */
    #[ORM\ManyToMany(targetEntity: Equipment::class)]
    private Collection $equipment;

    #[ORM\ManyToOne(inversedBy: 'characters')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct()
    {
        $this->equipment = new ArrayCollection();
    }

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

    public function isShared(): ?bool
    {
        return $this->isShared;
    }

    public function setIsShared(bool $is_shared): static
    {
        $this->isShared = $is_shared;

        return $this;
    }


    /**
     * @return Collection<int, Equipment>
     */
    public function getEquipment(): Collection
    {
        return $this->equipment;
    }

    public function addEquipment(Equipment $equipment): static
    {
        if (!$this->equipment->contains($equipment)) {
            $this->equipment->add($equipment);
        }

        return $this;
    }

    public function removeEquipment(Equipment $equipment): static
    {
        $this->equipment->removeElement($equipment);

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
