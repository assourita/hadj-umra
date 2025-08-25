<?php

namespace App\Entity;

use App\Repository\BilletRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BilletRepository::class)]
class Billet
{
    public const STATUT_NON_EMIS = 'non_emis';
    public const STATUT_EMIS = 'emis';
    public const STATUT_CONFIRME = 'confirme';
    public const STATUT_UTILISE = 'utilise';
    public const STATUT_ANNULE = 'annule';

    public const STATUTS = [
        self::STATUT_NON_EMIS => 'Non émis',
        self::STATUT_EMIS => 'Émis',
        self::STATUT_CONFIRME => 'Confirmé',
        self::STATUT_UTILISE => 'Utilisé',
        self::STATUT_ANNULE => 'Annulé'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Pelerin::class, inversedBy: 'billet')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pelerin $pelerin = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $pnr = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $compagnie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $volAller = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $volRetour = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateVolAller = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateVolRetour = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $siegeAller = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $siegeRetour = null;

    #[ORM\Column(length: 30)]
    private string $statut = self::STATUT_NON_EMIS;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $remarques = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $emisPar = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateEmission = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $classeVol = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $escales = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->escales = [];
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

    public function getPnr(): ?string
    {
        return $this->pnr;
    }

    public function setPnr(?string $pnr): static
    {
        $this->pnr = $pnr;
        return $this;
    }

    public function getCompagnie(): ?string
    {
        return $this->compagnie;
    }

    public function setCompagnie(?string $compagnie): static
    {
        $this->compagnie = $compagnie;
        return $this;
    }

    public function getVolAller(): ?string
    {
        return $this->volAller;
    }

    public function setVolAller(?string $volAller): static
    {
        $this->volAller = $volAller;
        return $this;
    }

    public function getVolRetour(): ?string
    {
        return $this->volRetour;
    }

    public function setVolRetour(?string $volRetour): static
    {
        $this->volRetour = $volRetour;
        return $this;
    }

    public function getDateVolAller(): ?\DateTimeImmutable
    {
        return $this->dateVolAller;
    }

    public function setDateVolAller(?\DateTimeImmutable $dateVolAller): static
    {
        $this->dateVolAller = $dateVolAller;
        return $this;
    }

    public function getDateVolRetour(): ?\DateTimeImmutable
    {
        return $this->dateVolRetour;
    }

    public function setDateVolRetour(?\DateTimeImmutable $dateVolRetour): static
    {
        $this->dateVolRetour = $dateVolRetour;
        return $this;
    }

    public function getSiegeAller(): ?string
    {
        return $this->siegeAller;
    }

    public function setSiegeAller(?string $siegeAller): static
    {
        $this->siegeAller = $siegeAller;
        return $this;
    }

    public function getSiegeRetour(): ?string
    {
        return $this->siegeRetour;
    }

    public function setSiegeRetour(?string $siegeRetour): static
    {
        $this->siegeRetour = $siegeRetour;
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

    public function getRemarques(): ?string
    {
        return $this->remarques;
    }

    public function setRemarques(?string $remarques): static
    {
        $this->remarques = $remarques;
        return $this;
    }

    public function getEmisPar(): ?User
    {
        return $this->emisPar;
    }

    public function setEmisPar(?User $emisPar): static
    {
        $this->emisPar = $emisPar;
        return $this;
    }

    public function getDateEmission(): ?\DateTimeImmutable
    {
        return $this->dateEmission;
    }

    public function setDateEmission(?\DateTimeImmutable $dateEmission): static
    {
        $this->dateEmission = $dateEmission;
        return $this;
    }

    public function getClasseVol(): ?string
    {
        return $this->classeVol;
    }

    public function setClasseVol(?string $classeVol): static
    {
        $this->classeVol = $classeVol;
        return $this;
    }

    public function getEscales(): ?array
    {
        return $this->escales;
    }

    public function setEscales(?array $escales): static
    {
        $this->escales = $escales;
        return $this;
    }

    public function emettre(User $user, string $pnr, string $compagnie): void
    {
        $this->statut = self::STATUT_EMIS;
        $this->pnr = $pnr;
        $this->compagnie = $compagnie;
        $this->emisPar = $user;
        $this->dateEmission = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function confirmer(): void
    {
        $this->statut = self::STATUT_CONFIRME;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function utiliser(): void
    {
        $this->statut = self::STATUT_UTILISE;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function annuler(string $raison = null): void
    {
        $this->statut = self::STATUT_ANNULE;
        if ($raison) {
            $this->remarques = $raison;
        }
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isEmis(): bool
    {
        return in_array($this->statut, [self::STATUT_EMIS, self::STATUT_CONFIRME, self::STATUT_UTILISE]);
    }

    public function getItineraire(): string
    {
        $itineraire = [];
        if ($this->volAller && $this->dateVolAller) {
            $itineraire[] = sprintf('Aller: %s le %s', $this->volAller, $this->dateVolAller->format('d/m/Y H:i'));
        }
        if ($this->volRetour && $this->dateVolRetour) {
            $itineraire[] = sprintf('Retour: %s le %s', $this->volRetour, $this->dateVolRetour->format('d/m/Y H:i'));
        }
        return implode(' | ', $itineraire);
    }

    public function __toString(): string
    {
        return sprintf('Billet %s - %s', 
            $this->pnr ?? 'N/A',
            $this->pelerin?->getFullName() ?? 'Pèlerin'
        );
    }
} 