<?php

namespace App\Entity;

use App\Repository\ContactMessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactMessageRepository::class)]
class ContactMessage
{
    public const STATUT_NOUVEAU = 'nouveau';
    public const STATUT_EN_COURS = 'en_cours';
    public const STATUT_REPONDU = 'repondu';
    public const STATUT_CLOS = 'clos';

    public const STATUTS = [
        self::STATUT_NOUVEAU => 'Nouveau',
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_REPONDU => 'Répondu',
        self::STATUT_CLOS => 'Clos'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire')]
    #[Assert\Email(message: 'Veuillez saisir un email valide')]
    private ?string $email = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pays = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le sujet est obligatoire')]
    private ?string $sujet = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le message est obligatoire')]
    private ?string $message = null;

    #[ORM\Column]
    private bool $newsletter = false;

    #[ORM\Column]
    private bool $rgpd = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, options: ['default' => 'nouveau'])]
    private string $statut = self::STATUT_NOUVEAU;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reponse = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $reponduAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reponduPar = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): static
    {
        $this->pays = $pays;
        return $this;
    }

    public function getSujet(): ?string
    {
        return $this->sujet;
    }

    public function setSujet(string $sujet): static
    {
        $this->sujet = $sujet;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function isNewsletter(): bool
    {
        return $this->newsletter;
    }

    public function setNewsletter(bool $newsletter): static
    {
        $this->newsletter = $newsletter;
        return $this;
    }

    public function isRgpd(): bool
    {
        return $this->rgpd;
    }

    public function setRgpd(bool $rgpd): static
    {
        $this->rgpd = $rgpd;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    public function setReponse(?string $reponse): static
    {
        $this->reponse = $reponse;
        return $this;
    }

    public function getReponduAt(): ?\DateTimeImmutable
    {
        return $this->reponduAt;
    }

    public function setReponduAt(?\DateTimeImmutable $reponduAt): static
    {
        $this->reponduAt = $reponduAt;
        return $this;
    }

    public function getReponduPar(): ?string
    {
        return $this->reponduPar;
    }

    public function setReponduPar(?string $reponduPar): static
    {
        $this->reponduPar = $reponduPar;
        return $this;
    }

    public function getFullName(): string
    {
        return $this->nom;
    }

    public function hasReponse(): bool
    {
        return !empty($this->reponse);
    }

    public function isLu(): bool
    {
        // Simuler le statut "lu" basé sur la présence d'une réponse
        return $this->hasReponse();
    }

    public function setLu(bool $lu): static
    {
        // Cette méthode ne fait rien car il n'y a pas de colonne lu dans la base
        return $this;
    }

    public function __toString(): string
    {
        return $this->getFullName() . ' - ' . $this->sujet;
    }
}
