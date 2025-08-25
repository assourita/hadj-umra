<?php

namespace App\Entity;

use App\Repository\PaiementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PaiementRepository::class)]
class Paiement
{
    public const MODE_CARTE = 'carte';
    public const MODE_VIREMENT = 'virement';
    public const MODE_ESPECES = 'especes';
    public const MODE_MOBILE_MONEY = 'mobile_money';

    public const MODES = [
        self::MODE_CARTE => 'Carte bancaire',
        self::MODE_VIREMENT => 'Virement bancaire',
        self::MODE_ESPECES => 'Espèces',
        self::MODE_MOBILE_MONEY => 'Mobile Money'
    ];

    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_VALIDE = 'valide';
    public const STATUT_REFUSE = 'refuse';
    public const STATUT_REMBOURSE = 'rembourse';

    public const STATUTS = [
        self::STATUT_EN_ATTENTE => 'En attente',
        self::STATUT_VALIDE => 'Validé',
        self::STATUT_REFUSE => 'Refusé',
        self::STATUT_REMBOURSE => 'Remboursé'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Reservation::class, inversedBy: 'paiements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reservation $reservation = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le mode de paiement est obligatoire')]
    #[Assert\Choice(choices: [self::MODE_CARTE, self::MODE_VIREMENT, self::MODE_ESPECES, self::MODE_MOBILE_MONEY])]
    private ?string $mode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le montant est obligatoire')]
    #[Assert\Positive(message: 'Le montant doit être positif')]
    private ?string $montant = null;

    #[ORM\Column(length: 30)]
    private string $statut = self::STATUT_EN_ATTENTE;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 3)]
    private string $devise = 'XOF';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripePaymentIntentId = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $validePar = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateValidation = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->metadata = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): static
    {
        $this->reservation = $reservation;
        return $this;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(string $mode): static
    {
        $this->mode = $mode;
        return $this;
    }

    public function getModeLabel(): string
    {
        return self::MODES[$this->mode] ?? $this->mode;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;
        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
    {
        $this->montant = $montant;
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

    public function getDevise(): string
    {
        return $this->devise;
    }

    public function setDevise(string $devise): static
    {
        $this->devise = $devise;
        return $this;
    }

    public function getStripePaymentIntentId(): ?string
    {
        return $this->stripePaymentIntentId;
    }

    public function setStripePaymentIntentId(?string $stripePaymentIntentId): static
    {
        $this->stripePaymentIntentId = $stripePaymentIntentId;
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): static
    {
        $this->metadata = $metadata;
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

    public function getValidePar(): ?User
    {
        return $this->validePar;
    }

    public function setValidePar(?User $validePar): static
    {
        $this->validePar = $validePar;
        return $this;
    }

    public function getDateValidation(): ?\DateTimeImmutable
    {
        return $this->dateValidation;
    }

    public function setDateValidation(?\DateTimeImmutable $dateValidation): static
    {
        $this->dateValidation = $dateValidation;
        return $this;
    }

    public function getFormattedAmount(): string
    {
        return number_format((float) $this->montant, 0, ',', ' ') . ' ' . $this->devise;
    }

    public function valider(User $user): void
    {
        $this->statut = self::STATUT_VALIDE;
        $this->validePar = $user;
        $this->dateValidation = new \DateTimeImmutable();
    }

    public function refuser(User $user, string $commentaire = null): void
    {
        $this->statut = self::STATUT_REFUSE;
        $this->validePar = $user;
        $this->dateValidation = new \DateTimeImmutable();
        if ($commentaire) {
            $this->commentaire = $commentaire;
        }
    }

    public function rembourser(User $user, string $commentaire = null): void
    {
        $this->statut = self::STATUT_REMBOURSE;
        $this->validePar = $user;
        $this->dateValidation = new \DateTimeImmutable();
        if ($commentaire) {
            $this->commentaire = $commentaire;
        }
    }

    public function isValidated(): bool
    {
        return $this->statut === self::STATUT_VALIDE;
    }

    public function __toString(): string
    {
        return sprintf('%s - %s (%s)', 
            $this->getModeLabel(),
            $this->getFormattedAmount(),
            $this->getStatutLabel()
        );
    }
} 