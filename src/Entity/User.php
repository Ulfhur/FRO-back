<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $pseudo = null;

    #[ORM\Column(length: 120)]
    private ?string $mail = null;

    #[ORM\Column(length: 50)]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $suspended = null;

    #[ORM\Column]
    private ?bool $changePassword = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function isSuspended(): ?bool
    {
        return $this->suspended;
    }

    public function setSuspended(bool $suspended): static
    {
        $this->suspended = $suspended;

        return $this;
    }

    public function isChangePassword(): ?bool
    {
        return $this->changePassword;
    }

    public function setChangePassword(bool $changePassword): static
    {
        $this->changePassword = $changePassword;

        return $this;
    }
}
