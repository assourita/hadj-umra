<?php

namespace App\Command;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestLoginCommand extends Command
{
    protected static $defaultName = 'app:test-login';
    protected static $defaultDescription = 'Tester la connexion admin';

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

        $io->title('Test de connexion admin');

        // Récupérer l'admin
        $admin = $this->entityManager->getRepository(Admin::class)->findOneBy(['email' => 'admin@demetravel.com']);
        
        if (!$admin) {
            $io->error('Aucun administrateur trouvé avec l\'email admin@demetravel.com');
            return Command::FAILURE;
        }

        $io->success([
            '✅ Administrateur trouvé :',
            '📧 Email: ' . $admin->getEmail(),
            '👤 Nom: ' . $admin->getFullName(),
            '🔑 Mot de passe hashé: ' . substr($admin->getPassword(), 0, 20) . '...',
            '📅 Créé le: ' . $admin->getCreatedAt()->format('d/m/Y H:i'),
            '✅ Actif: ' . ($admin->getIsActive() ? 'Oui' : 'Non')
        ]);

        // Tester le mot de passe
        $testPassword = 'admin123';
        $isValid = $this->passwordHasher->isPasswordValid($admin, $testPassword);
        
        if ($isValid) {
            $io->success('✅ Le mot de passe "admin123" est valide !');
        } else {
            $io->error('❌ Le mot de passe "admin123" n\'est pas valide !');
            
            // Recréer le mot de passe
            $io->note('Recréation du mot de passe...');
            $hashedPassword = $this->passwordHasher->hashPassword($admin, $testPassword);
            $admin->setPassword($hashedPassword);
            $this->entityManager->flush();
            $io->success('✅ Mot de passe recréé avec succès !');
        }

        return Command::SUCCESS;
    }
}
