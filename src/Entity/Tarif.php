<?php

namespace App\Entity;

use App\Repository\TarifRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TarifRepository::class)]
class Tarif
{
    public const TYPE_CHAMBRE_QUAD = 'quad';
    public const TYPE_CHAMBRE_TRIPLE = 'triple';
    public const TYPE_CHAMBRE_DOUBLE = 'double';
    public const TYPE_CHAMBRE_SINGLE = 'single';

    public const TYPES_CHAMBRE = [
        self::TYPE_CHAMBRE_QUAD => 'Quadruple',
        self::TYPE_CHAMBRE_TRIPLE => 'Triple',
        self::TYPE_CHAMBRE_DOUBLE => 'Double',
        self::TYPE_CHAMBRE_SINGLE => 'Simple'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Depart::class, inversedBy: 'tarifs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Depart $depart = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le type de chambre est obligatoire')]
    #[Assert\Choice(choices: [self::TYPE_CHAMBRE_QUAD, self::TYPE_CHAMBRE_TRIPLE, self::TYPE_CHAMBRE_DOUBLE, self::TYPE_CHAMBRE_SINGLE])]
    private ?string $typeChambre = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le prix est obligatoire')]
    #[Assert\Positive(message: 'Le prix doit être positif')]
    private ?string $prixBase = null;

    #[ORM\Column(length: 3)]
    private string $devise = 'XOF';

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $reduction = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTypeChambreLabel(): string
    {
        return self::TYPES_CHAMBRE[$this->typeChambre] ?? $this->typeChambre;
    }

    public function getPrixBase(): ?string
    {
        return $this->prixBase;
    }

    public function setPrixBase(string $prixBase): static
    {
        $this->prixBase = $prixBase;
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

    public function getReduction(): ?string
    {
        return $this->reduction;
    }

    public function setReduction(?string $reduction): static
    {
        $this->reduction = $reduction;
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

    public function getPrixFinal(): float
    {
        $prix = (float) $this->prixBase;
        if ($this->reduction && $this->reduction > 0) {
            $prix = $prix * (1 - ((float) $this->reduction / 100));
        }
        return $prix;
    }

    public function getFormattedPrice(): string
    {
        return number_format($this->getPrixFinal(), 0, ',', ' ') . ' ' . $this->devise;
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', 
            $this->getTypeChambreLabel(),
            $this->getFormattedPrice()
        );
    }
} 