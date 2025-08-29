<?php

namespace App\Controller\Admin;

use App\Entity\Package;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/packages/{id}/images')]
#[IsGranted('ROLE_ADMIN')]
class PackageImageController extends AbstractController
{
    public function __construct(
        private ImageService $imageService,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/remove', name: 'app_admin_package_remove_image', methods: ['POST'])]
    public function removeImage(Request $request, Package $package): JsonResponse
    {
        $imagePath = $request->request->get('image_path');
        
        if (!$imagePath) {
            return $this->json([
                'success' => false,
                'message' => 'Chemin de l\'image manquant'
            ], 400);
        }

        try {
            $removed = $this->imageService->removeImage($package, $imagePath);
            
            if ($removed) {
                $this->entityManager->flush();
                
                return $this->json([
                    'success' => true,
                    'message' => 'Image supprimée avec succès',
                    'remaining_images' => $package->getImages()
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'Image non trouvée'
                ], 404);
            }
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/reorder', name: 'app_admin_package_reorder_images', methods: ['POST'])]
    public function reorderImages(Request $request, Package $package): JsonResponse
    {
        $imageOrder = $request->request->get('image_order');
        
        if (!is_array($imageOrder)) {
            return $this->json([
                'success' => false,
                'message' => 'Ordre des images invalide'
            ], 400);
        }

        try {
            $currentImages = $package->getImages() ?? [];
            $reorderedImages = [];
            
            foreach ($imageOrder as $index) {
                if (isset($currentImages[$index])) {
                    $reorderedImages[] = $currentImages[$index];
                }
            }
            
            $package->setImages($reorderedImages);
            $this->entityManager->flush();
            
            return $this->json([
                'success' => true,
                'message' => 'Ordre des images mis à jour',
                'images' => $reorderedImages
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors du réordonnancement: ' . $e->getMessage()
            ], 500);
        }
    }
}
