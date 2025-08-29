<?php

namespace App\Command;

use App\Entity\Package;
use App\Repository\PackageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-default-images-to-packages',
    description: 'Ajoute des images par défaut aux packages qui n\'en ont pas',
)]
class AddDefaultImagesToPackagesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PackageRepository $packageRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $packages = $this->packageRepository->findAll();
        $updatedCount = 0;

        // Images par défaut disponibles
        $defaultImages = [
            '/uploads/packages/omra1.webp',
            '/uploads/packages/omra2.webp', 
            '/uploads/packages/omra3.webp',
            '/uploads/packages/omra4.webp',
            '/uploads/packages/omra5.webp',
            '/uploads/packages/kaaba.svg',
            '/uploads/packages/mosque.svg',
            '/uploads/packages/pilgrims.svg'
        ];

        foreach ($packages as $index => $package) {
            $images = $package->getImages();
            
            // Si le package n'a pas d'images ou a un tableau vide
            if (empty($images)) {
                // Sélectionner 2-3 images de manière cyclique
                $selectedImages = [];
                $numImages = rand(2, 3); // Entre 2 et 3 images
                
                for ($i = 0; $i < $numImages; $i++) {
                    $imageIndex = ($index + $i) % count($defaultImages);
                    $selectedImages[] = $defaultImages[$imageIndex];
                }
                
                $package->setImages($selectedImages);
                $updatedCount++;
                
                $io->text(sprintf(
                    'Images ajoutées au package "%s": %s', 
                    $package->getTitre(), 
                    implode(', ', $selectedImages)
                ));
            }
        }

        if ($updatedCount > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('%d packages ont été mis à jour avec des images par défaut.', $updatedCount));
        } else {
            $io->info('Tous les packages ont déjà des images.');
        }

        return Command::SUCCESS;
    }
}
