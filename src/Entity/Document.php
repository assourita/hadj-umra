<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[Vich\Uploadable]
class Document
{
    public const TYPE_PASSEPORT = 'passeport';
    public const TYPE_PHOTO = 'photo';
    public const TYPE_VACCINATION = 'vaccination';
    public const TYPE_VISA = 'visa';
    public const TYPE_AUTRE = 'autre';

    public const TYPES = [
        self::TYPE_PASSEPORT => 'Passeport',
        self::TYPE_PHOTO => 'Photo d\'identité',
        self::TYPE_VACCINATION => 'Carnet de vaccination',
        self::TYPE_VISA => 'Visa',
        self::TYPE_AUTRE => 'Autre document'
    ];

    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_VALIDE = 'valide';
    public const STATUT_REFUSE = 'refuse';

    public const STATUTS = [
        self::STATUT_EN_ATTENTE => 'En attente de validation',
        self::STATUT_VALIDE => 'Validé',
        self::STATUT_REFUSE => 'Refusé'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Pelerin::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pelerin $pelerin = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le type de document est obligatoire')]
    #[Assert\Choice(choices: [self::TYPE_PASSEPORT, self::TYPE_PHOTO, self::TYPE_VACCINATION, self::TYPE_VISA, self::TYPE_AUTRE])]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fileName = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $fileSize = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $mimeType = null;

    #[ORM\Column(length: 30)]
    private string $statut = self::STATUT_EN_ATTENTE;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaireRefus = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $validePar = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateValidation = null;

    #[Vich\UploadableField(mapping: 'documents', fileNameProperty: 'fileName', size: 'fileSize', mimeType: 'mimeType')]
    private ?File $documentFile = null;

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

    public function setPelerin(?Pelerin $pelerin): static
    {
        $this->pelerin = $pelerin;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): static
    {
        $this->fileName = $fileName;
        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(?int $fileSize): static
    {
        $this->fileSize = $fileSize;
        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): static
    {
        $this->mimeType = $mimeType;
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

    public function getCommentaireRefus(): ?string
    {
        return $this->commentaireRefus;
    }

    public function setCommentaireRefus(?string $commentaireRefus): static
    {
        $this->commentaireRefus = $commentaireRefus;
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

    public function getDocumentFile(): ?File
    {
        return $this->documentFile;
    }

    public function setDocumentFile(?File $documentFile = null): void
    {
        $this->documentFile = $documentFile;

        if (null !== $documentFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getFormattedFileSize(): string
    {
        if (!$this->fileSize) {
            return 'N/A';
        }

        $bytes = $this->fileSize;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isImage(): bool
    {
        return $this->mimeType && str_starts_with($this->mimeType, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mimeType === 'application/pdf';
    }

    public function valider(User $user): void
    {
        $this->statut = self::STATUT_VALIDE;
        $this->validePar = $user;
        $this->dateValidation = new \DateTimeImmutable();
        $this->commentaireRefus = null;
    }

    public function refuser(User $user, string $commentaire): void
    {
        $this->statut = self::STATUT_REFUSE;
        $this->validePar = $user;
        $this->dateValidation = new \DateTimeImmutable();
        $this->commentaireRefus = $commentaire;
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', 
            $this->getTypeLabel(),
            $this->pelerin?->getFullName() ?? 'Pèlerin'
        );
    }
} 