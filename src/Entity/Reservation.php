<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    public const STATUT_BROUILLON = 'brouillon';
    public const STATUT_EN_ATTENTE_APPROBATION = 'en_attente_approbation';
    public const STATUT_EN_ATTENTE_DOCUMENTS = 'en_attente_documents';
    public const STATUT_EN_ATTENTE_PAIEMENT = 'en_attente_paiement';
    public const STATUT_ACOMPTE_PAYE = 'acompte_paye';
    public const STATUT_CONFIRME = 'confirme';
    public const STATUT_COMPLET = 'complet';
    public const STATUT_ARCHIVE = 'archive';
    public const STATUT_ANNULE = 'annule';

    public const STATUTS = [
        self::STATUT_BROUILLON => 'Brouillon',
        self::STATUT_EN_ATTENTE_APPROBATION => 'En attente d\'approbation',
        self::STATUT_EN_ATTENTE_DOCUMENTS => 'En attente des documents',
        self::STATUT_EN_ATTENTE_PAIEMENT => 'En attente de paiement',
        self::STATUT_ACOMPTE_PAYE => 'Acompte payé',
        self::STATUT_CONFIRME => 'Confirmé',
        self::STATUT_COMPLET => 'Complet',
        self::STATUT_ARCHIVE => 'Archivé',
        self::STATUT_ANNULE => 'Annulé'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Depart::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Depart $depart = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le type de chambre est obligatoire')]
    private ?string $typeChambre = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\NotBlank(message: 'Le nombre de pèlerins est obligatoire')]
    #[Assert\Positive(message: 'Le nombre de pèlerins doit être positif')]
    #[Assert\Range(min: 1, max: 4, notInRangeMessage: 'Le nombre de pèlerins doit être entre 1 et 4')]
    private ?int $nbPelerins = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $total = null;

    #[ORM\Column(length: 30)]
    private string $statut = self::STATUT_BROUILLON;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $codeDossier = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: Pelerin::class, mappedBy: 'reservation', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $pelerins;

    #[ORM\OneToMany(targetEntity: Paiement::class, mappedBy: 'reservation', orphanRemoval: true)]
    private Collection $paiements;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $acompte = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $reste = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $remarques = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateLimiteDocument = null;

    public function __construct()
    {
        $this->pelerins = new ArrayCollection();
        $this->paiements = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->generateCodeDossier();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDepart(): ?Depart
    {
        return $this->depart;
    }

    public function setDepart(?Depart $depart): static
    {
        $this->depart = $depart;
        return $this;
    }

    public function getTypeChambre(): ?string
    {
        return $this->typeChambre;
    }

    public function setTypeChambre(string $typeChambre): static
    {
        $this->typeChambre = $typeChambre;
        return $this;
    }

    public function getNbPelerins(): ?int
    {
        return $this->nbPelerins;
    }

    public function setNbPelerins(int $nbPelerins): static
    {
        $this->nbPelerins = $nbPelerins;
        return $this;
    }

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(string $total): static
    {
        $this->total = $total;
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

    public function getCodeDossier(): ?string
    {
        return $this->codeDossier;
    }

    public function setCodeDossier(string $codeDossier): static
    {
        $this->codeDossier = $codeDossier;
        return $this;
    }

    private function generateCodeDossier(): void
    {
        $year = date('Y');
        $random = str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $this->codeDossier = sprintf('OM-%s-%s', $year, $random);
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

    /**
     * @return Collection<int, Pelerin>
     */
    public function getPelerins(): Collection
    {
        return $this->pelerins;
    }

    public function addPelerin(Pelerin $pelerin): static
    {
        if (!$this->pelerins->contains($pelerin)) {
            $this->pelerins->add($pelerin);
            $pelerin->setReservation($this);
        }

        return $this;
    }

    public function removePelerin(Pelerin $pelerin): static
    {
        if ($this->pelerins->removeElement($pelerin)) {
            if ($pelerin->getReservation() === $this) {
                $pelerin->setReservation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Paiement>
     */
    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function addPaiement(Paiement $paiement): static
    {
        if (!$this->paiements->contains($paiement)) {
            $this->paiements->add($paiement);
            $paiement->setReservation($this);
        }

        return $this;
    }

    public function removePaiement(Paiement $paiement): static
    {
        if ($this->paiements->removeElement($paiement)) {
            if ($paiement->getReservation() === $this) {
                $paiement->setReservation(null);
            }
        }

        return $this;
    }

    public function getAcompte(): ?string
    {
        return $this->acompte;
    }

    public function setAcompte(?string $acompte): static
    {
        $this->acompte = $acompte;
        return $this;
    }

    public function getReste(): ?string
    {
        return $this->reste;
    }

    public function setReste(?string $reste): static
    {
        $this->reste = $reste;
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

    public function getDateLimiteDocument(): ?\DateTimeImmutable
    {
        return $this->dateLimiteDocument;
    }

    public function setDateLimiteDocument(?\DateTimeImmutable $dateLimiteDocument): static
    {
        $this->dateLimiteDocument = $dateLimiteDocument;
        return $this;
    }

    public function getTotalPaye(): float
    {
        $total = 0;
        foreach ($this->paiements as $paiement) {
            if ($paiement->getStatut() === Paiement::STATUT_VALIDE) {
                $total += (float) $paiement->getMontant();
            }
        }
        return $total;
    }

    public function getMontantRestant(): float
    {
        return (float) $this->total - $this->getTotalPaye();
    }

    public function isFullyPaid(): bool
    {
        return $this->getMontantRestant() <= 0;
    }

    public function calculateAcompte(float $percentage = 30): void
    {
        $acompte = ((float) $this->total * $percentage) / 100;
        $this->acompte = (string) $acompte;
        $this->reste = (string) ((float) $this->total - $acompte);
    }

    public function getAllDocumentsUploaded(): bool
    {
        foreach ($this->pelerins as $pelerin) {
            if (!$pelerin->hasAllRequiredDocuments()) {
                return false;
            }
        }
        return true;
    }

    public function canBeCancelled(): bool
    {
        $limitDate = $this->depart?->getDateDepart()?->modify('-30 days');
        return $limitDate && new \DateTimeImmutable() < $limitDate;
    }

    public function __toString(): string
    {
        return $this->codeDossier ?? 'Réservation';
    }
} 