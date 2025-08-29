<?php

namespace App\Command;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateAdminCommand extends Command
{
    protected static $defaultName = 'app:create-admin';
    protected static $defaultDescription = 'Créer un administrateur pour le dashboard';

    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Création d\'un administrateur pour DƐMƐ Travel');

        // Vérifier si un admin existe déjà
        $existingAdmin = $this->entityManager->getRepository(Admin::class)->findOneBy(['email' => 'admin@demetravel.com']);
        
        if ($existingAdmin) {
            $io->warning('Un administrateur existe déjà avec l\'email admin@demetravel.com');
            $io->note('Email: admin@demetravel.com');
            $io->note('Pour changer le mot de passe, utilisez la commande: php bin/console app:change-admin-password');
            return Command::SUCCESS;
        }

        // Créer l'administrateur
        $admin = new Admin();
        $admin->setEmail('admin@demetravel.com');
        $admin->setFirstName('Administrateur');
        $admin->setLastName('DƐMƐ Travel');
        $admin->setIsActive(true);

        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123');
        $admin->setPassword($hashedPassword);

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success([
            '✅ Administrateur créé avec succès !',
            '',
            '📧 Email: admin@demetravel.com',
            '🔑 Mot de passe: admin123',
            '',
            '⚠️  IMPORTANT: Changez ce mot de passe après votre première connexion !',
            '',
            '🌐 Accédez au dashboard: http://localhost:8000/admin/login'
        ]);

        return Command::SUCCESS;
    }
}
