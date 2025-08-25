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
    name: 'app:add-package-images',
    description: 'Ajoute des images aux packages existants',
)]
class AddPackageImagesCommand extends Command
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
        
        if (empty($packages)) {
            $io->warning('Aucun package trouvé dans la base de données.');
            return Command::FAILURE;
        }

        $io->title('Ajout d\'images aux packages existants');

        $images = [
            '/uploads/packages/kaaba.svg',
            '/uploads/packages/mosque.svg',
            '/uploads/packages/pilgrims.svg'
        ];

        foreach ($packages as $index => $package) {
            // Assigner des images de manière cyclique
            $packageImages = [$images[$index % count($images)]];
            
            $package->setImages($packageImages);
            
            $io->text(sprintf(
                'Package "%s" (ID: %d) -> Images: %s',
                $package->getTitre(),
                $package->getId(),
                implode(', ', $packageImages)
            ));
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            'Images ajoutées avec succès à %d package(s)',
            count($packages)
        ));

        return Command::SUCCESS;
    }
} 