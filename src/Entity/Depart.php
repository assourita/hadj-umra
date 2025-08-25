<?php

namespace App\Entity;

use App\Repository\DepartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DepartRepository::class)]
class Depart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Package::class, inversedBy: 'departs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Package $package = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La ville de départ est obligatoire')]
    private ?string $villeDepart = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Assert\NotBlank(message: 'La date de départ est obligatoire')]
    #[Assert\GreaterThan('today', message: 'La date de départ doit être dans le futur')]
    private ?\DateTimeImmutable $dateDepart = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $heureDepart = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Assert\NotBlank(message: 'La date de retour est obligatoire')]
    private ?\DateTimeImmutable $dateRetour = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\NotBlank(message: 'Le quota total est obligatoire')]
    #[Assert\Positive(message: 'Le quota doit être positif')]
    private ?int $quotaTotal = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $quotaVendu = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: Tarif::class, mappedBy: 'depart', orphanRemoval: true, cascade: ['persist'])]
    private Collection $tarifs;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'depart', orphanRemoval: true)]
    private Collection $reservations;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $compagnieAerienne = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $numeroVol = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $remarques = null;

    public function __construct()
    {
        $this->tarifs = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPackage(): ?Package
    {
        return $this->package;
    }

    public function setPackage(?Package $package): static
    {
        $this->package = $package;
        return $this;
    }

    public function getVilleDepart(): ?string
    {
        return $this->villeDepart;
    }

    public function setVilleDepart(string $villeDepart): static
    {
        $this->villeDepart = $villeDepart;
        return $this;
    }

    public function getDateDepart(): ?\DateTimeImmutable
    {
        return $this->dateDepart;
    }

    public function setDateDepart(\DateTimeImmutable $dateDepart): static
    {
        $this->dateDepart = $dateDepart;
        return $this;
    }

    public function getHeureDepart(): ?\DateTime
    {
        return $this->heureDepart;
    }

    public function setHeureDepart(?\DateTime $heureDepart): static
    {
        $this->heureDepart = $heureDepart;
        return $this;
    }

    public function getDateRetour(): ?\DateTimeImmutable
    {
        return $this->dateRetour;
    }

    public function setDateRetour(\DateTimeImmutable $dateRetour): static
    {
        $this->dateRetour = $dateRetour;
        return $this;
    }

    public function getQuotaTotal(): ?int
    {
        return $this->quotaTotal;
    }

    public function setQuotaTotal(int $quotaTotal): static
    {
        $this->quotaTotal = $quotaTotal;
        return $this;
    }

    public function getQuotaVendu(): int
    {
        return $this->quotaVendu;
    }

    public function setQuotaVendu(int $quotaVendu): static
    {
        $this->quotaVendu = $quotaVendu;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): static
    {
        $this->isActive = $isActive;
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

    /**
     * @return Collection<int, Tarif>
     */
    public function getTarifs(): Collection
    {
        return $this->tarifs;
    }

    public function addTarif(Tarif $tarif): static
    {
        if (!$this->tarifs->contains($tarif)) {
            $this->tarifs->add($tarif);
            $tarif->setDepart($this);
        }

        return $this;
    }

    public function removeTarif(Tarif $tarif): static
    {
        if ($this->tarifs->removeElement($tarif)) {
            if ($tarif->getDepart() === $this) {
                $tarif->setDepart(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setDepart($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getDepart() === $this) {
                $reservation->setDepart(null);
            }
        }

        return $this;
    }

    public function getCompagnieAerienne(): ?string
    {
        return $this->compagnieAerienne;
    }

    public function setCompagnieAerienne(?string $compagnieAerienne): static
    {
        $this->compagnieAerienne = $compagnieAerienne;
        return $this;
    }

    public function getNumeroVol(): ?string
    {
        return $this->numeroVol;
    }

    public function setNumeroVol(?string $numeroVol): static
    {
        $this->numeroVol = $numeroVol;
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

    public function getPlacesRestantes(): int
    {
        return $this->quotaTotal - $this->quotaVendu;
    }

    public function hasAvailableSpots(): bool
    {
        return $this->getPlacesRestantes() > 0;
    }

    public function canAccommodate(int $nbPelerins): bool
    {
        return $this->getPlacesRestantes() >= $nbPelerins;
    }

    public function getTarifForChambre(string $typeChambre): ?Tarif
    {
        foreach ($this->tarifs as $tarif) {
            if ($tarif->getTypeChambre() === $typeChambre) {
                return $tarif;
            }
        }
        return null;
    }

    public function __toString(): string
    {
        return sprintf('%s - %s (%s)', 
            $this->package?->getTitre() ?? 'Package', 
            $this->villeDepart ?? 'Ville',
            $this->dateDepart?->format('d/m/Y') ?? 'Date'
        );
    }
} 