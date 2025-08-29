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
    name: 'app:fix-package-images',
    description: 'Corrige les noms d\'images des packages pour utiliser les noms standardisés',
)]
class FixPackageImagesCommand extends Command
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

        // Mapping des anciens noms vers les nouveaux noms standardisés
        $imageMapping = [
            'omra2-68adc81f8e686.webp' => 'omra2.webp',
            'omra3-68adc6f558ef6.webp' => 'omra3.webp',
            'omra4-68ab54b7cf99c.webp' => 'omra4.webp',
        ];

        foreach ($packages as $package) {
            $images = $package->getImages();
            $updated = false;
            
            if (is_array($images)) {
                $newImages = [];
                foreach ($images as $image) {
                    $filename = basename($image);
                    if (isset($imageMapping[$filename])) {
                        $newImage = '/uploads/packages/' . $imageMapping[$filename];
                        $newImages[] = $newImage;
                        $updated = true;
                        $io->text(sprintf('Image corrigée: %s -> %s', $image, $newImage));
                    } else {
                        $newImages[] = $image;
                    }
                }
                
                if ($updated) {
                    $package->setImages($newImages);
                    $updatedCount++;
                }
            }
        }

        if ($updatedCount > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('%d packages ont été mis à jour avec des noms d\'images corrigés.', $updatedCount));
        } else {
            $io->info('Aucun package nécessitait une correction des noms d\'images.');
        }

        return Command::SUCCESS;
    }
}
