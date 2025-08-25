<?php

namespace App\Entity;

use App\Repository\PackageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\String\Slugger\SluggerInterface;

#[ORM\Entity(repositoryClass: PackageRepository::class)]
class Package
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire')]
    #[Assert\Length(min: 5, max: 255, minMessage: 'Le titre doit faire au moins 5 caractères')]
    private ?string $titre = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La description est obligatoire')]
    private ?string $description = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\NotBlank(message: 'La durée est obligatoire')]
    #[Assert\Positive(message: 'La durée doit être positive')]
    private ?int $dureeJours = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $inclus = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $nonInclus = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $images = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hotelMakkah = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hotelMadinah = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: Depart::class, mappedBy: 'package', orphanRemoval: true)]
    private Collection $departs;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $programme = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $prixBase = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $devise = 'XOF';

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $documentsRequis = null;

    public function __construct()
    {
        $this->departs = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->images = [];
        $this->documentsRequis = [
            'passeport' => [
                'nom' => 'Passeport',
                'description' => 'Passeport valide avec une validité d\'au moins 6 mois après le retour',
                'obligatoire' => true
            ],
            'photo' => [
                'nom' => 'Photo d\'identité',
                'description' => 'Photo d\'identité récente (format passeport)',
                'obligatoire' => true
            ],
            'certificat_vaccination' => [
                'nom' => 'Certificat de vaccination',
                'description' => 'Certificat de vaccination COVID-19 (si requis)',
                'obligatoire' => false
            ],
            'attestation_bancaire' => [
                'nom' => 'Attestation bancaire',
                'description' => 'Attestation bancaire pour le visa',
                'obligatoire' => true
            ],
            'justificatif_emploi' => [
                'nom' => 'Justificatif d\'emploi',
                'description' => 'Attestation de travail ou justificatif de ressources',
                'obligatoire' => true
            ]
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function computeSlug(SluggerInterface $slugger): static
    {
        if (!$this->slug || $this->slug === '') {
            $this->slug = (string) $slugger->slug((string) $this->titre)->lower();
        }
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getDureeJours(): ?int
    {
        return $this->dureeJours;
    }

    public function setDureeJours(int $dureeJours): static
    {
        $this->dureeJours = $dureeJours;
        return $this;
    }

    public function getInclus(): ?string
    {
        return $this->inclus;
    }

    public function setInclus(?string $inclus): static
    {
        $this->inclus = $inclus;
        return $this;
    }

    public function getNonInclus(): ?string
    {
        return $this->nonInclus;
    }

    public function setNonInclus(?string $nonInclus): static
    {
        $this->nonInclus = $nonInclus;
        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function setImages(?array $images): static
    {
        $this->images = $images;
        return $this;
    }

    public function getHotelMakkah(): ?string
    {
        return $this->hotelMakkah;
    }

    public function setHotelMakkah(?string $hotelMakkah): static
    {
        $this->hotelMakkah = $hotelMakkah;
        return $this;
    }

    public function getHotelMadinah(): ?string
    {
        return $this->hotelMadinah;
    }

    public function setHotelMadinah(?string $hotelMadinah): static
    {
        $this->hotelMadinah = $hotelMadinah;
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
     * @return Collection<int, Depart>
     */
    public function getDeparts(): Collection
    {
        return $this->departs;
    }

    public function addDepart(Depart $depart): static
    {
        if (!$this->departs->contains($depart)) {
            $this->departs->add($depart);
            $depart->setPackage($this);
        }

        return $this;
    }

    public function removeDepart(Depart $depart): static
    {
        if ($this->departs->removeElement($depart)) {
            if ($depart->getPackage() === $this) {
                $depart->setPackage(null);
            }
        }

        return $this;
    }

    public function getProgramme(): ?string
    {
        return $this->programme;
    }

    public function setProgramme(?string $programme): static
    {
        $this->programme = $programme;
        return $this;
    }

    public function getPrixBase(): ?string
    {
        return $this->prixBase;
    }

    public function setPrixBase(?string $prixBase): static
    {
        $this->prixBase = $prixBase;
        return $this;
    }

    public function getDevise(): ?string
    {
        return $this->devise;
    }

    public function setDevise(?string $devise): static
    {
        $this->devise = $devise;
        return $this;
    }

    public function getDocumentsRequis(): ?array
    {
        return $this->documentsRequis;
    }

    public function setDocumentsRequis(?array $documentsRequis): static
    {
        $this->documentsRequis = $documentsRequis;
        return $this;
    }

    public function getAvailableDeparts(): Collection
    {
        return $this->departs->filter(function(Depart $depart) {
            return $depart->isActive() && $depart->hasAvailableSpots();
        });
    }

    public function __toString(): string
    {
        return $this->titre ?? '';
    }
} 