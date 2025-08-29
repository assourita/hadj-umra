<?php

namespace App\Twig;

use App\Entity\Package;
use App\Service\ImageService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageExtension extends AbstractExtension
{
    public function __construct(
        private ImageService $imageService
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('package_first_image', [$this, 'getPackageFirstImage']),
            new TwigFunction('package_all_images', [$this, 'getPackageAllImages']),
            new TwigFunction('package_has_images', [$this, 'packageHasImages']),
            new TwigFunction('package_image_count', [$this, 'getPackageImageCount']),
        ];
    }

    /**
     * Obtient la première image d'un package
     */
    public function getPackageFirstImage(Package $package): string
    {
        return $this->imageService->getFirstImage($package);
    }

    /**
     * Obtient toutes les images d'un package
     */
    public function getPackageAllImages(Package $package): array
    {
        return $this->imageService->getAllImages($package);
    }

    /**
     * Vérifie si un package a des images
     */
    public function packageHasImages(Package $package): bool
    {
        return $this->imageService->hasValidImages($package);
    }

    /**
     * Obtient le nombre d'images d'un package
     */
    public function getPackageImageCount(Package $package): int
    {
        $images = $this->imageService->getAllImages($package);
        return count($images);
    }
}
