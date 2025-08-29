<?php

namespace App\Service;

use App\Entity\Package;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageService
{
    private array $defaultImages = [
        '/uploads/packages/omra1.webp',
        '/uploads/packages/omra2.webp',
        '/uploads/packages/omra3.webp',
        '/uploads/packages/omra4.webp',
        '/uploads/packages/omra5.webp',
        '/uploads/packages/kaaba.svg',
        '/uploads/packages/mosque.svg',
        '/uploads/packages/pilgrims.svg'
    ];

    public function __construct(
        private string $packagesDirectory,
        private SluggerInterface $slugger
    ) {}

    /**
     * Traite les images uploadées pour un package
     */
    public function processUploadedImages(array $uploadedImages, Package $package): array
    {
        $imageNames = $package->getImages() ?? [];
        
        foreach ($uploadedImages as $image) {
            if ($image instanceof UploadedFile) {
                $newFilename = $this->generateUniqueFilename($image);
                
                try {
                    $image->move($this->packagesDirectory, $newFilename);
                    $imageNames[] = '/uploads/packages/' . $newFilename;
                } catch (\Exception $e) {
                    throw new \Exception('Erreur lors du téléchargement de l\'image : ' . $image->getClientOriginalName());
                }
            }
        }
        
        return $imageNames;
    }

    /**
     * Génère un nom de fichier unique
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        return $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
    }

    /**
     * Obtient les images par défaut pour un package
     */
    public function getDefaultImagesForPackage(Package $package, int $count = 3): array
    {
        $packageId = $package->getId() ?? 0;
        $selectedImages = [];
        
        for ($i = 0; $i < $count; $i++) {
            $imageIndex = ($packageId + $i) % count($this->defaultImages);
            $selectedImages[] = $this->defaultImages[$imageIndex];
        }
        
        return $selectedImages;
    }

    /**
     * Vérifie si un package a des images valides
     */
    public function hasValidImages(Package $package): bool
    {
        $images = $package->getImages();
        return !empty($images) && is_array($images);
    }

    /**
     * Obtient la première image d'un package ou une image par défaut
     */
    public function getFirstImage(Package $package): string
    {
        if ($this->hasValidImages($package)) {
            $images = $package->getImages();
            return $images[0];
        }
        
        // Retourner une image par défaut
        return $this->defaultImages[0];
    }

    /**
     * Obtient toutes les images d'un package avec fallback
     */
    public function getAllImages(Package $package): array
    {
        if ($this->hasValidImages($package)) {
            return $package->getImages();
        }
        
        return $this->getDefaultImagesForPackage($package);
    }

    /**
     * Supprime une image spécifique d'un package
     */
    public function removeImage(Package $package, string $imagePath): bool
    {
        $images = $package->getImages() ?? [];
        $key = array_search($imagePath, $images);
        
        if ($key !== false) {
            unset($images[$key]);
            $package->setImages(array_values($images)); // Réindexer le tableau
            return true;
        }
        
        return false;
    }

    /**
     * Vérifie si un fichier image existe
     */
    public function imageExists(string $imagePath): bool
    {
        $fullPath = $this->packagesDirectory . '/' . basename($imagePath);
        return file_exists($fullPath);
    }
}
