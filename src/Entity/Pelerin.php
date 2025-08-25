<?php

namespace App\Entity;

use App\Repository\PelerinRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PelerinRepository::class)]
class Pelerin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Reservation::class, inversedBy: 'pelerins')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reservation $reservation = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire')]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $prenom = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank(message: 'La date de naissance est obligatoire')]
    private ?\DateTimeImmutable $dateNaissance = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La nationalité est obligatoire')]
    private ?string $nationalite = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $passportNumber = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $passportExpiry = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Choice(choices: ['M', 'F'], message: 'Le sexe doit être M ou F')]
    private ?string $sexe = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lieuNaissance = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'pelerin', orphanRemoval: true)]
    private Collection $documents;

    #[ORM\OneToOne(targetEntity: Visa::class, mappedBy: 'pelerin', cascade: ['persist', 'remove'])]
    private ?Visa $visa = null;

    #[ORM\OneToOne(targetEntity: Billet::class, mappedBy: 'pelerin', cascade: ['persist', 'remove'])]
    private ?Billet $billet = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nomUrgence = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phoneUrgence = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $relationUrgence = null;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getDateNaissance(): ?\DateTimeImmutable
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(\DateTimeImmutable $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;
        return $this;
    }

    public function getNationalite(): ?string
    {
        return $this->nationalite;
    }

    public function setNationalite(string $nationalite): static
    {
        $this->nationalite = $nationalite;
        return $this;
    }

    public function getPassportNumber(): ?string
    {
        return $this->passportNumber;
    }

    public function setPassportNumber(?string $passportNumber): static
    {
        $this->passportNumber = $passportNumber;
        return $this;
    }

    public function getPassportExpiry(): ?\DateTimeImmutable
    {
        return $this->passportExpiry;
    }

    public function setPassportExpiry(?\DateTimeImmutable $passportExpiry): static
    {
        $this->passportExpiry = $passportExpiry;
        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(?string $sexe): static
    {
        $this->sexe = $sexe;
        return $this;
    }

    public function getLieuNaissance(): ?string
    {
        return $this->lieuNaissance;
    }

    public function setLieuNaissance(?string $lieuNaissance): static
    {
        $this->lieuNaissance = $lieuNaissance;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;
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
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setPelerin($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): static
    {
        if ($this->documents->removeElement($document)) {
            if ($document->getPelerin() === $this) {
                $document->setPelerin(null);
            }
        }

        return $this;
    }

    public function getVisa(): ?Visa
    {
        return $this->visa;
    }

    public function setVisa(?Visa $visa): static
    {
        $this->visa = $visa;
        return $this;
    }

    public function getBillet(): ?Billet
    {
        return $this->billet;
    }

    public function setBillet(?Billet $billet): static
    {
        $this->billet = $billet;
        return $this;
    }

    public function getNomUrgence(): ?string
    {
        return $this->nomUrgence;
    }

    public function setNomUrgence(?string $nomUrgence): static
    {
        $this->nomUrgence = $nomUrgence;
        return $this;
    }

    public function getPhoneUrgence(): ?string
    {
        return $this->phoneUrgence;
    }

    public function setPhoneUrgence(?string $phoneUrgence): static
    {
        $this->phoneUrgence = $phoneUrgence;
        return $this;
    }

    public function getRelationUrgence(): ?string
    {
        return $this->relationUrgence;
    }

    public function setRelationUrgence(?string $relationUrgence): static
    {
        $this->relationUrgence = $relationUrgence;
        return $this;
    }

    public function getFullName(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getAge(): int
    {
        if (!$this->dateNaissance) {
            return 0;
        }
        return $this->dateNaissance->diff(new \DateTimeImmutable())->y;
    }

    public function hasAllRequiredDocuments(): bool
    {
        $requiredTypes = [Document::TYPE_PASSEPORT, Document::TYPE_PHOTO, Document::TYPE_VACCINATION];
        $uploadedTypes = [];
        
        foreach ($this->documents as $document) {
            if ($document->getStatut() === Document::STATUT_VALIDE) {
                $uploadedTypes[] = $document->getType();
            }
        }

        foreach ($requiredTypes as $type) {
            if (!in_array($type, $uploadedTypes)) {
                return false;
            }
        }

        return true;
    }

    public function getDocumentByType(string $type): ?Document
    {
        foreach ($this->documents as $document) {
            if ($document->getType() === $type) {
                return $document;
            }
        }
        return null;
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }
} 