<?php

namespace App\Entity;

use App\Repository\VisaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VisaRepository::class)]
class Visa
{
    public const STATUT_NON_DEMANDE = 'non_demande';
    public const STATUT_EN_COURS = 'en_cours';
    public const STATUT_APPROUVE = 'approuve';
    public const STATUT_REFUSE = 'refuse';

    public const STATUTS = [
        self::STATUT_NON_DEMANDE => 'Non demandé',
        self::STATUT_EN_COURS => 'En cours de traitement',
        self::STATUT_APPROUVE => 'Approuvé',
        self::STATUT_REFUSE => 'Refusé'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Pelerin::class, inversedBy: 'visa')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pelerin $pelerin = null;

    #[ORM\Column(length: 30)]
    private string $statut = self::STATUT_NON_DEMANDE;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $numero = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $submittedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $approvedAt = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateExpiration = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $traitePar = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referenceConsulat = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPelerin(): ?Pelerin
    {
        return $this->pelerin;
    }

    public function setPelerin(Pelerin $pelerin): static
    {
        $this->pelerin = $pelerin;
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

    public function getStatutLabel(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): static
    {
        $this->numero = $numero;
        return $this;
    }

    public function getSubmittedAt(): ?\DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(?\DateTimeImmutable $submittedAt): static
    {
        $this->submittedAt = $submittedAt;
        return $this;
    }

    public function getApprovedAt(): ?\DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function setApprovedAt(?\DateTimeImmutable $approvedAt): static
    {
        $this->approvedAt = $approvedAt;
        return $this;
    }

    public function getDateExpiration(): ?\DateTimeImmutable
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(?\DateTimeImmutable $dateExpiration): static
    {
        $this->dateExpiration = $dateExpiration;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getTraitePar(): ?User
    {
        return $this->traitePar;
    }

    public function setTraitePar(?User $traitePar): static
    {
        $this->traitePar = $traitePar;
        return $this;
    }

    public function getReferenceConsulat(): ?string
    {
        return $this->referenceConsulat;
    }

    public function setReferenceConsulat(?string $referenceConsulat): static
    {
        $this->referenceConsulat = $referenceConsulat;
        return $this;
    }

    public function soumettre(User $user): void
    {
        $this->statut = self::STATUT_EN_COURS;
        $this->submittedAt = new \DateTimeImmutable();
        $this->traitePar = $user;
    }

    public function approuver(User $user, string $numero, ?\DateTimeImmutable $dateExpiration = null): void
    {
        $this->statut = self::STATUT_APPROUVE;
        $this->numero = $numero;
        $this->approvedAt = new \DateTimeImmutable();
        $this->dateExpiration = $dateExpiration;
        $this->traitePar = $user;
    }

    public function refuser(User $user, string $commentaire): void
    {
        $this->statut = self::STATUT_REFUSE;
        $this->commentaire = $commentaire;
        $this->traitePar = $user;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isApproved(): bool
    {
        return $this->statut === self::STATUT_APPROUVE;
    }

    public function isExpired(): bool
    {
        if (!$this->dateExpiration) {
            return false;
        }
        return $this->dateExpiration < new \DateTimeImmutable();
    }

    public function getDaysUntilExpiration(): ?int
    {
        if (!$this->dateExpiration || $this->isExpired()) {
            return null;
        }
        return $this->dateExpiration->diff(new \DateTimeImmutable())->days;
    }

    public function __toString(): string
    {
        return sprintf('Visa %s - %s', 
            $this->numero ?? 'N/A',
            $this->getStatutLabel()
        );
    }
} 