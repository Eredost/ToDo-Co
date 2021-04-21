<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *     fields = {"email"},
 *     message = "Cette adresse email est déjà utilisée"
 * )
 * @UniqueEntity(
 *     fields = {"username"},
 *     message = "Ce nom d'utilisateur est déjà utilisée"
 * )
 */
class User implements UserInterface
{
    public const ROLES = [
        'user'  => 'ROLE_USER',
        'admin' => 'ROLE_ADMIN',
    ];

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue()
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\NotBlank(message="Vous devez saisir une adresse email.")
     * @Assert\Email(message="Le format de l'adresse email n'est pas correcte.")
     */
    private ?string $email;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     * @Assert\NotBlank(message="Vous devez saisir un nom d'utilisateur.")
     * @Assert\Length(
     *     max = 25,
     *     maxMessage = "Le nom d'utilisateur ne peut pas dépasser {{ limit }} caractères"
     * )
     */
    private ?string $username;

    /**
     * @ORM\Column(type="json")
     * @Assert\All(
     *     @Assert\Choice(
     *         choices = self::ROLES,
     *         message = "Vous devez fournir un rôle d'utilisateur valide. Rôles disponibles : {{ choices }}"
     *     )
     * )
     */
    private array $roles = [];

    /**
     * @ORM\Column(type="string", length=120)
     * @Assert\NotBlank(message = "Le mot de passe ne peut pas être vide")
     * @Assert\Regex(
     *     pattern = "/(?=^.{8,40}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/",
     *     message = "Le mot de passe doit contenit un minimum de 8 caractères et un maximum de 40 caractères dont une minuscule, une majuscule et un chiffre"
     * )
     */
    private ?string $password;

    public function __construct()
    {
        $this->id = null;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     * @codeCoverageIgnore
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    /**
     * @see UserInterface
     * @codeCoverageIgnore
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }
}
